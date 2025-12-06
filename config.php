<?php
// --- KONFIGURASI UTAMA ---
$conf = [
    "app_name"  => "SIMKLINIK", // DIUBAH
    "app_ver"   => "V.1.0",
    "author"    => "SkripsiCode",
    "color"     => "blue", // DIUBAH: Menggunakan warna biru untuk nuansa medis
    
    // Database
    "db_host"   => "localhost",
    "db_user"   => "root",
    "db_pass"   => "",
    "db_name"   => "db_simklinik" // DIUBAH
];

$koneksi = mysqli_connect($conf['db_host'], $conf['db_user'], $conf['db_pass'], $conf['db_name']);
if (!$koneksi) { die("Koneksi Gagal: " . mysqli_connect_error()); }

session_start();

// Fungsi Cek Login
function cek_login(){
    if(empty($_SESSION['status'])){
        header("location:login.php");
        exit;
    }
}
?>