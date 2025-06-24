CREATE DATABASE IF NOT EXISTS perkebunan_db;

USE perkebunan_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('penjual', 'pembeli') NOT NULL,
    alamat TEXT,
    no_hp VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_penjual INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(10,2) NOT NULL,
    stok INT NOT NULL,
    kategori VARCHAR(50),
    gambar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_penjual) REFERENCES users(id)
);

CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pembeli INT NOT NULL,
    id_produk INT NOT NULL,
    jumlah INT NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    status ENUM('menunggu pembayaran', 'diproses', 'dikirim', 'selesai', 'dibatalkan') DEFAULT 'menunggu pembayaran',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pembeli) REFERENCES users(id),
    FOREIGN KEY (id_produk) REFERENCES produk(id)
);

CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    metode ENUM('transfer bank', 'e-wallet', 'cod'),
    jumlah DECIMAL(10,2) NOT NULL,
    status ENUM('menunggu', 'dikonfirmasi', 'ditolak') DEFAULT 'menunggu',
    bukti_pembayaran VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id)
);