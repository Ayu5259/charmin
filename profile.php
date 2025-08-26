<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

$page_title = 'پروفایل کاربری - چرمین';
$error = '';
$success = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([getUserId()]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($full_name) || empty($email)) {
        $error = 'نام کامل و ایمیل الزامی هستند';
    } else {
        // Check if email is already used by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, getUserId()]);

        if ($stmt->fetch()) {
            $error = 'این ایمیل توسط کاربر دیگری استفاده می‌شود';
        } else {
            // Update user information
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $phone, $address, getUserId()]);

            // Update password if provided
            if (!empty($new_password)) {
                if (password_verify($current_password, $user['password'])) {
                    if ($new_password === $confirm_password) {
                        if (strlen($new_password) >= 6) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                            $stmt->execute([$hashed_password, getUserId()]);
                            $success = 'اطلاعات و رمز عبور با موفقیت به‌روزرسانی شد';
                        } else {
                            $error = 'رمز عبور جدید باید حداقل 6 کاراکتر باشد';
                        }
                    } else {
                        $error = 'رمز عبور جدید و تأیید آن مطابقت ندارند';
                    }
                } else {
                    $error = 'رمز عبور فعلی اشتباه است';
                }
            } else {
                $success = 'اطلاعات با موفقیت به‌روزرسانی شد';
            }

            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([getUserId()]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="form-container">
                <h2 class="text-center mb-4">پروفایل کاربری</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" id="profileForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">نام کامل</label>
                                <input type="text" class="form-control" id="full_name" name="full_name"
                                    value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">نام کاربری</label>
                                <input type="text" class="form-control" id="username"
                                    value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">ایمیل</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">شماره تلفن</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">آدرس</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>

                    <hr class="my-4">

                    <h5>تغییر رمز عبور</h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">رمز عبور فعلی</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">رمز عبور جدید</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">تأیید رمز عبور جدید</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>
                            ذخیره تغییرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>