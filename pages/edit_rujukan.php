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
$id_psm = isset($_POST['id_psm']) ? $_POST['id_psm'] : '';
$back_url = safe_redirect_url(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?act=Dashboard');

if(!empty($no_rawat) && !empty($tanggal) && !empty($id_psm)) {
    $id_psm = validTeks4($id_psm, 11);
    
    // Update the record
    $q = bukaquery_prepared("UPDATE pelaksanaan_rujukan_psm SET id_psm=? WHERE no_rawat=? AND tanggal=?", "sss", $id_psm, $no_rawat, $tanggal);
    
    if($q) {
        catat_log("Mengedit data rujukan PSM dengan no rawat $no_rawat (ID PSM baru: $id_psm)");
        swal_alert('Data rujukan berhasil diperbarui!', e($back_url));
    } else {
        swal_alert('Gagal mengupdate data. Silakan coba lagi.', e($back_url));
    }
} else {
    swal_alert('Gagal! Parameter tidak lengkap.', e($back_url));
}
?>
