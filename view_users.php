<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireAdmin();

$page_title = 'مشاهده کاربران - چرمین';

// Get all users
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>مدیریت کاربران</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-2"></i>
            بازگشت
        </a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>شناسه</th>
                    <th>نام کاربری</th>
                    <th>نام کامل</th>
                    <th>ایمیل</th>
                    <th>تلفن</th>
                    <th>نقش</th>
                    <th>تاریخ ثبت نام</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                <?php echo $user['role'] === 'admin' ? 'مدیر' : 'کاربر'; ?>
                            </span>
                        </td>
                        <td><?php echo date('Y/m/d', strtotime($user['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (empty($users)): ?>
        <div class="text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h3 class="text-muted">هیچ کاربری یافت نشد</h3>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>