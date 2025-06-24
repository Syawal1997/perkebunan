<?php
require_once '../includes/auth-check.php';
require_once '../includes/header.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../member/dashboard.php");
    exit();
}

// Hitung total produk
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$totalProducts = $stmt->fetchColumn();

// Hitung total transaksi
$stmt = $pdo->query("SELECT COUNT(*) FROM transactions");
$totalTransactions = $stmt->fetchColumn();

// Hitung total pendapatan
$stmt = $pdo->query("SELECT SUM(total_price) FROM transactions WHERE status = 'completed'");
$totalRevenue = $stmt->fetchColumn();
?>

<div class="dashboard">
    <div class="sidebar">
        <div class="profile">
            <img src="../assets/images/default-profile.png" alt="Profile">
            <h3><?php echo $_SESSION['name']; ?></h3>
            <p>Admin</p>
        </div>
        
        <nav>
            <ul>
                <li class="active"><a href="dashboard.php">Dashboard</a></li>
                <li><a href="products/">Produk</a></li>
                <li><a href="transactions/">Transaksi</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="main-content">
        <h1>Dashboard Admin</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Produk</h3>
                <p><?php echo $totalProducts; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Total Transaksi</h3>
                <p><?php echo $totalTransactions; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Total Pendapatan</h3>
                <p>Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></p>
            </div>
        </div>
        
        <div class="recent-transactions">
            <h2>Transaksi Terbaru</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 5");
                    while ($transaction = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<tr>';
                        echo '<td>'.$transaction['id'].'</td>';
                        echo '<td>'.date('d M Y', strtotime($transaction['created_at'])).'</td>';
                        echo '<td>Rp '.number_format($transaction['total_price'], 0, ',', '.').'</td>';
                        echo '<td><span class="status '.$transaction['status'].'">'.ucfirst($transaction['status']).'</span></td>';
                        echo '<td><a href="transactions/detail.php?id='.$transaction['id'].'" class="btn-small">Detail</a></td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>