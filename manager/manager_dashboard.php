<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"]) || empty($_SESSION["id"]) || $_SESSION["usertype"] !== "manager") {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Get manager's username
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Get assigned hostel
$hostel_stmt = $conn->prepare("SELECT * FROM hostels WHERE manager_id = ?");
$hostel_stmt->bind_param("i", $user_id);
$hostel_stmt->execute();
$assigned_hostel = $hostel_stmt->get_result()->fetch_assoc();
$hostel_stmt->close();

$latest_bookings = [];
$latest_messages = [];

if ($assigned_hostel) {
    $hostel_id = $assigned_hostel['hostel_id'];

    // Get latest bookings for this hostel (join with rooms and users)
    $sql = "SELECT b.*, u.username, r.room_number 
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN rooms r ON b.room_id = r.room_id 
            WHERE r.hostel_id = ? 
            ORDER BY b.created_at DESC 
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $hostel_id);
    $stmt->execute();
    $latest_bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get latest messages
    $sql = "SELECT m.*, u.username 
            FROM messages m 
            LEFT JOIN users u ON m.student_id = u.id 
            WHERE m.hostel_id = ? 
            ORDER BY m.created_at DESC 
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $hostel_id);
    $stmt->execute();
    $latest_messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manager Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .dashboard-card {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .dashboard-header {
            font-size: 1.5rem;
        }
        .card-body img {
            border-radius: 10px;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h1 class="text-center mb-4">Welcome, <?= htmlspecialchars($username) ?></h1>

    <?php if ($assigned_hostel): ?>
        <!-- Assigned Hostel -->
        <div class="card dashboard-card mb-4">
    <div class="card-header bg-primary text-white dashboard-header">Assigned Hostel</div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5><?= htmlspecialchars($assigned_hostel['name']) ?></h5>
                <p><strong>Location:</strong> <?= htmlspecialchars($assigned_hostel['location']) ?></p>
                <p><strong>Total Rooms:</strong> <?= $assigned_hostel['total_rooms'] ?></p>
                <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($assigned_hostel['description'])) ?></p>
            </div>
            <?php if (!empty($assigned_hostel['image'])): ?>
                <div class="col-md-4 text-right">
                    <img src="../admin/uploads/<?= htmlspecialchars($assigned_hostel['image']) ?>" class="img-fluid rounded" style="max-height: 200px;">
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


        <!-- Latest Bookings -->
        <div class="card dashboard-card mb-4">
            <div class="card-header bg-success text-white dashboard-header">Latest Bookings</div>
            <div class="card-body">
                <?php if ($latest_bookings): ?>
                    <ul class="list-group">
                        <?php foreach ($latest_bookings as $booking): ?>
                            <li class="list-group-item">
                                <strong><?= htmlspecialchars($booking['username']) ?></strong> booked Room #<?= htmlspecialchars($booking['room_number']) ?> 
                                for <?= $booking['number_of_students'] ?> student(s)<br>
                                Status: <strong><?= ucfirst($booking['status']) ?></strong><br>
                                <small class="text-muted">Date: <?= $booking['created_at'] ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No recent bookings.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Latest Messages -->
        <div class="card dashboard-card mb-4">
            <div class="card-header bg-info text-white dashboard-header">Latest Messages</div>
            <div class="card-body">
                <?php if ($latest_messages): ?>
                    <ul class="list-group">
                        <?php foreach ($latest_messages as $msg): ?>
                            <li class="list-group-item">
                                <strong><?= htmlspecialchars($msg['username'] ?? 'Anonymous Student') ?>:</strong><br>
                                <?= htmlspecialchars($msg['message_content']) ?><br>
                                <small class="text-muted"><?= $msg['created_at'] ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No messages yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-center">
            <a href="manager_students.php" class="btn btn-outline-dark mr-2">View All Students</a>
            <a href="manager_messages.php?hostel_id=<?= $hostel_id ?>" class="btn btn-outline-warning">Manage Messages</a>
        </div>

    <?php else: ?>
        <div class="alert alert-info text-center">No hostel assigned to you yet.</div>
    <?php endif; ?>
</div>

</body>
</html>
