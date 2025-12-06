<?php 
include 'config.php'; 
cek_login(); 
if($_SESSION['role'] != 'admin') { header("Location: transaksi.php"); exit; }

// --- LOGIC HANDLE FILTER (redirect ke preview/print) ---
if(isset($_POST['filter_laporan'])){
    $jenis = $_POST['jenis_laporan'];
    $tgl_awal = $_POST['tgl_awal'];
    $tgl_akhir = $_POST['tgl_akhir'];

    header("Location: cetak_preview.php?jenis=$jenis&tgl_awal=$tgl_awal&tgl_akhir=$tgl_akhir");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Filter Laporan - <?= $conf['app_name'] ?></title>
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
        
        <div class="mb-8 pt-10 lg:pt-0">
            <h1 class="text-2xl font-bold text-slate-800">Cetak Laporan Keuangan & Data</h1>
            <p class="text-gray-500 text-sm mt-1">Pilih jenis laporan dan rentang tanggal yang akan dicetak.</p>
        </div>

        <div class="bg-white p-4 sm:p-8 rounded-2xl shadow-lg border border-gray-100 max-w-2xl mx-auto lg:mx-0">
            <h3 class="font-bold text-gray-700 mb-6 border-b pb-2 flex items-center gap-2">
                <i class="fa-solid fa-filter text-lg text-<?= $conf['color'] ?>-600"></i> Opsi Filter Laporan
            </h3>
            
            <form method="POST">
                
                <div class="mb-5">
                    <label class="block text-sm font-medium mb-2">Jenis Laporan</label>
                    <select name="jenis_laporan" class="w-full border rounded-xl p-3 bg-gray-50 focus:ring-<?= $conf['color'] ?>-500" required>
                        <option value="">-- Pilih Jenis Laporan --</option>
                        <option value="billing">Laporan Billing/Pendapatan Klinik</option>
                        <option value="layanan">Laporan Data Layanan & Obat</option>
                        <option value="pasien">Laporan Data Master Pasien</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                    <div>
                        <label class="block text-sm font-medium mb-2">Tanggal Awal</label>
                        <input type="date" name="tgl_awal" class="w-full border rounded-xl p-3 bg-gray-50 focus:ring-<?= $conf['color'] ?>-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Tanggal Akhir</label>
                        <input type="date" name="tgl_akhir" class="w-full border rounded-xl p-3 bg-gray-50 focus:ring-<?= $conf['color'] ?>-500" required>
                    </div>
                </div>

                <button type="submit" name="filter_laporan" class="w-full bg-<?= $conf['color'] ?>-600 text-white font-bold py-3 rounded-xl hover:bg-<?= $conf['color'] ?>-700 transition shadow-lg shadow-<?= $conf['color'] ?>-200">
                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Tampilkan & Cetak Laporan
                </button>
            </form>
        </div>

    </div>
</body>
</html>