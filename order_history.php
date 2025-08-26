<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

$page_title = 'تاریخچه سفارشات - چرمین';

// Get user's orders
$stmt = $pdo->prepare("
    SELECT o.*, 
           GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ')') SEPARATOR ', ') as items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([getUserId()]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4">تاریخچه سفارشات</h2>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <i class="fas fa-history fa-3x text-muted mb-3"></i>
            <h3 class="text-muted">هنوز سفارشی ثبت نکرده‌اید</h3>
            <p class="text-muted">سفارشات شما اینجا نمایش داده می‌شود</p>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag me-2"></i>
                مشاهده محصولات
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>شماره سفارش</th>
                        <th>تاریخ</th>
                        <th>محصولات</th>
                        <th>مبلغ</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo date('Y/m/d', strtotime($order['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($order['items']); ?></td>
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
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>