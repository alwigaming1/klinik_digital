<?php
// --- KONFIGURASI UTAMA ---
$conf = [
    "app_name"  => "SIMKLINIK",
    "app_ver"   => "V.1.0",
    "author"    => "SkripsiCode",
    "color"     => "blue", 
    
    // Database Config (Mengambil dari Environment Variable Vercel)
    "db_host"   => getenv('DB_HOST') ? getenv('DB_HOST') : '127.0.0.1',
    "db_port"   => getenv('DB_PORT') ? (int)getenv('DB_PORT') : 4000, // Default TiDB Port 4000
    "db_user"   => getenv('DB_USER') ? getenv('DB_USER') : 'root',
    "db_pass"   => getenv('DB_PASS') ? getenv('DB_PASS') : '',
    "db_name"   => getenv('DB_NAME') ? getenv('DB_NAME') : 'test'
];

// Inisialisasi MySQLi
$koneksi = mysqli_init();

// Set Timeout agar tidak loading selamanya jika gagal
mysqli_options($koneksi, MYSQLI_OPT_CONNECT_TIMEOUT, 10);

// TiDB Cloud mewajibkan koneksi SSL yang aman
// Kita set SSL ke NULL agar menggunakan CA bawaan sistem (biasanya cukup untuk TiDB)
mysqli_ssl_set($koneksi, NULL, NULL, NULL, NULL, NULL);

// Lakukan koneksi menggunakan real_connect dengan Flag SSL
$connected = mysqli_real_connect(
    $koneksi, 
    $conf['db_host'], 
    $conf['db_user'], 
    $conf['db_pass'], 
    $conf['db_name'], 
    $conf['db_port'], 
    NULL, 
    MYSQLI_CLIENT_SSL // Flag Penting untuk TiDB!
);

if (!$connected) { 
    // Tampilkan error connection untuk debugging (Hapus saat production live)
    die("Koneksi Database Gagal: " . mysqli_connect_error() . " (Errno: " . mysqli_connect_errno() . ")");
}

// Session Start
session_start();

function cek_login(){
    if(empty($_SESSION['status'])){
        header("location:login.php");
        exit;
    }
}
?>
