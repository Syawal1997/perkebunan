<?php
require_once __DIR__ . '/../../includes/auth-check.php';
if ($userRole !== 'admin') {
    header('Location: /perkebunan/member/dashboard.php');
    exit();
}

$pageTitle = "Tambah Produk";
require_once __DIR__ . '/../../includes/header.php';

$categories = ['Sayuran', 'Buah', 'Biji-bijian', 'Rempah', 'Lainnya'];

// Proses form tambah produk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $category = $_POST['category'] ?? '';
    
    // Validasi
    $errors = [];
    if (empty($name)) $errors[] = 'Nama produk wajib diisi';
    if (empty($price) || $price <= 0) $errors[] = 'Harga harus lebih dari 0';
    if (empty($stock) || $stock < 0) $errors[] = 'Stok tidak valid';
    
    // Proses upload gambar
    $imageName = '';
    if (isset($_FILES['image']['name']) && !empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/../../../uploads/products/';
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExt, $allowedExt)) {
            $errors[] = 'Hanya file JPG, JPEG, PNG, atau GIF yang diizinkan';
        } elseif ($_FILES['image']['size'] > 2000000) {
            $errors[] = 'Ukuran file terlalu besar. Maksimal 2MB';
        } else {
            $imageName = 'product_' . time() . '.' . $fileExt;
            $targetPath = $uploadDir . $imageName;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $errors[] = 'Gagal mengupload gambar';
                $imageName = '';
            }
        }
    }
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products (user_id, name, description, price, stock, image, category)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $name,
                $description,
                $price,
                $stock,
                $imageName,
                $category
            ]);
            
            $_SESSION['success'] = 'Produk berhasil ditambahkan';
            header('Location: index.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = 'Gagal menyimpan produk: ' . $e->getMessage();
        }
    }
}
?>

<div class="product-form">
    <h1>Tambah Produk Baru</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="add.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Nama Produk</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="price">Harga (Rp)</label>
                <input type="number" id="price" name="price" min="0" step="100" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stok</label>
                <input type="number" id="stock" name="stock" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="category">Kategori</label>
                <select id="category" name="category" required>
                    <option value="">Pilih Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="image">Gambar Produk</label>
            <input type="file" id="image" name="image" accept="image/*">
            <small>Format: JPG, JPEG, PNG, atau GIF (maks. 2MB)</small>
        </div>
        
        <button type="submit" class="btn btn-primary">Simpan Produk</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>