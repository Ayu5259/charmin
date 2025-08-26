<?php
require_once 'config/database.php';
require_once 'config/session.php';

$page_title = 'ورود به چرمین';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'لطفاً تمام فیلدها را پر کنید';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password, full_name, role FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            header('Location: index.php');
            exit();
        } else {
            $error = 'نام کاربری یا رمز عبور اشتباه است';
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2 class="text-center mb-4">ورود به چرمین</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <div class="mb-3">
                <label for="username" class="form-label">نام کاربری یا ایمیل</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">رمز عبور</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i>
                ورود
            </button>
        </form>

        <div class="text-center">
            <p>حساب کاربری ندارید؟ <a href="register.php">ثبت نام کنید</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>