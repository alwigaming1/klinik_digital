<?php 
include 'config.php'; 
cek_login(); 
if($_SESSION['role'] != 'admin') { header("Location: transaksi.php"); exit; }

// --- LOGIC HAPUS (SIMULASI) ---
if(isset($_GET['hapus'])){
    $id_hapus = $_GET['hapus'];
    // mysqli_query($koneksi, "DELETE FROM layanan WHERE id='$id_hapus'");
    echo "<script>alert('Simulasi: Data Layanan/Obat ID $id_hapus berhasil dihapus!'); window.location='data_layanan.php';</script>";
    exit;
}

// --- LOGIC TAMBAH/EDIT DATA (SIMULASI) ---
$msg_form = "";
if(isset($_POST['simpan_layanan'])){
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kategori = $_POST['kategori'];
    $tarif = $_POST['tarif'];
    $stok = $_POST['stok'];
    $id_edit = $_POST['id_edit'];

    if($id_edit == ''){
        // LOGIC TAMBAH BARU
        // mysqli_query($koneksi, "INSERT INTO layanan VALUES (NULL, '$nama', '$kategori', '$tarif', '$stok')");
        $msg_form = "<p class='bg-green-100 text-green-600 p-3 rounded text-center text-sm mb-4'>Simulasi: Layanan/Obat berhasil ditambahkan!</p>";
    } else {
        // LOGIC UPDATE
        // mysqli_query($koneksi, "UPDATE layanan SET nama='$nama', kategori='$kategori', tarif='$tarif', stok='$stok' WHERE id='$id_edit'");
        $msg_form = "<p class='bg-green-100 text-green-600 p-3 rounded text-center text-sm mb-4'>Simulasi: Layanan/Obat ID $id_edit berhasil diupdate!</p>";
    }
}

// Query untuk menampilkan data
$query = mysqli_query($koneksi, "SELECT * FROM layanan ORDER BY kategori, nama ASC");

// Fungsi untuk mengambil data layanan jika ada permintaan edit
$data_edit = [];
if(isset($_GET['edit'])){
    $id_edit_get = $_GET['edit'];
    $res = mysqli_query($koneksi, "SELECT * FROM layanan WHERE id='$id_edit_get'");
    $data_edit = mysqli_fetch_assoc($res);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Data Layanan & Obat - <?= $conf['app_name'] ?></title>
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
        editNama: '<?= $data_edit['nama'] ?? '' ?>', 
        editKategori: '<?= $data_edit['kategori'] ?? 'Tindakan' ?>', 
        editTarif: '<?= $data_edit['tarif'] ?? '' ?>', 
        editStok: '<?= $data_edit['stok'] ?? '' ?>' 
    }"
    x-init="() => {
        if (window.innerWidth >= 1024) { isSidebarOpen = true; }
        window.addEventListener('resize', () => { if (window.innerWidth >= 1024) { isSidebarOpen = true; } });
    }">

    <?php include 'sidebar.php'; ?>

    <div class="lg:ml-64 p-4 md:p-8 pt-14 lg:pt-8 min-h-screen">
        
        <div class="flex justify-between items-center mb-6 pt-10 lg:pt-0">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Kelola Data Layanan & Obat</h1>
                <p class="text-gray-500 text-sm">Manajemen daftar tindakan, tarif, dan stok obat klinik.</p>
            </div>
            
            <button @click="openModal = true; editId=''; editNama=''; editKategori='Tindakan'; editTarif=''; editStok=''" class="bg-<?= $conf['color'] ?>-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-<?= $conf['color'] ?>-700 transition shadow-lg flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Tambah Data
            </button>
        </div>

        <?= $msg_form ?>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4 bg-<?= $conf['color'] ?>-50 border-b border-<?= $conf['color'] ?>-100 flex justify-between items-center">
                <form class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" placeholder="Cari Layanan/Obat..." class="pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-<?= $conf['color'] ?>-200 text-sm">
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-bold">
                        <tr>
                            <th class="p-4 border-b">No</th>
                            <th class="p-4 border-b">Nama Layanan/Obat</th>
                            <th class="p-4 border-b">Kategori</th>
                            <th class="p-4 border-b">Tarif/Harga</th>
                            <th class="p-4 border-b">Stok Obat</th>
                            <th class="p-4 border-b text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600">
                        <?php $no = 1; while($d = mysqli_fetch_array($query)): ?>
                        <tr class="hover:bg-<?= $conf['color'] ?>-50/30 transition border-b">
                            <td class="p-4 font-bold"><?= $no++ ?></td>
                            <td class="p-4"><?= $d['nama'] ?></td>
                            <td class="p-4">
                                <span class="py-1 px-3 rounded-full text-xs font-bold 
                                    <?= $d['kategori'] == 'Tindakan' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600' ?>">
                                    <?= $d['kategori'] ?>
                                </span>
                            </td>
                            <td class="p-4 font-semibold">Rp <?= number_format($d['tarif']) ?></td>
                            <td class="p-4"><?= $d['kategori'] == 'Obat' ? $d['stok'] : 'N/A' ?></td>
                            <td class="p-4 text-center">
                                <a href="?edit=<?= $d['id'] ?>" class="text-blue-500 hover:text-blue-700 mx-1"><i class="fa-solid fa-pen-to-square"></i></a>
                                <a href="?hapus=<?= $d['id'] ?>" onclick="return confirm('Yakin hapus data ini?')" class="text-red-500 hover:text-red-700 mx-1"><i class="fa-solid fa-trash"></i></a>
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
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg mx-auto mt-20" @click.away="openModal = false; window.location='data_layanan.php'">
            <h3 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2" x-text="editId ? 'Edit Layanan/Obat' : 'Tambah Layanan/Obat Baru'"></h3>
            
            <form method="POST">
                <input type="hidden" name="id_edit" :value="editId">

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Kategori</label>
                    <select name="kategori" x-model="editKategori" class="w-full border rounded p-2.5 focus:ring-<?= $conf['color'] ?>-500">
                        <option value="Tindakan">Tindakan Medis</option>
                        <option value="Obat">Obat-obatan</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Nama Layanan/Obat</label>
                    <input type="text" name="nama" x-model="editNama" class="w-full border rounded p-2.5 focus:ring-<?= $conf['color'] ?>-500" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Tarif / Harga (Rp)</label>
                        <input type="number" name="tarif" x-model="editTarif" class="w-full border rounded p-2.5 focus:ring-<?= $conf['color'] ?>-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Stok (Khusus Obat)</label>
                        <input type="number" name="stok" x-model="editStok" class="w-full border rounded p-2.5 focus:ring-<?= $conf['color'] ?>-500">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="openModal = false; window.location='data_layanan.php'" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">Batal</button>
                    <button type="submit" name="simpan_layanan" class="bg-<?= $conf['color'] ?>-600 text-white px-4 py-2 rounded-lg hover:bg-<?= $conf['color'] ?>-700 transition">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>