<?php
require_once __DIR__ . '/../../includes/auth-check.php';
if ($userRole !== 'member') {
    header('Location: /perkebunan/admin/dashboard.php');
    exit();
}

$pageTitle = "Dashboard Member";
require_once __DIR__ . '/../../includes/header.php';

// Hitung transaksi user
$stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
$stmt->execute([$userId]);
$totalTransactions = $stmt->fetchColumn();

// Transaksi terbaru
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$userId]);
$recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard">
    <h1>Selamat Datang, <?php echo htmlspecialchars($user['name']); ?></h1>
    
    <div class="user-info">
        <div class="info-card">
            <h3>Informasi Akun</h3>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Alamat:</strong> <?php echo htmlspecialchars($user['address'] ?? '-'); ?></p>
            <p><strong>Telepon:</strong> <?php echo htmlspecialchars($user['phone'] ?? '-'); ?></p>
        </div>
        
        <div class="info-card">
            <h3>Statistik</h3>
            <p><strong>Total Transaksi:</strong> <?php echo $totalTransactions; ?></p>
        </div>
    </div>
    
    <div class="recent-transactions">
        <h2>Transaksi Terbaru</h2>
        <?php if (empty($recentTransactions)): ?>
            <p>Belum ada transaksi.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
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
                            <a href="/perkebunan/member/transactions/detail.php?id=<?php echo $transaction['id']; ?>" class="btn btn-small">Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
            <a href="/perkebunan/products.php" class="btn btn-primary">Belanja Sekarang</a>
            <a href="/perkebunan/member/transactions/" class="btn btn-secondary">Lihat Semua Transaksi</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>