<?php
$pageTitle = "Beranda";
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <h1>Hasil Perkebunan Berkualitas</h1>
        <p>Temukan produk perkebunan segar langsung dari petani</p>
        <a href="/perkebunan/products.php" class="btn btn-primary">Belanja Sekarang</a>
    </div>
</section>

<section class="featured-products">
    <div class="container">
        <h2>Produk Unggulan</h2>
        
        <div class="products-grid">
            <?php
            // Ambil 4 produk terbaru
            $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 4");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($products as $product):
            ?>
            <div class="product-card">
                <img src="/perkebunan/uploads/products/<?php echo $product['image'] ?? 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                <a href="/perkebunan/products.php#product-<?php echo $product['id']; ?>" class="btn btn-small">Lihat Detail</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="benefits">
    <div class="container">
        <h2>Mengapa Memilih Kami?</h2>
        
        <div class="benefits-grid">
            <div class="benefit-card">
                <i class="fas fa-leaf"></i>
                <h3>Produk Segar</h3>
                <p>Dipetik langsung dari kebun untuk memastikan kesegaran</p>
            </div>
            <div class="benefit-card">
                <i class="fas fa-truck"></i>
                <h3>Pengiriman Cepat</h3>
                <p>Pesanan dikirim dalam waktu 24 jam setelah pembayaran</p>
            </div>
            <div class="benefit-card">
                <i class="fas fa-money-bill-wave"></i>
                <h3>Harga Terbaik</h3>
                <p>Harga langsung dari petani tanpa perantara</p>
            </div>
            <div class="benefit-card">
                <i class="fas fa-headset"></i>
                <h3>Dukungan 24/7</h3>
                <p>Tim kami siap membantu kapan saja</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>