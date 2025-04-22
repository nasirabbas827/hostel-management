<?php
include('config.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

// Get user details
$user_id = $_SESSION["id"];
$sql = "SELECT username, usertype FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
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

// Get room details
$room_id = $_GET['room_id'] ?? 0;
if (!$room_id) {
    echo "Room ID is required.";
    exit;
}

// Fetch room details
$room_query = "SELECT * FROM rooms WHERE room_id = ?";
$room_stmt = $conn->prepare($room_query);
$room_stmt->bind_param("i", $room_id);
$room_stmt->execute();
$room_result = $room_stmt->get_result();
$room = $room_result->fetch_assoc();
$room_stmt->close();

if (!$room) {
    echo "Room not found.";
    exit;
}

// Check if the room is available
if ($room['availability'] == 0) {
    echo "Sorry, this room is not available for booking.";
    exit;
}

// Process booking
$booking_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $number_of_students = $_POST['number_of_students'] ?? 1;
    $status = 'pending';

    // Insert booking into the database (created_at auto-filled by DB)
    $booking_query = "INSERT INTO bookings (user_id, room_id, number_of_students, status) 
                      VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($booking_query);
    $stmt->bind_param("iiis", $user_id, $room_id, $number_of_students, $status);

    if ($stmt->execute()) {
        // Mark the room as booked by setting availability to 0
        $update_room_query = "UPDATE rooms SET availability = 0 WHERE room_id = ?";
        $update_stmt = $conn->prepare($update_room_query);
        $update_stmt->bind_param("i", $room_id);
        $update_stmt->execute();
        $update_stmt->close();

        $booking_message = "Booking successful! Your room has been reserved. You will receive an update once the booking is approved.";
    } else {
        $booking_message = "Error: Could not process your booking.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h2>Book Room: <?= htmlspecialchars($room['room_number']) ?></h2>
            <p><strong>Capacity:</strong> <?= htmlspecialchars($room['capacity']) ?> students</p>
            <p><strong>Price:</strong> RS: <?= htmlspecialchars($room['rent_price']) ?> per month</p>
            <p><strong>Status:</strong> <?= $room['availability'] == 1 ? 'Available' : 'Not Available' ?></p>

            <?php if ($booking_message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($booking_message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="number_of_students">Number of Students</label>
                    <input type="number" name="number_of_students" id="number_of_students" class="form-control" 
                           value="1" min="1" max="<?= htmlspecialchars($room['capacity']) ?>" required>
                </div>

                <button type="submit" class="btn btn-success">Book Now</button>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
