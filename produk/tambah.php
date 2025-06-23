<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'penjual') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $kategori = $_POST['kategori'];
    $id_penjual = $_SESSION['user'];
    
    // Proses upload gambar
    $gambar = null;
    if (isset($_FILES['gambar']) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["gambar"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["gambar"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $error = "File bukan gambar.";
            $uploadOk = 0;
        }
        
        // Check file size
        if ($_FILES["gambar"]["size"] > 5000000) {
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
            
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $gambar = $new_filename;
            } else {
                $error = "Maaf, terjadi kesalahan saat upload file.";
            }
        }
    }
    
    if (!isset($error)) {
        // Insert produk baru
        $stmt = $pdo->prepare("INSERT INTO produk (id_penjual, nama, deskripsi, harga, stok, gambar, kategori) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_penjual, $nama, $deskripsi, $harga, $stok, $gambar, $kategori]);
        
        header("Location: ../dashboard/penjual.php");
        exit();
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
    <style>
        .tambah-produk-container {
            max-width: 600px;
            margin: 5rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .tambah-produk-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #4CAF50;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .form-group textarea {
            height: 100px;
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

    <div class="tambah-produk-container">
        <h2>Tambah Produk Baru</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama">Nama Produk</label>
                <input type="text" id="nama" name="nama" required>
            </div>
            
            <div class="form-group">
                <label for="deskripsi">Deskripsi Produk</label>
                <textarea id="deskripsi" name="deskripsi" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="harga">Harga per kg (Rp)</label>
                <input type="number" id="harga" name="harga" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="stok">Stok (kg)</label>
                <input type="number" id="stok" name="stok" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori" required>
                    <option value="">Pilih Kategori</option>
                    <option value="kopi">Kopi</option>
                    <option value="teh">Teh</option>
                    <option value="sawit">Kelapa Sawit</option>
                    <option value="karet">Karet</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="gambar">Gambar Produk (Opsional)</label>
                <input type="file" id="gambar" name="gambar" accept="image/*">
            </div>
            
            <button type="submit" class="btn-submit">Tambah Produk</button>
        </form>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2023 Hasil Perkebunan Online. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>