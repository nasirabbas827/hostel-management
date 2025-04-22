<?php
session_start();
include('config.php');

// Check login
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION["id"];
$hostel_id = isset($_GET['hostel_id']) ? intval($_GET['hostel_id']) : 0;

// Get manager info
$manager_query = "SELECT h.name AS hostel_name, u.username AS manager_name, h.manager_id 
                  FROM hostels h
                  JOIN users u ON h.manager_id = u.id
                  WHERE h.hostel_id = ?";
$stmt = $conn->prepare($manager_query);
$stmt->bind_param("i", $hostel_id);
$stmt->execute();
$result = $stmt->get_result();
$hostel = $result->fetch_assoc();

if (!$hostel) {
    echo "Hostel or Manager not found.";
    exit;
}

$manager_id = $hostel['manager_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["message"])) {
    $message = trim($_POST["message"]);
    $insert_query = "INSERT INTO messages (manager_id, hostel_id, student_id, message_content) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiis", $manager_id, $hostel_id, $student_id, $message);
    $stmt->execute();
}

// Fetch messages
$fetch_query = "SELECT * FROM messages 
                WHERE hostel_id = ? AND (student_id = ? OR manager_id = ?)
                ORDER BY created_at ASC";
$stmt = $conn->prepare($fetch_query);
$stmt->bind_param("iii", $hostel_id, $student_id, $manager_id);
$stmt->execute();
$messages = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Manager - <?= htmlspecialchars($hostel['hostel_name']) ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

    
    <style>


        .chat-wrapper {
            display: flex;
            justify-content: center;
            height: 85vh;
            margin-top: 30px;
        }

        .chat-box {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 100%;
            max-width: 900px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 10px;
        }

        .message {
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 20px;
            max-width: 60%;
            word-wrap: break-word;
        }

        .message.student {
            background-color: #007bff;
            color: white;
            margin-left: auto;
            text-align: right;
        }

        .message.manager {
            background-color: #e4e6eb;
            color: black;
            margin-right: auto;
            text-align: left;
        }

        .message-time {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 5px;
        }

        .chat-input {
            margin-top: 20px;
            display: flex;
        }

        .chat-input textarea {
            flex: 1;
            resize: none;
        }

        .chat-input button {
            margin-left: 10px;
        }

    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mb-4">
    <h4 class="text-center mt-4">Chat with Manager: <?= htmlspecialchars($hostel['manager_name']) ?> (<?= htmlspecialchars($hostel['hostel_name']) ?>)</h4>
    
    <div class="chat-wrapper">
        <div class="chat-box">

            <!-- Chat Messages -->
            <div class="chat-messages">
                <?php if ($messages->num_rows > 0): ?>
                    <?php while ($msg = $messages->fetch_assoc()): ?>
                        <div class="message <?= $msg['student_id'] ? 'student' : 'manager' ?>">
                            <?= htmlspecialchars($msg['message_content']) ?>
                            <div class="message-time"><?= date("d M Y H:i", strtotime($msg['created_at'])) ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No messages yet.</p>
                <?php endif; ?>
            </div>

            <!-- Send Message -->
            <form method="POST" class="chat-input">
                <textarea name="message" id="message" rows="2" class="form-control" placeholder="Type your message..." required></textarea>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>

        </div>
    </div>

</div>

</body>
</html>
