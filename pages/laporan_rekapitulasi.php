<?php
    if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) || (empty($_SESSION['permissions_psm']['can_view_laporan']) && !empty($_SESSION['permissions_psm']))) {
        echo "<META HTTP-EQUIV = 'Refresh' Content = '0; URL = ?act=Dashboard'>";
        exit;
    }
    global $db_name_sik, $konektor;

    $perms_rekap = isset($_SESSION['permissions_psm']) ? $_SESSION['permissions_psm'] : [];
    $is_admin_rekap = (isset($_SESSION['role_psm']) && $_SESSION['role_psm'] == 'Admin');
    $can_manage_rujukan = !empty($perms_rekap['can_manage_rujukan']) || (empty($perms_rekap) && $is_admin_rekap);

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
    $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 15;
    $offset = ($page - 1) * $limit;

    try {
        bukakoneksi();
        @mysqli_query($konektor, "ALTER TABLE pelaksanaan_rujukan_psm ADD COLUMN override_status VARCHAR(20) DEFAULT NULL AFTER keterangan_diberikan_pada");
    } catch (Exception $e) {}

    // Query untuk mengambil data total pasien per PSM pada periode terpilih
    $sql = "SELECT psm.nama_psm, COUNT(DISTINCT p.no_rawat) as total_pasien 
            FROM master_psm psm 
            LEFT JOIN (
                SELECT p.id_psm, p.no_rawat 
                FROM pelaksanaan_rujukan_psm p
                INNER JOIN {$db_name_sik}.reg_periksa r ON p.no_rawat = r.no_rawat
                INNER JOIN {$db_name_sik}.pasien ps ON r.no_rkm_medis = ps.no_rkm_medis
                WHERE DATE(p.tanggal) BETWEEN ? AND ?
            ) p ON psm.id_psm = p.id_psm
            GROUP BY psm.id_psm 
            ORDER BY total_pasien DESC LIMIT 10";
            
    $hasil = bukaquery_prepared($sql, "ss", $tgl_awal, $tgl_akhir);
    
    $psm_names = [];
    $psm_totals = [];
    
    $table_data = "";
    $no = 1;
    if ($hasil && mysqli_num_rows($hasil) > 0) {
        while ($row = mysqli_fetch_array($hasil)) {
            $psm_names[] = $row['nama_psm'];
            $psm_totals[] = $row['total_pasien'];
            
            $table_data .= "<tr>
                                <td>".$no++."</td>
                                <td class='fw-bold' style='color:#0f766e;'>".htmlspecialchars($row['nama_psm'])."</td>
                                <td class='text-center'><span class='badge' style='background:#e0f2fe; color:#0369a1; font-size: 14px;'>".$row['total_pasien']." Pasien</span></td>
                            </tr>";
        }
    } else {
        $table_data = "<tr><td colspan='3' class='text-center text-muted'>Tidak ada data pada periode ini</td></tr>";
    }

    // Ambil Master Fee
    bukaquery2("CREATE TABLE IF NOT EXISTS setting_fee_psm (id INT PRIMARY KEY DEFAULT 1, fee_ranap_op DOUBLE, fee_ranap DOUBLE, fee_ralan DOUBLE)");
    try {
        @mysqli_query($konektor, "ALTER TABLE setting_fee_psm ADD COLUMN fee_ralan_op DOUBLE AFTER fee_ralan");
    } catch (Exception $e) {}
    $master_fee = mysqli_fetch_array(bukaquery2("SELECT * FROM setting_fee_psm WHERE id=1"));
    if(!$master_fee) $master_fee = ['fee_ranap_op'=>75000, 'fee_ranap'=>50000, 'fee_ralan'=>25000];
    $f_ranap_op = (double)$master_fee['fee_ranap_op'];
    $f_ranap = (double)$master_fee['fee_ranap'];
    $f_ralan = (double)$master_fee['fee_ralan'];
    $f_ralan_op = isset($master_fee['fee_ralan_op']) ? (double)$master_fee['fee_ralan_op'] : 125000;

    // Query untuk mengambil detail pasien
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
        IF(IFNULL(p.override_status, r.status_lanjut)='Ranap', IF(op.no_rawat IS NOT NULL, {$f_ranap_op}, {$f_ranap}), IF(op.no_rawat IS NOT NULL, {$f_ralan_op}, {$f_ralan})) as fee,
        p.no_rawat,
        p.tanggal,
        p.id_psm,
        p.override_status
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
    
    // Hitung total data untuk pagination
    $sql_count = "SELECT COUNT(DISTINCT p.no_rawat) as total 
                  FROM pelaksanaan_rujukan_psm p 
                  INNER JOIN master_psm mpsm ON p.id_psm = mpsm.id_psm
                  INNER JOIN {$db_name_sik}.reg_periksa r ON p.no_rawat = r.no_rawat
                  INNER JOIN {$db_name_sik}.pasien ps ON r.no_rkm_medis = ps.no_rkm_medis
                  WHERE DATE(p.tanggal) BETWEEN ? AND ? $search_query";
    $r_count = bukaquery_prepared($sql_count, $types, ...$params);
    $d_count = mysqli_fetch_array($r_count);
    $total_data = $d_count['total'];
    $total_pages = ceil($total_data / $limit);

    // Terapkan limit dan offset
    $sql_detail .= " LIMIT $limit OFFSET $offset";
    
    $hasil_detail = bukaquery_prepared($sql_detail, $types, ...$params);
    $detail_rows = "";
    $no_detail = $offset + 1;
    $total_fee = 0;
    if ($hasil_detail && mysqli_num_rows($hasil_detail) > 0) {
        while ($r_det = mysqli_fetch_array($hasil_detail)) {
            $fee = (float)$r_det['fee'];
            $total_fee += $fee;
            
            $action_td = "";
            if ($can_manage_rujukan) {
                $editOnclick = "showEditModal(" . json_encode($r_det['no_rawat']) . "," . json_encode($r_det['tanggal']) . "," . json_encode($r_det['id_psm']) . "," . json_encode($r_det['override_status']) . ")";
                $confirmOnsubmit = "return confirm(" . json_encode('Yakin ingin menghapus data rujukan pasien ' . $r_det['nm_pasien'] . ' ini? Fotonya juga akan terhapus.') . ")";
                $action_td = "<td class='text-center' style='white-space:nowrap;'>
                    <button onclick=\"".e($editOnclick)."\" class='btn btn-sm btn-warning me-1' style='color:white; border-radius:6px;' title='Edit PSM'><i class='bi bi-pencil-square'></i></button>
                    <form method='POST' action='?act=HapusRujukan' style='display:inline;' onsubmit=\"".e($confirmOnsubmit)."\">
                        ".csrf_input()."
                        <input type='hidden' name='no_rawat' value='".e($r_det['no_rawat'])."'>
                        <input type='hidden' name='tanggal' value='".e($r_det['tanggal'])."'>
                        <button type='submit' class='btn btn-sm btn-danger' style='border-radius:6px;' title='Hapus Data'><i class='bi bi-trash-fill'></i></button>
                    </form>
                </td>";
            }

            $detail_rows .= "<tr>
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
                <td class='text-end'>".number_format((float)$r_det['billing'], 0, ',', '.')."</td>
                <td class='text-end'>".number_format($fee, 0, ',', '.')."</td>
                ".$action_td."
            </tr>";
        }
        $colspan_tot = $can_manage_rujukan ? 13 : 12;
        $colspan_label = $can_manage_rujukan ? 11 : 11;
        $action_empty = $can_manage_rujukan ? "<td></td>" : "";
        $detail_rows .= "<tr class='fw-bold bg-subtotal'><td colspan='{$colspan_label}' class='text-end'>TOTAL FEE (Hal. ini):</td><td class='text-end'>".number_format($total_fee, 0, ',', '.')."</td>{$action_empty}</tr>";
    } else {
        $colspan_tot = $can_manage_rujukan ? 13 : 12;
        $detail_rows = "<tr><td colspan='{$colspan_tot}' class='text-center text-muted'>Tidak ada data pasien pada bulan ini</td></tr>";
    }

?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Rekapitulasi</title>
    <script src="js/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .page-title { color: #0f766e; font-weight: 800; font-size: 24px; margin-bottom: 20px; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); background: white; padding: 20px; margin-bottom: 20px;}
        .table-custom th { background: #f1f5f9; color: #0f766e; border-bottom: 2px solid #cbd5e1; }
        .table-custom td { vertical-align: middle; }
        .btn-filter { background: #0f766e; color: white; border: none; font-weight: 600; border-radius: 8px; }
        .btn-filter:hover { background: #0d9488; color: white; }
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container-fluid mb-5 pb-4 px-4">
        <div class="d-flex align-items-center mb-4 mt-2">
            <div style="background: rgba(15, 118, 110, 0.1); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                <i class="bi bi-bar-chart-fill" style="color: #0f766e; font-size: 20px;"></i>
            </div>
            <h4 class="mb-0" style="color: #1e293b; font-weight: 800; letter-spacing: -0.5px;">Laporan Rekapitulasi PSM</h4>
        </div>
        
        <div class="card mb-4" style="border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 12px;">
            <div class="card-body p-4">
                <form method="GET" action="index.php" class="row g-3 align-items-end">
                    <input type="hidden" name="act" value="Rekapitulasi">
                    <div class="col-md-4">
                        <label class="form-label text-muted fw-bold mb-2" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Tanggal Awal</label>
                        <input type="date" name="tgl_awal" class="form-control" style="border-radius: 8px;" value="<?= htmlspecialchars($tgl_awal) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted fw-bold mb-2" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Tanggal Akhir</label>
                        <input type="date" name="tgl_akhir" class="form-control" style="border-radius: 8px;" value="<?= htmlspecialchars($tgl_akhir) ?>">
                    </div>
                    <input type="hidden" name="keyword" value="<?= htmlspecialchars($keyword) ?>">
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm" style="background:#0f766e; border:none; font-weight: 600;"><i class="bi bi-funnel-fill me-2"></i> Tampilkan Data</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8 col-md-12">
                <div class="card h-100" style="border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
                        <h6 class="fw-bold mb-0" style="color: #334155;"><i class="bi bi-graph-up-arrow text-info me-2"></i>Grafik Kinerja PSM</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <canvas id="psmChart" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="card h-100" style="border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
                        <h6 class="fw-bold mb-0" style="color: #334155;"><i class="bi bi-trophy-fill text-warning me-2"></i>Peringkat PSM</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="table-responsive">
                            <table class="table table-hover table-custom mb-0 align-middle">
                                <thead style="border-bottom: 2px solid #e2e8f0;">
                                    <tr>
                                        <th width="15%" class="text-center py-3">#</th>
                                        <th class="py-3">Nama PSM</th>
                                        <th class="text-center py-3">Total Pasien</th>
                                    </tr>
                                </thead>
                                <tbody style="border-top: none;">
                                    <?= $table_data ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card" style="border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h6 class="fw-bold mb-0" style="color: #334155;"><i class="bi bi-file-earmark-spreadsheet-fill text-success me-2"></i>Data Rekap Pasien Rujukan PSM</h6>
                        <div class="d-flex gap-2 align-items-center">
                            <form method="GET" action="index.php" class="d-flex gap-2 mb-0 me-2">
                                <input type="hidden" name="act" value="Rekapitulasi">
                                <input type="hidden" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">
                                <input type="hidden" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">
                                <input type="text" name="keyword" class="form-control form-control-sm" style="border-radius: 6px; width: 220px; border: 2px solid #ef4444;" placeholder="Pencarian..." value="<?= htmlspecialchars($keyword) ?>">
                            </form>
                            <button class="btn btn-light text-success btn-sm px-3 rounded-pill border" onclick="window.open('pages/export_excel_rekap.php?tgl_awal=<?= urlencode($tgl_awal) ?>&tgl_akhir=<?= urlencode($tgl_akhir) ?>&keyword=<?= urlencode($keyword) ?>', '_blank')" style="font-weight: 600;"><i class="bi bi-file-earmark-excel-fill me-1"></i> Excel All</button>
                            <button class="btn btn-light text-primary btn-sm px-3 rounded-pill border" onclick="window.open('pages/cetak_rekap.php?tgl_awal=<?= urlencode($tgl_awal) ?>&tgl_akhir=<?= urlencode($tgl_akhir) ?>&keyword=<?= urlencode($keyword) ?>', '_blank')" style="font-weight: 600;"><i class="bi bi-printer-fill me-1"></i> Cetak</button>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-4 pt-0">
                        <div class="table-responsive" id="areaRekap">
                            <table id="tableRekap" class="table table-hover table-custom align-middle" style="font-size: 12px; white-space: nowrap; min-width: 1000px;">
                                <thead class="text-center" style="border-bottom: 2px solid #e2e8f0;">
                                    <tr>
                                        <th class="py-3">NO</th>
                                        <th class="py-3">TGL MASUK</th>
                                        <th class="py-3">TGL PULANG</th>
                                        <th class="py-3">NO RM</th>
                                        <th class="py-3">NAMA PASIEN</th>
                                        <th class="py-3">JENIS PERAWATAN</th>
                                        <th class="py-3">JAMINAN</th>
                                        <th class="py-3">NAMA PSM</th>
                                        <th class="py-3">ALAMAT</th>
                                        <th class="py-3">NO HP</th>
                                        <th class="py-3">BILLING</th>
                                        <th class="py-3">FEE</th>
                                        <?php if($can_manage_rujukan): ?>
                                        <th class="py-3">AKSI</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody style="border-top: none;">
                                    <?= $detail_rows ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?act=Rekapitulasi&tgl_awal=<?=urlencode($tgl_awal)?>&tgl_akhir=<?=urlencode($tgl_akhir)?>&keyword=<?=urlencode($keyword)?>&page=<?=$page-1?>">Previous</a>
                                </li>
                                <?php 
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    for($i=$start_page; $i<=$end_page; $i++): 
                                ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?act=Rekapitulasi&tgl_awal=<?=urlencode($tgl_awal)?>&tgl_akhir=<?=urlencode($tgl_akhir)?>&keyword=<?=urlencode($keyword)?>&page=<?=$i?>"><?=$i?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?act=Rekapitulasi&tgl_awal=<?=urlencode($tgl_awal)?>&tgl_akhir=<?=urlencode($tgl_akhir)?>&keyword=<?=urlencode($keyword)?>&page=<?=$page+1?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center mt-2 text-muted" style="font-size:12px;">Menampilkan halaman <?=$page?> dari <?=$total_pages?> (Total <?=$total_data?> Data)</div>
                        <div class="alert alert-info mt-3 mb-0" style="font-size: 13px;"><i class="bi bi-info-circle-fill me-2"></i><strong>Info:</strong> Tombol Cetak dan Excel hanya akan mencetak data yang tampil pada halaman ini.</div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('psmChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($psm_names) ?>,
                datasets: [{
                    label: 'Jumlah Pasien Dirujuk',
                    data: <?= json_encode($psm_totals) ?>,
                    backgroundColor: '#0ea5e9',
                    borderColor: '#0284c7',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                scales: {
                    x: {
                        ticks: { color: '#94a3b8' },
                        grid: { color: 'rgba(148, 163, 184, 0.1)' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#94a3b8' },
                        grid: { color: 'rgba(148, 163, 184, 0.1)' }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        function cetakArea(areaId, title) {
            let content = document.getElementById(areaId).innerHTML;
            
            let win = window.open('', '_blank');
            win.document.write(`
                <html>
                <head>
                    <title>Cetak Laporan</title>
                    <link rel="stylesheet" href="css/bootstrap.min.css" />
                    <style>
                        @page { size: landscape; }
                        body { padding: 20px; font-family: sans-serif; font-size: 12px; }
                        table { width: 100%; border-collapse: collapse !important; margin-bottom: 20px; }
                        table, th, td { border: 1px solid #000 !important; padding: 6px !important; }
                        th { background-color: #f8f9fa !important; text-align: center; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        .text-center { text-align: center; }
                        .text-end { text-align: right; }
                        .fw-bold { font-weight: bold; }
                    </style>
                </head>
                <body>
                    <h4 class="text-center fw-bold mb-4">${title}</h4>
                    ${content}
                </body>
                </html>
            `);
            win.document.close();
            win.focus();
            setTimeout(function(){ win.print(); win.close(); }, 500);
        }

        function exportExcel(tableID, filename = ''){
            var tableSelect = document.getElementById(tableID);
            var modifiedTableHTML = tableSelect.outerHTML.replace(/<table/g, '<table border="1"');
            var css = '<style> table { border-collapse: collapse; } th, td { font-size: 12pt !important; font-family: Calibri, sans-serif !important; padding: 5px; border: 1px solid #000000; vertical-align: middle; } th { font-weight: bold; background-color: #e2e8f0; text-align: center; } </style>';
            var tableHTML = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8">' + css + '</head><body>' + modifiedTableHTML + '</body></html>';
            
            filename = filename ? filename + '.xls' : 'excel_data.xls';
            
            var blob = new Blob([tableHTML], {
                type: 'application/vnd.ms-excel'
            });
            
            var downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);
            
            if(navigator.msSaveOrOpenBlob){
                navigator.msSaveOrOpenBlob(blob, filename);
            }else{
                var url = URL.createObjectURL(blob);
                downloadLink.href = url;
                downloadLink.download = filename;
                downloadLink.click();
                URL.revokeObjectURL(url);
            }
            document.body.removeChild(downloadLink);
        }
    </script>

    <?php if($can_manage_rujukan): ?>
    <!-- Modal Edit PSM & Override -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; border: none;">
                <div class="modal-header" style="background: #0ea5e9; color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title fw-bold" id="editModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Data Rujukan Pasien</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="?act=EditRujukan">
                        <?= csrf_input() ?>
                        <input type="hidden" name="no_rawat" id="edit_no_rawat">
                        <input type="hidden" name="tanggal" id="edit_tanggal">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="color: #475569;">Pilih Nama PSM Baru</label>
                            <select name="id_psm" id="edit_id_psm" class="form-select" required>
                                <?php
                                    $q_psm = bukaquery2("SELECT id_psm, nama_psm FROM master_psm ORDER BY nama_psm ASC");
                                    if($q_psm && mysqli_num_rows($q_psm) > 0) {
                                        while($d_psm = mysqli_fetch_array($q_psm)){
                                            echo "<option value='".e($d_psm['id_psm'])."'>".e($d_psm['nama_psm'])."</option>";
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="color: #475569;">Ubah Jenis Perawatan (Manual Fee)</label>
                            <select name="override_status" id="edit_override_status" class="form-select">
                                <option value="">Otomatis dari SIMRS</option>
                                <option value="Ranap">Rawat Inap (Override)</option>
                                <option value="Ralan">Rawat Jalan (Override)</option>
                            </select>
                            <small class="text-muted" style="font-size: 11px;">Gunakan fitur ini JIKA pasien tertahan di IGD/transit tapi ingin dihitung tarif rawat inap.</small>
                        </div>
                        <div class="mb-3">
                            <div class="card bg-light border-0 p-3 mt-3 text-center">
                                <p class="mb-2 text-muted" style="font-size: 13px;">Ingin memperbarui foto bukti dan tanda tangan?</p>
                                <a id="btnGantiFoto" href="#" class="btn btn-outline-secondary fw-bold" style="border-radius: 8px;">
                                    <i class="bi bi-camera me-2"></i>Ambil Ulang Foto & TTD
                                </a>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn fw-bold text-white" style="background: #0ea5e9; border-radius: 8px;">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showEditModal(noRawat, tanggal, currentIdPsm, overrideStatus) {
            $('#edit_no_rawat').val(noRawat);
            $('#edit_tanggal').val(tanggal);
            $('#edit_id_psm').val(currentIdPsm);
            $('#edit_override_status').val(overrideStatus || '');
            
            var cameraLink = '?act=Kamera&norawat=' + encodeURIComponent(noRawat) + '&tanggal=' + encodeURIComponent(tanggal);
            $('#btnGantiFoto').attr('href', cameraLink);
            
            var myModal = new bootstrap.Modal(document.getElementById('editModal'));
            myModal.show();
        }
    </script>
    <?php endif; ?>
</body>
</html>
