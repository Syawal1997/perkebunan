-- Buat database
CREATE DATABASE IF NOT EXISTS perkebunan_db;
USE perkebunan_db;

-- Tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('pembeli', 'penjual') NOT NULL,
    alamat TEXT NOT NULL,
    telepon VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel produk
CREATE TABLE produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_penjual INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    stok INT NOT NULL,
    gambar VARCHAR(255),
    kategori VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_penjual) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel transaksi
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pembeli INT NOT NULL,
    id_produk INT NOT NULL,
    jumlah INT NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    status ENUM('menunggu_pembayaran', 'diproses', 'dikirim', 'selesai', 'dibatalkan') DEFAULT 'menunggu_pembayaran',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pembeli) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES produk(id) ON DELETE CASCADE
);

-- Tabel pembayaran
CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    metode ENUM('transfer_bank', 'e_wallet', 'cod') NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    bukti_pembayaran VARCHAR(255),
    status ENUM('menunggu_konfirmasi', 'diterima', 'ditolak') DEFAULT 'menunggu_konfirmasi',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id) ON DELETE CASCADE
);

-- Data contoh
INSERT INTO users (nama, email, password, role, alamat, telepon) VALUES 
('Petani Joko', 'joko@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'penjual', 'Jl. Kebun Raya No. 123, Bandung', '081234567890'),
('Budi Pembeli', 'budi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pembeli', 'Jl. Pasar Baru No. 45, Jakarta', '081298765432');

INSERT INTO produk (id_penjual, nama, deskripsi, harga, stok, kategori) VALUES 
(1, 'Kopi Arabica', 'Kopi berkualitas tinggi dari perkebunan di Jawa Barat', 120000, 50, 'kopi'),
(1, 'Teh Hijau', 'Teh hijau organik dari perkebunan di Jawa Tengah', 85000, 30, 'teh'),
(1, 'Kelapa Sawit', 'Kelapa sawit segar langsung dari perkebunan', 10000, 200, 'sawit');