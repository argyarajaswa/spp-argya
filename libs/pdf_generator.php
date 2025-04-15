<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require __DIR__ . '/../../vendor/autoload.php'; // Pastikan composer dan TCPDF terinstall

function generatePaymentReceipt($id_pembayaran) {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Ambil data pembayaran
    $stmt = $conn->prepare("
        SELECT p.*, s.nama as nama_siswa, s.alamat, s.no_telp, s.email,
               k.nama_kelas, k.kompetensi_keahlian,
               pt.nama_petugas, sp.tahun as tahun_spp, sp.nominal
        FROM pembayaran p
        JOIN siswa s ON p.nisn = s.nisn
        JOIN kelas k ON s.id_kelas = k.id_kelas
        JOIN petugas pt ON p.id_petugas = pt.id_petugas
        JOIN spp sp ON p.id_spp = sp.id_spp
        WHERE p.id_pembayaran = :id
    ");
    $stmt->execute([':id' => $id_pembayaran]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$data) {
        throw new Exception("Data pembayaran tidak ditemukan");
    }
    
    // Buat PDF baru
    $pdf = new TCPDF('P', 'mm', 'A5', true, 'UTF-8', false);
    
    // Set dokumen meta
    $pdf->SetCreator('MySPP');
    $pdf->SetAuthor('MySPP');
    $pdf->SetTitle('Kwitansi Pembayaran SPP');
    $pdf->SetSubject('Kwitansi Pembayaran SPP');
    
    // Margin
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    
    // Auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Add a page
    $pdf->AddPage();
    
    // Logo sekolah
    $logo = __DIR__ . '/../assets/images/logo.png';
    if (file_exists($logo)) {
        $pdf->Image($logo, 15, 10, 25, 25, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    }
    
    // Header
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'SEKOLAH MENENGAH KEJURUAN', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'SMK BINA INSAN MANDIRI', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'Jl. Pendidikan No. 123, Jakarta - Telp. (021) 1234567', 0, 1, 'C');
    
    // Garis pemisah
    $pdf->Line(15, 40, 140, 40);
    
    // Judul kwitansi
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'KWITANSI PEMBAYARAN SPP', 0, 1, 'C');
    
    // Informasi pembayaran
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(30, 6, 'No. Kwitansi', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, 'SPP-' . str_pad($id_pembayaran, 5, '0', STR_PAD_LEFT), 0, 1);
    
    $pdf->Cell(30, 6, 'Tanggal', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, date('d F Y', strtotime($data['tgl_bayar'])), 0, 1);
    
    $pdf->Cell(30, 6, 'NISN', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $data['nisn'], 0, 1);
    
    $pdf->Cell(30, 6, 'Nama Siswa', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $data['nama_siswa'], 0, 1);
    
    $pdf->Cell(30, 6, 'Kelas', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $data['nama_kelas'] . ' - ' . $data['kompetensi_keahlian'], 0, 1);
    
    $pdf->Cell(30, 6, 'Periode', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, getBulan($data['bulan_dibayar']) . ' ' . $data['tahun_dibayar'], 0, 1);
    
    $pdf->Cell(30, 6, 'Tahun SPP', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $data['tahun_spp'], 0, 1);
    
    $pdf->Cell(30, 6, 'Nominal', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, 'Rp ' . number_format($data['nominal'], 0, ',', '.'), 0, 1);
    
    $pdf->Cell(30, 6, 'Jumlah Bayar', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'Rp ' . number_format($data['jumlah_bayar'], 0, ',', '.'), 0, 1);
    
    // Garis pemisah
    $pdf->Line(15, $pdf->GetY() + 5, 140, $pdf->GetY() + 5);
    
    // Tanda tangan
    $pdf->Ln(15);
    $pdf->Cell(0, 6, 'Jakarta, ' . date('d F Y'), 0, 1, 'R');
    $pdf->Ln(15);
    
    // Tambahkan tanda tangan digital
    $signature = __DIR__ . '/../assets/images/signature.png';
    if (file_exists($signature)) {
        $pdf->Image($signature, 110, $pdf->GetY(), 30, 15, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    }
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, '( ' . $data['nama_petugas'] . ' )', 0, 1, 'R');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 4, 'Petugas MySPP', 0, 1, 'R');
    
    // Simpan file PDF
    $pdf_dir = __DIR__ . '/../../uploads/receipts/';
    if (!file_exists($pdf_dir)) {
        mkdir($pdf_dir, 0777, true);
    }
    
    $filename = 'receipt_' . $id_pembayaran . '_' . time() . '.pdf';
    $filepath = $pdf_dir . $filename;
    $pdf->Output($filepath, 'F');
    
    return $filepath;
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