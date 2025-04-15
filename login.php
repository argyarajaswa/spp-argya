<?php


require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $stmt = $conn->prepare("SELECT * FROM petugas WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Debugging: Tampilkan data yang dibaca
            echo "<pre>Data dari database:";
            print_r($user);
            echo "Password input: ".$password;
            echo "Password hash: ".$user['password'];
            echo "Verifikasi: ".password_verify($password, $user['password']);
            echo "</pre>";

            if (password_verify($password, $user['password']))  {
                $_SESSION['user'] = [
                    'id' => $user['id_petugas'],
                    'username' => $user['username'],
                    'nama' => $user['nama_petugas'],
                    'level' => $user['level']
                ];
                header('Location: admin/dashboard.php');
                exit;
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Username tidak ditemukan!';
        }
    } catch(PDOException $e) {
        $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Head content dengan CSS dari proyek referensi 1 -->
    <?php include 'includes/header.php'; ?>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4><i class="fas fa-lock"></i> Login MySPP</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>