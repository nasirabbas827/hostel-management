<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Update booking status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['status'];

    $allowed_statuses = ['approved', 'cancelled', 'completed', 'pending'];
    if (in_array($status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $stmt->bind_param("si", $status, $booking_id);
        $stmt->execute();
        $stmt->close();
        $message = "Booking status updated successfully.";
    } else {
        $message = "Invalid status selected.";
    }
}

// Fetch all bookings
$bookings = mysqli_query($conn, "
    SELECT b.*, u.username, r.room_number, h.name AS hostel_name 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.room_id
    JOIN hostels h ON r.hostel_id = h.hostel_id
    ORDER BY b.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Bookings</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">All Bookings</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Hostel</th>
                    <th>Room</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($bookings)): ?>
                    <tr>
                        <td><?= $row['booking_id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['hostel_name']) ?></td>
                        <td><?= htmlspecialchars($row['room_number']) ?></td>
                        <td><?= $row['number_of_students'] ?></td>
                        <td>
                            <form method="post" class="form-inline">
                                <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
                                <select name="status" class="form-control mr-2">
                                    <?php
                                    $statuses = ['pending', 'approved', 'cancelled', 'completed'];
                                    foreach ($statuses as $status) {
                                        $selected = ($row['status'] === $status) ? 'selected' : '';
                                        echo "<option value='$status' $selected>" . ucfirst($status) . "</option>";
                                    }
                                    ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                        <td><?= $row['created_at'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
