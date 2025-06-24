<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && verifyPassword($password, $user['password'])) {
                // Set session
                $_SESSION['user'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nama'] = $user['nama'];
                
                // Redirect berdasarkan role
                if ($user['role'] === 'penjual') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: member/dashboard.php");
                }
                exit();
            } else {
                $error = 'Email atau password salah!';
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
    <title>Login - Perkebunan</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2>Login</h2>
            
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-primary">Login</button>
            </form>
            
            <p class="auth-link">Belum punya akun? <a href="register.php">Daftar disini</a></p>
        </div>
    </div>
</body>
</html>
