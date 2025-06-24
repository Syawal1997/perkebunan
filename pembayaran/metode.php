<?php
session_start();
include '../database/config.php';
include '../includes/auth.php';

// Pastikan hanya pembeli yang bisa akses
if ($_SESSION['role'] != 'pembeli') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../dashboard/pembeli.php");
    exit();
}

$transaksi_id = $_GET['id'];

// Verifikasi transaksi milik user
$query = "SELECT t.*, p.nama as produk_nama, p.harga 
          FROM transaksi t 
          JOIN produk p ON t.id_produk = p.id 
          WHERE t.id = ? AND t.id_pembeli = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $transaksi_id, $_SESSION['user_id']);
$stmt->execute();
$transaksi = $stmt->get_result()->fetch_assoc();

if (!$transaksi) {
    header("Location: ../dashboard/pembeli.php");
    exit();
}

// Proses pemilihan metode pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $metode = $_POST['metode'];
    
    $query = "INSERT INTO pembayaran (id_transaksi, metode, jumlah) 
              VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isd", $transaksi_id, $metode, $transaksi['total_harga']);
    
    if ($stmt->execute()) {
        // Update status transaksi
        $update = "UPDATE transaksi SET status = 'menunggu pembayaran' WHERE id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("i", $transaksi_id);
        $stmt->execute();
        
        if ($metode == 'cod') {
            header("Location: ../transaksi/riwayat.php");
        } else {
            header("Location: konfirmasi.php?id=" . $transaksi_id);
        }
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metode Pembayaran - Hasil Perkebunan</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard-menu">
            <ul>
                <li><a href="../dashboard/pembeli.php">Beranda</a></li>
                <li><a href="../transaksi/riwayat.php">Riwayat Transaksi</a></li>
                <li><a href="../includes/logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="form-container">
            <h2>Pilih Metode Pembayaran</h2>
            
            <div class="transaksi-info" style="margin-bottom: 20px;">
                <h3>Detail Transaksi</h3>
                <p>Produk: <?php echo $transaksi['produk_nama']; ?></p>
                <p>Jumlah: <?php echo $transaksi['jumlah']; ?></p>
                <p>Harga Satuan: Rp <?php echo number_format($transaksi['harga'], 0, ',', '.'); ?></p>
                <p>Total Harga: Rp <?php echo number_format($transaksi['total_harga'], 0, ',', '.'); ?></p>
            </div>
            
            <form action="metode.php?id=<?php echo $transaksi_id; ?>" method="POST">
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <div style="margin-top: 10px;">
                        <input type="radio" id="transfer" name="metode" value="transfer bank" checked>
                        <label for="transfer" style="display: inline; margin-left: 5px;">Transfer Bank</label>
                    </div>
                    
                    <div style="margin-top: 10px;">
                        <input type="radio" id="ewallet" name="metode" value="e-wallet">
                        <label for="ewallet" style="display: inline; margin-left: 5px;">E-Wallet</label>
                    </div>
                    
                    <div style="margin-top: 10px;">
                        <input type="radio" id="cod" name="metode" value="cod">
                        <label for="cod" style="display: inline; margin-left: 5px;">Cash On Delivery (COD)</label>
                    </div>
                </div>
                
                <button type="submit" class="btn">Lanjutkan</button>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>