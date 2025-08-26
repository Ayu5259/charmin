<?php
require_once 'config/database.php';
require_once 'config/session.php';


$page_title = 'چرمین - محصولات چرمی دست‌ساز';

// Get products from database
$stmt = $pdo->prepare("SELECT * FROM products WHERE stock_quantity > 0 ORDER BY created_at DESC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>چرمین</h1>
        <p class="lead">محصولات چرمی دست‌ساز با کیفیت بالا</p>
        <a href="#products" class="btn btn-primary btn-lg">
            <i class="fas fa-shopping-bag me-2"></i>
            مشاهده محصولات
        </a>
    </div>
</section>

<!-- Products Section -->
<section id="products" class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2>محصولات ما</h2>
                <p class="text-muted">بهترین محصولات چرمی دست‌ساز</p>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="جستجو در محصولات...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                        class="product-image"
                        onerror="this.src='https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg?auto=compress&cs=tinysrgb&w=400&h=400&dpr=2'">

                    <div class="product-info">
                        <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-price"><?php echo number_format($product['price']); ?> تومان</div>

                        <div class="d-flex gap-2">
                            <?php if (isLoggedIn()): ?>
                                <form method="POST" action="cart.php" class="d-inline">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-cart-plus me-1"></i>
                                        افزودن به سبد
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-primary add-to-cart"
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-product-price="<?php echo $product['price']; ?>"
                                    data-product-image="<?php echo htmlspecialchars($product['image']); ?>">
                                    <i class="fas fa-cart-plus me-1"></i>
                                    افزودن به سبد
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                <h3 class="text-muted">هنوز محصولی اضافه نشده است</h3>
                <p class="text-muted">لطفاً بعداً مراجعه کنید</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>