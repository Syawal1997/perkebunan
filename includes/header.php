<?php
// Start session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<header>
    <div class="container">
        <div class="logo">Hasil Perkebunan</div>
        <nav>
            <ul>
                <li><a href="../index.php">Beranda</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] == 'penjual'): ?>
                        <li><a href="../dashboard/penjual.php">Dashboard Penjual</a></li>
                    <?php else: ?>
                        <li><a href="../dashboard/pembeli.php">Dashboard Pembeli</a></li>
                    <?php endif; ?>
                    <li><a href="../includes/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="../login.php">Login</a></li>
                    <li><a href="../register.php">Daftar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>