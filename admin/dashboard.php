<?php
require_once __DIR__ . '/../../includes/auth_check.php';
checkAccess('admin');

$title = 'Dashboard Admin';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../../includes/navbar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
            </div>
            
            <!-- Statistik -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body">
                            <h5 class="card-title">Total Siswa</h5>
                            <?php
                            $stmt = $conn->query("SELECT COUNT(*) FROM siswa");
                            $total_siswa = $stmt->fetchColumn();
                            ?>
                            <h2 class="card-text"><?= $total_siswa ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-success h-100">
                        <div class="card-body">
                            <h5 class="card-title">Pembayaran Bulan Ini</h5>
                            <?php
                            $current_month = date('m');
                            $current_year = date('Y');
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM pembayaran WHERE bulan_dibayar = :bulan AND tahun_dibayar = :tahun");
                            $stmt->execute([':bulan' => $current_month, ':tahun' => $current_year]);
                            $total_pembayaran = $stmt->fetchColumn();
                            ?>
                            <h2 class="card-text"><?= $total_pembayaran ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-info h-100">
                        <div class="card-body">
                            <h5 class="card-title">Total Petugas</h5>
                            <?php
                            $stmt = $conn->query("SELECT COUNT(*) FROM petugas");
                            $total_petugas = $stmt->fetchColumn();
                            ?>
                            <h2 class="card-text"><?= $total_petugas ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Grafik Pembayaran -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Statistik Pembayaran Tahun <?= date('Y') ?></h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentChart" height="100"></canvas>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>