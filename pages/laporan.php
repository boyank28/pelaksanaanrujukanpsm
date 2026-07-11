<?php
    if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
        echo "<META HTTP-EQUIV = 'Refresh' Content = '0; URL = ?act=Home'>";
        exit;
    }
    global $db_name_sik;

    $tgl_awal = validTanggal(isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : '', date('Y-m-01'));
    $tgl_akhir = validTanggal(isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '', date('Y-m-t'));

    // Filter Pencarian
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

    $limit = 50;
    $page = max(1, isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1);
    $offset = ($page - 1) * $limit;

    $count_sql = "SELECT COUNT(*) as total 
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
                  WHERE DATE(p.tanggal) BETWEEN ? AND ? $search_query";
    $count_res = bukaquery_prepared($count_sql, $types, ...$params);
    $total_data = ($count_res && mysqli_num_rows($count_res)>0) ? mysqli_fetch_array($count_res)['total'] : 0;
    $total_pages = ceil($total_data / $limit);

    $_sql = "SELECT p.*, r.no_rkm_medis, pasien.nm_pasien, psm.nama_psm, 
             IFNULL(bsl.nm_bangsal, pl.nm_poli) as nm_bangsal,
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
             ORDER BY p.tanggal DESC 
             LIMIT ?, ?";
    $data_params = $params;
    $data_params[] = $offset;
    $data_params[] = $limit;
    $hasil = bukaquery_prepared($_sql, $types . "ii", ...$data_params);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Rujukan PSM</title>
    <script src="js/jquery.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style type="text/css">
        body {
            background: #f8fafc !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            color: #334155 !important;
            padding-bottom: 40px;
            font-size: 13px !important;
        }
        .content-wrapper {
            background: #ffffff !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05) !important;
            padding: 25px !important;
            margin-top: 20px;
        }
        table { width: 100% !important; border-collapse: collapse !important; margin-top: 15px; }
        th { background: #f1f5f9; color: #0f766e; font-weight: 700; padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; color: #1e293b; }
        tr:hover { background: #f8fafc; }
        .btn-view { background: #0ea5e9; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; white-space: nowrap; display: inline-block; }
        .btn-view:hover { background: #0284c7; }
        .search-box { width: 100%; padding: 10px 15px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-family: inherit; outline: none; transition: all 0.2s; }
        .search-box:focus { border-color: #0f766e; box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1); }
        .pagination .page-item.active .page-link { background-color: #0f766e; border-color: #0f766e; }
        .pagination .page-link { color: #0f766e; box-shadow: none; }
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container content-wrapper">
        <h4 class="mb-4" style="color: #0f766e; font-weight: 800;"><i class="bi bi-file-earmark-bar-graph-fill me-2"></i> Laporan Detail Rujukan PSM</h4>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <form method="GET" action="" class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="hidden" name="act" value="Laporan">
                    <div class="input-group" style="width: auto;">
                        <span class="input-group-text bg-light text-dark fw-bold border-end-0"><i class="bi bi-calendar3 me-2"></i> Periode</span>
                        <input type="date" name="tgl_awal" class="form-control" value="<?= e($tgl_awal) ?>">
                        <span class="input-group-text bg-light text-dark">s/d</span>
                        <input type="date" name="tgl_akhir" class="form-control" value="<?= e($tgl_akhir) ?>">
                    </div>
                    <div class="input-group" style="flex: 1; min-width: 200px;">
                        <input type="text" name="keyword" class="form-control" placeholder="Cari Pasien, PSM, atau No. Rawat..." value="<?= htmlspecialchars($keyword) ?>">
                        <button class="btn btn-primary" style="background:#0f766e; border:none;" type="submit"><i class="bi bi-search"></i> Cari</button>
                    </div>
                    <a href="?act=CetakLaporan&tgl_awal=<?= urlencode($tgl_awal) ?>&tgl_akhir=<?= urlencode($tgl_akhir) ?>&keyword=<?= urlencode($keyword) ?>" target="_blank" class="btn btn-secondary" style="background: #475569; color: white; border: none; border-radius: 8px;"><i class="bi bi-printer me-1"></i> Cetak PDF</a>
                    <a href="pages/export_excel.php?tgl_awal=<?= urlencode($tgl_awal) ?>&tgl_akhir=<?= urlencode($tgl_akhir) ?>&keyword=<?= urlencode($keyword) ?>" class="btn btn-success" style="border: none; border-radius: 8px;"><i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel</a>
                </form>
            </div>
        </div>

        <div style="overflow-x: auto; border: 1px solid #cbd5e1; border-radius: 10px;">
            <table style="margin-top: 0; min-width: 900px;">
                <thead>
                    <tr>
                        <th style="border-radius: 10px 0 0 0; padding-left: 20px;">Tanggal</th>
                        <th>No. Rawat</th>
                        <th>Nama Pasien</th>
                        <th>Poli / Kamar / Tindakan</th>
                        <th>PSM Pengantar</th>
                        <th>Petugas Verifikasi</th>
                        <th style="border-radius: 0 10px 0 0; text-align: center;">Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if($hasil && mysqli_num_rows($hasil) > 0) {
                            while($row = mysqli_fetch_array($hasil)) {
                                $lokasi = htmlspecialchars($row['nm_bangsal']);
                                if ($row['nm_operasi'] != '') {
                                    $lokasi = "<span class='badge bg-light text-dark border'><i class='bi bi-heart-pulse text-danger me-1'></i> OK: ".htmlspecialchars($row['nm_operasi'])."</span>";
                                } else {
                                    $lokasi = "<span class='badge bg-light text-dark border'>".$lokasi."</span>";
                                }
                                echo "<tr>
                                        <td style='padding-left: 20px;'>".date('d/m/Y H:i', strtotime($row['tanggal']))."</td>
                                        <td>".htmlspecialchars($row['no_rawat'])."</td>
                                        <td class='fw-bold' style='color:#0f766e;'>".htmlspecialchars($row['nm_pasien'])."</td>
                                        <td>".$lokasi."</td>
                                        <td><span class='badge' style='background:#e0f2fe; color:#0369a1;'>".htmlspecialchars($row['nama_psm'])."</span></td>
                                        <td>".htmlspecialchars($row['keterangan_diberikan_pada'])."</td>
                                        <td style='text-align: center;'>
                                            <a href='?act=LihatFoto&img=".urlencode($row['bukti'])."' target='_blank' class='btn-view'><i class='bi bi-image me-1'></i> Lihat Foto</a>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center py-4 text-muted'>Tidak ada data pelaksanaan rujukan untuk periode ini.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
        
        <?php if($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?act=Laporan&tgl_awal=<?= urlencode($tgl_awal) ?>&tgl_akhir=<?= urlencode($tgl_akhir) ?>&keyword=<?= urlencode($keyword) ?>&halaman=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</body>
</html>
