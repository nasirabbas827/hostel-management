<?php
include('config.php');
session_start();

// Update order status and payment status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $new_payment_status = ($new_status == 'Completed') ? 'Paid' : 'Pending';

    $update_sql = "UPDATE Orders SET status = ?, payment_status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $new_status, $new_payment_status, $order_id);

    if ($stmt->execute()) {
        $message = "Order status updated successfully!";
    } else {
        $message = "Failed to update status.";
    }
}

// Fetch all orders with customer and table details
$order_sql = "SELECT o.id, o.table_id, o.order_time, o.status, o.total_amount, 
                     o.payment_method, o.payment_status, o.order_address, 
                     u.username, u.email, u.phone
              FROM Orders o
              JOIN users u ON o.customer_id = u.id
              ORDER BY o.order_time DESC";
$orders = $conn->query($order_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Orders</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('admin_navbar.php'); ?>

    <div class="container mt-4">
        <h2>Orders Management</h2>

        <?php if (isset($message)) { echo "<div class='alert alert-info'>$message</div>"; } ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Payment Status</th>
                    <th>Order Items</th> <!-- New Column -->
                    <th>Order Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $orders->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= $row['username']; ?></td>
                    <td>$<?= number_format($row['total_amount'], 2); ?></td>
                    <td>
                        <span class="badge badge-<?= $row['payment_status'] == 'Pending' ? 'warning' : 'success'; ?>">
                            <?= $row['payment_status']; ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        // Fetch order items
                        $order_id = $row['id'];
                        $items_sql = "SELECT m.name, oi.price, oi.subtotal 
                                      FROM Order_Items oi 
                                      JOIN menu_items m ON oi.menu_item_id = m.id 
                                      WHERE oi.order_id = ?";
                        $stmt_items = $conn->prepare($items_sql);
                        $stmt_items->bind_param("i", $order_id);
                        $stmt_items->execute();
                        $result_items = $stmt_items->get_result();
                        
                        while ($item = $result_items->fetch_assoc()):
                        ?>
                            <p><?= $item['name']; ?> - $<?= number_format($item['price'], 2); ?> (Subtotal: $<?= number_format($item['subtotal'], 2); ?>)</p>
                        <?php endwhile; ?>
                    </td>
                    <td><?= $row['order_time']; ?></td>
                    <td>
                        <span class="badge badge-<?= 
                            $row['status'] == 'Pending' ? 'warning' : 
                            ($row['status'] == 'Preparing' ? 'info' : 
                            ($row['status'] == 'Served' ? 'primary' : 
                            ($row['status'] == 'Completed' ? 'success' : 'danger'))); ?>">
                            <?= $row['status']; ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
                            <select name="status" class="form-control">
                                <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Preparing" <?= $row['status'] == 'Preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="Served" <?= $row['status'] == 'Served' ? 'selected' : ''; ?>>Served</option>
                                <option value="Completed" <?= $row['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="Cancelled" <?= $row['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary btn-sm mt-2">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
