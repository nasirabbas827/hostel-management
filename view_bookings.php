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

// Handle booking deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking_id'])) {
    $booking_id = intval($_POST['delete_booking_id']);

    // Ensure the booking belongs to the user and is still pending
    $check_stmt = $conn->prepare("SELECT room_id FROM bookings WHERE booking_id = ? AND user_id = ? AND status = 'pending'");
    $check_stmt->bind_param("ii", $booking_id, $user_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows === 1) {
        $check_stmt->bind_result($room_id);
        $check_stmt->fetch();

        // Delete booking
        $delete_stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
        $delete_stmt->bind_param("i", $booking_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        // Update room availability
        $update_stmt = $conn->prepare("UPDATE rooms SET availability = 1 WHERE room_id = ?");
        $update_stmt->bind_param("i", $room_id);
        $update_stmt->execute();
        $update_stmt->close();
    }

    $check_stmt->close();
}

// Fetch user's bookings
$booking_query = "SELECT b.booking_id, b.created_at, b.number_of_students, b.status, r.room_number, h.name AS hostel_name 
                  FROM bookings b
                  JOIN rooms r ON b.room_id = r.room_id
                  JOIN hostels h ON r.hostel_id = h.hostel_id
                  WHERE b.user_id = ?";
$booking_stmt = $conn->prepare($booking_query);
$booking_stmt->bind_param("i", $user_id);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
$booking_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Bookings</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Your Bookings</h2>
    <?php if ($booking_result->num_rows > 0): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Room Number</th>
                <th>Hostel Name</th>
                <th>Number of Students</th>
                <th>Booking Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($booking = $booking_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                <td><?= htmlspecialchars($booking['room_number']) ?></td>
                <td><?= htmlspecialchars($booking['hostel_name']) ?></td>
                <td><?= htmlspecialchars($booking['number_of_students']) ?></td>
                <td><?= htmlspecialchars($booking['created_at']) ?></td>
                <td class="text-center">
                    <?php 
                    $status = htmlspecialchars($booking['status']);
                    $status_class = match($status) {
                        'approved' => 'bg-success text-white',
                        'pending' => 'bg-warning text-dark',
                        'cancelled' => 'bg-danger text-white',
                        default => 'bg-secondary text-white'
                    };
                    ?>
                    <span class="badge <?= $status_class ?>"><?= ucfirst($status) ?></span>
                </td>
                <td>
                    <?php if ($status === 'pending'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_booking_id" value="<?= $booking['booking_id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this pending booking?');">Delete</button>
                        </form>
                    <?php else: ?>
                        <span class="text-muted">N/A</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>You have no bookings yet.</p>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
