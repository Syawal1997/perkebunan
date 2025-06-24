<?php
require_once __DIR__ . '/../../../includes/auth-check.php';
if ($userRole !== 'member') {
    header('Location: /perkebunan/admin/dashboard.php');
    exit();
}

if (!isset($_GET['transaction_id'])) {
    header('Location: /perkebunan/member/dashboard.php');
    exit();
}

$transactionId = (int)$_GET['transaction_id'];

// Verifikasi transaksi milik user
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
$stmt->execute([$transactionId, $userId]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    header('Location: /perkebunan/member/dashboard.php');
    exit();
}

$pageTitle = "Metode Pembayaran";
require_once __DIR__ . '/../../../includes/header.php';

$paymentMethods = [
    'bank_transfer' => 'Transfer Bank',
    'credit_card' => 'Kartu Kredit',
    'e_wallet' => 'E-Wallet',
    'cash_on_delivery' => 'Bayar di Tempat'
];
?>

<div class="payment-method">
    <h1>Metode Pembayaran</h1>
    
    <div class="transaction-summary">
        <h3>Ringkasan Transaksi</h3>
        <p><strong>ID Transaksi:</strong> #<?php echo $transaction['id']; ?></p>
        <p><strong>Total:</strong> Rp <?php echo number_format($transaction['total'], 0, ',', '.'); ?></p>
    </div>
    
    <form action="confirm.php" method="POST">
        <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
        
        <div class="form-group">
            <h3>Pilih Metode Pembayaran</h3>
            <?php foreach ($paymentMethods as $value => $label): ?>
                <div class="payment-option">
                    <input type="radio" id="<?php echo $value; ?>" name="payment_method" value="<?php echo $value; ?>" required>
                    <label for="<?php echo $value; ?>"><?php echo $label; ?></label>
                </div>
            <?php endforeach; ?>
        </div>
        
        <button type="submit" class="btn btn-primary">Lanjutkan ke Pembayaran</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>