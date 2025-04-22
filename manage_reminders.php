<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch username
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Add reminder
if (isset($_POST['add_reminder'])) {
    $title = $_POST['title'];
    $date = $_POST['reminder_date'];
    $stmt = $conn->prepare("INSERT INTO reminders (user_id, title, reminder_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $date);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_reminders.php");
    exit;
}

// Mark as done
if (isset($_GET['complete_id'])) {
    $rid = $_GET['complete_id'];
    $stmt = $conn->prepare("UPDATE reminders SET is_completed = 1 WHERE reminder_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $rid, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_reminders.php");
    exit;
}

// Delete reminder
if (isset($_GET['delete_id'])) {
    $rid = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM reminders WHERE reminder_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $rid, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_reminders.php");
    exit;
}

// Fetch all reminders
$reminders = [];
$stmt = $conn->prepare("SELECT * FROM reminders WHERE user_id = ? ORDER BY reminder_date ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $reminders[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Reminders</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h2>Manage Your Reminders - <?php echo htmlspecialchars($username); ?></h2>
            <!-- Add Reminder Form -->
            <form method="POST">
                <div class="form-group">
                    <label for="title">Reminder Title</label>
                    <input type="text" name="title" required class="form-control">
                </div>
                <div class="form-group">
                    <label for="reminder_date">Reminder Date & Time</label>
                    <input type="datetime-local" name="reminder_date" min="<?= date('Y-m-d\TH:i'); ?>" required class="form-control">
                </div>
                <button type="submit" name="add_reminder" class="btn btn-primary">Add Reminder</button>
            </form>
        </div>
    </div>

    <!-- Reminders Table -->
    <?php if (!empty($reminders)): ?>
        <div class="mt-4">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Title</th>
                        <th>Reminder Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reminders as $rem): ?>
                        <tr class="<?php echo $rem['is_completed'] ? 'table-success' : ''; ?>">
                            <td><?php echo htmlspecialchars($rem['title']); ?></td>
                            <td><?php echo date("F j, Y, g:i A", strtotime($rem['reminder_date'])); ?></td>
                            <td><?php echo $rem['is_completed'] ? 'Completed' : 'Pending'; ?></td>
                            <td>
                                <?php if (!$rem['is_completed']): ?>
                                    <a href="?complete_id=<?php echo $rem['reminder_id']; ?>" class="btn btn-sm btn-success">Mark Done</a>
                                <?php endif; ?>
                                <a href="?delete_id=<?php echo $rem['reminder_id']; ?>" class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="mt-4 text-muted">No reminders added yet.</p>
    <?php endif; ?>
</div>
</body>
</html>
