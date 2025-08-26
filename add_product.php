<?php
require_once 'config/database.php';
require_once 'config/session.php';


requireAdmin();

$page_title = 'افزودن محصول - چرمین';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    //$category = trim($_POST['category']);

    // Handle file upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'assets/images/';
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_extension, $allowed_extensions)) {
            $filename = uniqid() . '.' . $file_extension;
            $image_path = $upload_dir . $filename;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $error = 'خطا در آپلود تصویر';
            }
        } else {
            $error = 'فرمت تصویر مجاز نیست';
        }
    }

    if (empty($error)) {
        if (empty($name) || empty($description) || $price <= 0) {
            $error = 'لطفاً تمام فیلدهای الزامی را پر کنید';
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, stock_quantity) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$name, $description, $price, $image_path, $stock_quantity])) {
            $success = 'محصول با موفقیت اضافه شد';
                // Clear form
                $_POST = [];
            } else {
                $error = 'خطا در افزودن محصول';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="form-container">
                <h2 class="text-center mb-4">افزودن محصول جدید</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="productForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">نام محصول</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            </div>
                        </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">توضیحات</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">قیمت (تومان)</label>
                                <input type="number" class="form-control" id="price" name="price"
                                    value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" min="0" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="stock_quantity" class="form-label">موجودی</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity"
                                    value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? ''); ?>" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">تصویر محصول</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*"
                            onchange="previewImage(this, 'imagePreview')">
                        <div class="mt-2">
                            <img id="imagePreview" src="" alt="پیش‌نمایش" class="img-thumbnail" style="max-width: 200px; display: none;">
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>
                            افزودن محصول
                        </button>
                        <a href="admin_dashboard.php" class="btn btn-secondary btn-lg ms-2">
                            <i class="fas fa-arrow-right me-2"></i>
                            بازگشت
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php include 'includes/footer.php'; ?>