<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'pembeli') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../dashboard/pembeli.php");
    exit();
}

$transaksi_id = $_GET['id'];

// Ambil data transaksi
$stmt = $pdo->prepare("SELECT t.*, p.nama as nama_produk FROM transaksi t JOIN produk p ON t.id_produk = p.id WHERE t.id = ? AND t.id_pembeli = ?");
$stmt->execute([$transaksi_id, $_SESSION['user']]);
$transaksi = $stmt->fetch();

if (!$transaksi || $transaksi['status'] != 'menunggu_pembayaran') {
    header("Location: ../dashboard/pembeli.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $metode = $_POST['metode'];
    
    // Simpan metode pembayaran
    $stmt = $pdo->prepare("INSERT INTO pembayaran (id_transaksi, metode, jumlah) VALUES (?, ?, ?)");
    $stmt->execute([$transaksi_id, $metode, $transaksi['total_harga']]);
    
    if ($metode == 'cod') {
        // Untuk COD, langsung update status transaksi menjadi diproses
        $stmt = $pdo->prepare("UPDATE transaksi SET status = 'diproses' WHERE id = ?");
        $stmt->execute([$transaksi_id]);
        
        header("Location: ../dashboard/pembeli.php");
    } else {
        // Untuk transfer/e-wallet, arahkan ke halaman konfirmasi
        header("Location: konfirmasi.php?id=" . $transaksi_id);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metode Pembayaran - Hasil Perkebunan</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .pembayaran-container {
            max-width: 600px;
            margin: 5rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .pembayaran-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #4CAF50;
        }
        
        .transaksi-info {
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        
        .transaksi-info p {
            margin-bottom: 0.5rem;
        }
        
        .transaksi-info .total {
            font-weight: bold;
            font-size: 1.2rem;
            color: #4CAF50;
        }
        
        .metode-list {
            margin-bottom: 2rem;
        }
        
        .metode-item {
            display: block;
            margin-bottom: 1rem;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .metode-item:hover {
            background-color: #f5f5f5;
        }
        
        .metode-item input {
            margin-right: 10px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Hasil Perkebunan Online</h1>
            <nav>
                <ul>
                    <li><a href="../index.html">Beranda</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="pembayaran-container">
        <h2>Pilih Metode Pembayaran</h2>
        
        <div class="transaksi-info">
            <p>Produk: <?php echo htmlspecialchars($transaksi['nama_produk']); ?></p>
            <p>Jumlah: <?php echo $transaksi['jumlah']; ?> kg</p>
            <p class="total">Total: Rp <?php echo number_format($transaksi['total_harga'], 0, ',', '.'); ?></p>
        </div>
        
        <form method="POST" action="">
            <div class="metode-list">
                <label class="metode-item">
                    <input type="radio" name="metode" value="transfer_bank" required> 
                    Transfer Bank
                    <p>Transfer ke rekening BCA 1234567890 a.n. Hasil Perkebunan Online</p>
                </label>
                
                <label class="metode-item">
                    <input type="radio" name="metode" value="e_wallet"> 
                    E-Wallet (Dana/OVO/Gopay)
                    <p>Pembayaran via aplikasi e-wallet</p>
                </label>
                
                <label class="metode-item">
                    <input type="radio" name="metode" value="cod"> 
                    Cash on Delivery (COD)
                    <p>Bayar ketika barang diterima</p>
                </label>
            </div>
            
            <button type="submit" class="btn-submit">Lanjutkan</button>
        </form>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2023 Hasil Perkebunan Online. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
