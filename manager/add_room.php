<?php
session_start();
include('config.php');

// Check if manager is logged in
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "manager") {
    header("Location: ../index.php");
    exit;
}

$manager_id = $_SESSION["id"];
$room_number = $capacity = $rent_price = "";
$availability = 1;
$room_picture = "";
$error = $success = "";

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
$total_rooms_allowed = $hostel['total_rooms'];

// Count existing rooms
$count_query = "SELECT COUNT(*) as total FROM rooms WHERE hostel_id = ?";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("i", $hostel_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_data = $count_result->fetch_assoc();
$current_rooms = $count_data['total'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($current_rooms >= $total_rooms_allowed) {
        $error = "Room limit reached. Cannot add more rooms.";
    } else {
        $room_number = $_POST['room_number'];
        $capacity = $_POST['capacity'];
        $rent_price = $_POST['rent_price'];
        $availability = isset($_POST['availability']) ? 1 : 0;

        // Handle image upload
        if ($_FILES['room_picture']['name']) {
            $target_dir = "uploads/";
            $room_picture = $target_dir . basename($_FILES["room_picture"]["name"]);
            move_uploaded_file($_FILES["room_picture"]["tmp_name"], $room_picture);
        }

        $insert_sql = "INSERT INTO rooms (hostel_id, room_number, capacity, availability, rent_price, room_picture) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isiiis", $hostel_id, $room_number, $capacity, $availability, $rent_price, $room_picture);
        if ($insert_stmt->execute()) {
            $success = "Room added successfully!";
        } else {
            $error = "Failed to add room.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Room</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>
<div class="container mt-5 mb-5">
    <h2 class="text-center">Add Room to <?= htmlspecialchars($hostel['name']) ?></h2>

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
                    <input type="text" name="room_number" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Rent Price (in PKR)</label>
                    <input type="number" name="rent_price" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="availability" class="form-check-input" checked>
                    <label class="form-check-label">Available</label>
                </div>
                <div class="form-group">
                    <label>Room Picture</label>
                    <input type="file" name="room_picture" class="form-control-file">
                </div>
                <button type="submit" class="btn btn-primary">Add Room</button>
                <a class="btn btn-dark" href="view_rooms.php">View Rooms</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
