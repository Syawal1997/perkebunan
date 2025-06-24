<?php
session_start();
include '../database/config.php';
include '../includes/auth.php';

// Pastikan hanya penjual yang bisa akses
if ($_SESSION['role'] != 'penjual') {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $kategori = $_POST['kategori'];
    $id_penjual = $_SESSION['user_id'];
    
    // Handle file upload
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['gambar']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($ext), $allowed)) {
            $gambar = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['gambar']['tmp_name'], '../images/produk/' . $gambar);
        } else {
            $error = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
        }
    }
    
    if (empty($error)) {
        $query = "INSERT INTO produk (id_penjual, nama, deskripsi, harga, stok, kategori, gambar) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issdiss", $id_penjual, $nama, $deskripsi, $harga, $stok, $kategori, $gambar);
        
        if ($stmt->execute()) {
            $success = "Produk berhasil ditambahkan";
            header("Refresh: 2; url=list.php");
        } else {
            $error = "Gagal menambahkan produk. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Hasil Perkebunan</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard-menu">
            <ul>
                <li><a href="../dashboard/penjual.php">Dashboard</a></li>
                <li><a href="tambah.php">Tambah Produk</a></li>
                <li><a href="list.php">Kelola Produk</a></li>
                <li><a href="../transaksi/riwayat.php">Riwayat Transaksi</a></li>
                <li><a href="../includes/logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="form-container">
            <h2>Tambah Produk Baru</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert" style="color: green; margin-bottom: 15px;"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form action="tambah.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama">Nama Produk</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="harga">Harga (Rp)</label>
                    <input type="number" id="harga" name="harga" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="stok">Stok</label>
                    <input type="number" id="stok" name="stok" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori" required>
                        <option value="">Pilih Kategori</option>
                        <option value="buah">Buah-buahan</option>
                        <option value="sayur">Sayuran</option>
                        <option value="rempah">Rempah-rempah</option>
                        <option value="biji">Biji-bijian</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="gambar">Gambar Produk</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*" required>
                </div>
                
                <button type="submit" class="btn">Tambah Produk</button>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>