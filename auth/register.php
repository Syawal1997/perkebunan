<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    // Validasi
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Semua field wajib diisi!';
    } elseif ($password !== $confirmPassword) {
        $error = 'Password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Cek apakah email sudah terdaftar
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Simpan ke database
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, address, phone) VALUES (?, ?, ?, 'member', ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword, $address, $phone]);
            
            // Redirect ke halaman login
            header('Location: login.php?registered=1');
            exit();
        }
    }
}

$pageTitle = "Daftar";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <h2>Daftar Akun</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" required>
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
            <label for="confirm_password">Konfirmasi Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <div class="form-group">
            <label for="address">Alamat</label>
            <textarea id="address" name="address"></textarea>
        </div>
        <div class="form-group">
            <label for="phone">Nomor Telepon</label>
            <input type="text" id="phone" name="phone">
        </div>
        <button type="submit" class="btn btn-primary">Daftar</button>
    </form>
    <p>Sudah punya akun? <a href="login.php">Login disini</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>