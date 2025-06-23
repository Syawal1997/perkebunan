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

// Ambil data pembayaran
$stmt = $pdo->prepare("SELECT * FROM pembayaran WHERE id_transaksi = ?");
$stmt->execute([$transaksi_id]);
$pembayaran = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Proses upload bukti pembayaran
    if (isset($_FILES['bukti_pembayaran']) {
        $target_dir = "../uploads/bukti/";
        $target_file = $target_dir . basename($_FILES["bukti_pembayaran"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["bukti_pembayaran"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $error = "File bukan gambar.";
            $uploadOk = 0;
        }
        
        // Check file size
        if ($_FILES["bukti_pembayaran"]["size"] > 5000000) {
            $error = "Maaf, file terlalu besar. Maksimal 5MB.";
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $error = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
            $uploadOk = 0;
        }
        
        if ($uploadOk == 1) {
            // Generate unique filename
            $new_filename = uniqid() . '.' . $imageFileType;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["bukti_pembayaran"]["tmp_name"], $target_file)) {
                // Update pembayaran dengan bukti
                $stmt = $pdo->prepare("UPDATE pembayaran SET bukti_pembayaran = ? WHERE id_transaksi = ?");
                $stmt->execute([$new_filename, $transaksi_id]);
                
                $success = "Bukti pembayaran berhasil diupload. Menunggu konfirmasi penjual.";
            } else {
                $error = "Maaf, terjadi kesalahan saat upload file.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran - Hasil Perkebunan</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .konfirmasi-container {
            max-width: 600px;
            margin: 5rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .konfirmasi-container h2 {
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
        
        .metode-info {
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: #f0f8ff;
            border-radius: 5px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input[type="file"] {
            width: 100%;
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
        
        .success {
            color: #4CAF50;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .error {
            color: red;
            margin-bottom: 1rem;
            text-align: center;
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

    <div class="konfirmasi-container">
        <h2>Konfirmasi Pembayaran</h2>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="transaksi-info">
            <p>Produk: <?php echo htmlspecialchars($transaksi['nama_produk']); ?></p>
            <p>Jumlah: <?php echo $transaksi['jumlah']; ?> kg</p>
            <p class="total">Total: Rp <?php echo number_format($transaksi['total_harga'], 0, ',', '.'); ?></p>
        </div>
        
        <div class="metode-info">
            <p>Metode Pembayaran: 
                <?php 
                    if ($pembayaran['metode'] == 'transfer_bank') {
                        echo 'Transfer Bank (BCA 1234567890 a.n. Hasil Perkebunan Online)';
                    } elseif ($pembayaran['metode'] == 'e_wallet') {
                        echo 'E-Wallet (Dana/OVO/Gopay)';
                    }
                ?>
            </p>
        </div>
        
        <?php if (empty($pembayaran['bukti_pembayaran'])): ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="bukti_pembayaran">Upload Bukti Pembayaran</label>
                <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" required accept="image/*">
            </div>
            
            <button type="submit" class="btn-submit">Kirim Bukti Pembayaran</button>
        </form>
        <?php else: ?>
        <div class="success">
            Anda sudah mengupload bukti pembayaran. Silahkan tunggu konfirmasi dari penjual.
        </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2023 Hasil Perkebunan Online. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>