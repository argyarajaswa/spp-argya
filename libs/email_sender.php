<?php
require_once __DIR__ . '/../config/config.php';
require __DIR__ . '/../../vendor/autoload.php'; // Pastikan PHPMailer terinstall via composer

function sendReceiptEmail($id_pembayaran, $pdf_path) {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Ambil data pembayaran dan siswa
    $stmt = $conn->prepare("
        SELECT p.*, s.nama as nama_siswa, s.email as siswa_email
        FROM pembayaran p
        JOIN siswa s ON p.nisn = s.nisn
        WHERE p.id_pembayaran = :id
    ");
    $stmt->execute([':id' => $id_pembayaran]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$data) {
        throw new Exception("Data pembayaran tidak ditemukan");
    }
    
    // Buat instance PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'argya.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'arrajzgay@gmail.com'; // Ganti dengan email sekolah
        $mail->Password   = 'argyaargya6';         // Ganti dengan password email
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        
        // Recipients
        $mail->setFrom(APP_EMAIL, APP_NAME);
        $mail->addAddress($data['siswa_email'], $data['nama_siswa']);
        
        // Attachments
        $mail->addAttachment($pdf_path, 'Kwitansi_SPP_' . $data['nisn'] . '.pdf');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Kwitansi Pembayaran SPP - ' . APP_NAME;
        
        $email_body = '
            <!DOCTYPE html>
            <html>
            <head>
                <title>Kwitansi Pembayaran SPP</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .content { margin-bottom: 20px; }
                    .footer { text-align: center; font-size: 12px; color: #777; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>' . APP_NAME . '</h2>
                        <h3>Kwitansi Pembayaran SPP</h3>
                    </div>
                    <div class="content">
                        <p>Halo ' . $data['nama_siswa'] . ',</p>
                        <p>Terima kasih telah melakukan pembayaran SPP. Berikut detail pembayaran Anda:</p>
                        <ul>
                            <li>No. Kwitansi: SPP-' . str_pad($id_pembayaran, 5, "0", STR_PAD_LEFT) . '</li>
                            <li>Tanggal: ' . date('d F Y', strtotime($data['tgl_bayar'])) . '</li>
                            <li>Bulan: ' . getBulan($data['bulan_dibayar']) . ' ' . $data['tahun_dibayar'] . '</li>
                            <li>Jumlah: Rp ' . number_format($data['jumlah_bayar'], 0, ',', '.') . '</li>
                        </ul>
                        <p>Kwitansi resmi dalam format PDF terlampir pada email ini.</p>
                    </div>
                    <div class="footer">
                        <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
                        <p>&copy; ' . date('Y') . ' ' . APP_NAME . ' - All rights reserved</p>
                    </div>
                </div>
            </body>
            </html>
        ';
        
        $mail->Body    = $email_body;
        $mail->AltBody = strip_tags($email_body);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email tidak dapat dikirim. Error: {$mail->ErrorInfo}");
        return false;
    }
}

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