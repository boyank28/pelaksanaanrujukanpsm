<?php
session_start();
require_once('../conf/command.php');
require_once('../conf/conf.php');

if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) || (empty($_SESSION['permissions_psm']['can_view_laporan']) && !empty($_SESSION['permissions_psm']))) {
    die("Akses Ditolak");
}
global $db_name_sik, $konektor;

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Rekap_Pasien_PSM_{$bulan}_{$tahun}.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Ambil Master Fee
$master_fee = mysqli_fetch_array(bukaquery2("SELECT * FROM setting_fee_psm WHERE id=1"));
if(!$master_fee) $master_fee = ['fee_ranap_op'=>75000, 'fee_ranap'=>50000, 'fee_ralan'=>25000];
$f_ranap_op = (double)$master_fee['fee_ranap_op'];
$f_ranap = (double)$master_fee['fee_ranap'];
$f_ralan = (double)$master_fee['fee_ralan'];
$f_ralan_op = isset($master_fee['fee_ralan_op']) ? (double)$master_fee['fee_ralan_op'] : 125000;

$search_query = "";
$params = [$bulan, $tahun];
$types = "ii";
if ($keyword != '') {
    $search_query = " AND (ps.nm_pasien LIKE ? OR r.no_rkm_medis LIKE ? OR mpsm.nama_psm LIKE ?)";
    $like = "%$keyword%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}

$sql_detail = "SELECT 
    DATE(r.tgl_registrasi) as tgl_masuk,
    IFNULL(MAX(ki.tgl_keluar), '-') as tgl_pulang,
    r.no_rkm_medis as no_rm,
    ps.nm_pasien,
    IF(IFNULL(p.override_status, r.status_lanjut)='Ranap', IF(op.no_rawat IS NOT NULL, 'RANAP-OP', 'RANAP'), IF(op.no_rawat IS NOT NULL, 'RALAN-OP', 'RALAN')) as jenis_perawatan,
    pj.png_jawab as jaminan,
    mpsm.nama_psm,
    concat(ps.alamat, ', ', kel.nm_kel, ', ', kec.nm_kec) as alamat,
    ps.no_tlp as no_hp,
    IFNULL((SELECT totalpiutang FROM {$db_name_sik}.piutang_pasien WHERE no_rawat=r.no_rawat LIMIT 1), 0) as billing,
    IF(IFNULL(p.override_status, r.status_lanjut)='Ranap', IF(op.no_rawat IS NOT NULL, {$f_ranap_op}, {$f_ranap}), IF(op.no_rawat IS NOT NULL, {$f_ralan_op}, {$f_ralan})) as fee
FROM pelaksanaan_rujukan_psm p
INNER JOIN master_psm mpsm ON p.id_psm = mpsm.id_psm
INNER JOIN {$db_name_sik}.reg_periksa r ON p.no_rawat = r.no_rawat
INNER JOIN {$db_name_sik}.pasien ps ON r.no_rkm_medis = ps.no_rkm_medis
INNER JOIN {$db_name_sik}.penjab pj ON r.kd_pj = pj.kd_pj
LEFT JOIN {$db_name_sik}.kelurahan kel ON ps.kd_kel = kel.kd_kel
LEFT JOIN {$db_name_sik}.kecamatan kec ON ps.kd_kec = kec.kd_kec
LEFT JOIN {$db_name_sik}.kamar_inap ki ON r.no_rawat = ki.no_rawat AND ki.stts_pulang <> 'Pindah Kamar'
LEFT JOIN (SELECT no_rawat FROM {$db_name_sik}.operasi GROUP BY no_rawat) op ON r.no_rawat = op.no_rawat
WHERE MONTH(p.tanggal) = ? AND YEAR(p.tanggal) = ? $search_query
GROUP BY p.no_rawat
ORDER BY r.tgl_registrasi ASC";

$hasil_detail = bukaquery_prepared($sql_detail, $types, ...$params);

$bulans = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
$nama_bulan = $bulans[$bulan-1];
?>
<center>
    <h2>Data Rekap Pasien Rujukan PSM</h2>
    <h3>Bulan <?= $nama_bulan ?> Tahun <?= $tahun ?></h3>
</center>
<table border="1" cellpadding="5" cellspacing="0">
    <thead style="background-color: #e2e8f0;">
        <tr>
            <th>NO</th>
            <th>TGL MASUK</th>
            <th>TGL PULANG</th>
            <th>NO RM</th>
            <th>NAMA PASIEN</th>
            <th>JENIS PERAWATAN</th>
            <th>JAMINAN</th>
            <th>NAMA PSM</th>
            <th>ALAMAT</th>
            <th>NO HP</th>
            <th>BILLING PASIEN</th>
            <th>FEE</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no_detail = 1;
        $total_fee = 0;
        if ($hasil_detail && mysqli_num_rows($hasil_detail) > 0) {
            while ($r_det = mysqli_fetch_array($hasil_detail)) {
                $fee = (float)$r_det['fee'];
                $total_fee += $fee;
                echo "<tr>
                    <td>".$no_detail++."</td>
                    <td>".date('d/m/Y', strtotime($r_det['tgl_masuk']))."</td>
                    <td>".($r_det['tgl_pulang'] !== '-' && $r_det['tgl_pulang'] !== '0000-00-00' ? date('d/m/Y', strtotime($r_det['tgl_pulang'])) : '-')."</td>
                    <td>".e($r_det['no_rm'])."</td>
                    <td>".e($r_det['nm_pasien'])."</td>
                    <td>".e($r_det['jenis_perawatan'])."</td>
                    <td>".e($r_det['jaminan'])."</td>
                    <td>".e($r_det['nama_psm'])."</td>
                    <td>".e($r_det['alamat'])."</td>
                    <td>".e($r_det['no_hp'])."</td>
                    <td>".$r_det['billing']."</td>
                    <td>".$fee."</td>
                </tr>";
            }
            echo "<tr style='font-weight:bold;'><td colspan='11' style='text-align:right;'>TOTAL FEE:</td><td>".$total_fee."</td></tr>";
        } else {
            echo "<tr><td colspan='12' style='text-align:center;'>Tidak ada data pasien pada bulan ini</td></tr>";
        }
        ?>
    </tbody>
</table>
