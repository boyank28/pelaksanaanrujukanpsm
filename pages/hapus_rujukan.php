<?php
if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) || (empty($_SESSION['permissions_psm']['can_manage_rujukan']) && !(empty($_SESSION['permissions_psm']) && $_SESSION['role_psm'] == 'Admin'))) {
    echo "<META HTTP-EQUIV='Refresh' Content='0; URL=?act=Dashboard'>";
    exit;
}

global $konektor;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<META HTTP-EQUIV='Refresh' Content='0; URL=?act=Dashboard'>";
    exit;
}
verify_csrf();
$no_rawat = isset($_POST['no_rawat']) ? $_POST['no_rawat'] : '';
$tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : '';
$back_url = safe_redirect_url(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?act=Dashboard');

if(!empty($no_rawat) && !empty($tanggal)) {
    // 1. Get the image path first to delete the physical file
    $q_bukti = bukaquery_prepared("SELECT bukti FROM bukti_pelaksanaan_rujukan_psm WHERE no_rawat=? AND tanggal=?", "ss", $no_rawat, $tanggal);
    if($d_bukti = mysqli_fetch_array($q_bukti)) {
        $img_path = $d_bukti['bukti'];
        // Make path absolute to ensure file_exists finds it
        $absolute_path = __DIR__ . '/../' . $img_path;
        if(!empty($img_path) && file_exists($absolute_path)) {
            @unlink($absolute_path);
        }
    }
    
    // 2. Delete from DB tables
    bukaquery_prepared("DELETE FROM bukti_pelaksanaan_rujukan_psm WHERE no_rawat=? AND tanggal=?", "ss", $no_rawat, $tanggal);
    bukaquery_prepared("DELETE FROM pelaksanaan_rujukan_psm WHERE no_rawat=? AND tanggal=?", "ss", $no_rawat, $tanggal);
    
    catat_log("Menghapus data rujukan PSM dengan no rawat $no_rawat");
    swal_alert('Data rujukan berhasil dihapus!', e($back_url));
} else {
    swal_alert('Gagal! Parameter tidak lengkap.', e($back_url));
}
?>
