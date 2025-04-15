<?php
require_once __DIR__ . '/../../includes/auth_check.php';

$title = 'Laporan Pembayaran';
require_once __DIR__ . '/../../includes/header.php';

// Filter default: bulan dan tahun ini
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Ambil data pembayaran berdasarkan filter
$stmt = $conn->prepare("
    SELECT p.*, s.nama as nama_siswa, k.nama_kelas, pt.nama_petugas, sp.tahun as tahun_spp
    FROM pembayaran p
    JOIN siswa s ON p.nisn = s.nisn
    JOIN kelas k ON s.id_kelas = k.id_kelas
    JOIN petugas pt ON p.id_petugas = pt.id_petugas
    JOIN spp sp ON p.id_spp = sp.id_spp
    WHERE p.bulan_dibayar = :bulan AND p.tahun_dibayar = :tahun
    ORDER BY p.tgl_bayar DESC
");
$stmt->execute([':bulan' => $bulan, ':tahun' => $tahun]);
$pembayaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total pembayaran
$total = 0;
foreach ($pembayaran as $p) {
    $total += $p['jumlah_bayar'];
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Laporan Pembayaran</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="cetak.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
                       class="btn btn-sm btn-success me-2" target="_blank">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </a>
                </div>
            </div>
            
            <!-- Filter Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select class="form-select" id="bulan" name="bulan" required>
                                <?php
                                $bulan_list = [
                                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                ];
                                foreach ($bulan_list as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= $bulan == $key ? 'selected' : '' ?>>
                                        <?= $value ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <input type="number" class="form-control" id="tahun" name="tahun" 
                                   min="2000" max="2099" value="<?= $tahun ?>" required>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Total Pembayaran -->
            <div class="alert alert-info">
                <strong>Total Pembayaran Bulan <?= getBulan($bulan) ?> <?= $tahun ?>:</strong> 
                Rp <?= number_format($total, 0, ',', '.') ?>
            </div>
            
            <!-- Tabel Laporan -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal</th>
                                    <th>NISN</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Tahun SPP</th>
                                    <th>Jumlah</th>
                                    <th>Petugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pembayaran as $key => $p): ?>
                                <tr>
                                    <td><?= $key + 1 ?></td>
                                    <td><?= date('d/m/Y', strtotime($p['tgl_bayar'])) ?></td>
                                    <td><?= $p['nisn'] ?></td>
                                    <td><?= $p['nama_siswa'] ?></td>
                                    <td><?= $p['nama_kelas'] ?></td>
                                    <td><?= $p['tahun_spp'] ?></td>
                                    <td>Rp <?= number_format($p['jumlah_bayar'], 0, ',', '.') ?></td>
                                    <td><?= $p['nama_petugas'] ?></td>
                                </tr>
                                <?php endforeach; ?>
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