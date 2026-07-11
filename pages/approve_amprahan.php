<?php
session_start();
if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
    http_response_code(401);
    exit;
}

require_once('../conf/conf.php');

$bulan = isset($_POST['bulan']) ? (int)$_POST['bulan'] : 0;
$tahun = isset($_POST['tahun']) ? (int)$_POST['tahun'] : 0;
$tipe  = isset($_POST['tipe']) ? $_POST['tipe'] : ''; // mengetahui, dicek, menyetujui

if($bulan == 0 || $tahun == 0 || empty($tipe)) {
    http_response_code(400);
    exit;
}

// Pastikan tabel ada
bukaquery2("CREATE TABLE IF NOT EXISTS amprahan_approval (
    bulan INT, 
    tahun INT, 
    tipe_approval VARCHAR(50), 
    username VARCHAR(100), 
    nama_lengkap VARCHAR(150),
    waktu_approval DATETIME,
    PRIMARY KEY(bulan, tahun, tipe_approval)
)");

$username = $_SESSION['ses_admin_pelaksanaanrujukanpsm'];
$nama_lengkap = isset($_SESSION['nama_lengkap_psm']) ? $_SESSION['nama_lengkap_psm'] : $username;
$perms = isset($_SESSION['permissions_psm']) ? $_SESSION['permissions_psm'] : [];
$role = isset($_SESSION['role_psm']) ? $_SESSION['role_psm'] : '';

// Cek hak akses sesuai tipe
$has_access = false;
if($role == 'Admin') {
    $has_access = true;
} else {
    if($tipe == 'mengetahui' && !empty($perms['can_approve_mengetahui'])) $has_access = true;
    if($tipe == 'dicek' && !empty($perms['can_approve_dicek'])) $has_access = true;
    if($tipe == 'menyetujui' && !empty($perms['can_approve_menyetujui'])) $has_access = true;
}

if(!$has_access) {
    echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk validasi ini.']);
    exit;
}

// Cek apakah sudah ada (kalau sudah, berarti batal/hapus, kalau belum, berarti insert)
$cek = bukaquery_prepared("SELECT * FROM amprahan_approval WHERE bulan=? AND tahun=? AND tipe_approval=?", "iis", $bulan, $tahun, $tipe);
if($cek && mysqli_num_rows($cek) > 0) {
    // Batal validasi
    bukaquery_prepared("DELETE FROM amprahan_approval WHERE bulan=? AND tahun=? AND tipe_approval=?", "iis", $bulan, $tahun, $tipe);
    catat_log("Membatalkan validasi ($tipe) laporan Amprahan bulan $bulan tahun $tahun");
    echo json_encode(['status' => 'success', 'action' => 'removed']);
} else {
    // Setujui
    bukaquery_prepared("INSERT INTO amprahan_approval (bulan, tahun, tipe_approval, username, nama_lengkap, waktu_approval) VALUES (?, ?, ?, ?, ?, NOW())", 
        "iisss", $bulan, $tahun, $tipe, $username, $nama_lengkap);
    catat_log("Melakukan validasi ($tipe) laporan Amprahan bulan $bulan tahun $tahun");
    
    // Ambil waktu
    $dt = bukaquery_prepared("SELECT waktu_approval FROM amprahan_approval WHERE bulan=? AND tahun=? AND tipe_approval=?", "iis", $bulan, $tahun, $tipe);
    $waktu = date('d/m/Y H:i');
    if($dt && mysqli_num_rows($dt) > 0) {
        $row = mysqli_fetch_array($dt);
        $waktu = date('d/m/Y H:i', strtotime($row['waktu_approval']));
    }
    
    echo json_encode(['status' => 'success', 'action' => 'added', 'nama_lengkap' => $nama_lengkap, 'waktu' => $waktu]);
}
?>
