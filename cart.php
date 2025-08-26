<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();
$page_title = 'سبد خرید - چرمین';

function ownsCartItem($item_id)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT id FROM cart_items 
        WHERE id = ? AND cart_id IN (SELECT id FROM carts WHERE user_id = ?)
    ");
    $stmt->execute([$item_id, getUserId()]);
    return $stmt->fetch() !== false;
}

$message = '';
$cart_id = null;

// گرفتن یا ساختن سبد خرید کاربر
$stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmt->execute([getUserId()]);
$cart = $stmt->fetch();

if (!$cart) {
    $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
    $stmt->execute([getUserId()]);
    $cart_id = $pdo->lastInsertId();
} else {
    $cart_id = $cart['id'];
}

// عملیات POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $product_id = intval($_POST['product_id']);
        $quantity = max(1, intval($_POST['quantity']));

        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product || $product['stock_quantity'] < $quantity) {
            $message = '❌ موجودی کافی برای این محصول وجود ندارد.';
        } else {
            $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
            $stmt->execute([$cart_id, $product_id]);
            $existing = $stmt->fetch();

            if ($existing) {
                $new_quantity = $existing['quantity'] + $quantity;
                if ($new_quantity > $product['stock_quantity']) {
                    $message = '⛔ مقدار درخواستی بیش از موجودی است.';
                } else {
                    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                    $stmt->execute([$new_quantity, $existing['id']]);
                    header("Location: cart.php?success=1");
                    exit();
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$cart_id, $product_id, $quantity]);
                header("Location: cart.php?success=1");
                exit();
            }
        }
    } elseif ($action === 'update') {
        $item_id = intval($_POST['cart_item_id']);
        $quantity = max(1, intval($_POST['quantity']));

        if (ownsCartItem($item_id)) {
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $stmt->execute([$quantity, $item_id]);
            $message = '✅ تعداد محصول به‌روزرسانی شد.';
        } else {
            $message = '⛔ شما اجازه ویرایش این آیتم را ندارید.';
        }
    } elseif ($action === 'remove') {
        $item_id = intval($_POST['cart_item_id']);

        if (ownsCartItem($item_id)) {
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ?");
            $stmt->execute([$item_id]);
            $message = '🗑️ محصول از سبد حذف شد.';
        } else {
            $message = '⛔ شما اجازه حذف این آیتم را ندارید.';
        }
    }
}

// دریافت آیتم‌های سبد فقط با محصولات موجود در انبار
$stmt = $pdo->prepare("
    SELECT ci.id, ci.quantity, p.id AS product_id, p.name, p.price, p.image, p.stock_quantity 
    FROM cart_items ci 
    JOIN products p ON ci.product_id = p.id 
    WHERE ci.cart_id = ? AND p.stock_quantity > 0
");
$stmt->execute([$cart_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// نمایش
include 'includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-5 text-center fw-bold">🛍️ سبد خرید شما</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success shadow-sm text-center">✅ محصول با موفقیت به سبد خرید اضافه شد.</div>
    <?php elseif (!empty($message)): ?>
        <div class="alert alert-info shadow-sm text-center"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">سبد خرید شما خالی است</h4>
            <a href="index.php" class="btn btn-primary btn-lg rounded-pill mt-4 px-4">
                <i class="fas fa-arrow-right me-2"></i> بازگشت به فروشگاه
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <?php $total_price = 0; ?>
                <?php foreach ($cart_items as $item): ?>
                    <?php $total_price += $item['price'] * $item['quantity']; ?>
                    <div class="card mb-4 shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid w-100 h-100 object-fit-cover">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body d-flex flex-column justify-content-between h-100">
                                    <div>
                                        <h5 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <p class="card-text text-secondary mb-3">قیمت: <strong><?php echo number_format($item['price']); ?></strong> تومان</p>
                                        <p class="small text-muted">موجودی: <?php echo $item['stock_quantity']; ?> عدد</p>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" class="form-control w-50 mb-3" form="update-form-<?php echo $item['id']; ?>">
                                    </div>
                                    <div class="d-flex flex-column align-items-start">
                                        <form method="POST" id="update-form-<?php echo $item['id']; ?>" class="mb-2">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="cart_item_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-outline-success btn-sm rounded-pill">به‌روزرسانی</button>
                                        </form>

                                        <form method="POST">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="cart_item_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">🗑 حذف</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="col-lg-4">
                <div class="cart-total bg-light p-4 rounded-4 shadow-sm">
                    <h4 class="text-center text-dark mb-3">💰 جمع کل</h4>
                    <h3 class="text-center text-success fw-bold mb-4"><?php echo number_format($total_price); ?> تومان</h3>
                    <div class="d-grid gap-2">
                        <a href="checkout.php" class="btn btn-success btn-lg rounded-pill">
                            <i class="fas fa-credit-card me-2"></i> ادامه خرید
                        </a>
                        <a href="index.php" class="btn btn-secondary rounded-pill">افزودن محصولات بیشتر</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>