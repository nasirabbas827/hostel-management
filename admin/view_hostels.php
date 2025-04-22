<?php
session_start();
include('config.php');

// Check if the user is admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Delete hostel if requested
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM hostels WHERE hostel_id=$id");
    header("Location: view_hostels.php");
    exit;
}

// Fetch hostels
$query = "SELECT h.*, u.username AS manager_name 
          FROM hostels h 
          LEFT JOIN users u ON h.manager_id = u.id";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Hostels</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>
<div class="container mt-5">
    <h2 class="text-center">Hostel List</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Location</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Total Rooms</th>
                <th>Manager</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?= $row['hostel_id'] ?></td>
                    <td>
                        <?php if (!empty($row['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($row['image']) ?>" width="60" height="50">
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= htmlspecialchars($row['latitude']) ?></td>
                    <td><?= htmlspecialchars($row['longitude']) ?></td>
                    <td><?= htmlspecialchars($row['total_rooms']) ?></td>
                    <td><?= htmlspecialchars($row['manager_name']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="edit_hostel.php?id=<?= $row['hostel_id'] ?>" class="btn btn-warning btn-sm mb-2">Edit</a>
                        <a href="view_hostels.php?delete=<?= $row['hostel_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
