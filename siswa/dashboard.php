<?php
require_once __DIR__ . '/../../includes/auth_check.php';

// Pastikan hanya siswa yang bisa mengakses
if (!isset($_SESSION['siswa'])) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$title = 'Dashboard Siswa';
require_once __DIR__ . '/../../includes/header.php';

$nisn = $_SESSION['siswa']['nisn'];

// Ambil data siswa
$stmt = $conn->prepare("
    SELECT s.*, k.nama_kelas, k.kompetensi_keahlian 
    FROM siswa s 
    JOIN kelas k ON s.id_kelas = k.id_kelas 
    WHERE s.nisn = :nisn
");
$stmt->execute([':nisn' => $nisn]);
$siswa = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil data pembayaran terakhir
$stmt = $conn->prepare("
    SELECT p.*, sp.tahun as tahun_spp, sp.nominal
    FROM pembayaran p
    JOIN spp sp ON p.id_spp = sp.id_spp
    WHERE p.nisn = :nisn
    ORDER BY p.tgl_bayar DESC
    LIMIT 1
");
$stmt->execute([':nisn' => $nisn]);
$pembayaran_terakhir = $stmt->fetch(PDO::FETCH_ASSOC);

// Hitung total pembayaran
$stmt = $conn->prepare("SELECT COUNT(*) FROM pembayaran WHERE nisn = :nisn");
$stmt->execute([':nisn' => $nisn]);
$total_pembayaran = $stmt->fetchColumn();

// Data untuk chart
$stmt = $conn->prepare("
    SELECT YEAR(tgl_bayar) as tahun, MONTH(tgl_bayar) as bulan, SUM(jumlah_bayar) as total
    FROM pembayaran
    WHERE nisn = :nisn
    GROUP BY YEAR(tgl_bayar), MONTH(tgl_bayar)
    ORDER BY tgl_bayar DESC
    LIMIT 6
");
$stmt->execute([':nisn' => $nisn]);
$chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Siapkan data untuk chart
$labels = [];
$data = [];
foreach ($chart_data as $row) {
    $labels[] = getBulan($row['bulan']) . ' ' . $row['tahun'];
    $data[] = $row['total'];
}

$chart_config = [
    'labels' => array_reverse($labels),
    'data' => array_reverse($data)
];
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../../includes/navbar_siswa.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard Siswa</h1>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body">
                            <h5 class="card-title">Total Pembayaran</h5>
                            <h2 class="card-text"><?= $total_pembayaran ?></h2>
                            <p class="card-text">bulan</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-success h-100">
                        <div class="card-body">
                            <h5 class="card-title">Pembayaran Terakhir</h5>
                            <?php if ($pembayaran_terakhir): ?>
                                <p class="card-text">
                                    <?= getBulan($pembayaran_terakhir['bulan_dibayar']) ?> <?= $pembayaran_terakhir['tahun_dibayar'] ?>
                                </p>
                                <h4 class="card-text">Rp <?= number_format($pembayaran_terakhir['jumlah_bayar'], 0, ',', '.') ?></h4>
                            <?php else: ?>
                                <p class="card-text">Belum ada pembayaran</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-info h-100">
                        <div class="card-body">
                            <h5 class="card-title">Kelas</h5>
                            <h2 class="card-text"><?= $siswa['nama_kelas'] ?></h2>
                            <p class="card-text"><?= $siswa['kompetensi_keahlian'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Grafik Pembayaran -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Riwayat Pembayaran 6 Bulan Terakhir</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentChart" height="100"></canvas>
                </div>
            </div>
            
            <!-- Riwayat Pembayaran -->
            <div class="card">
                <div class="card-header">
                    <h5>Riwayat Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal</th>
                                    <th>Bulan</th>
                                    <th>Tahun</th>
                                    <th>Nominal SPP</th>
                                    <th>Jumlah Bayar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $conn->prepare("
                                    SELECT p.*, sp.nominal
                                    FROM pembayaran p
                                    JOIN spp sp ON p.id_spp = sp.id_spp
                                    WHERE p.nisn = :nisn
                                    ORDER BY p.tgl_bayar DESC
                                ");
                                $stmt->execute([':nisn' => $nisn]);
                                $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($riwayat as $key => $r): ?>
                                <tr>
                                    <td><?= $key + 1 ?></td>
                                    <td><?= date('d/m/Y', strtotime($r['tgl_bayar'])) ?></td>
                                    <td><?= getBulan($r['bulan_dibayar']) ?></td>
                                    <td><?= $r['tahun_dibayar'] ?></td>
                                    <td>Rp <?= number_format($r['nominal'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($r['jumlah_bayar'], 0, ',', '.') ?></td>
                                    <td>
                                        <a href="pembayaran/cetak.php?id=<?= $r['id_pembayaran'] ?>" 
                                           class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-print"></i> Kwitansi
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($riwayat)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada riwayat pembayaran</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';

function getBulan($angka) {
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    return $bulan[$angka] ?? $angka;
}
?>