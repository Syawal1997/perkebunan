<?php
require_once 'includes/header.php';
?>

<main class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Belanja Hasil Perkebunan Segar</h1>
            <p>Dapatkan produk langsung dari petani dengan harga terbaik</p>
            <a href="products.php" class="btn-primary">Lihat Produk</a>
        </div>
    </div>
</main>

<section class="featured-products">
    <div class="container">
        <h2>Produk Unggulan</h2>
        <div class="product-grid">
            <?php
            // Query untuk mendapatkan 4 produk terbaru
            $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 4");
            while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="product-card">';
                echo '<img src="uploads/products/'.$product['image'].'" alt="'.$product['name'].'">';
                echo '<h3>'.$product['name'].'</h3>';
                echo '<p class="price">Rp '.number_format($product['price'], 0, ',', '.').'</p>';
                echo '<a href="products.php?id='.$product['id'].'" class="btn-secondary">Detail</a>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>