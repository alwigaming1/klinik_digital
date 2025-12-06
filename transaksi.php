<?php 
include 'config.php'; cek_login(); 
if(!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

// --- 1. LOGIC TAMBAH ITEM LAYANAN/OBAT ---
if(isset($_GET['add'])){
    $id = $_GET['add'];
    $found = false;
    foreach($_SESSION['cart'] as $key => $item){
        if($item['id'] == $id){ $_SESSION['cart'][$key]['qty'] += 1; $found = true; }
    }
    if(!$found){
        $l = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM layanan WHERE id='$id'"));
        // Pastikan tabel layanan memiliki kolom 'nama' dan 'tarif'
        $_SESSION['cart'][] = [
            'id' => $l['id'], 
            'nama' => $l['nama'], 
            'harga' => $l['tarif'], 
            'qty' => 1, 
            'cat' => $l['kategori']
        ];
    }
    header("Location: transaksi.php"); exit;
}

// --- 2. LOGIC RESET ---
if(isset($_GET['reset'])){ 
    unset($_SESSION['cart']); 
    unset($_SESSION['pasien_id']);
    unset($_SESSION['pasien_nama']);
    header("Location: transaksi.php"); exit; 
}

// --- 3. LOGIC SIMPAN ID PASIEN ---
if(isset($_POST['set_pasien'])){
    $_SESSION['pasien_id'] = $_POST['pasien_id'];
    $p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pasien WHERE id='$_SESSION[pasien_id]'"));
    $_SESSION['pasien_nama'] = $p['nama_lengkap'];
    header("Location: transaksi.php"); exit;
}

// --- 4. LOGIC BAYAR (SIMPAN TRANSAKSI KLINIK) ---
if(isset($_POST['bayar'])){
    $subtotal = $_POST['total_kotor'];
    $diskon_persen = $_POST['diskon'];
    $uang_bayar = $_POST['uang']; 
    $pasien_id = $_SESSION['pasien_id'];
    
    $potongan = ($subtotal * $diskon_persen) / 100;
    $total_bersih = $subtotal - $potongan;
    
    if($uang_bayar < $total_bersih){ 
        echo "<script>alert('Uang Kurang!');</script>"; 
    } else {
        $kembali = $uang_bayar - $total_bersih;
        $struk = "BILL-".date("ymdHis"); $tgl = date("Y-m-d H:i:s");
        
        // Simpan Transaksi (Asumsi: tabel transaksi_klinik)
        $query_trx = "INSERT INTO transaksi_klinik VALUES (NULL, '$struk', '$tgl', '$pasien_id', '$total_bersih', '$potongan', '$uang_bayar', '$kembali', '$_SESSION[user_id]')";
        mysqli_query($koneksi, $query_trx);
        $id_trx = mysqli_insert_id($koneksi);
        
        // Simpan Detail Transaksi
        foreach($_SESSION['cart'] as $c){
            $sub = $c['harga']*$c['qty'];
            mysqli_query($koneksi, "INSERT INTO transaksi_detail VALUES (NULL, '$id_trx', '$c[id]', '$c[harga]', '$c[qty]', '$sub')");
            
            // Kurangi Stok hanya untuk Obat (jika kategori=='Obat')
            if($c['cat'] == 'Obat') {
                mysqli_query($koneksi, "UPDATE layanan SET stok = stok - $c[qty] WHERE id='$c[id]'");
            }
        }
        
        unset($_SESSION['cart']);
        unset($_SESSION['pasien_id']);
        unset($_SESSION['pasien_nama']);
        
        header("Location: struk.php?id=$id_trx");
    }
}

// Hitung Data Awal untuk JS
$total_php = 0; $qty_php = 0;
foreach(isset($_SESSION['cart']) ? $_SESSION['cart'] : [] as $c){ $total_php += ($c['harga']*$c['qty']); $qty_php += $c['qty']; }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Billing & Pembayaran - <?= $conf['app_name'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> 
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-100 h-screen w-full overflow-hidden flex flex-col lg:flex-row" 
      x-data="{ 
          mobileCartOpen: false, 
          subtotal: <?= $total_php ?>, 
          diskon: 0,
          bayar: '',
          get potongan() { return Math.round(this.subtotal * (this.diskon / 100)); },
          get total() { return this.subtotal - this.potongan; },
          get kembali() { return (this.bayar - this.total) > 0 ? (this.bayar - this.total) : 0; }
      }">

    <div class="flex-1 flex flex-col h-full relative z-0">
        
        <header class="h-16 bg-white border-b border-gray-200 px-4 flex justify-between items-center shadow-md shrink-0 z-20">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-<?= $conf['color'] ?>-600 rounded-lg flex items-center justify-center text-white text-lg shadow-md shadow-<?= $conf['color'] ?>-200">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
                <div>
                    <h1 class="font-bold text-base text-slate-800 leading-none">BILLING <?= strtoupper($conf['app_name']) ?></h1>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">Shift: <?= $_SESSION['nama'] ?? 'Kasir' ?></p>
                </div>
            </div>

            <div class="hidden sm:flex relative w-64 lg:w-80">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                <input type="text" id="cariMenu" onkeyup="filterMenu()" placeholder="Cari Layanan atau Obat..." class="w-full bg-gray-100 border-none rounded-full py-2 pl-9 pr-4 text-sm focus:ring-2 focus:ring-<?= $conf['color'] ?>-500 transition">
            </div>

            <div class="flex gap-2">
                <?php if($_SESSION['role']=='admin'): ?>
                <a href="dashboard.php" class="w-9 h-9 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center hover:bg-gray-200 transition" title="Dashboard Admin">
                    <i class="fa-solid fa-chart-line text-sm"></i>
                </a>
                <?php endif; ?>
                <a href="logout.php" onclick="return confirm('Keluar?')" class="w-9 h-9 bg-red-50 text-red-500 rounded-full flex items-center justify-center hover:bg-red-100 transition" title="Logout">
                    <i class="fa-solid fa-power-off text-sm"></i>
                </a>
            </div>
        </header>

        <div class="px-4 py-3 bg-white shrink-0 shadow-sm z-10">
            <div class="flex gap-2 overflow-x-auto no-scrollbar">
                <button onclick="filterKategori('all')" class="cat-btn px-4 py-1.5 bg-<?= $conf['color'] ?>-600 text-white rounded-full text-xs font-bold shadow-md shadow-<?= $conf['color'] ?>-200 whitespace-nowrap transition">Semua</button>
                <button onclick="filterKategori('Tindakan')" class="cat-btn px-4 py-1.5 bg-white text-gray-500 border border-gray-200 rounded-full text-xs font-bold hover:border-<?= $conf['color'] ?>-500 hover:text-<?= $conf['color'] ?>-600 whitespace-nowrap transition">Tindakan Medis</button>
                <button onclick="filterKategori('Obat')" class="cat-btn px-4 py-1.5 bg-white text-gray-500 border border-gray-200 rounded-full text-xs font-bold hover:border-<?= $conf['color'] ?>-500 hover:text-<?= $conf['color'] ?>-600 whitespace-nowrap transition">Obat-obatan</button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 bg-gray-50 pb-24 lg:pb-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                <?php 
                // Mengambil data dari tabel layanan, bukan produk
                $menu = mysqli_query($koneksi, "SELECT * FROM layanan WHERE stok > 0 OR kategori='Tindakan'"); 
                while($m = mysqli_fetch_array($menu)){
                    // Ikon sesuai kategori
                    $icon = $m['kategori'] == 'Obat' ? 'fa-pills' : 'fa-syringe';
                    $stok_badge = $m['kategori'] == 'Obat' ? "<div class=\"absolute top-2 right-2 bg-white/90 backdrop-blur px-2 py-0.5 rounded text-[10px] font-bold text-slate-600 shadow-sm\">Stok: $m[stok]</div>" : '';
                ?>
                <a href="transaksi.php?add=<?= $m['id'] ?>" class="item-card group bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-lg hover:border-<?= $conf['color'] ?>-300 transition active:scale-95 flex flex-col relative" data-name="<?= strtolower($m['nama']) ?>" data-cat="<?= $m['kategori'] ?>">
                    <div class="relative h-28 sm:h-36 overflow-hidden bg-<?= $conf['color'] ?>-50 flex items-center justify-center text-center">
                        <i class="fa-solid <?= $icon ?> text-5xl text-<?= $conf['color'] ?>-300 group-hover:text-<?= $conf['color'] ?>-500 transition"></i>
                        <?= $stok_badge ?>
                    </div>
                    <div class="p-3 flex-1 flex flex-col justify-between">
                        <h4 class="font-bold text-slate-800 text-xs sm:text-sm leading-tight mb-1 line-clamp-2"><?= $m['nama'] ?></h4>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-<?= $conf['color'] ?>-600 font-extrabold text-xs sm:text-sm">Rp <?= number_format($m['tarif']) ?></span>
                            <div class="w-6 h-6 rounded-full bg-<?= $conf['color'] ?>-100 text-<?= $conf['color'] ?>-600 flex items-center justify-center group-hover:bg-<?= $conf['color'] ?>-600 group-hover:text-white transition">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                </a>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="lg:hidden fixed bottom-6 left-6 right-6 z-40">
        <button @click="mobileCartOpen = true" class="w-full bg-<?= $conf['color'] ?>-900 text-white p-4 rounded-2xl shadow-xl flex justify-between items-center animate-bounce-slow">
            <div class="flex items-center gap-3">
                <div class="bg-<?= $conf['color'] ?>-500 w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs">
                    <?= $qty_php ?>
                </div>
                <span class="font-bold text-sm">Lihat Tagihan</span>
            </div>
            <span class="font-extrabold text-lg">Rp <?= number_format($total_php) ?></span>
        </button>
    </div>

    <div class="fixed inset-0 z-50 lg:static lg:inset-auto lg:z-auto w-full lg:w-96 flex flex-col" 
         :class="mobileCartOpen ? 'flex' : 'hidden lg:flex'">
        
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm lg:hidden" @click="mobileCartOpen = false"></div>

        <div class="relative w-full h-[85vh] mt-auto lg:h-full lg:mt-0 bg-white shadow-2xl lg:shadow-none lg:border-l border-gray-200 rounded-t-3xl lg:rounded-none flex flex-col transition-transform duration-300">
            
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mt-3 mb-1 lg:hidden"></div>

            <div class="h-auto p-6 flex flex-col border-b border-gray-100 shrink-0">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-bold text-lg text-slate-800 flex items-center gap-2">
                        Billing Pasien
                        <span class="bg-<?= $conf['color'] ?>-100 text-<?= $conf['color'] ?>-600 text-xs px-2 py-0.5 rounded-full"><?= count(isset($_SESSION['cart']) ? $_SESSION['cart'] : []) ?></span>
                    </span>
                    <a href="transaksi.php?reset=true" class="text-xs font-bold text-red-500 hover:bg-red-50 px-3 py-1.5 rounded-lg transition">
                        <i class="fa-solid fa-eraser mr-1"></i> Reset Billing
                    </a>
                </div>
                
                <?php if(!isset($_SESSION['pasien_id'])): ?>
                <form method="POST" class="bg-red-50 p-3 rounded-lg border border-red-200">
                    <label class="text-sm font-bold text-red-600 block mb-2">Pilih Pasien (Wajib)</label>
                    <select name="pasien_id" class="w-full border rounded p-2 text-sm" required>
                        <option value="">-- Cari Pasien --</option>
                        <?php 
                        $q_pasien = mysqli_query($koneksi, "SELECT id, nama_lengkap, no_rekam_medis FROM pasien ORDER BY nama_lengkap ASC");
                        while($p = mysqli_fetch_assoc($q_pasien)):
                        ?>
                        <option value="<?= $p['id'] ?>"><?= $p['nama_lengkap'] ?> (<?= $p['no_rekam_medis'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" name="set_pasien" class="w-full bg-red-600 text-white font-bold py-2 mt-2 rounded hover:bg-red-700 transition text-sm">SET PASIEN</button>
                </form>
                <?php else: ?>
                <div class="bg-green-50 p-3 rounded-lg border border-green-200 flex items-center gap-3">
                    <i class="fa-solid fa-user-injured text-green-600 text-lg"></i>
                    <div>
                        <div class="text-xs font-medium text-green-600">Pasien Terpilih:</div>
                        <div class="font-bold text-sm text-slate-800"><?= $_SESSION['pasien_nama'] ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50/50">
                <?php if(empty($_SESSION['cart'])): ?>
                    <div class="flex flex-col items-center justify-center h-64 text-gray-300">
                        <i class="fa-solid fa-file-invoice text-6xl mb-4 text-gray-200"></i>
                        <p class="text-sm font-medium">Belum ada Layanan/Obat ditambahkan.</p>
                    </div>
                <?php endif; ?>

                <?php foreach(isset($_SESSION['cart']) ? $_SESSION['cart'] : [] as $c): ?>
                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex justify-between items-center group">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-8 h-8 rounded-lg bg-<?= $conf['color'] ?>-50 text-<?= $conf['color'] ?>-600 flex items-center justify-center font-bold text-xs shrink-0">
                            <?= $c['qty'] ?>x
                        </div>
                        <div class="truncate">
                            <div class="font-bold text-sm text-gray-800 truncate"><?= $c['nama'] ?></div>
                            <div class="text-[10px] text-gray-400">@ <?= number_format($c['harga']) ?></div>
                        </div>
                    </div>
                    <div class="font-bold text-sm text-slate-700 shrink-0">
                        Rp <?= number_format(($c['harga']*$c['qty'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="p-6 bg-white border-t border-gray-100 shadow-[0_-5px_20px_rgba(0,0,0,0.03)] shrink-0">
                <?php if(isset($_SESSION['pasien_id']) && !empty($_SESSION['cart'])): ?>
                <form method="POST">
                    <input type="hidden" name="total_kotor" :value="subtotal">
                    
                    <div class="space-y-2 mb-4 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Subtotal Tagihan</span>
                            <span x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal)"></span>
                        </div>
                        <div class="flex justify-between items-center text-<?= $conf['color'] ?>-600">
                            <span class="text-xs font-bold">DISKON (%)</span>
                            <input type="number" name="diskon" x-model="diskon" class="w-12 text-right border-b border-<?= $conf['color'] ?>-200 focus:border-<?= $conf['color'] ?>-500 outline-none text-sm font-bold bg-transparent" placeholder="0">
                        </div>
                        <div class="flex justify-between text-gray-500 text-xs" x-show="diskon > 0">
                            <span>Potongan Diskon</span>
                            <span x-text="'- Rp ' + new Intl.NumberFormat('id-ID').format(potongan)"></span>
                        </div>
                        <div class="flex justify-between text-lg font-extrabold text-slate-800 pt-2 border-t border-dashed">
                            <span>TOTAL BAYAR</span>
                            <span x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(total)"></span>
                        </div>
                    </div>

                    <div class="relative mb-3">
                        <span class="absolute left-4 top-3.5 text-gray-400 text-sm font-bold">Rp</span>
                        <input type="number" name="uang" x-model="bayar" class="w-full pl-12 pr-4 py-3.5 bg-gray-100 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-<?= $conf['color'] ?>-500 transition outline-none text-lg" placeholder="Uang Tunai" required>
                    </div>

                    <div class="flex justify-between text-xs text-gray-500 mb-4 px-1" x-show="bayar > 0">
                        <span>Kembalian:</span>
                        <span class="font-bold text-green-600 text-sm" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(kembali)"></span>
                    </div>

                    <button type="submit" name="bayar" :disabled="bayar < total" class="w-full text-white font-bold py-4 rounded-xl transition shadow-lg flex justify-center items-center gap-2 group active:scale-95 transform duration-100" :class="bayar < total ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'">
                        <span>PROSES PEMBAYARAN</span> 
                        <i class="fa-solid fa-check-circle group-hover:scale-110 transition"></i>
                    </button>
                </form>
                <?php else: ?>
                    <p class="text-center text-sm text-gray-500 font-medium p-4 border rounded-lg">Pilih Pasien dan Layanan untuk memproses tagihan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function filterKategori(cat) {
            const cards = document.querySelectorAll('.item-card');
            const btns = document.querySelectorAll('.cat-btn');
            
            // Reset style tombol
            btns.forEach(btn => {
                const isMatch = (cat === 'all' && btn.innerText === 'Semua') || btn.innerText.toLowerCase().includes(cat.toLowerCase());

                if(isMatch){
                    btn.className = `cat-btn px-4 py-1.5 bg-<?= $conf['color'] ?>-600 text-white rounded-full text-xs font-bold shadow-md shadow-<?= $conf['color'] ?>-200 whitespace-nowrap transition`;
                } else {
                    btn.className = "cat-btn px-4 py-1.5 bg-white text-gray-500 border border-gray-200 rounded-full text-xs font-bold hover:border-<?= $conf['color'] ?>-500 hover:text-<?= $conf['color'] ?>-600 whitespace-nowrap transition";
                }
            });

            cards.forEach(card => {
                if (cat === 'all' || card.getAttribute('data-cat') === cat) card.style.display = 'flex';
                else card.style.display = 'none';
            });
        }

        function filterMenu() {
            const input = document.getElementById('cariMenu').value.toLowerCase();
            const cards = document.querySelectorAll('.item-card');
            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                if (name.includes(input)) card.style.display = 'flex';
                else card.style.display = 'none';
            });
        }
    </script>

</body>
</html>