<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_petugas = $_POST['id_petugas'] ?? '';
    $nisn = $_POST['nisn'] ?? '';
    $tgl_bayar = date('Y-m-d');
    $bulan_dibayar = $_POST['bulan_dibayar'] ?? '';
    $tahun_dibayar = $_POST['tahun_dibayar'] ?? '';
    $id_spp = $_POST['id_spp'] ?? '';
    $jumlah_bayar = $_POST['jumlah_bayar'] ?? '';
    
    // Validasi
    if (empty($nisn)) $errors[] = 'NISN siswa wajib diisi';
    if (empty($bulan_dibayar)) $errors[] = 'Bulan dibayar wajib dipilih';
    if (empty($tahun_dibayar)) $errors[] = 'Tahun dibayar wajib diisi';
    if (empty($id_spp)) $errors[] = 'Tahun SPP wajib dipilih';
    if (empty($jumlah_bayar)) $errors[] = 'Jumlah bayar wajib diisi';
    
    // Cek apakah siswa sudah membayar SPP bulan ini
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM pembayaran 
        WHERE nisn = :nisn 
        AND bulan_dibayar = :bulan 
        AND tahun_dibayar = :tahun
    ");
    $stmt->execute([
        ':nisn' => $nisn,
        ':bulan' => $bulan_dibayar,
        ':tahun' => $tahun_dibayar
    ]);
    
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Siswa sudah membayar SPP bulan ini';
    }
    
    if (empty($errors)) {
        try {
            // Mulai transaksi
            $conn->beginTransaction();
            
            // Insert pembayaran
            $stmt = $conn->prepare("
                INSERT INTO pembayaran (
                    id_petugas, nisn, tgl_bayar, bulan_dibayar, 
                    tahun_dibayar, id_spp, jumlah_bayar, status
                ) VALUES (
                    :id_petugas, :nisn, :tgl_bayar, :bulan_dibayar, 
                    :tahun_dibayar, :id_spp, :jumlah_bayar, 'lunas'
                )
            ");
            $stmt->execute([
                ':id_petugas' => $id_petugas,
                ':nisn' => $nisn,
                ':tgl_bayar' => $tgl_bayar,
                ':bulan_dibayar' => $bulan_dibayar,
                ':tahun_dibayar' => $tahun_dibayar,
                ':id_spp' => $id_spp,
                ':jumlah_bayar' => $jumlah_bayar
            ]);
            
            // Dapatkan ID pembayaran yang baru dibuat
            $id_pembayaran = $conn->lastInsertId();
            
            // Commit transaksi
            $conn->commit();
            
            // Kirim email kwitansi ke siswa
            require_once __DIR__ . '/../../libs/email_sender.php';
            require_once __DIR__ . '/../../libs/pdf_generator.php';
            
            $pdf_path = generatePaymentReceipt($id_pembayaran);
            sendReceiptEmail($id_pembayaran, $pdf_path);
            
            $_SESSION['success'] = 'Pembayaran berhasil dicatat dan kwitansi telah dikirim ke email siswa';
            header('Location: index.php');
            exit;
        } catch(PDOException $e) {
            $conn->rollBack();
            $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
            header('Location: index.php');
            exit;
        }
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>