<?php
include('config.php');
session_start();

// Ensure manager is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"]) || $_SESSION["usertype"] !== "manager") {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch assigned hostel
$hostel_sql = "SELECT hostel_id, name FROM hostels WHERE manager_id = ?";
$hostel_stmt = $conn->prepare($hostel_sql);
$hostel_stmt->bind_param("i", $user_id);
$hostel_stmt->execute();
$hostel_result = $hostel_stmt->get_result();
$hostel = $hostel_result->fetch_assoc();
$hostel_stmt->close();

if (!$hostel) {
    echo "<script>alert('No hostel assigned.'); window.location.href='manager_dashboard.php';</script>";
    exit;
}

// Get students who booked rooms in the manager's hostel
$students_sql = "
    SELECT DISTINCT u.*
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    JOIN users u ON b.user_id = u.id
    WHERE r.hostel_id = ? AND u.usertype = 'student'
";
$stmt = $conn->prepare($students_sql);
$stmt->bind_param("i", $hostel['hostel_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Students in <?= htmlspecialchars($hostel['name']) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Students in <?= htmlspecialchars($hostel['name']) ?></h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Age</th>
                        <th>Bio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $student['id'] ?></td>
                            <td><?= htmlspecialchars($student['full_name']) ?></td>
                            <td><?= htmlspecialchars($student['username']) ?></td>
                            <td><?= htmlspecialchars($student['email']) ?></td>
                            <td><?= htmlspecialchars($student['phone']) ?></td>
                            <td><?= $student['age'] ?></td>
                            <td><?= nl2br(htmlspecialchars($student['bio'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">No students found for your assigned hostel.</div>
    <?php endif; ?>
</div>

</body>
</html>
