<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireAdmin();

$page_title = 'مشاهده سفارشات - چرمین';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
}

// Get all orders with user information
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name, u.username,
           GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ')') SEPARATOR ', ') as items
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>مدیریت سفارشات</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-2"></i>
            بازگشت
        </a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>شماره سفارش</th>
                    <th>مشتری</th>
                    <th>محصولات</th>
                    <th>مبلغ</th>
                    <th>وضعیت</th>
                    <th>تاریخ</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($order['full_name']); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($order['username']); ?></small>
                        </td>
                        <td>
                            <small><?php echo htmlspecialchars($order['items']); ?></small>
                        </td>
                        <td><?php echo number_format($order['total_price']); ?> تومان</td>
                        <td>
                            <?php
                          $status_labels = [
                            'pending' => 'در انتظار',
                            'paid' => 'پرداخت شده',
                            'shipped' => 'ارسال شده',
                            'cancelled' => 'لغو شده'
                        ];
                        $status_colors = [
                            'pending' => 'warning',
                            'paid' => 'info',
                            'shipped' => 'primary',
                            'cancelled' => 'danger'
                        ];
                            ?>
                            <span class="badge bg-<?php echo $status_colors[$order['status']]; ?>">
                                <?php echo $status_labels[$order['status']]; ?>
                            </span>
                        </td>
                        <td><?php echo date('Y/m/d H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                                    <?php foreach ($status_labels as $status => $label): ?>
                                        <option value="<?php echo $status; ?>"
                                            <?php echo $order['status'] === $status ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
            <h3 class="text-muted">هیچ سفارشی یافت نشد</h3>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>