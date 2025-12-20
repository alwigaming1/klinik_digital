<?php
// --- ROUTER VERCEL ---

// Ambil URL yang diminta browser
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ATURAN PENTING:
// Jika file yang diminta browser (misal: login.php, style.css, logo.png) BENAR-BENAR ADA,
// maka kembalikan "false". Ini memberi tahu Vercel untuk memproses file itu secara langsung.
if ($request_uri !== '/' && file_exists(__DIR__ . $request_uri)) {
    return false;
}

// --- LOGIKA HALAMAN UTAMA (ROOT /) ---
// Kode di bawah ini hanya jalan jika user membuka halaman utama (/) saja.

include 'config.php'; 

// Cek status login
if (isset($_SESSION['status']) && $_SESSION['status'] == 'login') {
    // Jika sudah login, arahkan sesuai role
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: transaksi.php");
    }
} else {
    // Jika belum login, arahkan ke login
    header("Location: login.php");
}
exit;
?>
