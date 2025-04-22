<?php
include('config.php');
session_start();

// Check if student is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

// Get student details
$student_id = $_SESSION["id"];
$sql = "SELECT username, usertype FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows == 1) {
    $stmt->bind_result($username, $usertype);
    $stmt->fetch();
} else {
    header("location: index.php");
    exit;
}
$stmt->close();

// Get hostel_id from URL
$hostel_id = isset($_GET['hostel_id']) ? intval($_GET['hostel_id']) : 0;

// Fetch hostel information
$hostel_query = "SELECT * FROM hostels WHERE hostel_id = ?";
$stmt = $conn->prepare($hostel_query);
$stmt->bind_param("i", $hostel_id);
$stmt->execute();
$hostel_result = $stmt->get_result();
$hostel = $hostel_result->fetch_assoc();
$stmt->close();

if (!$hostel) {
    echo "Hostel not found.";
    exit;
}

// Handle adding feedback
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["rating"]) && !empty($_POST["message"])) {
    $rating = $_POST["rating"];
    $message = trim($_POST["message"]);

    // Insert the feedback
    $insert_feedback_query = "INSERT INTO feedbacks (user_id, hostel_id, rating, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_feedback_query);
    $stmt->bind_param("iiis", $student_id, $hostel_id, $rating, $message);
    $stmt->execute();
    $stmt->close();

    // Redirect to the same page to refresh the feedback list
    header("Location: feedbacks.php?hostel_id=" . $hostel_id);
    exit;
}

// Fetch existing feedback for the hostel
$fetch_feedback_query = "SELECT f.feedback_id, f.rating, f.message, f.created_at, u.username 
                         FROM feedbacks f 
                         JOIN users u ON f.user_id = u.id 
                         WHERE f.hostel_id = ? 
                         ORDER BY f.created_at DESC";
$stmt = $conn->prepare($fetch_feedback_query);
$stmt->bind_param("i", $hostel_id);
$stmt->execute();
$feedback_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedbacks - <?= htmlspecialchars($hostel['name']) ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">

    <style>
        /* Optional: Style for star ratings */
        .rating {
            color: #FFD700;
            font-size: 18px;
        }
        .feedback-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h2>Feedbacks for Hostel: <?= htmlspecialchars($hostel['name']) ?></h2>

            <!-- Feedback Form -->
            <h4>Add Your Feedback</h4>
            <form method="POST" class="mb-4">
                <div class="form-group">
                    <label for="rating">Rating (1 to 5)</label>
                    <select name="rating" id="rating" class="form-control" required>
                        <option value="">Select Rating</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Your Feedback</label>
                    <textarea name="message" id="message" rows="3" class="form-control" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Feedback</button>
            </form>
        </div>
    </div>

    <!-- Display Existing Feedbacks -->
    <h4>Existing Feedbacks</h4>

    <div class="row">
        <?php if ($feedback_result->num_rows > 0): ?>
            <?php 
            $count = 0;
            while ($feedback = $feedback_result->fetch_assoc()):
                if ($count % 3 == 0 && $count > 0) {
                    echo '</div><div class="row mt-3">';  // Start a new row after every 3 feedbacks
                }
            ?>
            <div class="col-md-4">
                <div class="card feedback-card">
                    <div class="card-body">
                        <strong><?= date("d M Y H:i", strtotime($feedback['created_at'])) ?>:</strong>
                        <br><span class="rating">
                            <?php for ($i = 0; $i < $feedback['rating']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </span>
                        <br><strong><?= htmlspecialchars($feedback['username']) ?>:</strong> <?= htmlspecialchars($feedback['message']) ?>
                    </div>
                </div>
            </div>
            <?php 
                $count++;
            endwhile;
            ?>
        <?php else: ?>
            <p class="text-muted">No feedbacks yet.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
