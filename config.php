<?php
// --- CONFIG DATABASE VERCEL + TIDB ---
// Matikan display error jika sudah berhasil login nanti
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conf = [
    "app_name"  => "SIMKLINIK",
    "app_ver"   => "V.1.0",
    "author"    => "SkripsiCode",
    "color"     => "blue", 
    
    // Ambil dari Environment Variables Vercel
    "db_host"   => getenv('DB_HOST') ?: '127.0.0.1',
    "db_port"   => getenv('DB_PORT') ? (int)getenv('DB_PORT') : 4000,
    "db_user"   => getenv('DB_USER') ?: 'root',
    "db_pass"   => getenv('DB_PASS') ?: '',
    "db_name"   => getenv('DB_NAME') ?: 'test'
];

// Auto-Download Sertifikat SSL (CA) ke folder sementara Vercel
$ca_path = "/tmp/isrgrootx1.pem";
if (!file_exists($ca_path)) {
    $ca_content = file_get_contents("https://letsencrypt.org/certs/isrgrootx1.pem");
    if ($ca_content) file_put_contents($ca_path, $ca_content);
}

$koneksi = mysqli_init();
mysqli_options($koneksi, MYSQLI_OPT_CONNECT_TIMEOUT, 10);

// Setup SSL
if (file_exists($ca_path)) {
    mysqli_ssl_set($koneksi, NULL, NULL, $ca_path, NULL, NULL);
}

// Koneksi Real
$connected = @mysqli_real_connect(
    $koneksi, 
    $conf['db_host'], 
    $conf['db_user'], 
    $conf['db_pass'], 
    $conf['db_name'], 
    $conf['db_port'], 
    NULL, 
    MYSQLI_CLIENT_SSL
);

if (!$connected) {
    die("<h3>Koneksi Gagal:</h3> " . mysqli_connect_error());
}

session_start();

function cek_login(){
    if(empty($_SESSION['status'])){
        header("location:login.php");
        exit;
    }
}
?>
