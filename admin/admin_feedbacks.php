<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Delete feedback
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM feedbacks WHERE feedback_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $message = "Feedback deleted successfully.";
}

// Fetch all feedbacks with user and hostel info
$feedbacks = mysqli_query($conn, "
    SELECT f.*, u.username, h.name AS hostel_name 
    FROM feedbacks f
    JOIN users u ON f.user_id = u.id
    JOIN hostels h ON f.hostel_id = h.hostel_id
    ORDER BY f.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Feedbacks</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">All Feedbacks</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Hostel</th>
                    <th>Rating</th>
                    <th>Message</th>
                    <th>Submitted At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($feedbacks)): ?>
                    <tr>
                        <td><?= $row['feedback_id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['hostel_name']) ?></td>
                        <td><?= $row['rating'] ?> ⭐</td>
                        <td><?= htmlspecialchars($row['message']) ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <a href="?delete_id=<?= $row['feedback_id'] ?>" 
                               onclick="return confirm('Are you sure you want to delete this feedback?')" 
                               class="btn btn-sm btn-danger">
                               Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
