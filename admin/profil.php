<?php
require_once __DIR__ . '/../includes/auth_check.php';

$title = 'Profil Pengguna';
require_once __DIR__ . '/../includes/header.php';

// Ambil data petugas
$id_petugas = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT * FROM petugas WHERE id_petugas = :id");
$stmt->execute([':id' => $id_petugas]);
$petugas = $stmt->fetch(PDO::FETCH_ASSOC);

$errors = [];
$success = '';

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_petugas = $_POST['nama_petugas'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validasi
    if (empty($nama_petugas)) $errors[] = 'Nama petugas wajib diisi';
    if (empty($username)) $errors[] = 'Username wajib diisi';
    
    // Cek username unik
    $stmt = $conn->prepare("SELECT COUNT(*) FROM petugas WHERE username = :username AND id_petugas != :id");
    $stmt->execute([':username' => $username, ':id' => $id_petugas]);
    if ($stmt->fetchColumn() > 0) $errors[] = 'Username sudah digunakan';
    
    // Validasi password jika diisi
    if (!empty($password)) {
        if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter';
        if ($password !== $password_confirm) $errors[] = 'Konfirmasi password tidak sesuai';
    }
    
    if (empty($errors)) {
        try {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    UPDATE petugas 
                    SET nama_petugas = :nama, username = :username, password = :password 
                    WHERE id_petugas = :id
                ");
                $stmt->execute([
                    ':nama' => $nama_petugas,
                    ':username' => $username,
                    ':password' => $hashed_password,
                    ':id' => $id_petugas
                ]);
            } else {
                $stmt = $conn->prepare("
                    UPDATE petugas 
                    SET nama_petugas = :nama, username = :username 
                    WHERE id_petugas = :id
                ");
                $stmt->execute([
                    ':nama' => $nama_petugas,
                    ':username' => $username,
                    ':id' => $id_petugas
                ]);
            }
            
            // Update session
            $_SESSION['user']['nama'] = $nama_petugas;
            $_SESSION['user']['username'] = $username;
            
            $success = 'Profil berhasil diperbarui';
        } catch(PDOException $e) {
            $errors[] = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Profil Pengguna</h1>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
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
                                    <label for="nama_petugas" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama_petugas" name="nama_petugas" 
                                           value="<?= htmlspecialchars($petugas['nama_petugas']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?= htmlspecialchars($petugas['username']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirm" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>