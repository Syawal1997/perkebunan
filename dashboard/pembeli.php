<?php
session_start();
include '../database/config.php';
include '../includes/auth.php';

// Pastikan hanya pembeli yang bisa akses
if ($_SESSION['role'] != 'pembeli') {
    header("Location: ../login.php");
    exit();
}

// Query untuk mendapatkan semua produk
$query = "SELECT p.*, u.nama as nama_penjual FROM produk p JOIN users u ON p.id_penjual = u.id";
$produk = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pembeli - Hasil Perkebunan</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-header">
        <div class="container">
            <h1>Halo, <?php echo $_SESSION['nama']; ?></h1>
            <p>Dashboard Pembeli</p>
        </div>
    </div>
    
    <div class="container">
        <div class="dashboard-menu">
            <ul>
                <li><a href="pembeli.php">Beranda</a></li>
                <li><a href="../transaksi/riwayat.php">Riwayat Transaksi</a></li>
                <li><a href="../includes/logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="content">
            <h2>Produk Tersedia</h2>
            
            <?php if ($produk->num_rows > 0): ?>
                <div class="produk-list">
                    <?php while ($row = $produk->fetch_assoc()): ?>
                        <div class="produk-card">
                            <div class="produk-img">
                                <img src="../images/produk/<?php echo $row['gambar']; ?>" alt="<?php echo $row['nama']; ?>">
                            </div>
                            <div class="produk-info">
                                <h3><?php echo $row['nama']; ?></h3>
                                <p>Penjual: <?php echo $row['nama_penjual']; ?></p>
                                <p class="harga">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                                <p>Stok: <?php echo $row['stok']; ?></p>
                                <div class="actions">
                                    <a href="../transaksi/proses.php?id=<?php echo $row['id']; ?>" class="btn">Beli Sekarang</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>Tidak ada produk tersedia saat ini.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>