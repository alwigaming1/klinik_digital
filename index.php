<?php
// Sertakan config.php untuk memulai session
include 'config.php'; 

// Jika pengguna sudah login, arahkan ke dashboard/transaksi sesuai role
if (isset($_SESSION['status'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: transaksi.php");
    }
} else {
    // Jika pengguna belum login, arahkan ke halaman login.php
    header("Location: login.php");
}

exit;
?>