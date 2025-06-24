<?php
session_start();
include '../database/config.php';
include '../includes/auth.php';

// Pastikan hanya penjual yang bisa akses
if ($_SESSION['role'] != 'penjual') {
    header("Location: ../login.php");
    exit();
}

// Query untuk mendapatkan produk penjual
$query = "SELECT * FROM produk WHERE id_penjual = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$produk = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual - Hasil Perkebunan</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-header">
        <div class="container">
            <h1>Halo, <?php echo $_SESSION['nama']; ?></h1>
            <p>Dashboard Penjual</p>
        </div>
    </div>
    
    <div class="container">
        <div class="dashboard-menu">
            <ul>
                <li><a href="penjual.php">Dashboard</a></li>
                <li><a href="../produk/tambah.php">Tambah Produk</a></li>
                <li><a href="../produk/list.php">Kelola Produk</a></li>
                <li><a href="../transaksi/riwayat.php">Riwayat Transaksi</a></li>
                <li><a href="../includes/logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="content">
            <h2>Produk Anda</h2>
            
            <?php if ($produk->num_rows > 0): ?>
                <div class="produk-list">
                    <?php while ($row = $produk->fetch_assoc()): ?>
                        <div class="produk-card">
                            <div class="produk-img">
                                <img src="../images/produk/<?php echo $row['gambar']; ?>" alt="<?php echo $row['nama']; ?>">
                            </div>
                            <div class="produk-info">
                                <h3><?php echo $row['nama']; ?></h3>
                                <p class="harga">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                                <p>Stok: <?php echo $row['stok']; ?></p>
                                <div class="actions">
                                    <a href="../produk/edit.php?id=<?php echo $row['id']; ?>" class="btn">Edit</a>
                                    <a href="../produk/hapus.php?id=<?php echo $row['id']; ?>" class="btn" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>Anda belum memiliki produk. <a href="../produk/tambah.php">Tambah produk pertama Anda</a></p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>