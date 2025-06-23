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

$produk_id = $_GET['id'];

// Ambil data produk
$stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->execute([$produk_id]);
$produk = $stmt->fetch();

if (!$produk) {
    header("Location: ../dashboard/pembeli.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jumlah = $_POST['jumlah'];
    $total_harga = $jumlah * $produk['harga'];
    
    // Cek stok
    if ($jumlah > $produk['stok']) {
        $error = "Stok tidak mencukupi! Stok tersedia: " . $produk['stok'] . " kg";
    } else {
        // Buat transaksi
        $stmt = $pdo->prepare("INSERT INTO transaksi (id_pembeli, id_produk, jumlah, total_harga) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user'], $produk_id, $jumlah, $total_harga]);
        
        // Kurangi stok
        $stmt = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
        $stmt->execute([$jumlah, $produk_id]);
        
        $transaksi_id = $pdo->lastInsertId();
        
        header("Location: ../pembayaran/metode.php?id=" . $transaksi_id);
        exit();
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
    <style>
        .transaksi-container {
            max-width: 600px;
            margin: 5rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .transaksi-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #4CAF50;
        }
        
        .produk-info {
            display: flex;
            margin-bottom: 2rem;
        }
        
        .produk-img {
            width: 150px;
            height: 150px;
            overflow: hidden;
            margin-right: 1.5rem;
        }
        
        .produk-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .produk-detail h3 {
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .produk-detail .harga {
            font-weight: bold;
            color: #4CAF50;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .produk-detail .stok {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
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

    <div class="transaksi-container">
        <h2>Proses Pembelian</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="produk-info">
            <div class="produk-img">
                <img src="<?php echo $produk['gambar'] ? '../uploads/' . $produk['gambar'] : 'https://via.placeholder.com/300x200?text=Produk'; ?>" alt="<?php echo htmlspecialchars($produk['nama']); ?>">
            </div>
            <div class="produk-detail">
                <h3><?php echo htmlspecialchars($produk['nama']); ?></h3>
                <p class="harga">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?>/kg</p>
                <p class="stok">Stok tersedia: <?php echo $produk['stok']; ?> kg</p>
            </div>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="jumlah">Jumlah (kg)</label>
                <input type="number" id="jumlah" name="jumlah" min="1" max="<?php echo $produk['stok']; ?>" required>
            </div>
            
            <button type="submit" class="btn-submit">Lanjut ke Pembayaran</button>
        </form>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2023 Hasil Perkebunan Online. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>