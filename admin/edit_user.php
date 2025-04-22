<?php
session_start();
include('config.php');

// Check if the user is admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Get user ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: view_users.php");
    exit;
}

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: view_users.php");
    exit;
}

// Update user details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $usertype = $_POST["usertype"];
    $age = $_POST["age"];

    // Update query
    $updateQuery = "UPDATE users SET username='$username', email='$email', phone='$phone', usertype='$usertype', age='$age' WHERE id=$id";
    
    if (mysqli_query($conn, $updateQuery)) {
        header("Location: view_users.php");
        exit;
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>
<div class="container mt-5">
    <h2 class="text-center">Edit User</h2>
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <form method="post">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                </div>
                <div class="form-group">
    <label>User Type</label>
    <select name="usertype" class="form-control">
        <option value="student" <?= isset($user['usertype']) && $user['usertype'] == 'student' ? 'selected' : '' ?>>Student</option>
        <option value="manager" <?= isset($user['usertype']) && $user['usertype'] == 'manager' ? 'selected' : '' ?>>Manager</option>
    </select>
</div>

                <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="age" class="form-control" value="<?= htmlspecialchars($user['age']) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="view_users.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
