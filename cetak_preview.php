<?php 
include 'config.php'; 
cek_login(); 
if($_SESSION['role'] != 'admin') { header("Location: transaksi.php"); exit; }

$jenis = $_GET['jenis'] ?? 'billing';
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$data = [];
$judul = "LAPORAN";

// --- LOGIKA PENGAMBILAN DATA BERDASARKAN JENIS LAPORAN ---
if ($jenis == 'billing') {
    $judul = "LAPORAN BILLING & PENDAPATAN";
    $query = mysqli_query($koneksi, "
        SELECT 
            tk.*, p.nama_lengkap AS nama_pasien, u.nama_lengkap AS nama_kasir
        FROM 
            transaksi_klinik tk
        JOIN 
            pasien p ON tk.pasien_id = p.id
        JOIN
            users u ON tk.user_id = u.id
        WHERE 
            DATE(tk.tanggal) BETWEEN '$tgl_awal' AND '$tgl_akhir'
        ORDER BY tk.tanggal ASC
    ");
    while($d = mysqli_fetch_assoc($query)) { $data[] = $d; }

} else if ($jenis == 'layanan') {
    $judul = "LAPORAN DATA MASTER LAYANAN & OBAT";
    $query = mysqli_query($koneksi, "SELECT * FROM layanan ORDER BY kategori, nama ASC");
    while($d = mysqli_fetch_assoc($query)) { $data[] = $d; }

} else if ($jenis == 'pasien') {
    $judul = "LAPORAN DATA MASTER PASIEN";
    $query = mysqli_query($koneksi, "SELECT * FROM pasien ORDER BY nama_lengkap ASC");
    while($d = mysqli_fetch_assoc($query)) { $data[] = $d; }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Preview Laporan - <?= $conf['app_name'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: sans-serif; padding: 20px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #eee; font-size: 11px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 18px; }
        .print-btn-container { text-align: right; margin-bottom: 20px; }
        @media print { .print-btn-container { display: none; } }
    </style>
</head>
<body>

    <div class="print-btn-container">
        <button onclick="window.print()" style="padding: 10px 15px; background-color: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer;">
            <i class="fa-solid fa-print"></i> Cetak Dokumen
        </button>
        <a href="cetak.php" style="padding: 10px 15px; background-color: #f3f4f6; color: #333; border: 1px solid #ccc; border-radius: 5px; cursor: pointer; text-decoration: none; margin-left: 10px;">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="header">
        <h1><?= strtoupper($judul) ?></h1>
        <p><?= $conf['app_name'] ?></p>
        <?php if ($jenis == 'billing'): ?>
            <p>Periode: **<?= date('d-m-Y', strtotime($tgl_awal)) ?>** s/d **<?= date('d-m-Y', strtotime($tgl_akhir)) ?>**</p>
        <?php endif; ?>
        <hr>
    </div>

    <table>
        <thead>
            <?php if ($jenis == 'billing'): ?>
                <tr>
                    <th>No</th>
                    <th>Invoice</th>
                    <th>Tanggal</th>
                    <th>Pasien</th>
                    <th>Kasir</th>
                    <th>Potongan</th>
                    <th>Total Bersih</th>
                </tr>
            <?php elseif ($jenis == 'layanan'): ?>
                <tr>
                    <th>No</th>
                    <th>Nama Layanan/Obat</th>
                    <th>Kategori</th>
                    <th>Tarif (Rp)</th>
                    <th>Stok</th>
                </tr>
            <?php elseif ($jenis == 'pasien'): ?>
                <tr>
                    <th>No</th>
                    <th>No RM</th>
                    <th>Nama Pasien</th>
                    <th>Jenis Kelamin</th>
                    <th>Tgl Lahir</th>
                    <th>No Telp</th>
                </tr>
            <?php endif; ?>
        </thead>
        <tbody>
            <?php if (empty($data)): ?>
                <tr><td colspan="7" style="text-align: center;">Tidak ada data ditemukan.</td></tr>
            <?php else: ?>
                <?php $no = 1; $grand_total = 0; foreach($data as $d): ?>
                <tr>
                    <?php if ($jenis == 'billing'): ?>
                        <td><?= $no++ ?></td>
                        <td><?= $d['invoice'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($d['tanggal'])) ?></td>
                        <td><?= $d['nama_pasien'] ?></td>
                        <td><?= $d['nama_kasir'] ?></td>
                        <td>Rp <?= number_format($d['potongan']) ?></td>
                        <td>Rp <?= number_format($d['total_bersih']) ?></td>
                        <?php $grand_total += $d['total_bersih']; ?>
                    <?php elseif ($jenis == 'layanan'): ?>
                        <td><?= $no++ ?></td>
                        <td><?= $d['nama'] ?></td>
                        <td><?= $d['kategori'] ?></td>
                        <td>Rp <?= number_format($d['tarif']) ?></td>
                        <td><?= $d['kategori'] == 'Obat' ? $d['stok'] : '-' ?></td>
                    <?php elseif ($jenis == 'pasien'): ?>
                        <td><?= $no++ ?></td>
                        <td><?= $d['no_rekam_medis'] ?></td>
                        <td><?= $d['nama_lengkap'] ?></td>
                        <td><?= $d['jenis_kelamin'] ?></td>
                        <td><?= date('d-m-Y', strtotime($d['tanggal_lahir'])) ?></td>
                        <td><?= $d['no_telepon'] ?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                
                <?php if ($jenis == 'billing'): ?>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold;">GRAND TOTAL PENDAPATAN:</td>
                    <td colspan="1" style="font-weight: bold; font-size: 14px;">Rp <?= number_format($grand_total) ?></td>
                </tr>
                <?php endif; ?>

            <?php endif; ?>
        </tbody>
    </table>

    <div style="float: right; margin-top: 30px; text-align: center;">
        <p>..., <?= date('d-m-Y') ?></p>
        <br><br><br>
        <p>_______________________</p>
        <p>Administrator</p>
    </div>

</body>
</html>