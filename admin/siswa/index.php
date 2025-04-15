<?php
require_once __DIR__ . '/../../includes/auth_check.php';
checkAccess('admin');

$title = 'Manajemen Siswa';
require_once __DIR__ . '/../../includes/header.php';

// Ambil data siswa dengan join ke tabel kelas
$stmt = $conn->query("
    SELECT s.*, k.nama_kelas, k.kompetensi_keahlian 
    FROM siswa s 
    JOIN kelas k ON s.id_kelas = k.id_kelas
    ORDER BY s.nama ASC
");
$siswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Data Siswa</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="tambah.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tambah Siswa
                    </a>
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
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Jurusan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siswa as $key => $s): ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td><?= htmlspecialchars($s['nisn']) ?></td>
                            <td><?= htmlspecialchars($s['nis']) ?></td>
                            <td><?= htmlspecialchars($s['nama']) ?></td>
                            <td><?= htmlspecialchars($s['nama_kelas']) ?></td>
                            <td><?= htmlspecialchars($s['kompetensi_keahlian']) ?></td>
                            <td>
                                <a href="edit.php?nisn=<?= $s['nisn'] ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="hapus.php?nisn=<?= $s['nisn'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                    <i class="fas fa-trash"></i>
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

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>