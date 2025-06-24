<?php
// Konfigurasi Database SQLite
$databaseFile = __DIR__ . '/perkebunan.db';

try {
    // Membuat koneksi ke database SQLite
    $pdo = new PDO("sqlite:" . $databaseFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Membuat tabel-tabel jika belum ada
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT CHECK(role IN ('pembeli', 'penjual')) NOT NULL,
            alamat TEXT NOT NULL,
            telepon TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS produk (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_penjual INTEGER NOT NULL,
            nama TEXT NOT NULL,
            deskripsi TEXT NOT NULL,
            harga REAL NOT NULL,
            stok INTEGER NOT NULL,
            gambar TEXT,
            kategori TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_penjual) REFERENCES users(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS transaksi (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_pembeli INTEGER NOT NULL,
            id_produk INTEGER NOT NULL,
            jumlah INTEGER NOT NULL,
            total_harga REAL NOT NULL,
            status TEXT CHECK(status IN ('menunggu_pembayaran', 'diproses', 'dikirim', 'selesai', 'dibatalkan')) DEFAULT 'menunggu_pembayaran',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_pembeli) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (id_produk) REFERENCES produk(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS pembayaran (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_transaksi INTEGER NOT NULL,
            metode TEXT CHECK(metode IN ('transfer_bank', 'e_wallet', 'cod')) NOT NULL,
            jumlah REAL NOT NULL,
            bukti_pembayaran TEXT,
            status TEXT CHECK(status IN ('menunggu_konfirmasi', 'diterima', 'ditolak')) DEFAULT 'menunggu_konfirmasi',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_transaksi) REFERENCES transaksi(id) ON DELETE CASCADE
        );
    ");
    
    // Memeriksa apakah database kosong dan menambahkan data contoh
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    
    if ($userCount == 0) {
        // Data contoh users
        $pdo->exec("
            INSERT INTO users (nama, email, password, role, alamat, telepon) VALUES 
            ('Petani Joko', 'joko@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'penjual', 'Jl. Kebun Raya No. 123, Bandung', '081234567890'),
            ('Budi Pembeli', 'budi@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pembeli', 'Jl. Pasar Baru No. 45, Jakarta', '081298765432');
            
            INSERT INTO produk (id_penjual, nama, deskripsi, harga, stok, kategori) VALUES 
            (1, 'Kopi Arabica', 'Kopi berkualitas tinggi dari perkebunan di Jawa Barat', 120000, 50, 'kopi'),
            (1, 'Teh Hijau', 'Teh hijau organik dari perkebunan di Jawa Tengah', 85000, 30, 'teh'),
            (1, 'Kelapa Sawit', 'Kelapa sawit segar langsung dari perkebunan', 10000, 200, 'sawit');
        ");
    }
    
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi bantuan untuk hashing password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Fungsi untuk verifikasi password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>
