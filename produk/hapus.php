<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'penjual') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../dashboard/penjual.php");
    exit();
}

$produk_id = $_GET['id'];

// Ambil data produk untuk memastikan produk milik penjual yang login
$stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ? AND id_penjual = ?");
$stmt->execute([$produk_id, $_SESSION['user']]);
$produk = $stmt->fetch();

if ($produk) {
    // Hapus gambar jika ada
    if ($produk['gambar']) {
        unlink("../uploads/" . $produk['gambar']);
    }
    
    // Hapus produk dari database
    $stmt = $pdo->prepare("DELETE FROM produk WHERE id = ?");
    $stmt->execute([$produk_id]);
}

header("Location: ../dashboard/penjual.php");
exit();
?>