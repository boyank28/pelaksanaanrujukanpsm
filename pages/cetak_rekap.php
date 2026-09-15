<?php
session_start();
require_once('../conf/command.php');
require_once('../conf/conf.php');

if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) || (empty($_SESSION['permissions_psm']['can_view_laporan']) && !empty($_SESSION['permissions_psm']))) {
    die("Akses Ditolak");
}
global $db_name_sik, $konektor;

if (isset($_GET['tgl_awal']) && isset($_GET['tgl_akhir'])) {
    $tgl_awal  = $_GET['tgl_awal'];
    $tgl_akhir = $_GET['tgl_akhir'];
} elseif (isset($_GET['bulan']) && isset($_GET['tahun'])) {
    $b         = sprintf("%02d", (int)$_GET['bulan']);
    $t         = (int)$_GET['tahun'];
    $tgl_awal  = "$t-$b-01";
    $tgl_akhir = date('Y-m-t', strtotime($tgl_awal));
} else {
    $tgl_awal  = date('Y-m-01');
    $tgl_akhir = date('Y-m-d');
}
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

// Ambil Master Fee
$master_fee = mysqli_fetch_array(bukaquery2("SELECT * FROM setting_fee_psm WHERE id=1"));
if(!$master_fee) $master_fee = ['fee_ranap_op'=>75000, 'fee_ranap'=>50000, 'fee_ralan'=>25000];
$f_ranap_op = (double)$master_fee['fee_ranap_op'];
$f_ranap = (double)$master_fee['fee_ranap'];
$f_ralan = (double)$master_fee['fee_ralan'];
$f_ralan_op = isset($master_fee['fee_ralan_op']) ? (double)$master_fee['fee_ralan_op'] : 125000;

$search_query = "";
$params = [$tgl_awal, $tgl_akhir];
$types = "ss";
if ($keyword != '') {
    $search_query = " AND mpsm.nama_psm LIKE ?";
    $like = "%$keyword%";
    $params[] = $like;
    $types .= "s";
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
    mpsm.no_telp as no_hp,
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
WHERE DATE(p.tanggal) BETWEEN ? AND ? $search_query
GROUP BY p.no_rawat
ORDER BY r.tgl_registrasi ASC, r.no_rawat ASC";

$hasil_detail = bukaquery_prepared($sql_detail, $types, ...$params);
$no_detail = 1;
$total_fee = 0;

$setting = null;
$res = bukaquery2("SELECT * FROM setting_aplikasi WHERE id=1");
if($res) $setting = mysqli_fetch_array($res);
$nama_instansi = $setting ? $setting['nama_instansi'] : 'Instansi Kesehatan';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cetak Rekap Pasien Rujukan PSM</title>
    <style>
        @page { size: landscape; margin: 10mm; }
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 0; padding: 10px; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #666; padding: 5px 7px; }
        th { background-color: #f1f5f9; font-weight: bold; text-align: center; }
    </style>
</head>
<body onload="window.print();">
    <h3 class="text-center fw-bold" style="margin-bottom: 5px;"><?= e($nama_instansi) ?></h3>
    <h4 class="text-center fw-bold" style="margin-top: 0; margin-bottom: 5px;">DATA REKAP PASIEN RUJUKAN PSM</h4>
    <p class="text-center" style="margin-top:0; margin-bottom: 15px; font-weight: 600;">Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> s.d. <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
    <table>
        <thead>
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
                <th>BILLING</th>
                <th>FEE</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($hasil_detail && mysqli_num_rows($hasil_detail) > 0): ?>
                <?php while ($r_det = mysqli_fetch_array($hasil_detail)): 
                    $fee = (float)$r_det['fee'];
                    $total_fee += $fee;
                ?>
                <tr>
                    <td class="text-center"><?= $no_detail++ ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($r_det['tgl_masuk'])) ?></td>
                    <td class="text-center"><?= ($r_det['tgl_pulang'] !== '-' && $r_det['tgl_pulang'] !== '0000-00-00' ? date('d/m/Y', strtotime($r_det['tgl_pulang'])) : '-') ?></td>
                    <td><?= e($r_det['no_rm']) ?></td>
                    <td><?= e($r_det['nm_pasien']) ?></td>
                    <td class="text-center"><?= e($r_det['jenis_perawatan']) ?></td>
                    <td><?= e($r_det['jaminan']) ?></td>
                    <td><?= e($r_det['nama_psm']) ?></td>
                    <td><?= e($r_det['alamat']) ?></td>
                    <td><?= e($r_det['no_hp']) ?></td>
                    <td class="text-end"><?= number_format((float)$r_det['billing'], 0, ',', '.') ?></td>
                    <td class="text-end"><?= number_format($fee, 0, ',', '.') ?></td>
                </tr>
                <?php endwhile; ?>
                <tr class="fw-bold">
                    <td colspan="11" class="text-end">TOTAL FEE KESELURUHAN:</td>
                    <td class="text-end"><?= number_format($total_fee, 0, ',', '.') ?></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="12" class="text-center">Tidak ada data</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
