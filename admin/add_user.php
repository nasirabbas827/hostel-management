<?php
session_start();
include('config.php');

// Check if the user is admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Initialize variables
$username = $email = $phone = $password = $usertype = $age = "";
$username_err = $email_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate inputs
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } else {
        $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
    }

    $phone = trim($_POST["phone"]);
    $usertype = $_POST["usertype"];
    $age = $_POST["age"];

    // Insert if no errors
    if (empty($username_err) && empty($email_err) && empty($password_err)) {
        $sql = "INSERT INTO users (username, email, phone, password, usertype, age) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssss", $username, $email, $phone, $password, $usertype, $age);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header("Location: view_users.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>
<div class="container mt-5 mb-5">
    <h2 class="text-center">Add User</h2>
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <form method="post">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <select name="usertype" class="form-control">
    <option value="student">Student</option>
    <option value="manager">Manager</option>
</select>

                <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="age" class="form-control">
                </div>
                <button type="submit" class="btn btn-success">Add User</button>
                <a href="view_users.php">View Users</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
