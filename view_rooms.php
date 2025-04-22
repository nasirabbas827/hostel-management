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

// Get hostel id from query string
$hostel_id = $_GET['hostel_id'] ?? 0;
if (!$hostel_id) {
    echo "Hostel ID is required.";
    exit;
}

// Fetch hostel details
$hostel_query = "SELECT * FROM hostels WHERE hostel_id = ?";
$hostel_stmt = $conn->prepare($hostel_query);
$hostel_stmt->bind_param("i", $hostel_id);
$hostel_stmt->execute();
$hostel_result = $hostel_stmt->get_result();
$hostel = $hostel_result->fetch_assoc();
$hostel_stmt->close();

if (!$hostel) {
    echo "Hostel not found.";
    exit;
}

// Handle filtering options
$availability_filter = isset($_GET['availability']) ? $_GET['availability'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
$capacity_filter = isset($_GET['capacity']) ? $_GET['capacity'] : '';

// Build SQL query based on filters
$sql = "SELECT * FROM rooms WHERE hostel_id = ?";
$params = [$hostel_id];

if ($availability_filter !== '') {
    $sql .= " AND availability = ?";
    $params[] = $availability_filter;
}

if ($min_price !== '') {
    $sql .= " AND rent_price >= ?";
    $params[] = $min_price;
}

if ($max_price !== '') {
    $sql .= " AND rent_price <= ?";
    $params[] = $max_price;
}

if ($capacity_filter !== '') {
    $sql .= " AND capacity >= ?";
    $params[] = $capacity_filter;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat("i", count($params)), ...$params);
$stmt->execute();
$rooms_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms in <?= htmlspecialchars($hostel['name']) ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        form .form-control {
    border-radius: 8px;
}

form .btn-primary {
    border-radius: 8px;
    background-color: #007bff;
    border: none;
    transition: background-color 0.2s ease-in-out;
}

form .btn-primary:hover {
    background-color: #0056b3;
}

    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Rooms in <?= htmlspecialchars($hostel['name']) ?></h2>

<!-- Filters -->
<form method="GET" class="bg-light p-4 rounded shadow-sm mb-4">
    <input type="hidden" name="hostel_id" value="<?= htmlspecialchars($hostel_id) ?>">
    <div class="form-row">

        <!-- Availability -->
        <div class="form-group col-md-3">
            <label for="availability">Availability</label>
            <select name="availability" id="availability" class="form-control">
                <option value="">All</option>
                <option value="1" <?= $availability_filter == '1' ? 'selected' : '' ?>>Available</option>
                <option value="0" <?= $availability_filter == '0' ? 'selected' : '' ?>>Not Available</option>
            </select>
        </div>

        <!-- Min Price -->
        <div class="form-group col-md-2">
            <label for="min_price">Min Price</label>
            <input type="number" name="min_price" id="min_price" class="form-control" placeholder="e.g. 1000" value="<?= htmlspecialchars($min_price) ?>">
        </div>

        <!-- Max Price -->
        <div class="form-group col-md-2">
            <label for="max_price">Max Price</label>
            <input type="number" name="max_price" id="max_price" class="form-control" placeholder="e.g. 5000" value="<?= htmlspecialchars($max_price) ?>">
        </div>

        <!-- Capacity -->
        <div class="form-group col-md-3">
            <label for="capacity">Minimum Capacity</label>
            <input type="number" name="capacity" id="capacity" class="form-control" placeholder="e.g. 2" value="<?= htmlspecialchars($capacity_filter) ?>">
        </div>

        <!-- Button -->
        <div class="form-group col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-filter"></i> Apply
            </button>
        </div>
    </div>
</form>

<h2>Rooms </h2>
    <!-- Display Rooms -->
    <div class="row">
        <?php
        if ($rooms_result->num_rows > 0) {
            while ($room = $rooms_result->fetch_assoc()) {
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="manager/<?= htmlspecialchars($room['room_picture']) ?>" class="card-img-top" alt="Room Image" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">Room Number: <?= htmlspecialchars($room['room_number']) ?></h5>
                            <p><strong>Capacity:</strong> <?= htmlspecialchars($room['capacity']) ?></p>
                            <p><strong>Price:</strong> RS: <?= htmlspecialchars($room['rent_price']) ?></p>
                            <p><strong>Status:</strong> <?= $room['availability'] ? 'Available' : 'Not Available' ?></p>

                            <?php if ($room['availability'] == 1): ?>
                                <a href="book_room.php?room_id=<?= $room['room_id'] ?>" class="btn btn-success">Book Now</a>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>Not Available</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>No rooms found based on your filters.</p>";
        }
        ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
