<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'penjual') {
    header("Location: ../login.php");
    exit();
}

// Ambil data produk penjual
$stmt = $pdo->prepare("SELECT * FROM produk WHERE id_penjual = ?");
$stmt->execute([$_SESSION['user']]);
$produk = $stmt->fetchAll();

// Ambil transaksi untuk produk penjual
$stmt = $pdo->prepare("SELECT t.*, p.nama as nama_produk, u.nama as nama_pembeli 
                      FROM transaksi t 
                      JOIN produk p ON t.id_produk = p.id 
                      JOIN users u ON t.id_pembeli = u.id 
                      WHERE p.id_penjual = ? 
                      ORDER BY t.created_at DESC");
$stmt->execute([$_SESSION['user']]);
$transaksi = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual - Hasil Perkebunan</title>
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
        
        .btn-aksi {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 5px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .btn-edit {
            background-color: #2196F3;
            color: white;
        }
        
        .btn-hapus {
            background-color: #F44336;
            color: white;
        }
        
        .btn-tambah {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .transaksi-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .transaksi-table th, .transaksi-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .transaksi-table th {
            background-color: #4CAF50;
            color: white;
        }
        
        .transaksi-table tr:nth-child(even) {
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
        
        .btn-aksi-transaksi {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 5px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 0.9rem;
            color: white;
        }
        
        .btn-proses {
            background-color: #2196F3;
        }
        
        .btn-kirim {
            background-color: #673AB7;
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
            <h3>Menu Penjual</h3>
            <ul>
                <li><a href="#produk">Kelola Produk</a></li>
                <li><a href="#transaksi">Kelola Transaksi</a></li>
                <li><a href="profil.php">Profil Saya</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="welcome">
                <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h2>
                <p>Anda login sebagai Penjual</p>
            </div>
            
            <section id="produk">
                <h3>Produk Saya</h3>
                <a href="../produk/tambah.php" class="btn-tambah">Tambah Produk Baru</a>
                
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
                            <div class="produk-aksi">
                                <a href="../produk/edit.php?id=<?php echo $item['id']; ?>" class="btn-aksi btn-edit">Edit</a>
                                <a href="../produk/hapus.php?id=<?php echo $item['id']; ?>" class="btn-aksi btn-hapus" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <section id="transaksi">
                <h3>Transaksi Produk Saya</h3>
                
                <table class="transaksi-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Pembeli</th>
                            <th>Jumlah</th>
                            <th>Total</th>
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
                            <td><?php echo htmlspecialchars($item['nama_pembeli']); ?></td>
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
                                    <span>Menunggu pembayaran</span>
                                <?php elseif ($item['status'] == 'diproses'): ?>
                                    <a href="../transaksi/proses_penjual.php?id=<?php echo $item['id']; ?>&status=dikirim" class="btn-aksi-transaksi btn-kirim">Kirim</a>
                                <?php elseif ($item['status'] == 'dikirim'): ?>
                                    <span>Menunggu konfirmasi penerimaan</span>
                                <?php elseif ($item['status'] == 'selesai'): ?>
                                    <span>Transaksi selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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