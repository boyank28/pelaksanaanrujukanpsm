<?php
    if(strpos($_SERVER['REQUEST_URI'],"pages")){
        exit(header("Location:../index.php"));
    }

    if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
        echo "<META HTTP-EQUIV='Refresh' Content='0; URL=?act=Home'>";
        exit;
    }

    global $db_name_sik, $konektor;

    $tgl_awal = validTanggal(isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : '', date('Y-m-d'));
    $tgl_akhir = validTanggal(isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '', date('Y-m-d'));
    $jenis_filter = isset($_GET['jenis_filter']) && in_array($_GET['jenis_filter'], array('Poli', 'Operasi'), true) ? $_GET['jenis_filter'] : '';
    $nama_filter = isset($_GET['nama_filter']) ? $_GET['nama_filter'] : '';

    // Fetch dynamic options for the second dropdown
    $options_html = '<option value="">-- Semua --</option>';
    if ($jenis_filter == 'Poli') {
        // Query Bangsal & Poli that have rujukan records
        $q_poli = "SELECT DISTINCT IFNULL(bsl.nm_bangsal, pl.nm_poli) as nama_lokasi 
                   FROM pelaksanaan_rujukan_psm p
                   INNER JOIN {$db_name_sik}.reg_periksa r ON p.no_rawat = r.no_rawat
                   LEFT JOIN {$db_name_sik}.kamar_inap ki ON r.no_rawat = ki.no_rawat AND ki.stts_pulang = '-'
                   LEFT JOIN {$db_name_sik}.kamar k ON ki.kd_kamar = k.kd_kamar
                   LEFT JOIN {$db_name_sik}.bangsal bsl ON k.kd_bangsal = bsl.kd_bangsal
                   LEFT JOIN {$db_name_sik}.poliklinik pl ON r.kd_poli = pl.kd_poli
                   WHERE DATE(p.tanggal) BETWEEN ? AND ?
                   ORDER BY nama_lokasi ASC";
        $r_poli = bukaquery_prepared($q_poli, "ss", $tgl_awal, $tgl_akhir);
        if($r_poli) {
            while($d = mysqli_fetch_array($r_poli)) {
                if(!empty($d['nama_lokasi'])) {
                    $selected = ($nama_filter == $d['nama_lokasi']) ? 'selected' : '';
                    $options_html .= "<option value='".htmlspecialchars($d['nama_lokasi'])."' $selected>".htmlspecialchars($d['nama_lokasi'])."</option>";
                }
            }
        }
    } else if ($jenis_filter == 'Operasi') {
        $q_op = "SELECT DISTINCT po.nm_perawatan as nama_lokasi 
                 FROM pelaksanaan_rujukan_psm p
                 INNER JOIN {$db_name_sik}.operasi op ON p.no_rawat = op.no_rawat
                 INNER JOIN {$db_name_sik}.paket_operasi po ON op.kode_paket = po.kode_paket
                 WHERE DATE(p.tanggal) BETWEEN ? AND ?
                 ORDER BY nama_lokasi ASC";
        $r_op = bukaquery_prepared($q_op, "ss", $tgl_awal, $tgl_akhir);
        if($r_op) {
            while($d = mysqli_fetch_array($r_op)) {
                if(!empty($d['nama_lokasi'])) {
                    $selected = ($nama_filter == $d['nama_lokasi']) ? 'selected' : '';
                    $options_html .= "<option value='".htmlspecialchars($d['nama_lokasi'])."' $selected>".htmlspecialchars($d['nama_lokasi'])."</option>";
                }
            }
        }
    }

    // Main Query
    $search_query = "";
    $params = array($tgl_awal, $tgl_akhir);
    $types = "ss";
    if (!empty($jenis_filter)) {
        if ($jenis_filter == 'Poli') {
            if (!empty($nama_filter)) {
                $search_query = " AND IFNULL(bsl.nm_bangsal, pl.nm_poli) = ? ";
                $params[] = $nama_filter;
                $types .= "s";
            } else {
                $search_query = " AND IFNULL(bsl.nm_bangsal, pl.nm_poli) IS NOT NULL AND IFNULL(bsl.nm_bangsal, pl.nm_poli) != '' ";
            }
        } else if ($jenis_filter == 'Operasi') {
            if (!empty($nama_filter)) {
                $search_query = " AND po.nm_perawatan = ? ";
                $params[] = $nama_filter;
                $types .= "s";
            } else {
                $search_query = " AND po.nm_perawatan IS NOT NULL AND po.nm_perawatan != '' ";
            }
        }
    }

    $limit = 50;
    $page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
    $offset = ($page - 1) * $limit;

    $count_sql = "SELECT COUNT(*) as total 
                  FROM pelaksanaan_rujukan_psm p
                  INNER JOIN {$db_name_sik}.reg_periksa r ON p.no_rawat = r.no_rawat
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
             po.nm_perawatan as nm_operasi, b.bukti
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
    <title>Laporan Khusus PSM</title>
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
        th { background: #fffbeb; color: #b45309; font-weight: 700; padding: 12px; border-bottom: 2px solid #fde68a; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; color: #1e293b; }
        tr:hover { background: #f8fafc; }
        .btn-view { background: #0ea5e9; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; white-space: nowrap; display: inline-block; text-decoration: none; }
        .btn-view:hover { background: #0284c7; color: white; }
        .form-select, .form-control { font-size: 13px; border-radius: 8px; border-color: #cbd5e1; }
        .form-select:focus, .form-control:focus { border-color: #d97706; box-shadow: 0 0 0 0.25rem rgba(217, 119, 6, 0.25); }
        .pagination .page-item.active .page-link { background-color: #d97706; border-color: #d97706; color: white; }
        .pagination .page-link { color: #d97706; box-shadow: none; }
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container content-wrapper mb-5 pb-4">
        <h4 class="mb-4" style="color: #d97706; font-weight: 800;"><i class="bi bi-funnel-fill me-2"></i> Laporan Harian Khusus</h4>
        
        <div class="p-3 mb-4 rounded" style="background: #fffbeb; border: 1px solid #fde68a;">
            <form method="GET" action="" id="filterForm" class="row align-items-end g-3">
                <input type="hidden" name="act" value="LaporanKhusus">
                
                <div class="col-md-4 d-flex flex-column justify-content-end">
                    <label class="form-label fw-bold mb-2" style="color: #92400e; font-size: 12px;">Periode Tanggal</label>
                    <div class="d-flex align-items-center">
                        <input type="date" name="tgl_awal" class="form-control" value="<?= e($tgl_awal) ?>" onchange="this.form.submit()">
                        <span class="mx-2 text-muted">s/d</span>
                        <input type="date" name="tgl_akhir" class="form-control" value="<?= e($tgl_akhir) ?>" onchange="this.form.submit()">
                    </div>
                </div>

                <div class="col-md-3 d-flex flex-column justify-content-end">
                    <label class="form-label fw-bold mb-2" style="color: #92400e; font-size: 12px;">Pilih Kategori</label>
                    <select name="jenis_filter" id="jenis_filter" class="form-select" onchange="resetAndSubmit()">
                        <option value="">-- Bebas --</option>
                        <option value="Poli" <?= ($jenis_filter == 'Poli') ? 'selected' : '' ?>>Poli / Kamar Bangsal</option>
                        <option value="Operasi" <?= ($jenis_filter == 'Operasi') ? 'selected' : '' ?>>Tindakan Operasi</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex flex-column justify-content-end">
                    <label class="form-label fw-bold mb-2" style="color: #92400e; font-size: 12px;">Nama Spesifik</label>
                    <select name="nama_filter" id="nama_filter" class="form-select" <?= empty($jenis_filter) ? 'disabled' : '' ?> onchange="this.form.submit()">
                        <?= $options_html ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn fw-bold w-100" style="background: #d97706; color: white; border-radius: 8px; font-size: 13px;">
                        <i class="bi bi-filter-circle me-1"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>

        <script>
            function resetAndSubmit() {
                document.getElementById('nama_filter').value = '';
                document.getElementById('filterForm').submit();
            }
        </script>

        <div style="overflow-x: auto; border: 1px solid #cbd5e1; border-radius: 10px;">
            <table style="margin-top: 0; min-width: 900px;">
                <thead>
                    <tr>
                        <th style="border-radius: 10px 0 0 0; padding-left: 20px;">Tanggal</th>
                        <th>No. Rawat</th>
                        <th>Nama Pasien</th>
                        <?php if($jenis_filter == 'Operasi'): ?>
                            <th>Tindakan Operasi</th>
                        <?php else: ?>
                            <th>Poli / Kamar</th>
                        <?php endif; ?>
                        <th>PSM Pengantar</th>
                        <th>Keterangan</th>
                        <th style="border-radius: 0 10px 0 0; text-align: center;">Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if($hasil && mysqli_num_rows($hasil) > 0) {
                            while($row = mysqli_fetch_array($hasil)) {
                                $lokasi = htmlspecialchars($row['nm_bangsal']);
                                if ($jenis_filter == 'Operasi' || $row['nm_operasi'] != '') {
                                    $lokasi = "<span class='badge bg-light text-dark border'><i class='bi bi-heart-pulse text-danger me-1'></i> OK: ".htmlspecialchars($row['nm_operasi'])."</span>";
                                } else {
                                    $lokasi = "<span class='badge bg-light text-dark border'>".$lokasi."</span>";
                                }
                                
                                $bukti_btn = "<span style='color: #94a3b8; font-style: italic; font-size:12px;'>Belum ada foto</span>";
                                if($row['bukti'] != "") {
                                    $bukti_btn = "<a href='?act=LihatFoto&img=".urlencode($row['bukti'])."' target='_blank' class='btn-view'><i class='bi bi-image me-1'></i> Lihat Foto</a>";
                                }

                                echo "<tr>
                                        <td style='padding-left: 20px;'>".e($row['tanggal'])."</td>
                                        <td><span style='color: #64748b; font-family: monospace;'>".e($row['no_rawat'])."</span></td>
                                        <td class='fw-bold'>".htmlspecialchars($row['nm_pasien'])."</td>
                                        <td>".$lokasi."</td>
                                        <td><span class='badge' style='background:#fef3c7; color:#b45309;'>".htmlspecialchars($row['nama_psm'])."</span></td>
                                        <td>".htmlspecialchars($row['keterangan_diberikan_pada'])."</td>
                                        <td style='text-align: center;'>".$bukti_btn."</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align:center; padding: 30px; color: #94a3b8;'>Belum ada data untuk filter ini.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?act=LaporanKhusus&tgl_awal=<?=urlencode($tgl_awal)?>&tgl_akhir=<?=urlencode($tgl_akhir)?>&jenis_filter=<?=urlencode($jenis_filter)?>&nama_filter=<?=urlencode($nama_filter)?>&page=<?=$page-1?>">Previous</a>
                </li>
                <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    for($i=$start_page; $i<=$end_page; $i++): 
                ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?act=LaporanKhusus&tgl_awal=<?=urlencode($tgl_awal)?>&tgl_akhir=<?=urlencode($tgl_akhir)?>&jenis_filter=<?=urlencode($jenis_filter)?>&nama_filter=<?=urlencode($nama_filter)?>&page=<?=$i?>"><?=$i?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?act=LaporanKhusus&tgl_awal=<?=urlencode($tgl_awal)?>&tgl_akhir=<?=urlencode($tgl_akhir)?>&jenis_filter=<?=urlencode($jenis_filter)?>&nama_filter=<?=urlencode($nama_filter)?>&page=<?=$page+1?>">Next</a>
                </li>
            </ul>
        </nav>
        <div class="text-center mt-2 text-muted" style="font-size:12px;">Menampilkan halaman <?=$page?> dari <?=$total_pages?> (Total <?=$total_data?> Data)</div>
        <?php endif; ?>

    </div>
</body>
</html>
