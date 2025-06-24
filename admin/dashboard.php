<?php
require_once __DIR__ . '/../../includes/auth-check.php';
if ($userRole !== 'admin') {
    header('Location: /perkebunan/member/dashboard.php');
    exit();
}

$pageTitle = "Dashboard Admin";
require_once __DIR__ . '/../../includes/header.php';

// Hitung total produk
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$totalProducts = $stmt->fetchColumn();

// Hitung total transaksi
$stmt = $pdo->query("SELECT COUNT(*) FROM transactions");
$totalTransactions = $stmt->fetchColumn();

// Hitung total pendapatan
$stmt = $pdo->query("SELECT SUM(total) FROM transactions WHERE status = 'completed'");
$totalRevenue = $stmt->fetchColumn() ?? 0;

// Transaksi terbaru
$stmt = $pdo->query("SELECT t.*, u.name as user_name FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY created_at DESC LIMIT 5");
$recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard">
    <h1>Dashboard Admin</h1>
    
    <div class="stats">
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
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentTransactions as $transaction): ?>
                <tr>
                    <td><?php echo $transaction['id']; ?></td>
                    <td><?php echo $transaction['user_name']; ?></td>
                    <td>Rp <?php echo number_format($transaction['total'], 0, ',', '.'); ?></td>
                    <td>
                        <span class="status-<?php echo $transaction['status']; ?>">
                            <?php 
                            $statusLabels = [
                                'pending' => 'Menunggu',
                                'paid' => 'Dibayar',
                                'cancelled' => 'Dibatalkan',
                                'completed' => 'Selesai'
                            ];
                            echo $statusLabels[$transaction['status']]; 
                            ?>
                        </span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($transaction['created_at'])); ?></td>
                    <td>
                        <a href="/perkebunan/admin/transactions/detail.php?id=<?php echo $transaction['id']; ?>" class="btn btn-small">Detail</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
            <a href="/perkebunan/admin/products/add.php" class="btn btn-primary">Tambah Produk</a>
            <a href="/perkebunan/admin/products/" class="btn btn-secondary">Kelola Produk</a>
            <a href="/perkebunan/admin/transactions/" class="btn btn-secondary">Lihat Semua Transaksi</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>