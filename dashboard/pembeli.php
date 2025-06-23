<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'pembeli') {
    header("Location: ../login.php");
    exit();
}

// Ambil data produk
$stmt = $pdo->query("SELECT * FROM produk WHERE stok > 0");
$produk = $stmt->fetchAll();

// Ambil riwayat transaksi
$stmt = $pdo->prepare("SELECT t.*, p.nama as nama_produk FROM transaksi t JOIN produk p ON t.id_produk = p.id WHERE t.id_pembeli = ? ORDER BY t.created_at DESC");
$stmt->execute([$_SESSION['user']]);
$transaksi = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pembeli - Hasil Perkebunan</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: calc(100vh - 120px);
        }
        
        .sidebar {
            background-color: #2E7D32;
            color: white;
            padding: 1.5rem;
        }
        
        .sidebar ul {
            list-style: none;
        }
        
        .sidebar ul li {
            margin-bottom: 1rem;
        }
        
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        .sidebar ul li a:hover {
            text-decoration: underline;
        }
        
        .main-content {
            padding: 1.5rem;
        }
        
        .welcome {
            margin-bottom: 2rem;
        }
        
        .produk-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }
        
        .produk-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .produk-item:hover {
            transform: translateY(-5px);
        }
        
        .produk-img {
            height: 200px;
            overflow: hidden;
        }
        
        .produk-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .produk-info {
            padding: 15px;
        }
        
        .produk-info h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .produk-info p {
            color: #666;
            margin-bottom: 10px;
        }
        
        .produk-info .harga {
            font-weight: bold;
            color: #4CAF50;
            font-size: 1.2rem;
        }
        
        .btn-beli {
            display: block;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 8px 0;
            margin-top: 10px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .riwayat-transaksi table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .riwayat-transaksi th, .riwayat-transaksi td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .riwayat-transaksi th {
            background-color: #4CAF50;
            color: white;
        }
        
        .riwayat-transaksi tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .status-menunggu {
            color: #FF9800;
        }
        
        .status-diproses {
            color: #2196F3;
        }
        
        .status-dikirim {
            color: #673AB7;
        }
        
        .status-selesai {
            color: #4CAF50;
        }
        
        .status-dibatalkan {
            color: #F44336;
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

    <div class="dashboard">
        <div class="sidebar">
            <h3>Menu Pembeli</h3>
            <ul>
                <li><a href="#produk">Produk</a></li>
                <li><a href="#riwayat">Riwayat Transaksi</a></li>
                <li><a href="profil.php">Profil Saya</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="welcome">
                <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h2>
                <p>Anda login sebagai Pembeli</p>
            </div>
            
            <section id="produk">
                <h3>Produk Tersedia</h3>
                <div class="produk-list">
                    <?php foreach ($produk as $item): ?>
                    <div class="produk-item">
                        <div class="produk-img">
                            <img src="<?php echo $item['gambar'] ? '../uploads/' . $item['gambar'] : 'https://via.placeholder.com/300x200?text=Produk'; ?>" alt="<?php echo htmlspecialchars($item['nama']); ?>">
                        </div>
                        <div class="produk-info">
                            <h3><?php echo htmlspecialchars($item['nama']); ?></h3>
                            <p><?php echo htmlspecialchars($item['deskripsi']); ?></p>
                            <p class="harga">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>/kg</p>
                            <p>Stok: <?php echo $item['stok']; ?> kg</p>
                            <a href="../transaksi/proses.php?id=<?php echo $item['id']; ?>" class="btn-beli">Beli Sekarang</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <section id="riwayat" class="riwayat-transaksi">
                <h3>Riwayat Transaksi</h3>
                <?php if (count($transaksi) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaksi as $key => $item): ?>
                        <tr>
                            <td><?php echo $key + 1; ?></td>
                            <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                            <td><?php echo $item['jumlah']; ?> kg</td>
                            <td>Rp <?php echo number_format($item['total_harga'], 0, ',', '.'); ?></td>
                            <td class="status-<?php echo str_replace('_', '-', $item['status']); ?>">
                                <?php 
                                    $status = str_replace('_', ' ', $item['status']);
                                    echo ucwords($status);
                                ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></td>
                            <td>
                                <?php if ($item['status'] == 'menunggu_pembayaran'): ?>
                                    <a href="../pembayaran/metode.php?id=<?php echo $item['id']; ?>">Bayar</a>
                                <?php elseif ($item['status'] == 'dikirim'): ?>
                                    <a href="../transaksi/selesai.php?id=<?php echo $item['id']; ?>">Konfirmasi Terima</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>Belum ada riwayat transaksi.</p>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2023 Hasil Perkebunan Online. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>