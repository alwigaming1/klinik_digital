<?php 
include 'config.php'; 
cek_login(); 
if($_SESSION['role'] != 'admin') { header("Location: transaksi.php"); exit; }

// --- LOGIC HAPUS (SIMULASI) ---
if(isset($_GET['hapus'])){
    $id_hapus = $_GET['hapus'];
    // mysqli_query($koneksi, "DELETE FROM transaksi_klinik WHERE id='$id_hapus'");
    echo "<script>alert('Simulasi: Transaksi ID $id_hapus berhasil dihapus!'); window.location='data_transaksi.php';</script>";
    exit;
}

// Query untuk menampilkan data transaksi
$query = mysqli_query($koneksi, "
    SELECT 
        tk.*, p.nama_lengkap AS nama_pasien, u.nama_lengkap AS nama_kasir
    FROM 
        transaksi_klinik tk
    JOIN 
        pasien p ON tk.pasien_id = p.id
    JOIN 
        users u ON tk.user_id = u.id
    ORDER BY 
        tk.tanggal DESC
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Data Billing & Pembayaran - <?= $conf['app_name'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-[#F3F4F6] text-slate-800" 
    x-data="{ isSidebarOpen: false }"
    x-init="() => {
        if (window.innerWidth >= 1024) { isSidebarOpen = true; }
        window.addEventListener('resize', () => { if (window.innerWidth >= 1024) { isSidebarOpen = true; } });
    }">

    <?php include 'sidebar.php'; ?>

    <div class="lg:ml-64 p-4 md:p-8 pt-14 lg:pt-8 min-h-screen">
        
        <div class="flex justify-between items-center mb-6 pt-10 lg:pt-0">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Data Billing & Pembayaran</h1>
                <p class="text-gray-500 text-sm">Riwayat transaksi layanan dan obat klinik.</p>
            </div>
            
            <a href="transaksi.php" class="bg-green-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-green-700 transition shadow-lg flex items-center gap-2">
                <i class="fa-solid fa-cash-register"></i> Transaksi Baru
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4 bg-<?= $conf['color'] ?>-50 border-b border-<?= $conf['color'] ?>-100 flex justify-between items-center">
                <form class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" placeholder="Cari Invoice atau Pasien..." class="pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-<?= $conf['color'] ?>-200 text-sm">
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-bold">
                        <tr>
                            <th class="p-4 border-b">No</th>
                            <th class="p-4 border-b">Invoice</th>
                            <th class="p-4 border-b">Tanggal</th>
                            <th class="p-4 border-b">Pasien</th>
                            <th class="p-4 border-b">Potongan</th>
                            <th class="p-4 border-b">Total Bersih</th>
                            <th class="p-4 border-b">Kasir</th>
                            <th class="p-4 border-b text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600">
                        <?php $no = 1; while($d = mysqli_fetch_array($query)): ?>
                        <tr class="hover:bg-<?= $conf['color'] ?>-50/30 transition border-b">
                            <td class="p-4 font-bold"><?= $no++ ?></td>
                            <td class="p-4 font-semibold text-<?= $conf['color'] ?>-600"><?= $d['invoice'] ?></td>
                            <td class="p-4"><?= date('d/m/Y H:i', strtotime($d['tanggal'])) ?></td>
                            <td class="p-4"><?= $d['nama_pasien'] ?></td>
                            <td class="p-4">Rp <?= number_format($d['potongan']) ?></td>
                            <td class="p-4 font-extrabold text-green-600">Rp <?= number_format($d['total_bersih']) ?></td>
                            <td class="p-4"><?= $d['nama_kasir'] ?></td>
                            <td class="p-4 text-center">
                                <a href="struk.php?id=<?= $d['id'] ?>" target="_blank" class="text-blue-500 hover:text-blue-700 mx-1" title="Cetak Ulang"><i class="fa-solid fa-print"></i></a>
                                <a href="?hapus=<?= $d['id'] ?>" onclick="return confirm('Yakin hapus transaksi ini?')" class="text-red-500 hover:text-red-700 mx-1" title="Hapus"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t flex justify-end">
                <p class="text-sm text-gray-500">Total Transaksi: <?= mysqli_num_rows($query) ?></p>
            </div>
        </div>
    </div>
</body>
</html>