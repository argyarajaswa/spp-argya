<?php
require_once __DIR__ . '/../../includes/auth_check.php';
checkAccess('admin');

$title = 'Tambah Siswa';
require_once __DIR__ . '/../../includes/header.php';

// Ambil data kelas untuk dropdown
$stmt = $conn->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");
$kelas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn = $_POST['nisn'] ?? '';
    $nis = $_POST['nis'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $id_kelas = $_POST['id_kelas'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $no_telp = $_POST['no_telp'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // Validasi
    if (empty($nisn)) $errors[] = 'NISN wajib diisi';
    if (empty($nis)) $errors[] = 'NIS wajib diisi';
    if (empty($nama)) $errors[] = 'Nama wajib diisi';
    if (empty($id_kelas)) $errors[] = 'Kelas wajib dipilih';
    if (empty($alamat)) $errors[] = 'Alamat wajib diisi';
    if (empty($no_telp)) $errors[] = 'No. Telepon wajib diisi';
    if (empty($email)) $errors[] = 'Email wajib diisi';
    
    // Cek NISN unik
    $stmt = $conn->prepare("SELECT COUNT(*) FROM siswa WHERE nisn = :nisn");
    $stmt->execute([':nisn' => $nisn]);
    if ($stmt->fetchColumn() > 0) $errors[] = 'NISN sudah terdaftar';
    
    // Cek NIS unik
    $stmt = $conn->prepare("SELECT COUNT(*) FROM siswa WHERE nis = :nis");
    $stmt->execute([':nis' => $nis]);
    if ($stmt->fetchColumn() > 0) $errors[] = 'NIS sudah terdaftar';
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO siswa (nisn, nis, nama, id_kelas, alamat, no_telp, email)
                VALUES (:nisn, :nis, :nama, :id_kelas, :alamat, :no_telp, :email)
            ");
            $stmt->execute([
                ':nisn' => $nisn,
                ':nis' => $nis,
                ':nama' => $nama,
                ':id_kelas' => $id_kelas,
                ':alamat' => $alamat,
                ':no_telp' => $no_telp,
                ':email' => $email
            ]);
            
            $_SESSION['success'] = 'Data siswa berhasil ditambahkan';
            header('Location: index.php');
            exit;
        } catch(PDOException $e) {
            $errors[] = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Tambah Siswa</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nisn" class="form-label">NISN</label>
                                    <input type="text" class="form-control" id="nisn" name="nisn" required>
                                </div>
                                <div class="mb-3">
                                    <label for="nis" class="form-label">NIS</label>
                                    <input type="text" class="form-control" id="nis" name="nis" required>
                                </div>
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama" name="nama" required>
                                </div>
                                <div class="mb-3">
                                    <label for="id_kelas" class="form-label">Kelas</label>
                                    <select class="form-select" id="id_kelas" name="id_kelas" required>
                                        <option value="">Pilih Kelas</option>
                                        <?php foreach ($kelas as $k): ?>
                                            <option value="<?= $k['id_kelas'] ?>">
                                                <?= htmlspecialchars($k['nama_kelas']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="no_telp" class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control" id="no_telp" name="no_telp" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>