<?php
session_start();
require_once('../conf/command.php');
require_once('../conf/conf.php');

if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
    die("Akses Ditolak");
}

$tgl_awal = validTanggal(isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : '', date('Y-m-01'));
$tgl_akhir = validTanggal(isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '', date('Y-m-t'));
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

$search_query = "";
$params = array($tgl_awal, $tgl_akhir);
$types = "ss";
if ($keyword != '') {
    $search_query = " AND (p.no_rawat LIKE ? OR r.no_rkm_medis LIKE ? OR pasien.nm_pasien LIKE ? OR psm.nama_psm LIKE ? OR p.keterangan_diberikan_pada LIKE ? OR bsl.nm_bangsal LIKE ? OR pl.nm_poli LIKE ? OR po.nm_perawatan LIKE ? OR DATE_FORMAT(p.tanggal, '%d/%m/%Y') LIKE ?) ";
    $like = '%' . $keyword . '%';
    for ($i = 0; $i < 9; $i++) {
        $params[] = $like;
        $types .= "s";
    }
}

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Rujukan_PSM_{$tgl_awal}_sd_{$tgl_akhir}.xls");
header("Pragma: no-cache");
header("Expires: 0");

$setting = null;
$res = bukaquery2("SELECT * FROM setting_aplikasi WHERE id=1");
if($res) $setting = mysqli_fetch_array($res);
$nama_instansi = $setting ? $setting['nama_instansi'] : 'Instansi Kesehatan';

$_sql = "SELECT p.tanggal, p.no_rawat, r.no_rkm_medis, pasien.nm_pasien, psm.nama_psm as nama_psm_pengantar, 
         p.keterangan_diberikan_pada, IFNULL(bsl.nm_bangsal, pl.nm_poli) as nm_bangsal,
         op.kode_paket, po.nm_perawatan as nm_operasi, b.bukti
         FROM pelaksanaan_rujukan_psm p
         INNER JOIN {$db_name_sik}.reg_periksa r ON p.no_rawat = r.no_rawat
         INNER JOIN {$db_name_sik}.pasien pasien ON r.no_rkm_medis = pasien.no_rkm_medis
         LEFT JOIN master_psm psm ON p.id_psm = psm.id_psm
         LEFT JOIN {$db_name_sik}.kamar_inap ki ON r.no_rawat = ki.no_rawat AND ki.stts_pulang = '-'
         LEFT JOIN {$db_name_sik}.kamar k ON ki.kd_kamar = k.kd_kamar
         LEFT JOIN {$db_name_sik}.bangsal bsl ON k.kd_bangsal = bsl.kd_bangsal
         LEFT JOIN {$db_name_sik}.poliklinik pl ON r.kd_poli = pl.kd_poli
         LEFT JOIN {$db_name_sik}.operasi op ON p.no_rawat = op.no_rawat
         LEFT JOIN {$db_name_sik}.paket_operasi po ON op.kode_paket = po.kode_paket
         LEFT JOIN bukti_pelaksanaan_rujukan_psm b ON p.no_rawat = b.no_rawat AND date(p.tanggal) = b.tanggal 
         WHERE DATE(p.tanggal) BETWEEN ? AND ? $search_query
         ORDER BY p.tanggal DESC";
$hasil = bukaquery_prepared($_sql, $types, ...$params);
?>
<center>
    <h2><?= e($nama_instansi) ?></h2>
    <h3>Laporan Pelaksanaan Rujukan PSM</h3>
    <p>Periode: <?= date('d-m-Y', strtotime($tgl_awal)) ?> s/d <?= date('d-m-Y', strtotime($tgl_akhir)) ?></p>
</center>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr style="background-color: #e2e8f0;">
            <th>No</th>
            <th>Tanggal</th>
            <th>No. RM</th>
            <th>Nama Pasien</th>
            <th>Poli / Kamar / Tindakan</th>
            <th>PSM Pengantar</th>
            <th>Petugas Verifikasi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if($hasil && mysqli_num_rows($hasil) > 0) {
            $no = 1;
            while($row = mysqli_fetch_array($hasil)) {
                $lokasi = htmlspecialchars($row['nm_bangsal']);
                if ($row['nm_operasi'] != '') {
                    $lokasi = "OK: " . htmlspecialchars($row['nm_operasi']);
                }
                echo "<tr>
                        <td>".$no++."</td>
                        <td>".date('d/m/Y H:i', strtotime($row['tanggal']))."</td>
                        <td>".htmlspecialchars($row['no_rkm_medis'])."</td>
                        <td>".htmlspecialchars($row['nm_pasien'])."</td>
                        <td>".$lokasi."</td>
                        <td>".htmlspecialchars($row['nama_psm'])."</td>
                        <td>".htmlspecialchars($row['keterangan_diberikan_pada'])."</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='8'>Tidak ada data</td></tr>";
        }
        ?>
    </tbody>
</table>
