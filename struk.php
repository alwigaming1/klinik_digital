<?php 
include 'config.php'; 
cek_login(); 

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: transaksi.php");
    exit;
}

$id_trx = $_GET['id'];

// 1. Ambil Data Header Transaksi
$q_trx = mysqli_query($koneksi, "
    SELECT 
        tk.*, p.nama_lengkap AS nama_pasien, u.nama_lengkap AS nama_kasir
    FROM 
        transaksi_klinik tk
    JOIN 
        pasien p ON tk.pasien_id = p.id
    JOIN 
        users u ON tk.user_id = u.id
    WHERE 
        tk.id = '$id_trx'
");
$trx = mysqli_fetch_assoc($q_trx);

if (!$trx) {
    echo "<script>alert('Transaksi tidak ditemukan!'); window.location='transaksi.php';</script>";
    exit;
}

// 2. Ambil Detail Item Layanan/Obat
$q_detail = mysqli_query($koneksi, "
    SELECT 
        td.*, l.nama AS nama_layanan
    FROM 
        transaksi_detail td
    JOIN 
        layanan l ON td.layanan_id = l.id
    WHERE 
        td.transaksi_id = '$id_trx'
");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Struk Pembayaran #<?= $trx['invoice'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Consolas', monospace; /* Font monospasi untuk kesan struk */
            font-size: 10px; 
            width: 300px; /* Lebar standar struk */
            margin: 0 auto;
            padding: 20px 0;
        }
        .container { 
            border: 1px dashed #000; 
            padding: 10px; 
        }
        .header, .footer { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .details th, .details td { 
            padding: 3px 0; 
            text-align: left; 
            border: none;
        }
        .separator { 
            border-top: 1px dashed #000; 
            margin: 5px 0; 
        }
        .total-row td { font-weight: bold; }
        
        @media print {
            body { 
                width: 70mm; /* Lebar fisik untuk printer thermal */
                margin: 0; 
                padding: 0;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="text-align: center; margin-bottom: 15px;">
        <a href="transaksi.php" style="padding: 8px 15px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 5px;">
            <i class="fa-solid fa-arrow-left"></i> Transaksi Baru
        </a>
    </div>

    <div class="container">
        <div class="header">
            <h3 style="margin: 0; font-size: 14px;"><?= strtoupper($conf['app_name']) ?></h3>
            <p style="margin: 3px 0;">Jl. Kesehatan No. 123, Kota Anda</p>
        </div>

        <div class="separator"></div>

        <table class="details">
            <tr>
                <td>INV</td>
                <td style="text-align: right;">: <?= $trx['invoice'] ?></td>
            </tr>
            <tr>
                <td>Tgl/Waktu</td>
                <td style="text-align: right;">: <?= date('d/m/Y H:i:s', strtotime($trx['tanggal'])) ?></td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td style="text-align: right;">: <?= $trx['nama_kasir'] ?></td>
            </tr>
            <tr>
                <td>Pasien</td>
                <td style="text-align: right;">: <?= $trx['nama_pasien'] ?></td>
            </tr>
        </table>

        <div class="separator"></div>

        <table class="details">
            <?php while($item = mysqli_fetch_assoc($q_detail)): ?>
            <tr>
                <td colspan="2" style="padding-top: 5px;">
                    <?= $item['nama_layanan'] ?>
                </td>
            </tr>
            <tr>
                <td style="padding-left: 10px;">
                    <?= $item['qty'] ?> x <?= number_format($item['harga_satuan']) ?>
                </td>
                <td style="text-align: right;">
                    <?= number_format($item['subtotal']) ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <div class="separator"></div>

        <table class="details">
            <tr>
                <td>SUBTOTAL</td>
                <td style="text-align: right;">: <?= number_format($trx['total_bersih'] + $trx['potongan']) ?></td>
            </tr>
            <tr>
                <td>DISKON</td>
                <td style="text-align: right;">: (<?= number_format($trx['potongan']) ?>)</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL</td>
                <td style="text-align: right;">: <?= number_format($trx['total_bersih']) ?></td>
            </tr>
            <tr class="separator"><td colspan="2"></td></tr>
            <tr>
                <td>TUNAI</td>
                <td style="text-align: right;">: <?= number_format($trx['uang_bayar']) ?></td>
            </tr>
            <tr class="total-row">
                <td>KEMBALI</td>
                <td style="text-align: right;">: <?= number_format($trx['kembali']) ?></td>
            </tr>
        </table>

        <div class="separator"></div>

        <div class="footer">
            <p style="margin: 3px 0;">Terima Kasih Atas Kunjungan Anda</p>
            <p style="margin: 3px 0;">Layanan Kami: <?= $conf['app_name'] ?></p>
        </div>
    </div>

</body>
</html>