<?php
require_once __DIR__ . '/../../includes/auth-check.php';
if ($userRole !== 'member') {
    header('Location: /perkebunan/admin/dashboard.php');
    exit();
}

$pageTitle = "Keranjang Belanja";
require_once __DIR__ . '/../../includes/header.php';

// Ambil data keranjang
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.image, p.stock 
    FROM carts c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Proses update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cartId => $quantity) {
        $quantity = (int)$quantity;
        if ($quantity > 0) {
            $stmt = $pdo->prepare("UPDATE carts SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$quantity, $cartId, $userId]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
            $stmt->execute([$cartId, $userId]);
        }
    }
    header('Location: cart.php');
    exit();
}

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (empty($cartItems)) {
        $_SESSION['error'] = 'Keranjang belanja kosong!';
        header('Location: cart.php');
        exit();
    }
    
    // Cek stok
    foreach ($cartItems as $item) {
        if ($item['quantity'] > $item['stock']) {
            $_SESSION['error'] = 'Stok produk "' . $item['name'] . '" tidak mencukupi!';
            header('Location: cart.php');
            exit();
        }
    }
    
    // Mulai transaksi
    $pdo->beginTransaction();
    
    try {
        // Buat transaksi
        $stmt = $pdo->prepare("
            INSERT INTO transactions (user_id, total, status) 
            VALUES (?, ?, 'pending')
        ");
        $stmt->execute([$userId, $total]);
        $transactionId = $pdo->lastInsertId();
        
        // Tambahkan item transaksi
        foreach ($cartItems as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO transaction_items (transaction_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $transactionId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
            
            // Kurangi stok
            $stmt = $pdo->prepare("
                UPDATE products SET stock = stock - ? WHERE id = ?
            ");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Kosongkan keranjang
        $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        $pdo->commit();
        
        header('Location: /perkebunan/member/payments/method.php?transaction_id=' . $transactionId);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Terjadi kesalahan saat proses checkout: ' . $e->getMessage();
        header('Location: cart.php');
        exit();
    }
}
?>

<div class="cart-page">
    <h1>Keranjang Belanja</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <p>Keranjang belanja Anda kosong.</p>
            <a href="/perkebunan/products.php" class="btn btn-primary">Lanjutkan Belanja</a>
        </div>
    <?php else: ?>
        <form action="cart.php" method="POST">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="/perkebunan/uploads/products/<?php echo $item['image'] ?? 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" width="80">
                                <div>
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p>Stok: <?php echo $item['stock']; ?></p>
                                </div>
                            </div>
                        </td>
                        <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                        <td>
                            <input type="number" name="quantity[<?php echo $item['id']; ?>]" 
                                   value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>">
                        </td>
                        <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                        <td>
                            <a href="remove-from-cart.php?id=<?php echo $item['id']; ?>" class="btn btn-small btn-danger">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total:</strong></td>
                        <td colspan="2">Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="cart-actions">
                <button type="submit" name="update_cart" class="btn btn-secondary">Perbarui Keranjang</button>
                <button type="submit" name="checkout" class="btn btn-primary">Checkout</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>