<?php
include 'config.php';
cek_login();

// --- AMBIL DATA PROFIL PENGGUNA SAAT INI ---
// Asumsi: Anda memiliki tabel 'users' dan Anda mengambil data berdasarkan $_SESSION['user_id']
$user_id = $_SESSION['user_id'];
$q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$user_id'");
$data_user = mysqli_fetch_assoc($q_user);

// Inisialisasi variabel untuk pesan
$msg_pass = $msg_type = $msg_profil = $msg_profil_type = '';

// --- PROSES GANTI PASSWORD ---
if(isset($_POST['ganti_pass'])){
    $pass_baru = $_POST['pass_baru'];
    $pass_konf = $_POST['pass_konf'];
    $user_id_sekarang = $_SESSION['user_id']; 
    
    if ($pass_baru !== $pass_konf) {
        $msg_pass = "Konfirmasi password tidak cocok!";
        $msg_type = "error";
    } else {
        $pass_hash = md5($pass_baru);
        
        // Ganti baris di bawah ini dengan kode update ke database Anda
        // $update_q = mysqli_query($koneksi, "UPDATE users SET password='$pass_hash' WHERE id='$user_id_sekarang'");
        
        // SIMULASI BERHASIL
        $msg_pass = "Password berhasil diubah!";
        $msg_type = "success";
    }
}

// --- PROSES UPDATE PROFIL (Nama/Role/Lainnya) ---
if(isset($_POST['update_profil'])){
    $nama_baru = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $role_baru = mysqli_real_escape_string($koneksi, $_POST['role']);
    $user_id_sekarang = $_SESSION['user_id'];

    // Ganti baris di bawah ini dengan kode update ke database Anda
    // $update_profil_q = mysqli_query($koneksi, "UPDATE users SET nama_lengkap='$nama_baru', role='$role_baru' WHERE id='$user_id_sekarang'");
    
    // SIMULASI BERHASIL
    $_SESSION['nama'] = $nama_baru; // Simulasikan update session
    $data_user['nama_lengkap'] = $nama_baru; // Simulasikan data yang diambil dari DB
    $data_user['role'] = $role_baru;
    $msg_profil = "Profil berhasil diupdate!";
    $msg_profil_type = "success";
    
    // Jika berhasil, refresh halaman untuk update data_user
    // header("Location: profil.php"); exit;
}

// Ambil data user lagi setelah mungkin ada perubahan (untuk memastikan data tampil terbaru)
$q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$user_id'");
$data_user = mysqli_fetch_assoc($q_user);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profil & Password - <?= $conf['app_name'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>

<body class="bg-[#F3F4F6] text-slate-800" 
      x-data="{ isSidebarOpen: window.innerWidth >= 1024 }"
      x-init="() => {
          // Atur status awal sidebar berdasarkan lebar layar
          if (window.innerWidth >= 1024) { isSidebarOpen = true; }
          // Listener untuk menjaga status sidebar saat resize
          window.addEventListener('resize', () => { 
              if (window.innerWidth >= 1024) { 
                  isSidebarOpen = true; 
              }
          });
      }">

    <?php include 'sidebar.php'; ?>

    <div class="lg:ml-64 p-4 md:p-8 pt-20 lg:pt-8 min-h-screen">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Profil & Password</h1>
                <p class="text-gray-500 text-sm">Kelola informasi dan keamanan akun Anda.</p>
            </div>
        </div>

        <?php if (isset($msg_profil) && $msg_profil): ?>
            <div class="bg-<?= $msg_profil_type == 'success' ? 'green' : 'red' ?>-100 text-<?= $msg_profil_type == 'success' ? 'green' : 'red' ?>-700 p-4 rounded-xl mb-6 font-medium">
                <?= $msg_profil ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="font-bold text-xl text-slate-800 mb-6 border-b pb-3">Informasi Akun</h3>
                
                <form method="POST">
                    
                    <div class="mb-5">
                        <label class="block text-gray-600 text-sm font-semibold mb-2">Username</label>
                        <input type="text" value="<?= isset($data_user['username']) ? $data_user['username'] : 'user_example' ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-<?= $conf['color'] ?>-500 transition cursor-not-allowed" disabled>
                        <p class="text-xs text-gray-400 mt-1">Username tidak dapat diubah.</p>
                    </div>

                    <div class="mb-5">
                        <label for="nama_lengkap" class="block text-gray-600 text-sm font-semibold mb-2">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?= isset($data_user['nama_lengkap']) ? $data_user['nama_lengkap'] : $_SESSION['nama'] ?>" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-<?= $conf['color'] ?>-500 transition" required>
                    </div>

                    <div class="mb-6">
                        <label for="role" class="block text-gray-600 text-sm font-semibold mb-2">Role/Hak Akses</label>
                        <select name="role" id="role" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-<?= $conf['color'] ?>-500 transition" required>
                            <option value="admin" <?= (isset($data_user['role']) && $data_user['role'] == 'admin') || $_SESSION['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="kasir" <?= (isset($data_user['role']) && $data_user['role'] == 'kasir') || $_SESSION['role'] == 'kasir' ? 'selected' : '' ?>>Kasir</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="update_profil" class="w-full bg-<?= $conf['color'] ?>-600 text-white font-bold py-3 rounded-xl hover:bg-<?= $conf['color'] ?>-700 transition shadow-lg shadow-<?= $conf['color'] ?>-200">
                        <i class="fa-solid fa-save mr-2"></i> Simpan Profil
                    </button>
                </form>
            </div>


            <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="font-bold text-xl text-slate-800 mb-6 border-b pb-3">Ganti Password</h3>

                <?php if (isset($msg_pass) && $msg_pass): ?>
                    <div class="bg-<?= $msg_type == 'success' ? 'green' : 'red' ?>-100 text-<?= $msg_type == 'success' ? 'green' : 'red' ?>-700 p-4 rounded-xl mb-4 font-medium">
                        <?= $msg_pass ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    
                    <div class="mb-5">
                        <label for="pass_baru" class="block text-gray-600 text-sm font-semibold mb-2">Password Baru</label>
                        <input type="password" name="pass_baru" id="pass_baru" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 transition" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="pass_konf" class="block text-gray-600 text-sm font-semibold mb-2">Konfirmasi Password</label>
                        <input type="password" name="pass_konf" id="pass_konf" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 transition" required>
                    </div>
                    
                    <button type="submit" name="ganti_pass" class="w-full bg-red-600 text-white font-bold py-3 rounded-xl hover:bg-red-700 transition shadow-lg shadow-red-200">
                        <i class="fa-solid fa-lock mr-2"></i> Ganti Password
                    </button>
                </form>
            </div>
            
        </div>

    </div>

</body>
</html>