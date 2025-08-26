<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireAdmin();

$page_title = 'پنل مدیریت - چرمین';

// Get statistics
$stats = [];

// Total users
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$stmt->execute();
$stats['users'] = $stmt->fetchColumn();

// Total products
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products");
$stmt->execute();
$stats['products'] = $stmt->fetchColumn();

// Total orders
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders");
$stmt->execute();
$stats['orders'] = $stmt->fetchColumn();

// Total revenue
$stmt = $pdo->prepare("SELECT SUM(total_price) as total FROM orders WHERE status != 'cancelled'");
$stmt->execute();
$stats['revenue'] = $stmt->fetchColumn() ?: 0;

include 'includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4">پنل مدیریت</h2>

    <!-- Statistics Cards -->
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="admin-card">
                <i class="fas fa-users"></i>
                <h3><?php echo $stats['users']; ?></h3>
                <p>کاربران</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="admin-card">
                <i class="fas fa-box"></i>
                <h3><?php echo $stats['products']; ?></h3>
                <p>محصولات</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="admin-card">
                <i class="fas fa-shopping-cart"></i>
                <h3><?php echo $stats['orders']; ?></h3>
                <p>سفارشات</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="admin-card">
                <i class="fas fa-money-bill-wave"></i>
                <h3><?php echo number_format($stats['revenue']); ?></h3>
                <p>درآمد (تومان)</p>
            </div>
        </div>
    </div>

    <!-- Management Links -->
    <div class="row">
        <div class="col-md-4">
            <div class="admin-card">
                <i class="fas fa-plus-circle"></i>
                <h4>افزودن محصول</h4>
                <p>افزودن محصول جدید به فروشگاه</p>
                <a href="add_product.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    افزودن محصول
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="admin-card">
                <i class="fas fa-users-cog"></i>
                <h4>مدیریت کاربران</h4>
                <p>مشاهده و مدیریت کاربران</p>
                <a href="view_users.php" class="btn btn-primary">
                    <i class="fas fa-users me-2"></i>
                    مشاهده کاربران
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="admin-card">
                <i class="fas fa-clipboard-list"></i>
                <h4>مدیریت سفارشات</h4>
                <p>مشاهده و مدیریت سفارشات</p>
                <a href="view_orders.php" class="btn btn-primary">
                    <i class="fas fa-list me-2"></i>
                    مشاهده سفارشات
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>