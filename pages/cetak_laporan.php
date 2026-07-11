<?php
    global $db_name_sik;
    if(strpos($_SERVER['REQUEST_URI'],"pages")){
        exit(header("Location:../index.php"));
    }
    
    // Date Filter Logic
    $tgl_awal = validTanggal(isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : '', date('Y-m-01'));
    $tgl_akhir = validTanggal(isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '', date('Y-m-t'));
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
    
    $where_keyword = "";
    $params = array($tgl_awal, $tgl_akhir);
    $types = "ss";
    if($keyword != "") {
        $where_keyword = " AND (p.no_rawat LIKE ? OR r.no_rkm_medis LIKE ? OR ps.nm_pasien LIKE ? OR mpsm.nama_psm LIKE ? OR p.keterangan_diberikan_pada LIKE ? OR bsl.nm_bangsal LIKE ? OR pl.nm_poli LIKE ? OR po.nm_perawatan LIKE ? OR DATE_FORMAT(p.tanggal, '%d/%m/%Y') LIKE ?) ";
        $like = '%' . $keyword . '%';
        for ($i = 0; $i < 9; $i++) {
            $params[] = $like;
            $types .= "s";
        }
    }
    
    $_sql = "SELECT p.tanggal, p.no_rawat, r.no_rkm_medis, ps.nm_pasien, mpsm.nama_psm as nama_psm_pengantar, p.keterangan_diberikan_pada, b.bukti,
             IFNULL((SELECT CONCAT('OK: ', po.nm_perawatan) FROM {$db_name_sik}.operasi op INNER JOIN {$db_name_sik}.paket_operasi po ON op.kode_paket=po.kode_paket WHERE op.no_rawat=p.no_rawat LIMIT 1), IFNULL(bsl.nm_bangsal, pl.nm_poli)) as nm_bangsal 
             FROM pelaksanaan_rujukan_psm p 
             INNER JOIN master_psm mpsm ON p.id_psm = mpsm.id_psm
             INNER JOIN {$db_name_sik}.reg_periksa r ON p.no_rawat = r.no_rawat 
             INNER JOIN {$db_name_sik}.pasien ps ON r.no_rkm_medis = ps.no_rkm_medis 
             LEFT JOIN {$db_name_sik}.kamar_inap ki ON r.no_rawat = ki.no_rawat AND ki.stts_pulang = '-'
             LEFT JOIN {$db_name_sik}.kamar k ON ki.kd_kamar = k.kd_kamar
             LEFT JOIN {$db_name_sik}.bangsal bsl ON k.kd_bangsal = bsl.kd_bangsal
             LEFT JOIN {$db_name_sik}.permintaan_ranap pr ON r.no_rawat = pr.no_rawat
             LEFT JOIN {$db_name_sik}.poliklinik pl ON r.kd_poli = pl.kd_poli
             LEFT JOIN bukti_pelaksanaan_rujukan_psm b ON p.no_rawat = b.no_rawat AND date(p.tanggal) = b.tanggal 
             WHERE DATE(p.tanggal) BETWEEN ? AND ?
             $where_keyword
             ORDER BY p.tanggal ASC";
    $hasil = bukaquery_prepared($_sql, $types, ...$params);
    
    $namars        = getOne("select setting.nama_instansi from {$db_name_sik}.setting");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cetak Laporan Rujukan PSM</title>
    <style>
        body { font-family: Tahoma, sans-serif; font-size: 12px; color: #000; margin: 30px 40px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 4px double #000; padding-bottom: 15px; }
        .header h3 { margin: 0 0 5px 0; font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header h4 { margin: 0 0 10px 0; font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        .header p { margin: 0; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; border: 1px solid #000; }
        th, td { border: 1px solid #000; padding: 10px 8px; text-align: left; vertical-align: middle; }
        th { background-color: #e9ecef; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 11px; border-top: 1px solid #000; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 20px; }
            tr:nth-child(even) { background-color: transparent; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; background: #f8f9fa; padding: 15px; border: 1px solid #ddd; border-radius: 5px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #0f766e; color: white; border: none; border-radius: 4px; font-weight: bold; margin-right: 10px;">Cetak Sekarang</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; background: #6c757d; color: white; border: none; border-radius: 4px; font-weight: bold;">Tutup</button>
    </div>

    <div class="header">
        <h3>LAPORAN PELAKSANAAN RUJUKAN PSM</h3>
        <h4><?=e($namars)?></h4>
        <p>Periode: <?= date('d-m-Y', strtotime($tgl_awal)) ?> s/d <?= date('d-m-Y', strtotime($tgl_akhir)) ?></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="15%">No. RM</th>
                <th width="25%">Nama Pasien</th>
                <th width="15%">Poli / Kamar</th>
                <th width="15%">PSM Pengantar</th>
                <th width="10%">Verifikator</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $no = 1;
                if($hasil && mysqli_num_rows($hasil) > 0) {
                    while ($row = mysqli_fetch_array($hasil)) {
                        echo "<tr>";
                        echo "<td style='text-align: center;'>".$no++."</td>";
                        echo "<td style='text-align: center;'>".date('d-m-Y', strtotime($row['tanggal']))."<br/><span style='color:#555; font-size:10px;'>".date('H:i:s', strtotime($row['tanggal']))."</span></td>";
                        echo "<td style='text-align: center;'>".e($row['no_rkm_medis'])."</td>";
                        echo "<td><strong>".e($row['nm_pasien'])."</strong></td>";
                        echo "<td>".e($row['nm_bangsal'])."</td>";
                        echo "<td style='text-align: center;'>".e($row['nama_psm_pengantar'])."</td>";
                        echo "<td style='text-align: center;'>".e(ucfirst($row['keterangan_diberikan_pada']))."</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center; padding: 20px;'>Tidak ada data rujukan pada periode ini.</td></tr>";
                }
            ?>
        </tbody>
    </table>
    
    <div style="margin-top: 50px; width: 100%; display: flex; justify-content: flex-end; font-family: Tahoma, sans-serif;">
        <div style="text-align: center; width: 250px;">
            <p style="margin-bottom: 70px;">Mengetahui,<br/><strong>Petugas Verifikator</strong></p>
            <p style="font-weight: bold; text-decoration: underline; font-size: 14px;">( <?= isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) ? e(ucfirst($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) : '.....................................' ?> )</p>
        </div>
    </div>
</body>
</html>
