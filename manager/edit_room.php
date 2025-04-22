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

// Check if room ID is provided
if (!isset($_GET['id'])) {
    header("Location: view_rooms.php");
    exit;
}

$room_id = $_GET['id'];

// Fetch room details
$room_query = "SELECT * FROM rooms WHERE room_id = ? AND hostel_id = ?";
$room_stmt = $conn->prepare($room_query);
$room_stmt->bind_param("ii", $room_id, $hostel_id);
$room_stmt->execute();
$room_result = $room_stmt->get_result();
$room = $room_result->fetch_assoc();

if (!$room) {
    die("Room not found.");
}

$room_number = $room['room_number'];
$capacity = $room['capacity'];
$availability = $room['availability'];
$rent_price = $room['rent_price'];
$current_picture = $room['room_picture'];
$error = $success = "";

// Handle room update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_number = $_POST['room_number'];
    $capacity = $_POST['capacity'];
    $availability = isset($_POST['availability']) ? 1 : 0;
    $rent_price = $_POST['rent_price'];

    // Handle image upload
    if ($_FILES['room_picture']['name']) {
        $target_dir = "uploads/";
        $room_picture = $target_dir . basename($_FILES["room_picture"]["name"]);
        move_uploaded_file($_FILES["room_picture"]["tmp_name"], $room_picture);
    } else {
        $room_picture = $current_picture; // Keep the existing image if no new one is uploaded
    }

    // Update room details
    $update_sql = "UPDATE rooms SET room_number = ?, capacity = ?, availability = ?, rent_price = ?, room_picture = ? WHERE room_id = ? AND hostel_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("siiissi", $room_number, $capacity, $availability, $rent_price, $room_picture, $room_id, $hostel_id);
    if ($update_stmt->execute()) {
        $success = "Room updated successfully!";
    } else {
        $error = "Failed to update room.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Room - <?= htmlspecialchars($hostel['name']) ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>
<div class="container mt-5 mb-5">
    <h2 class="text-center">Edit Room - <?= htmlspecialchars($hostel['name']) ?></h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Room Number</label>
                    <input type="text" name="room_number" class="form-control" value="<?= htmlspecialchars($room_number) ?>" required>
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" class="form-control" value="<?= htmlspecialchars($capacity) ?>" required>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="availability" class="form-check-input" <?= $availability ? 'checked' : '' ?>>
                    <label class="form-check-label">Available</label>
                </div>
                <div class="form-group">
                    <label>Rent Price (PKR)</label>
                    <input type="number" name="rent_price" class="form-control" value="<?= htmlspecialchars($rent_price) ?>" required>
                </div>
                <div class="form-group">
                    <label>Current Room Picture</label><br>
                    <?php if ($current_picture): ?>
                        <img src="<?= htmlspecialchars($current_picture) ?>" width="100"><br>
                    <?php else: ?>
                        No image uploaded.
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Upload New Room Picture</label>
                    <input type="file" name="room_picture" class="form-control-file">
                </div>
                <button type="submit" class="btn btn-primary">Update Room</button>
                <a href="view_rooms.php" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
