<?php
require_once __DIR__ . '/../../includes/auth_check.php';

$title = 'Transaksi Pembayaran';
require_once __DIR__ . '/../../includes/header.php';

// Ambil data pembayaran dengan join ke tabel siswa, petugas, dan spp
$stmt = $conn->query("
    SELECT p.*, s.nama as nama_siswa, pt.nama_petugas, sp.tahun, sp.nominal
    FROM pembayaran p
    JOIN siswa s ON p.nisn = s.nisn
    JOIN petugas pt ON p.id_petugas = pt.id_petugas
    JOIN spp sp ON p.id_spp = sp.id_spp
    ORDER BY p.tgl_bayar DESC
");
$pembayaran = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Transaksi Pembayaran</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="cetak.php" class="btn btn-sm btn-success me-2" target="_blank">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </a>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                        <i class="fas fa-plus"></i> Tambah Transaksi
                    </button>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Tanggal Bayar</th>
                            <th>Bulan</th>
                            <th>Tahun</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Petugas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pembayaran as $key => $p): ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td><?= htmlspecialchars($p['nisn']) ?></td>
                            <td><?= htmlspecialchars($p['nama_siswa']) ?></td>
                            <td><?= date('d/m/Y', strtotime($p['tgl_bayar'])) ?></td>
                            <td><?= getBulan($p['bulan_dibayar']) ?></td>
                            <td><?= $p['tahun_dibayar'] ?></td>
                            <td>Rp <?= number_format($p['jumlah_bayar'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge bg-<?= $p['status'] === 'lunas' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($p['nama_petugas']) ?></td>
                            <td>
                                <a href="cetak.php?id=<?= $p['id_pembayaran'] ?>" class="btn btn-sm btn-info" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Modal Tambah Transaksi -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahModalLabel">Tambah Transaksi Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="proses_tambah.php">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nisn" class="form-label">NISN Siswa</label>
                                <input type="text" class="form-control" id="nisn" name="nisn" required>
                                <div id="siswaInfo" class="mt-2"></div>
                            </div>
                            <div class="mb-3">
                                <label for="id_spp" class="form-label">Tahun SPP</label>
                                <select class="form-select" id="id_spp" name="id_spp" required>
                                    <option value="">Pilih Tahun SPP</option>
                                    <?php
                                    $stmt = $conn->query("SELECT * FROM spp ORDER BY tahun DESC");
                                    $spp = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($spp as $s): ?>
                                        <option value="<?= $s['id_spp'] ?>" data-nominal="<?= $s['nominal'] ?>">
                                            <?= $s['tahun'] ?> - Rp <?= number_format($s['nominal'], 0, ',', '.') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bulan_dibayar" class="form-label">Bulan Dibayar</label>
                                <select class="form-select" id="bulan_dibayar" name="bulan_dibayar" required>
                                    <option value="">Pilih Bulan</option>
                                    <?php
                                    $bulan = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', 
                                              '04' => 'April', '05' => 'Mei', '06' => 'Juni', 
                                              '07' => 'Juli', '08' => 'Agustus', '09' => 'September', 
                                              '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
                                    foreach ($bulan as $key => $value): ?>
                                        <option value="<?= $key ?>"><?= $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="tahun_dibayar" class="form-label">Tahun Dibayar</label>
                                <input type="number" class="form-control" id="tahun_dibayar" name="tahun_dibayar" 
                                       min="2000" max="2099" value="<?= date('Y') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="jumlah_bayar" class="form-label">Jumlah Bayar</label>
                                <input type="number" class="form-control" id="jumlah_bayar" name="jumlah_bayar" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id_petugas" value="<?= $_SESSION['user']['id'] ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// AJAX untuk cari siswa
$('#nisn').on('input', function() {
    const nisn = $(this).val();
    if (nisn.length === 10) {
        $.ajax({
            url: 'cari_siswa.php',
            method: 'POST',
            data: { nisn: nisn },
            success: function(response) {
                $('#siswaInfo').html(response);
            }
        });
    } else {
        $('#siswaInfo').html('');
    }
});

// Update jumlah bayar saat SPP dipilih
$('#id_spp').change(function() {
    const nominal = $(this).find(':selected').data('nominal');
    $('#jumlah_bayar').val(nominal);
});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';

// Helper function untuk konversi angka bulan ke nama bulan
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