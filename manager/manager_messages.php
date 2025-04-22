<?php
session_start();
include('config.php');

// Check if the manager is logged in
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "manager") {
    header("Location: index.php");
    exit;
}

// Get hostel ID from the URL
$hostel_id = isset($_GET['hostel_id']) ? intval($_GET['hostel_id']) : 0;

// Get manager details
$manager_id = $_SESSION["id"];

// Get hostel info managed by the manager
$hostel_query = "SELECT * FROM hostels WHERE manager_id = ? AND hostel_id = ?";
$stmt = $conn->prepare($hostel_query);
$stmt->bind_param("ii", $manager_id, $hostel_id);
$stmt->execute();
$hostel_result = $stmt->get_result();
$hostel = $hostel_result->fetch_assoc();

if (!$hostel) {
    echo "You are not managing this hostel.";
    exit;
}

// Handle sending new message
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["message"])) {
    $message = trim($_POST["message"]);
    
    // Declare student_id as NULL for manager's message
    $student_id = NULL;
    
    // Insert the new message from the manager
    $insert_message_query = "INSERT INTO messages (manager_id, hostel_id, student_id, message_content) 
                             VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_message_query);
    $stmt->bind_param("iiis", $manager_id, $hostel_id, $student_id, $message);  // Pass student_id as variable
    $stmt->execute();
}

// Fetch messages related to the specific hostel
$fetch_messages_query = "SELECT m.message_id, m.message_content, m.created_at, m.student_id, u.username AS student_name 
                         FROM messages m 
                         LEFT JOIN users u ON m.student_id = u.id 
                         WHERE m.hostel_id = ? 
                         ORDER BY m.created_at ASC";
$stmt = $conn->prepare($fetch_messages_query);
$stmt->bind_param("i", $hostel_id);
$stmt->execute();
$messages_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - <?= htmlspecialchars($hostel['name']) ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">


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
    <h4 class="text-center mt-4">Messages for Hostel: <?= htmlspecialchars($hostel['name']) ?></h4>

    <div class="chat-wrapper">
        <div class="chat-box">

            <!-- Chat Messages -->
            <div class="chat-messages">
                <?php if ($messages_result->num_rows > 0): ?>
                    <?php while ($msg = $messages_result->fetch_assoc()): ?>
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
