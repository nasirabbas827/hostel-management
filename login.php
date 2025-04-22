<?php
include('config.php');
session_start();

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // If no errors, check credentials
    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT id, email, password, usertype FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $email, $hashed_password, $usertype);
                if (mysqli_stmt_fetch($stmt)) {
                    if (password_verify($password, $hashed_password)) {
                        // Start session and store data
                        $_SESSION["id"] = $id;
                        $_SESSION["email"] = $email;
                        $_SESSION["usertype"] = $usertype;

                        // Redirect based on user type
                        if ($usertype === "manager") {
                            header("location: manager/manager_dashboard.php");
                        } else {
                            header("location: home.php");
                        }
                        exit;
                    } else {
                        $password_err = "The password you entered is incorrect.";
                    }
                }
            } else {
                $email_err = "No account found with that email.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
<div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">

    <div class="login-container">
        <h2 class="text-center">User Login</h2>
        <p class="text-center">Please enter your credentials to log in.</p>

        <!-- Display error messages -->
        <?php if (!empty($email_err) || !empty($password_err)): ?>
            <div class="alert alert-danger">
                <strong>Error!</strong> <?php echo $email_err ?: $password_err; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>

            <div class="form-group text-center">
                <input type="submit" value="Log In" class="btn btn-primary login-btn">
            </div>
        </form>
        
        <p class="text-center">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>
</div>
</div>

<!-- Bootstrap JS & dependencies -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
