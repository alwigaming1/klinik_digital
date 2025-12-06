<?php
include 'config.php';

// Cek apakah user sudah login, jika ya, arahkan ke dashboard
if(isset($_SESSION['status']) && $_SESSION['status'] == 'login'){
    if($_SESSION['role'] == 'admin'){
        header("Location: dashboard.php");
    } else {
        header("Location: transaksi.php");
    }
    exit;
}

$msg = "";
if(isset($_POST['login'])){
    $user = $_POST['username'];
    $pass = md5($_POST['password']); 

    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$user' AND password='$pass'");
    
    if(mysqli_num_rows($cek) > 0){
        $d = mysqli_fetch_array($cek);
        
        $_SESSION['status'] = "login";
        $_SESSION['user_id'] = $d['id'];
        $_SESSION['nama'] = $d['nama_lengkap'];
        $_SESSION['role'] = $d['role']; 

        if($d['role'] == 'admin'){
            header("Location: dashboard.php"); 
        } else {
            header("Location: transaksi.php"); 
        }
        exit;
    } else {
        $msg = "Username atau Password Salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login - <?= $conf['app_name'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { font-family: 'Poppins', sans-serif; } 
        /* Background yang lebih halus */
        .bg-medical {
            background-color: #f0f8ff; /* Warna biru muda */
            background-image: radial-gradient(#dbeafe 1px, transparent 1px);
            background-size: 20px 20px;
        }
    </style>
</head>
<body class="bg-medical text-slate-800 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md bg-white p-8 rounded-3xl shadow-2xl border border-gray-100/50 shadow-<?= $conf['color'] ?>-200/50 transform hover:scale-[1.01] transition duration-300">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-<?= $conf['color'] ?>-100 text-<?= $conf['color'] ?>-600 mb-4 shadow-lg">
                <i class="fa-solid fa-notes-medical text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Selamat Datang</h1>
            <p class="text-gray-500 text-sm mt-1">Sistem Informasi Manajemen Klinik</p>
        </div>

        <?php if(!empty($msg)) echo "<p class='bg-red-100 text-red-600 p-3 rounded-lg text-center text-sm mb-6'>$msg</p>"; ?>
        
        <form method="POST">
            <div class="mb-4 relative">
                <label class="block text-gray-600 text-xs font-bold mb-1 uppercase">Username</label>
                <i class="fa-solid fa-user absolute left-4 top-10 text-gray-400"></i>
                <input type="text" name="username" class="w-full px-10 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-<?= $conf['color'] ?>-500 transition" placeholder="Masukkan Username" required>
            </div>
            <div class="mb-6 relative">
                <label class="block text-gray-600 text-xs font-bold mb-1 uppercase">Password</label>
                <i class="fa-solid fa-lock absolute left-4 top-10 text-gray-400"></i>
                <input type="password" name="password" class="w-full px-10 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-<?= $conf['color'] ?>-500 transition" placeholder="Masukkan Password" required>
            </div>
            
            <button type="submit" name="login" class="w-full bg-<?= $conf['color'] ?>-600 text-white font-bold py-3 rounded-xl hover:bg-<?= $conf['color'] ?>-700 transition shadow-lg shadow-<?= $conf['color'] ?>-200/70 text-lg">
                <i class="fa-solid fa-sign-in-alt mr-2"></i> MASUK SISTEM
            </button>
        </form>
        
        <div class="mt-8 p-4 bg-gray-100 rounded-xl border border-gray-200">
            <p class="text-xs font-bold text-gray-700 mb-2 uppercase flex items-center gap-2"><i class="fa-solid fa-info-circle text-sm"></i> Akses Demo:</p>
            <div class="text-sm text-gray-600 space-y-1">
                <p><i class="fa-solid fa-user-tie text-blue-500 w-4"></i> <strong>Admin:</strong> `admin` / `123`</p>
                <p><i class="fa-solid fa-user-check text-green-500 w-4"></i> <strong>Kasir:</strong> `kasir` / `123`</p>
            </div>
        </div>
        
    </div>

</body>
</html>