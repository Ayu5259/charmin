<?php
require_once 'config/database.php';
require_once 'config/session.php';

$page_title = 'ثبت نام در چرمین';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'لطفاً تمام فیلدهای الزامی را پر کنید';
    } elseif ($password !== $confirm_password) {
        $error = 'رمز عبور و تأیید رمز عبور مطابقت ندارند';
    } elseif (strlen($password) < 6) {
        $error = 'رمز عبور باید حداقل 6 کاراکتر باشد';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = 'نام کاربری یا ایمیل قبلاً استفاده شده است';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");

            if ($stmt->execute([$username, $email, $hashed_password, $full_name])) {
                $success = 'ثبت نام با موفقیت انجام شد. اکنون می‌توانید وارد شوید.';
            } else {
                $error = 'خطا در ثبت نام. لطفاً مجدداً تلاش کنید.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2 class="text-center mb-4">ثبت نام در چرمین</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" id="registerForm">
            <div class="mb-3">
                <label for="full_name" class="form-label">نام کامل <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="full_name" name="full_name"
                    value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">نام کاربری <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">ایمیل <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>


            <div class="mb-3">
                <label for="password" class="form-label">رمز عبور <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password" name="password" required>
                <small id="passwordStrength" class="form-text"></small>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">تأیید رمز عبور <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-user-plus me-2"></i>
                ثبت نام
            </button>
        </form>

        <div class="text-center">
            <p>قبلاً ثبت نام کرده‌اید؟ <a href="login.php">وارد شوید</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>