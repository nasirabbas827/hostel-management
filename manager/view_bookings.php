<?php
session_start();
include('config.php');

// Check if manager is logged in
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "manager") {
    header("Location: ../index.php");
    exit;
}

$manager_id = $_SESSION["id"];

// Get assigned hostel
$hostel_query = "SELECT * FROM hostels WHERE manager_id = ?";
$stmt = $conn->prepare($hostel_query);
$stmt->bind_param("i", $manager_id);
$stmt->execute();
$hostel_result = $stmt->get_result();
$hostel = $hostel_result->fetch_assoc();

if (!$hostel) {
    die("No hostel assigned to you.");
}

$hostel_id = $hostel['hostel_id'];

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['booking_id'], $_POST['status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];
    $update_stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
    $update_stmt->bind_param("si", $new_status, $booking_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// Handle date filter
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$filter_query = "
    SELECT b.booking_id, b.created_at, b.number_of_students, b.status, 
           r.room_number, u.username 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.room_id 
    JOIN users u ON b.user_id = u.id 
    WHERE r.hostel_id = ?
";

$params = [$hostel_id];
$types = "i";

if (!empty($start_date) && !empty($end_date)) {
    $filter_query .= " AND DATE(b.created_at) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";
}

$filter_query .= " ORDER BY b.created_at DESC";

$filter_stmt = $conn->prepare($filter_query);
$filter_stmt->bind_param($types, ...$params);
$filter_stmt->execute();
$bookings = $filter_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Bookings - <?= htmlspecialchars($hostel['name']) ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2 class="text-center">Manage Bookings - <?= htmlspecialchars($hostel['name']) ?></h2>

    <form method="get" class="form-inline mb-4">
        <label for="start_date" class="mr-2">Start Date:</label>
        <input type="date" name="start_date" class="form-control mr-3" value="<?= htmlspecialchars($start_date) ?>">

        <label for="end_date" class="mr-2">End Date:</label>
        <input type="date" name="end_date" class="form-control mr-3" value="<?= htmlspecialchars($end_date) ?>">

        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="view_bookings.php" class="btn btn-secondary ml-2">Reset</a>
        <a href="" class="btn btn-success ml-2" onclick="window.print()">Print</a>
    </form>

    <?php if ($bookings->num_rows > 0): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Student</th>
                <th>Room Number</th>
                <th>Number of Students</th>
                <th>Booking Date</th>
                <th>Status</th>
                <th>Update</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $bookings->fetch_assoc()): ?>
            <tr>
                <td><?= $row['booking_id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $row['room_number'] ?></td>
                <td><?= $row['number_of_students'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <form method="POST" class="form-inline">
                        <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
                        <select name="status" class="form-control mr-2">
                            <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= $row['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="cancelled" <?= $row['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-success mt-2">Update</button>
                    </form>
                </td>
                <td>
                    <?php
                    $status = $row['status'];
                    $status_class = match ($status) {
                        'approved' => 'badge badge-success',
                        'pending' => 'badge badge-warning text-dark',
                        'cancelled' => 'badge badge-danger',
                        default => 'badge badge-secondary',
                    };
                    echo "<span class=\"$status_class\">" . ucfirst($status) . "</span>";
                    ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-info">No bookings found for the selected criteria.</div>
    <?php endif; ?>
</div>

</body>
</html>
