<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['telepon']);

    // Validasi input
    if (empty($nama) || empty($email) || empty($password) || empty($alamat) || empty($telepon)) {
        $error = 'Semua field harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        try {
            // Cek apakah email sudah terdaftar
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Email sudah terdaftar!';
            } else {
                // Hash password
                $hashedPassword = hashPassword($password);
                
                // Insert user baru
                $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role, alamat, telepon) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nama, $email, $hashedPassword, $role, $alamat, $telepon]);
                
                $success = 'Pendaftaran berhasil! Silakan login.';
                $_POST = array(); // Clear form
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Perkebunan</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2>Daftar Akun Baru</h2>
            
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Daftar Sebagai</label>
                    <select id="role" name="role" required>
                        <option value="pembeli" <?php echo ($_POST['role'] ?? '') === 'pembeli' ? 'selected' : ''; ?>>Pembeli</option>
                        <option value="penjual" <?php echo ($_POST['role'] ?? '') === 'penjual' ? 'selected' : ''; ?>>Penjual</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" required><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="telepon">Nomor Telepon</label>
                    <input type="text" id="telepon" name="telepon" value="<?php echo htmlspecialchars($_POST['telepon'] ?? ''); ?>" required>
                </div>
                
                <button type="submit" class="btn-primary">Daftar</button>
            </form>
            
            <p class="auth-link">Sudah punya akun? <a href="login.php">Login disini</a></p>
        </div>
    </div>
</body>
</html>
