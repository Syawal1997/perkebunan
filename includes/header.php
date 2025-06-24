<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Cek apakah user sudah login
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $isLoggedIn ? $_SESSION['role'] : '';
$userName = $isLoggedIn ? $_SESSION['name'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Perkebunan - <?php echo $pageTitle ?? 'Beranda'; ?></title>
    <link rel="stylesheet" href="/perkebunan/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="/perkebunan/index.php">
                    <img src="/perkebunan/assets/images/logo.png" alt="Logo Perkebunan">
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="/perkebunan/index.php">Beranda</a></li>
                    <li><a href="/perkebunan/products.php">Produk</a></li>
                    <li><a href="/perkebunan/about.php">Tentang Kami</a></li>
                    <?php if ($isLoggedIn): ?>
                        <?php if ($userRole === 'admin'): ?>
                            <li><a href="/perkebunan/admin/dashboard.php">Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="/perkebunan/member/dashboard.php">Akun Saya</a></li>
                        <?php endif; ?>
                        <li><a href="/perkebunan/auth/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/perkebunan/auth/login.php">Login</a></li>
                        <li><a href="/perkebunan/auth/register.php">Daftar</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php if ($isLoggedIn && $userRole === 'member'): ?>
                <div class="cart-icon">
                    <a href="/perkebunan/member/cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <?php
                        // Hitung item di keranjang
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM carts WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $cartCount = $stmt->fetchColumn();
                        if ($cartCount > 0): ?>
                            <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <main class="container">