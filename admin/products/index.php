<?php
require_once __DIR__ . '/../../includes/auth-check.php';
if ($userRole !== 'admin') {
    header('Location: /perkebunan/member/dashboard.php');
    exit();
}

$pageTitle = "Kelola Produk";
require_once __DIR__ . '/../../includes/header.php';

// Ambil semua produk
$stmt = $pdo->query("
    SELECT p.*, u.name as seller_name 
    FROM products p 
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="products-admin">
    <div class="page-header">
        <h1>Kelola Produk</h1>
        <a href="add.php" class="btn btn-primary">Tambah Produk</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Gambar</th>
                <th>Nama Produk</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Penjual</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?php echo $product['id']; ?></td>
                <td>
                    <img src="/perkebunan/uploads/products/<?php echo $product['image'] ?? 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="50">
                </td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                <td><?php echo $product['stock']; ?></td>
                <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                <td><?php echo date('d M Y', strtotime($product['created_at'])); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-small">Edit</a>
                    <a href="delete.php?id=<?php echo $product['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>