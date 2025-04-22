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

// Fetch rooms
$rooms_query = "SELECT * FROM rooms WHERE hostel_id = ?";
$rooms_stmt = $conn->prepare($rooms_query);
$rooms_stmt->bind_param("i", $hostel_id);
$rooms_stmt->execute();
$rooms_result = $rooms_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Rooms - <?= htmlspecialchars($hostel['name']) ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>
<div class="container mt-5 mb-5">
    <h2 class="text-center">Rooms in <?= htmlspecialchars($hostel['name']) ?></h2>

    <a href="add_room.php" class="btn btn-success mb-3">Add Room</a>

    <?php if ($rooms_result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Capacity</th>
                    <th>Availability</th>
                    <th>Rent Price</th>
                    <th>Room Picture</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($room = $rooms_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($room['room_number']) ?></td>
                        <td><?= htmlspecialchars($room['capacity']) ?></td>
                        <td><?= $room['availability'] ? 'Available' : 'Unavailable' ?></td>
                        <td><?= number_format($room['rent_price'], 2) ?> PKR</td>
                        <td>
                            <?php if ($room['room_picture']): ?>
                                <img src="<?= htmlspecialchars($room['room_picture']) ?>" width="100">
                            <?php else: ?>
                                No image
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_room.php?id=<?= $room['room_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="delete_room.php?id=<?= $room['room_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this room?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No rooms found.</div>
    <?php endif; ?>
</div>

</body>
</html>
