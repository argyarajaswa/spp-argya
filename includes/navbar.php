<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-header">
        <h3>
            <img src="<?= APP_URL ?>/assets/images/logo.png" alt="Logo">
            <?= APP_NAME ?>
        </h3>
    </div>
    
    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            
            <?php if ($_SESSION['user']['level'] == 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'siswa/') !== false ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/siswa/">
                    <i class="fas fa-users"></i> Data Siswa
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'kelas/') !== false ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/kelas/">
                    <i class="fas fa-school"></i> Data Kelas
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'spp/') !== false ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/spp/">
                    <i class="fas fa-money-bill-wave"></i> Data SPP
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'petugas/') !== false ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/petugas/">
                    <i class="fas fa-user-shield"></i> Data Petugas
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'transaksi/') !== false ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/petugas/transaksi/">
                    <i class="fas fa-cash-register"></i> Transaksi
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'laporan/') !== false ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/petugas/laporan/">
                    <i class="fas fa-file-alt"></i> Laporan
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer" style="padding: 20px; position: absolute; bottom: 0; width: 100%;">
        <a href="<?= APP_URL ?>/logout.php" class="btn btn-danger btn-sm w-100">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

<!-- Toggle Button for Mobile -->
<button class="btn btn-primary d-md-none position-fixed" 
        style="bottom: 20px; right: 20px; z-index: 1001;" 
        id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<script>
    // Toggle sidebar on mobile
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
        document.querySelector('.main-content').classList.toggle('active');
    });
</script>