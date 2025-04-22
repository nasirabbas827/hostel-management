<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Stats
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];
$total_admins = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM admins"))['total'];
$total_staff = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE usertype='manager'"))['total'];
$total_customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE usertype='student'"))['total'];

// Recent Actions Queries
$recent_bookings = mysqli_query($conn, "SELECT b.*, u.username FROM bookings b JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC LIMIT 5");
$recent_rooms = mysqli_query($conn, "SELECT r.*, h.name AS hostel_name FROM rooms r JOIN hostels h ON r.hostel_id = h.hostel_id ORDER BY r.room_id DESC LIMIT 5");
$recent_feedbacks = mysqli_query($conn, "SELECT f.*, u.username FROM feedbacks f JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC LIMIT 5");
$recent_messages = mysqli_query($conn, "SELECT m.*, u.username AS student_name FROM messages m LEFT JOIN users u ON m.student_id = u.id ORDER BY m.created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h1 class="text-center">Admin Dashboard</h1>

    <!-- Stats Cards -->
    <div class="row mt-4">
        <div class="col-md-3"><div class="card bg-primary text-white text-center"><div class="card-header">Total Users</div><div class="card-body"><h2><?= $total_users ?></h2></div></div></div>
        <div class="col-md-3"><div class="card bg-danger text-white text-center"><div class="card-header">Admins</div><div class="card-body"><h2><?= $total_admins ?></h2></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white text-center"><div class="card-header">Managers</div><div class="card-body"><h2><?= $total_staff ?></h2></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white text-center"><div class="card-header">Students</div><div class="card-body"><h2><?= $total_customers ?></h2></div></div></div>
    </div>

    <!-- Recent Actions -->
    <div class="mt-5">
        <h3 class="mb-3">Recent Actions</h3>

        <div class="row">
            <!-- Recent Bookings -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">Recent Bookings</div>
                    <ul class="list-group list-group-flush">
                        <?php while ($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($booking['username']) ?> booked Room ID <?= $booking['room_id'] ?> |
                                Status: <?= ucfirst($booking['status']) ?> |
                                <small class="text-muted"><?= $booking['created_at'] ?></small>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>

            <!-- Recent Rooms -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">Recently Added Rooms</div>
                    <ul class="list-group list-group-flush">
                        <?php while ($room = mysqli_fetch_assoc($recent_rooms)): ?>
                            <li class="list-group-item">
                                Room <?= htmlspecialchars($room['room_number']) ?> (<?= $room['hostel_name'] ?>) |
                                Rent: RS: <?= $room['rent_price'] ?> |
                                <small class="text-muted">Capacity: <?= $room['capacity'] ?></small>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>

            <!-- Recent Feedbacks -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">Recent Feedbacks</div>
                    <ul class="list-group list-group-flush">
                        <?php while ($fb = mysqli_fetch_assoc($recent_feedbacks)): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($fb['username']) ?> rated <?= $fb['rating'] ?>/5<br>
                                <?= htmlspecialchars(substr($fb['message'], 0, 50)) ?>...
                                <div class="text-muted"><small><?= $fb['created_at'] ?></small></div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">Recent Messages</div>
                    <ul class="list-group list-group-flush">
                        <?php while ($msg = mysqli_fetch_assoc($recent_messages)): ?>
                            <li class="list-group-item">
                                From <?= $msg['student_name'] ? htmlspecialchars($msg['student_name']) : 'N/A' ?>:
                                <?= htmlspecialchars(substr($msg['message_content'], 0, 50)) ?>...
                                <div class="text-muted"><small><?= $msg['created_at'] ?></small></div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
