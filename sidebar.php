<aside class="w-64 bg-white h-screen fixed left-0 top-0 border-r border-gray-200 flex flex-col z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300" 
       x-show="isSidebarOpen" 
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="-translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in duration-300"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="-translate-x-full"
       @click.away="isSidebarOpen = false" 
       :class="{'!translate-x-0': isSidebarOpen}"> 
       <div class="h-20 flex items-center px-8 border-b border-gray-100">
        <div class="flex items-center gap-3 text-<?= $conf['color'] ?>-600">
            <i class="fa-solid fa-notes-medical text-3xl"></i>
            <div>
                <h1 class="font-bold text-lg leading-tight text-slate-800"><?= strtoupper($conf['app_name']) ?></h1>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider"><?= $conf['app_ver'] ?></p>
            </div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
        
        <?php 
            $current_page = basename($_SERVER['PHP_SELF']);
            $is_admin = (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'); 
        ?>

        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 mt-2 px-4">Menu Utama</p>
        
        <?php if ($is_admin): ?>
            <a href="dashboard.php" @click="isSidebarOpen = false" class="<?= $current_page == 'dashboard.php' ? 'bg-'.$conf['color'].'-50 text-'.$conf['color'].'-600 font-bold' : 'text-gray-600 hover:bg-gray-50' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-sm">
                <i class="fa-solid fa-chart-line w-5"></i> Dashboard
            </a>
        <?php endif; ?>

        <a href="transaksi.php" @click="isSidebarOpen = false" class="<?= $current_page == 'transaksi.php' ? 'bg-'.$conf['color'].'-50 text-'.$conf['color'].'-600 font-bold' : 'text-gray-600 hover:bg-gray-50' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-sm">
            <i class="fa-solid fa-cash-register w-5"></i> Billing & Pembayaran
        </a>

        <?php if ($is_admin): ?>
        
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 mt-6 px-4">Kelola Data</p>

            <a href="data_pasien.php" @click="isSidebarOpen = false" class="<?= $current_page == 'data_pasien.php' ? 'bg-'.$conf['color'].'-50 text-'.$conf['color'].'-600 font-bold' : 'text-gray-600 hover:bg-gray-50' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-sm">
                <i class="fa-solid fa-user-injured w-5"></i> Data Pasien
            </a>
            
            <a href="data_layanan.php" @click="isSidebarOpen = false" class="<?= $current_page == 'data_layanan.php' ? 'bg-'.$conf['color'].'-50 text-'.$conf['color'].'-600 font-bold' : 'text-gray-600 hover:bg-gray-50' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-sm">
                <i class="fa-solid fa-syringe w-5"></i> Layanan & Obat
            </a>
            
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 mt-6 px-4">Laporan & Riwayat</p>
            
            <a href="data_transaksi.php" @click="isSidebarOpen = false" class="<?= $current_page == 'data_transaksi.php' ? 'bg-'.$conf['color'].'-50 text-'.$conf['color'].'-600 font-bold' : 'text-gray-600 hover:bg-gray-50' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-sm">
                <i class="fa-solid fa-file-invoice w-5"></i> Data Transaksi
            </a>

            <a href="cetak.php" @click="isSidebarOpen = false" class="<?= $current_page == 'cetak.php' ? 'bg-'.$conf['color'].'-50 text-'.$conf['color'].'-600 font-bold' : 'text-gray-600 hover:bg-gray-50' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-sm">
                <i class="fa-solid fa-print w-5"></i> Cetak Laporan
            </a>

        <?php endif; ?>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 mt-6 px-4">Pengaturan</p>
        
        <a href="profil.php" @click="isSidebarOpen = false" class="<?= $current_page == 'profil.php' ? 'bg-'.$conf['color'].'-50 text-'.$conf['color'].'-600 font-bold' : 'text-gray-600 hover:bg-gray-50' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-sm">
            <i class="fa-solid fa-user-gear w-5"></i> Profil & Password
        </a>

    </nav>

    <div class="p-4 border-t border-gray-100">
        <a href="logout.php" onclick="return confirm('Keluar aplikasi?')" class="flex items-center justify-center gap-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white py-3 rounded-xl font-bold transition-all duration-200 text-sm group">
            <i class="fa-solid fa-power-off transition-transform group-hover:scale-110"></i> Logout
        </a>
    </div>
</aside>

<div class="fixed top-0 left-0 right-0 bg-white shadow-md lg:hidden z-40 p-4 flex justify-between items-center">
    <div class="flex items-center gap-2 text-<?= $conf['color'] ?>-600">
        <i class="fa-solid fa-notes-medical"></i> 
        <h1 class="font-bold text-sm"><?= strtoupper($conf['app_name']) ?></h1>
    </div>
    <button @click="isSidebarOpen = true" class="text-gray-600 text-lg p-2 rounded hover:bg-gray-100">
        <i class="fa-solid fa-bars"></i>
    </button>
</div>