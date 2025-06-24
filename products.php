<?php
$pageTitle = "Produk";
require_once __DIR__ . '/includes/header.php';

// Ambil parameter pencarian dan filter
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';

// Query untuk mendapatkan produk
$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND name LIKE ?";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

if (!empty($minPrice)) {
    $query .= " AND price >= ?";
    $params[] = $minPrice;
}

if (!empty($maxPrice)) {
    $query .= " AND price <= ?";
    $params[] = $maxPrice;
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil kategori unik untuk filter
$stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="products-page">
    <div class="container">
        <h1>Daftar Produk</h1>
        
        <div class="products-filter">
            <form method="GET" action="products.php">
                <div class="filter-row">
                    <div class="filter-group">
                        <input type="text" name="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <select name="category">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <input type="number" name="min_price" placeholder="Harga Min" value="<?php echo htmlspecialchars($minPrice); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <input type="number" name="max_price" placeholder="Harga Max" value="<?php echo htmlspecialchars($maxPrice); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="products.php" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="products-grid">
            <?php if (empty($products)): ?>
                <p class="no-products">Tidak ada produk yang ditemukan.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                <div class="product-card" id="product-<?php echo $product['id']; ?>">
                    <img src="/perkebunan/uploads/products/<?php echo $product['image'] ?? 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                        <p class="stock">Stok: <?php echo $product['stock']; ?></p>
                        <p class="category">Kategori: <?php echo htmlspecialchars($product['category'] ?? '-'); ?></p>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'member'): ?>
                            <form action="/perkebunan/member/add-to-cart.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <div class="quantity-control">
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                    <button type="submit" class="btn btn-small">Tambah ke Keranjang</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>