<?php
session_start();
include '../database/config.php';
include '../includes/auth.php';

// Pastikan hanya pembeli yang bisa akses
if ($_SESSION['role'] != 'pembeli') {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

// Ambil data produk
if (!isset($_GET['id'])) {
    header("Location: ../dashboard/pembeli.php");
    exit();
}

$produk_id = $_GET['id'];
$query = "SELECT * FROM produk WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $produk_id);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();

if (!$produk) {
    header("Location: ../dashboard/pembeli.php");
    exit();
}

// Proses transaksi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jumlah = $_POST['jumlah'];
    $total_harga = $jumlah * $produk['harga'];
    
    // Cek stok
    if ($jumlah > $produk['stok']) {
        $error = "Stok tidak mencukupi. Stok tersedia: " . $produk['stok'];
    } else {
        // Buat transaksi
        $query = "INSERT INTO transaksi (id_pembeli, id_produk, jumlah, total_harga) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiid", $_SESSION['user_id'], $produk_id, $jumlah, $total_harga);
        
        if ($stmt->execute()) {
            $transaksi_id = $stmt->insert_id;
            
            // Kurangi stok
            $new_stok = $produk['stok'] - $jumlah;
            $update = "UPDATE produk SET stok = ? WHERE id = ?";
            $stmt = $conn->prepare($update);
            $stmt->bind_param("ii", $new_stok, $produk_id);
            $stmt->execute();
            
            $success = "Transaksi berhasil. Silakan lakukan pembayaran.";
            header("Location: ../pembayaran/metode.php?id=" . $transaksi_id);
            exit();
        } else {
            $error = "Gagal memproses transaksi. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Transaksi - Hasil Perkebunan</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard-menu">
            <ul>
                <li><a href="../dashboard/pembeli.php">Beranda</a></li>
                <li><a href="riwayat.php">Riwayat Transaksi</a></li>
                <li><a href="../includes/logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="form-container">
            <h2>Proses Pembelian</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert" style="color: green; margin-bottom: 15px;"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="produk-info" style="margin-bottom: 20px;">
                <h3><?php echo $produk['nama']; ?></h3>
                <p>Harga: Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?> / unit</p>
                <p>Stok tersedia: <?php echo $produk['stok']; ?></p>
            </div>
            
            <form action="proses.php?id=<?php echo $produk_id; ?>" method="POST">
                <div class="form-group">
                    <label for="jumlah">Jumlah</label>
                    <input type="number" id="jumlah" name="jumlah" min="1" max="<?php echo $produk['stok']; ?>" required>
                </div>
                
                <button type="submit" class="btn">Proses Pembelian</button>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>