<?php
include 'database/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    
    // Cek apakah email sudah terdaftar
    $check = "SELECT email FROM users WHERE email = ?";
    $stmt = $conn->prepare($check);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $error = "Email sudah terdaftar";
    } else {
        $query = "INSERT INTO users (nama, email, password, role, alamat, no_hp) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $nama, $email, $password, $role, $alamat, $no_hp);
        
        if ($stmt->execute()) {
            $success = "Pendaftaran berhasil. Silakan login.";
        } else {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Hasil Perkebunan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="form-container">
        <h2>Daftar Akun</h2>
        <?php if (isset($error)): ?>
            <div class="alert" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert" style="color: green; margin-bottom: 15px;"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="role">Daftar Sebagai</label>
                <select id="role" name="role" required>
                    <option value="">Pilih Role</option>
                    <option value="pembeli">Pembeli</option>
                    <option value="penjual">Penjual</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="no_hp">Nomor HP</label>
                <input type="text" id="no_hp" name="no_hp" required>
            </div>
            
            <button type="submit" class="btn">Daftar</button>
        </form>
        
        <p style="margin-top: 15px;">Sudah punya akun? <a href="login.php">Login disini</a></p>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>