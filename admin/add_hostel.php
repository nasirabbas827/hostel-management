<?php
session_start();
include('config.php');

// Only allow admins
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Variables
$name = $location = $latitude = $longitude = $description = $total_rooms = $manager_id = "";
$name_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validation
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter hostel name.";
    } else {
        $name = trim($_POST["name"]);
    }

    $location = trim($_POST["location"]);
    $latitude = trim($_POST["latitude"]);
    $longitude = trim($_POST["longitude"]);
    $description = trim($_POST["description"]);
    $total_rooms = trim($_POST["total_rooms"]);
    $manager_id = $_POST["manager_id"];

    // Image upload
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $image = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    // Insert
    if (empty($name_err)) {
        $sql = "INSERT INTO hostels (image, name, location, latitude, longitude, description, total_rooms, manager_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssssssi", $image, $name, $location, $latitude, $longitude, $description, $total_rooms, $manager_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header("Location: view_hostels.php");
            exit;
        }
    }
}

// Fetch only unassigned managers
$managers = [];
$result = mysqli_query($conn, "
    SELECT id, username 
    FROM users 
    WHERE usertype='manager' 
    AND id NOT IN (SELECT manager_id FROM hostels WHERE manager_id IS NOT NULL)
");
while ($row = mysqli_fetch_assoc($result)) {
    $managers[] = $row;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Hostel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="container mt-5 mb-5">
    <h2 class="text-center">Add Hostel</h2>
    <div class="card mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Hostel Image</label>
                    <input type="file" name="image" class="form-control-file">
                </div>
                <div class="form-group">
                    <label>Hostel Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control">
                </div>
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="text" name="latitude" class="form-control">
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="text" name="longitude" class="form-control">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Total Rooms</label>
                    <input type="number" name="total_rooms" class="form-control">
                </div>
                <div class="form-group">
                    <label>Assign Manager</label>
                    <select name="manager_id" class="form-control" required>
                        <option value="">-- Select Manager --</option>
                        <?php foreach ($managers as $manager): ?>
                            <option value="<?= $manager['id'] ?>"><?= htmlspecialchars($manager['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Add Hostel</button>
                <a href="view_hostels.php" class="btn btn-secondary">View Hostels</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
