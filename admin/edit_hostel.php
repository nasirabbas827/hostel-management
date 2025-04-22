<?php
session_start();
include('config.php');

// Check if admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: view_hostels.php");
    exit;
}

$hostel_id = $_GET['id'];
$sql = "SELECT * FROM hostels WHERE hostel_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $hostel_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$hostel = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$hostel) {
    echo "Hostel not found.";
    exit;
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $location = trim($_POST["location"]);
    $latitude = trim($_POST["latitude"]);
    $longitude = trim($_POST["longitude"]);
    $description = trim($_POST["description"]);
    $total_rooms = trim($_POST["total_rooms"]);
    $manager_id = $_POST["manager_id"];
    $created_at = date('Y-m-d H:i:s');

    // Handle image upload
    $image = $hostel['image'];
    if (!empty($_FILES["image"]["name"])) {
        $image = basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $image);
    }

    $update = "UPDATE hostels SET name=?, location=?, latitude=?, longitude=?, description=?, total_rooms=?, manager_id=?, image=?, created_at=? WHERE hostel_id=?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "ssssssissi", $name, $location, $latitude, $longitude, $description, $total_rooms, $manager_id, $image, $created_at, $hostel_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: view_hostels.php");
    exit;
}

// Fetch managers excluding those already assigned to other hostels
$managers = mysqli_query($conn, "
    SELECT id, username FROM users 
    WHERE usertype = 'manager' 
    AND (id = {$hostel['manager_id']} OR id NOT IN (SELECT manager_id FROM hostels WHERE manager_id IS NOT NULL AND manager_id != {$hostel['manager_id']}))
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Hostel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>
<div class="container mt-5 mb-5">
    <h2 class="text-center">Edit Hostel</h2>
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Hostel Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($hostel['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($hostel['location']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="text" name="latitude" class="form-control" value="<?= htmlspecialchars($hostel['latitude']) ?>">
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="text" name="longitude" class="form-control" value="<?= htmlspecialchars($hostel['longitude']) ?>">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control"><?= htmlspecialchars($hostel['description']) ?></textarea>
                </div>
                <div class="form-group">
                    <label>Total Rooms</label>
                    <input type="number" name="total_rooms" class="form-control" value="<?= $hostel['total_rooms'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Manager</label>
                    <select name="manager_id" class="form-control" required>
                        <option value="">Select Manager</option>
                        <?php while ($manager = mysqli_fetch_assoc($managers)) : ?>
                            <option value="<?= $manager['id'] ?>" <?= ($manager['id'] == $hostel['manager_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($manager['username']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Current Image</label><br>
                    <?php if ($hostel['image']) : ?>
                        <img src="uploads/<?= htmlspecialchars($hostel['image']) ?>" width="100"><br>
                    <?php else : ?>
                        No image uploaded.
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Upload New Image</label>
                    <input type="file" name="image" class="form-control-file">
                </div>
                <button type="submit" class="btn btn-primary">Update Hostel</button>
                <a href="view_hostels.php" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
