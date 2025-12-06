<?php 
include 'config.php'; 
cek_login(); 
if($_SESSION['role'] != 'admin') { header("Location: transaksi.php"); exit; }

// --- LOGIKA PENGAMBILAN DATA AKTUAL KLINIK ---
$tgl_hari_ini = date('Y-m-d');

// 1. Total Pasien Terdaftar
$total_pasien = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM pasien"));

// 2. Transaksi/Billing Hari Ini
$transaksi_hari_ini = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM transaksi_klinik WHERE DATE(tanggal) = '$tgl_hari_ini'"));

// 3. Total Layanan & Obat
$total_layanan = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM layanan"));

// 4. Pendapatan Hari Ini
$pendapatan_hari_ini_res = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_bersih) AS total FROM transaksi_klinik WHERE DATE(tanggal) = '$tgl_hari_ini'"));
$pendapatan_hari_ini = $pendapatan_hari_ini_res['total'] ?? 0;

// 5. 5 Transaksi Terbaru (untuk sidebar)
$q_trx_terbaru = mysqli_query($koneksi, "
    SELECT 
        tk.invoice, tk.total_bersih
    FROM 
        transaksi_klinik tk
    ORDER BY 
        tk.tanggal DESC 
    LIMIT 5
");

// --- DATA UNTUK GRAFIK (Pendapatan 7 Hari Terakhir) ---
$data_pendapatan = [];
$labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('D', strtotime($date)); 
    
    $q_harian = mysqli_query($koneksi, "SELECT SUM(total_bersih) AS total FROM transaksi_klinik WHERE DATE(tanggal) = '$date'");
    $d_harian = mysqli_fetch_assoc($q_harian);
    $data_pendapatan[] = $d_harian['total'] ?? 0;
}
$labels_json = json_encode($labels);
$data_json = json_encode($data_pendapatan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard - <?= $conf['app_name'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> 
        body { font-family: 'Poppins', sans-serif; } 
    </style>
</head>
<body class="bg-[#F3F4F6] text-slate-800" 
      x-data="{ isSidebarOpen: false }"
      x-init="() => {
          if (window.innerWidth >= 1024) { 
              isSidebarOpen = true; 
          }
          window.addEventListener('resize', () => {
              if (window.innerWidth >= 1024) {
                  isSidebarOpen = true;
              }
          });
      }">

    <?php include 'sidebar.php'; ?>
    
    <div class="lg:ml-64 p-4 md:p-8 pt-14 lg:pt-8 min-h-screen">
        
        <div class="flex justify-between items-center mb-8 pt-10 lg:pt-0">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Dashboard Medis</h1>
                <p class="text-gray-500 text-sm mt-1">Ringkasan aktivitas klinik hari ini (<?= date('d M Y') ?>).</p>
            </div>
            <div class="flex items-center gap-3 bg-white p-3 rounded-xl shadow-sm border hidden md:flex">
                <div class="text-right">
                    <p class="text-sm font-bold"><?= $_SESSION['nama'] ?? 'Administrator' ?></p>
                    <p class="text-xs text-green-500">● <?= strtoupper($_SESSION['role'] ?? 'Admin') ?></p>
                </div>
                <div class="w-10 h-10 rounded-full bg-<?= $conf['color'] ?>-100 text-<?= $conf['color'] ?>-600 flex items-center justify-center font-bold border border-<?= $conf['color'] ?>-200">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Pendapatan Hari Ini</p>
                        <h3 class="text-2xl font-extrabold text-green-600">Rp <?= number_format($pendapatan_hari_ini) ?></h3>
                    </div>
                    <div class="bg-green-50 p-3 rounded-xl text-green-500">
                        <i class="fa-solid fa-money-bill-transfer text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Transaksi Hari Ini</p>
                        <h3 class="text-3xl font-bold text-slate-800"><?= $transaksi_hari_ini ?></h3>
                    </div>
                    <div class="bg-<?= $conf['color'] ?>-50 p-3 rounded-xl text-<?= $conf['color'] ?>-500">
                        <i class="fa-solid fa-receipt text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Total Pasien</p>
                        <h3 class="text-3xl font-bold text-slate-800"><?= $total_pasien ?></h3>
                    </div>
                    <div class="bg-yellow-50 p-3 rounded-xl text-yellow-600">
                        <i class="fa-solid fa-user-injured text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Total Layanan & Obat</p>
                        <h3 class="text-3xl font-bold text-slate-800"><?= $total_layanan ?></h3>
                    </div>
                    <div class="bg-red-50 p-3 rounded-xl text-red-500">
                        <i class="fa-solid fa-syringe text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
             <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg border border-gray-100 p-6 min-h-[400px]">
                <h3 class="font-bold text-lg text-gray-700 mb-4 border-b pb-2">Pendapatan 7 Hari Terakhir</h3>
                <div style="height: 350px;">
                    <canvas id="pendapatanChart"></canvas>
                </div>
            </div>
             <div class="lg:col-span-1 bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="font-bold text-lg text-gray-700 mb-4 border-b pb-2">5 Transaksi Terbaru</h3>
                <ul class="space-y-3">
                    <?php if (mysqli_num_rows($q_trx_terbaru) > 0): ?>
                        <?php mysqli_data_seek($q_trx_terbaru, 0); ?>
                        <?php while($t = mysqli_fetch_assoc($q_trx_terbaru)): ?>
                        <li class="flex justify-between items-center text-sm">
                            <span><i class="fa-solid fa-arrow-up text-green-500 mr-2"></i> <?= $t['invoice'] ?></span>
                            <span class="font-bold text-slate-800">Rp <?= number_format($t['total_bersih']) ?></span>
                        </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="text-gray-400 text-sm">Belum ada transaksi hari ini.</li>
                    <?php endif; ?>
                </ul>
                <a href="data_transaksi.php" class="mt-4 block text-center text-xs text-<?= $conf['color'] ?>-600 font-semibold hover:underline">Lihat Semua Transaksi</a>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('pendapatanChart').getContext('2d');
        
        const labels = <?= $labels_json ?>;
        const data = <?= $data_json ?>;

        const pendapatanChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: data,
                    backgroundColor: 'rgba(59, 130, 246, 0.6)', 
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>