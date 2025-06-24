<?php
require_once __DIR__ . '/../../../includes/auth-check.php';
if ($userRole !== 'member') {
    header('Location: /perkebunan/admin/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['transaction_id'])) {
    header('Location: /perkebunan/member/dashboard.php');
    exit();
}

$transactionId = (int)$_POST['transaction_id'];
$paymentMethod = $_POST['payment_method'] ?? '';

// Verifikasi transaksi milik user
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
$stmt->execute([$transactionId, $userId]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    header('Location: /perkebunan/member/dashboard.php');
    exit();
}

$pageTitle = "Konfirmasi Pembayaran";
require_once __DIR__ . '/../../../includes/header.php';

// Proses upload bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    if (!empty($_FILES['payment_proof']['name'])) {
        $uploadDir = __DIR__ . '/../../../uploads/payments/';
        $fileName = 'payment_' . $transactionId . '_' . time() . '_' . basename($_FILES['payment_proof']['name']);
        $targetPath = $uploadDir . $fileName;
        
        // Cek ekstensi file
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $fileExtension = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            $_SESSION['error'] = 'Hanya file JPG, JPEG, PNG, atau PDF yang diizinkan.';
        } elseif ($_FILES['payment_proof']['size'] > 5000000) {
            $_SESSION['error'] = 'Ukuran file terlalu besar. Maksimal 5MB.';
        } elseif (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetPath)) {
            // Update transaksi
            $stmt = $pdo->prepare("
                UPDATE transactions 
                SET payment_method = ?, payment_proof = ?, status = 'paid' 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([
                $paymentMethod,
                $fileName,
                $transactionId,
                $userId
            ]);
            
            $_SESSION['success'] = 'Bukti pembayaran berhasil diupload. Pesanan Anda sedang diproses.';
            header('Location: /perkebunan/member/transactions/detail.php?id=' . $transactionId);
            exit();
        } else {
            $_SESSION['error'] = 'Terjadi kesalahan saat mengupload file.';
        }
    } else {
        $_SESSION['error'] = 'Harap upload bukti pembayaran.';
    }
    
    header('Location: confirm.php?transaction_id=' . $transactionId);
    exit();
}
?>

<div class="payment-confirmation">
    <h1>Konfirmasi Pembayaran</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="transaction-info">
        <h3>Informasi Transaksi</h3>
        <p><strong>ID Transaksi:</strong> #<?php echo $transaction['id']; ?></p>
        <p><strong>Total:</strong> Rp <?php echo number_format($transaction['total'], 0, ',', '.'); ?></p>
        <p><strong>Metode Pembayaran:</strong> 
            <?php 
            $paymentMethods = [
                'bank_transfer' => 'Transfer Bank',
                'credit_card' => 'Kartu Kredit',
                'e_wallet' => 'E-Wallet',
                'cash_on_delivery' => 'Bayar di Tempat'
            ];
            echo $paymentMethods[$paymentMethod]; 
            ?>
        </p>
    </div>
    
    <form action="confirm.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="transaction_id" value="<?php echo $transactionId; ?>">
        <input type="hidden" name="payment_method" value="<?php echo $paymentMethod; ?>">
        
        <div class="form-group">
            <label for="payment_proof">Upload Bukti Pembayaran</label>
            <input type="file" id="payment_proof" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required>
            <small>Format: JPG, JPEG, PNG, atau PDF (maks. 5MB)</small>
        </div>
        
        <button type="submit" name="confirm_payment" class="btn btn-primary">Konfirmasi Pembayaran</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>