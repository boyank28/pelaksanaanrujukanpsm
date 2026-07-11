<?php
session_start();
include "../conf/koneksi.php";

if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$bulan = isset($_POST['bulan']) ? (int)$_POST['bulan'] : 0;
$tahun = isset($_POST['tahun']) ? (int)$_POST['tahun'] : 0;
$id_psm = isset($_POST['id_psm']) ? (int)$_POST['id_psm'] : 0;
$keterangan = isset($_POST['keterangan']) ? trim($_POST['keterangan']) : '';
$user = $_SESSION['ses_admin_pelaksanaanrujukanpsm'];

if ($bulan > 0 && $tahun > 0 && $id_psm > 0) {
    // Save Keterangan
    $sql = "INSERT INTO amprahan_keterangan (bulan, tahun, id_psm, keterangan) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE keterangan = VALUES(keterangan)";
    bukaquery_prepared($sql, "iiis", $bulan, $tahun, $id_psm, $keterangan);
    
    // Log Activity
    $aktivitas = "Mengubah keterangan amprahan PSM ID $id_psm untuk bulan $bulan tahun $tahun menjadi: $keterangan";
    $sql_log = "INSERT INTO log_aktivitas (waktu, username, aktivitas) VALUES (NOW(), ?, ?)";
    bukaquery_prepared($sql_log, "ss", $user, $aktivitas);
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
?>
