<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$page_title = $page_title ?? 'چرمین - محصولات چرمی دست‌ساز';

$cart_count = 0;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("
        SELECT SUM(quantity) AS total 
        FROM cart_items 
        WHERE cart_id = (SELECT id FROM carts WHERE user_id = ?)
    ");
    $stmt->execute([getUserId()]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cart_count = $result['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shopping-bag me-2"></i>
                چرمین
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>
                            خانه
                        </a>
                    </li>

                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user me-1"></i>
                                پروفایل
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="order_history.php">
                                <i class="fas fa-history me-1"></i>
                                سفارشات
                            </a>
                        </li>

                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin_dashboard.php">
                                    <i class="fas fa-tachometer-alt me-1"></i>
                                    پنل مدیریت
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>
                                خروج
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                ورود
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>
                                ثبت نام
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="navbar-nav">
                    <a class="nav-link position-relative" href="cart.php">
                        <i class="fas fa-shopping-cart me-1"></i>
                        سبد خرید
                        <span class="badge bg-danger rounded-pill cart-badge position-absolute top-0 start-100 translate-middle">
                            <?php echo isLoggedIn() ? $cart_count : 0; ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>