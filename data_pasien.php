<?php 
include 'config.php'; 
cek_login(); 
if($_SESSION['role'] != 'admin') { header("Location: transaksi.php"); exit; }

// --- LOGIC HAPUS (SIMULASI) ---
if(isset($_GET['hapus'])){
    $id_hapus = $_GET['hapus'];
    // mysqli_query($koneksi, "DELETE FROM pasien WHERE id='$id_hapus'");
    echo "<script>alert('Simulasi: Data Pasien ID $id_hapus berhasil dihapus!'); window.location='data_pasien.php';</script>";
    exit;
}

// --- LOGIC TAMBAH/EDIT DATA (SIMULASI) ---
$msg_form = "";
if(isset($_POST['simpan_pasien'])){
    $rm = mysqli_real_escape_string($koneksi, $_POST['no_rekam_medis']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $jk = $_POST['jenis_kelamin'];
    $tgl_lahir = $_POST['tanggal_lahir'];
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $telp = $_POST['no_telepon'];
    $id_edit = $_POST['id_edit'];

    if($id_edit == ''){
        // LOGIC TAMBAH BARU
        // mysqli_query($koneksi, "INSERT INTO pasien VALUES (NULL, '$rm', '$nama', '$jk', '$tgl_lahir', '$alamat', '$telp')");
        $msg_form = "<p class='bg-green-100 text-green-600 p-3 rounded text-center text-sm mb-4'>Simulasi: Pasien baru berhasil ditambahkan!</p>";
    } else {
        // LOGIC UPDATE
        // mysqli_query($koneksi, "UPDATE pasien SET no_rekam_medis='$rm', nama_lengkap='$nama', jenis_kelamin='$jk', tanggal_lahir='$tgl_lahir', alamat='$alamat', no_telepon='$telp' WHERE id='$id_edit'");
        $msg_form = "<p class='bg-green-100 text-green-600 p-3 rounded text-center text-sm mb-4'>Simulasi: Data Pasien ID $id_edit berhasil diupdate!</p>";
    }
}

// Query untuk menampilkan data
$query = mysqli_query($koneksi, "SELECT * FROM pasien ORDER BY nama_lengkap ASC");

// Fungsi untuk mengambil data pasien jika ada permintaan edit
$data_edit = [];
if(isset($_GET['edit'])){
    $id_edit_get = $_GET['edit'];
    $res = mysqli_query($koneksi, "SELECT * FROM pasien WHERE id='$id_edit_get'");
    $data_edit = mysqli_fetch_assoc($res);
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Data Pasien - <?= $conf['app_name'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-[#F3F4F6] text-slate-800" 
    x-data="{ 
        isSidebarOpen: false,
        openModal: <?= isset($_GET['edit']) ? 'true' : 'false' ?>, 
        editId: '<?= $data_edit['id'] ?? '' ?>', 
        editRM: '<?= $data_edit['no_rekam_medis'] ?? '' ?>',
        editNama: '<?= $data_edit['nama_lengkap'] ?? '' ?>',
        editJK: '<?= $data_edit['jenis_kelamin'] ?? 'Laki-laki' ?>',
        editTglLahir: '<?= $data_edit['tanggal_lahir'] ?? '' ?>',
        editAlamat: '<?= $data_edit['alamat'] ?? '' ?>',
        editTelp: '<?= $data_edit['no_telepon'] ?? '' ?>'
    }"
    x-init="() => {
        if (window.innerWidth >= 1024) { isSidebarOpen = true; }
        window.addEventListener('resize', () => { if (window.innerWidth >= 1024) { isSidebarOpen = true; } });
    }">

    <?php include 'sidebar.php'; ?>

    <div class="lg:ml-64 p-4 md:p-8 pt-14 lg:pt-8 min-h-screen">
        
        <div class="flex justify-between items-center mb-6 pt-10 lg:pt-0">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Kelola Data Pasien</h1>
                <p class="text-gray-500 text-sm">Manajemen data riwayat rekam medis pasien klinik.</p>
            </div>
            
            <button @click="openModal = true; editId=''; editRM=''; editNama=''; editJK='Laki-laki'; editTglLahir=''; editAlamat=''; editTelp=''" class="bg-<?= $conf['color'] ?>-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-<?= $conf['color'] ?>-700 transition shadow-lg flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i> Tambah Pasien
            </button>
        </div>

        <?= $msg_form ?>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4 bg-<?= $conf['color'] ?>-50 border-b border-<?= $conf['color'] ?>-100 flex justify-between items-center">
                <form class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" placeholder="Cari Pasien..." class="pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-<?= $conf['color'] ?>-200 text-sm">
                </form>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-bold">
                        <tr>
                            <th class="p-4 border-b">No</th>
                            <th class="p-4 border-b">No RM</th>
                            <th class="p-4 border-b">Nama Pasien</th>
                            <th class="p-4 border-b">JK</th>
                            <th class="p-4 border-b">Tgl Lahir</th>
                            <th class="p-4 border-b">No Telp</th>
                            <th class="p-4 border-b text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600">
                        <?php $no = 1; while($d = mysqli_fetch_array($query)): ?>
                        <tr class="hover:bg-<?= $conf['color'] ?>-50/30 transition border-b">
                            <td class="p-4 font-bold"><?= $no++ ?></td>
                            <td class="p-4 font-semibold text-<?= $conf['color'] ?>-600"><?= $d['no_rekam_medis'] ?></td>
                            <td class="p-4"><?= $d['nama_lengkap'] ?></td>
                            <td class="p-4"><?= $d['jenis_kelamin'][0] ?></td>
                            <td class="p-4"><?= date('d-m-Y', strtotime($d['tanggal_lahir'])) ?></td>
                            <td class="p-4"><?= $d['no_telepon'] ?></td>
                            <td class="p-4 text-center">
                                <a href="?edit=<?= $d['id'] ?>" class="text-blue-500 hover:text-blue-700 mx-1"><i class="fa-solid fa-pen-to-square"></i></a>
                                <a href="?hapus=<?= $d['id'] ?>" onclick="return confirm('Yakin hapus data pasien ini?')" class="text-red-500 hover:text-red-700 mx-1"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t flex justify-end">
                <p class="text-sm text-gray-500">Total data: <?= mysqli_num_rows($query) ?></p>
            </div>
        </div>
    </div>

    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[99]" x-show="openModal" x-transition.opacity>
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-xl mx-auto mt-10" @click.away="openModal = false; window.location='data_pasien.php'">
            <h3 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2" x-text="editId ? 'Edit Data Pasien: ' + editNama : 'Registrasi Pasien Baru'"></h3>
            
            <form method="POST">
                <input type="hidden" name="id_edit" :value="editId">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">No. Rekam Medis (RM)</label>
                        <input type="text" name="no_rekam_medis" x-model="editRM" class="w-full border rounded p-2.5 bg-gray-50 focus:ring-<?= $conf['color'] ?>-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" x-model="editNama" class="w-full border rounded p-2.5 focus:ring-<?= $conf['color'] ?>-500" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" x-model="editJK" class="w-full border rounded p-2.5 focus:ring-<?= $conf['color'] ?>-500">
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" x-model="editTglLahir" class="w-full border rounded p-2.5 focus:ring-<?= $conf['color'] ?>-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">No. Telepon</label>
                        <input type="text" name="no_telepon" x-model="editTelp" class="w-full border rounded p-2.5 focus:ring-<?= $conf['color'] ?>-500" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Alamat</label>
                    <textarea name="alamat" x-model="editAlamat" class="w-full border rounded p-2.5 focus:ring-<?= $conf['color'] ?>-500" rows="2" required></textarea>
                </div>


                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="openModal = false; window.location='data_pasien.php'" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">Batal</button>
                    <button type="submit" name="simpan_pasien" class="bg-<?= $conf['color'] ?>-600 text-white px-4 py-2 rounded-lg hover:bg-<?= $conf['color'] ?>-700 transition">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>