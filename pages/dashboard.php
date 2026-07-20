<?php
    global $db_name_sik;
    if(strpos($_SERVER['REQUEST_URI'],"pages")){
        exit(header("Location:../index.php"));
    }
    
    // Permissions Check
    $perms_dash = isset($_SESSION['permissions_psm']) ? $_SESSION['permissions_psm'] : [];
    $is_admin_dash = (isset($_SESSION['role_psm']) && $_SESSION['role_psm'] == 'Admin');
    $can_manage_rujukan = !empty($perms_dash['can_manage_rujukan']) || (empty($perms_dash) && $is_admin_dash);

    // Pastikan kolom override ada
    global $konektor;
    bukakoneksi();
    try {
        @mysqli_query($konektor, "ALTER TABLE pelaksanaan_rujukan_psm ADD COLUMN override_status VARCHAR(20) DEFAULT NULL AFTER keterangan_diberikan_pada");
    } catch (Exception $e) {}

    // Fetch dashboard stats
    $total_rujukan = getOne("select count(no_rawat) from pelaksanaan_rujukan_psm");
    $total_hari_ini = getOne("select count(no_rawat) from pelaksanaan_rujukan_psm where date(tanggal) = curdate()");

    // Fetch PSM stats
    $psm_stats_query = "SELECT m.nama_psm, COUNT(p.no_rawat) as total FROM master_psm m LEFT JOIN pelaksanaan_rujukan_psm p ON m.id_psm = p.id_psm GROUP BY m.id_psm HAVING total > 0 ORDER BY total DESC";
    $psm_stats = bukaquery2($psm_stats_query);

    // Date Filter Logic
    $tgl_awal = validTanggal(isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : '', date('Y-m-01'));
    $tgl_akhir = validTanggal(isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '', date('Y-m-t'));

    // Pagination Logic
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;

    // Count Total Data for Pagination
    $count_query = "SELECT COUNT(*) FROM pelaksanaan_rujukan_psm p
                    INNER JOIN {$db_name_sik}.reg_periksa r ON p.no_rawat = r.no_rawat 
                    LEFT JOIN {$db_name_sik}.kamar_inap ki ON r.no_rawat = ki.no_rawat AND ki.stts_pulang = '-'
                    LEFT JOIN {$db_name_sik}.permintaan_ranap pr ON r.no_rawat = pr.no_rawat
                    WHERE DATE(p.tanggal) BETWEEN ? AND ?";
    $count_res = bukaquery_prepared($count_query, "ss", $tgl_awal, $tgl_akhir);
    $total_data = ($count_res && mysqli_num_rows($count_res) > 0) ? mysqli_fetch_array($count_res)[0] : 0;
    $total_pages = ceil($total_data / $limit);

    $_sql = "SELECT p.tanggal, p.no_rawat, r.no_rkm_medis, ps.nm_pasien, p.id_psm, mpsm.nama_psm as nama_psm_pengantar, p.keterangan_diberikan_pada, b.bukti,
             IFNULL((SELECT CONCAT(IF(r.status_lanjut='Ralan', CONCAT(pl.nm_poli, ' - '), IF(bsl.nm_bangsal IS NOT NULL, CONCAT(bsl.nm_bangsal, ' - '), '')), 'OK: ', po.nm_perawatan) FROM {$db_name_sik}.operasi op INNER JOIN {$db_name_sik}.paket_operasi po ON op.kode_paket=po.kode_paket WHERE op.no_rawat=p.no_rawat LIMIT 1), IFNULL(bsl.nm_bangsal, pl.nm_poli)) as nm_bangsal,
             p.override_status,
             r.status_lanjut
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
              ORDER BY p.tanggal DESC 
              LIMIT ?, ?";
    $hasil = bukaquery_prepared($_sql, "ssii", $tgl_awal, $tgl_akhir, $offset, $limit);
    // Trend Harian Query
    $q_trend = "SELECT DATE(p.tanggal) as tgl, COUNT(p.no_rawat) as total 
                FROM pelaksanaan_rujukan_psm p 
                WHERE DATE(p.tanggal) BETWEEN ? AND ? 
                GROUP BY DATE(p.tanggal) 
                ORDER BY DATE(p.tanggal) ASC";
    $r_trend = bukaquery_prepared($q_trend, "ss", $tgl_awal, $tgl_akhir);
    $trend_labels = [];
    $trend_data = [];
    if($r_trend && mysqli_num_rows($r_trend) > 0) {
        while($dt = mysqli_fetch_array($r_trend)) {
            $trend_labels[] = date('d/m', strtotime($dt['tgl']));
            $trend_data[] = $dt['total'];
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Rujukan PSM</title>
    <script src="js/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style type="text/css">
        body {
            background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            color: #1e293b !important;
            padding-bottom: 40px;
            font-size: 13px !important;
        }
        .content-wrapper {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px -5px rgba(15, 118, 110, 0.1), 0 8px 10px -6px rgba(15, 118, 110, 0.05) !important;
            padding: 30px !important;
            max-width: 1200px !important;
            border: 1px solid rgba(255, 255, 255, 0.5) !important;
            margin: auto;
        }
        .stat-card {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%) !important;
            color: white !important;
            border-radius: 12px;
            padding: 20px 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 15px -3px rgba(15, 118, 110, 0.3) !important;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .stat-card.green {
            background: linear-gradient(135deg, #0d9488 0%, #059669 100%) !important;
            box-shadow: 0 10px 15px -3px rgba(13, 148, 136, 0.3) !important;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 32px;
            font-weight: 800;
            margin-right: 20px;
            letter-spacing: -1px;
        }
        .stat-card p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
            font-weight: 600;
        }
        .card {
            background: rgba(255, 255, 255, 0.7) !important;
            backdrop-filter: blur(10px) !important;
            border: 1px solid rgba(255, 255, 255, 0.4) !important;
        }
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 15px;
            background: rgba(255,255,255,0.5);
            border-radius: 12px;
            overflow: hidden;
        }
        th {
            background: rgba(15, 118, 110, 0.05);
            color: #0f766e;
            font-weight: 700;
            padding: 15px 12px;
            border-bottom: 2px solid rgba(15, 118, 110, 0.1);
            text-align: left;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        td {
            padding: 15px 12px;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            color: #334155;
            vertical-align: middle;
        }
        tr:hover {
            background: rgba(255, 255, 255, 0.8);
        }
        .btn-view {
            background: #0ea5e9;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
            white-space: nowrap;
            display: inline-block;
        }
        .btn-view:hover {
            background: #0284c7;
            transform: translateY(-1px);
        }
        .search-box {
            width: 100%;
            padding: 12px 20px;
            border: 1.5px solid rgba(203, 213, 225, 0.5);
            border-radius: 10px;
            font-family: inherit;
            outline: none;
            transition: all 0.3s;
            margin-bottom: 15px;
            background: rgba(255,255,255,0.8);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }
        .search-box:focus {
            border-color: #0f766e;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15);
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .btn-nav {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%) !important;
            color: #fff !important;
            border: none !important;
            font-weight: 600 !important;
            padding: 10px 20px !important;
            border-radius: 10px !important;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 4px 6px -1px rgba(15, 118, 110, 0.2);
        }
        .btn-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 10px -2px rgba(15, 118, 110, 0.3);
            text-decoration: none;
        }
        .pagination .page-item.active .page-link {
            background-color: #0f766e;
            border-color: #0f766e;
        }
        .pagination .page-link {
            color: #0f766e;
            box-shadow: none;
        }
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container content-wrapper">
        <div class="header-section">
            <h2 style="color: #1e293b; font-weight: 700; margin: 0;">Dashboard Rujukan PSM</h2>
            <a href="?act=Kamera" class="btn-nav"><i class="bi bi-camera me-1"></i> Mulai Form Rujukan</a>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="stat-card">
                    <h3><?=$total_rujukan ?: '0'?></h3>
                    <p>Total Pelaksanaan Rujukan</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card green">
                    <h3><?=$total_hari_ini ?: '0'?></h3>
                    <p>Rujukan Hari Ini</p>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 10px;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                        <h6 class="fw-bold m-0" style="color: #334155;"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Tren Rujukan Harian</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" height="60"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; flex-wrap: wrap; margin: 0 -10px; margin-bottom: 25px;">
            <!-- Rekap PSM -->
            <div style="flex: 1; min-width: 300px; margin: 0 10px; margin-bottom: 15px;">
                <div class="card shadow-sm border-0" style="border-radius: 10px;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                        <h6 class="fw-bold m-0" style="color: #0ea5e9;"><i class="bi bi-people-fill me-2"></i>Rekap per PSM</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto; border-radius: 0 0 10px 10px;">
                            <?php
                                if($psm_stats && mysqli_num_rows($psm_stats) > 0) {
                                    while($pstat = mysqli_fetch_array($psm_stats)) {
                                        echo "<li class='list-group-item d-flex justify-content-between align-items-center border-0 px-3 py-2 border-bottom' style='font-size:14px;'>
                                                ".htmlspecialchars($pstat['nama_psm'])."
                                                <span class='badge rounded-pill' style='background-color:#e0f2fe; color:#0369a1; font-size:12px;'>".$pstat['total']."</span>
                                              </li>";
                                    }
                                } else {
                                    echo "<li class='list-group-item text-muted text-center' style='font-size:13px;'>Belum ada data</li>";
                                }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Rekap Operasi -->
            <div style="flex: 1; min-width: 300px; margin: 0 10px; margin-bottom: 15px;">
                <div class="card shadow-sm border-0" style="border-radius: 10px;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                        <h6 class="fw-bold m-0" style="color: #10b981;"><i class="bi bi-heart-pulse-fill me-2"></i>Tindakan Operasi</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto; border-radius: 0 0 10px 10px;">
                            <?php
                                $q_operasi = "SELECT po.nm_perawatan, COUNT(*) as total 
                                            FROM pelaksanaan_rujukan_psm p
                                            INNER JOIN {$db_name_sik}.operasi op ON p.no_rawat = op.no_rawat
                                            INNER JOIN {$db_name_sik}.paket_operasi po ON op.kode_paket = po.kode_paket
                                            WHERE DATE(p.tanggal) BETWEEN ? AND ?
                                            GROUP BY po.nm_perawatan
                                            ORDER BY total DESC";
                                $r_operasi = bukaquery_prepared($q_operasi, "ss", $tgl_awal, $tgl_akhir);
                                if(mysqli_num_rows($r_operasi) > 0) {
                                    while($d_op = mysqli_fetch_array($r_operasi)){
                                        echo "<li class='list-group-item d-flex justify-content-between align-items-center border-0 px-3 py-2 border-bottom' style='font-size:13px;'>
                                                ".htmlspecialchars($d_op['nm_perawatan'])."
                                                <span class='badge rounded-pill' style='background-color:#d1fae5; color:#047857; font-size:12px;'>".$d_op['total']."</span>
                                              </li>";
                                    }
                                } else {
                                    echo "<li class='list-group-item text-muted text-center' style='font-size:13px;'>Belum ada data</li>";
                                }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Rekap Rawat Inap -->
            <div style="flex: 1; min-width: 300px; margin: 0 10px; margin-bottom: 15px;">
                <div class="card shadow-sm border-0" style="border-radius: 10px;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                        <h6 class="fw-bold m-0" style="color: #f59e0b;"><i class="bi bi-hospital-fill me-2"></i>Rawat Inap (Bangsal)</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto; border-radius: 0 0 10px 10px;">
                            <?php
                                $q_ranap = "SELECT IFNULL(bsl.nm_bangsal, 'Belum Masuk Kamar/Bangsal') as ruangan, COUNT(*) as total 
                                            FROM pelaksanaan_rujukan_psm p
                                            INNER JOIN {$db_name_sik}.reg_periksa r ON p.no_rawat = r.no_rawat
                                            LEFT JOIN {$db_name_sik}.kamar_inap ki ON r.no_rawat = ki.no_rawat AND ki.stts_pulang = '-'
                                            LEFT JOIN {$db_name_sik}.kamar k ON ki.kd_kamar = k.kd_kamar
                                            LEFT JOIN {$db_name_sik}.bangsal bsl ON k.kd_bangsal = bsl.kd_bangsal
                                            WHERE r.status_lanjut = 'Ranap' AND DATE(p.tanggal) BETWEEN ? AND ?
                                            GROUP BY ruangan
                                            ORDER BY total DESC";
                                $r_ranap = bukaquery_prepared($q_ranap, "ss", $tgl_awal, $tgl_akhir);
                                if(mysqli_num_rows($r_ranap) > 0) {
                                    while($d_ranap = mysqli_fetch_array($r_ranap)){
                                        echo "<li class='list-group-item d-flex justify-content-between align-items-center border-0 px-3 py-2 border-bottom' style='font-size:13px;'>
                                                ".htmlspecialchars($d_ranap['ruangan'])."
                                                <span class='badge rounded-pill' style='background-color:#fef3c7; color:#b45309; font-size:12px;'>".$d_ranap['total']."</span>
                                              </li>";
                                    }
                                } else {
                                    echo "<li class='list-group-item text-muted text-center' style='font-size:13px;'>Belum ada data</li>";
                                }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <hr style="border-color: #cbd5e1; margin: 30px 0;">

        <div class="row mb-3">
            <div class="col-md-8">
                <form method="GET" action="">
                    <input type="hidden" name="act" value="Dashboard">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-dark fw-bold border-end-0"><i class="bi bi-calendar3 me-2"></i> Periode</span>
                        <input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal ?>">
                        <span class="input-group-text bg-light text-dark">s/d</span>
                        <input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>">
                        <button class="btn btn-primary" style="background:#0f766e; border:none;" type="submit"><i class="bi bi-filter"></i> Filter</button>
                        <a id="btnCetak" href="?act=CetakLaporan&tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>" target="_blank" class="btn btn-secondary" style="background: #475569; color: white; border: none; border-radius: 0 8px 8px 0; display:flex; align-items:center; text-decoration:none;"><i class="bi bi-printer me-1"></i> Cetak</a>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <input type="text" id="searchInput" class="search-box mb-0 mt-md-0 mt-3" placeholder="Cari data di halaman ini...">
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table id="dataTable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. RM</th>
                        <th>Nama Pasien</th>
                        <th>Poli / Kamar</th>
                        <th>PSM Pengantar</th>
                        <th>Petugas Verifikasi</th>
                        <th>Bukti</th>
                        <?php if($can_manage_rujukan): ?>
                        <th style="text-align:center;">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if($hasil && mysqli_num_rows($hasil) > 0) {
                            while ($row = mysqli_fetch_array($hasil)) {
                                $bukti_path = $row['bukti'] ? $row['bukti'] : "";
                                echo "<tr>";
                                echo "<td>".e($row['tanggal'])."</td>";
                                echo "<td>".e($row['no_rkm_medis'])."</td>";
                                echo "<td class='align-middle fw-medium text-dark'>".e($row['nm_pasien'])."</td>";
                                echo "<td class='align-middle'><span class='badge bg-light text-dark border'>".e($row['nm_bangsal'])."</span></td>";
                                echo "<td class='align-middle fw-bold' style='color:#0f766e;'>".e($row['nama_psm_pengantar'])."</td>";
                                echo "<td class='align-middle'>".e($row['keterangan_diberikan_pada'])."</td>";
                                echo "<td>";
                                if($row['bukti'] != "") {
                                    echo "<a href='?act=LihatFoto&img=".urlencode($bukti_path)."' target='_blank' class='btn-view' style='text-decoration:none;'><i class='bi bi-image me-1'></i> Lihat Foto</a>";
                                } else {
                                    echo "<span style='color: #94a3b8; font-style: italic;'>Belum ada foto</span>";
                                }
                                echo "</td>";
                                
                                if($can_manage_rujukan) {
                                    echo "<td style='text-align:center; white-space:nowrap;'>";
                                    $editOnclick = "showEditModal(" . json_encode($row['no_rawat']) . "," . json_encode($row['tanggal']) . "," . json_encode($row['id_psm']) . "," . json_encode($row['override_status']) . ")";
                                    $confirmOnsubmit = "return confirm(" . json_encode('Yakin ingin menghapus data rujukan pasien ' . $row['nm_pasien'] . ' ini? Fotonya juga akan terhapus.') . ")";
                                    echo "<button onclick=\"".e($editOnclick)."\" class='btn btn-sm btn-warning me-1' style='color:white; border-radius:6px;' title='Edit PSM'><i class='bi bi-pencil-square'></i></button>";
                                    echo "<form method='POST' action='?act=HapusRujukan' style='display:inline;' onsubmit=\"".e($confirmOnsubmit)."\">";
                                    echo csrf_input();
                                    echo "<input type='hidden' name='no_rawat' value='".e($row['no_rawat'])."'>";
                                    echo "<input type='hidden' name='tanggal' value='".e($row['tanggal'])."'>";
                                    echo "<button type='submit' class='btn btn-sm btn-danger' style='border-radius:6px;' title='Hapus Data'><i class='bi bi-trash-fill'></i></button>";
                                    echo "</form>";
                                    echo "</td>";
                                }
                                
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center;'>Belum ada data pelaksanaan rujukan.</td></tr>";
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
                    <a class="page-link" href="?act=Dashboard&tgl_awal=<?=$tgl_awal?>&tgl_akhir=<?=$tgl_akhir?>&page=<?=$page-1?>">Previous</a>
                </li>
                <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    for($i=$start_page; $i<=$end_page; $i++): 
                ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?act=Dashboard&tgl_awal=<?=$tgl_awal?>&tgl_akhir=<?=$tgl_akhir?>&page=<?=$i?>"><?=$i?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?act=Dashboard&tgl_awal=<?=$tgl_awal?>&tgl_akhir=<?=$tgl_akhir?>&page=<?=$page+1?>">Next</a>
                </li>
            </ul>
        </nav>
        <div class="text-center mt-2 text-muted" style="font-size:12px;">Menampilkan halaman <?=$page?> dari <?=$total_pages?> (Total <?=$total_data?> Data)</div>
        <?php endif; ?>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px; border: none;">
                <div class="modal-header" style="background: #fffbeb; border-bottom: 2px solid #fde68a; border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title fw-bold" id="editModalLabel" style="color: #b45309;"><i class="bi bi-pencil-square me-2"></i>Edit PSM Pengantar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="?act=EditRujukan" enctype="multipart/form-data">
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
        // Live search functionality
        $(document).ready(function(){
            $("#searchInput").on("keyup", function() {
                var rawValue = $(this).val();
                var value = rawValue.toLowerCase();
                $("#dataTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
                
                var tglAwal = "<?= $tgl_awal ?>";
                var tglAkhir = "<?= $tgl_akhir ?>";
                var href = "?act=CetakLaporan&tgl_awal=" + tglAwal + "&tgl_akhir=" + tglAkhir;
                if(rawValue.trim() !== "") {
                    href += "&keyword=" + encodeURIComponent(rawValue);
                }
                $("#btnCetak").attr("href", href);
            });
        });

        // Function to show Edit Modal
        function showEditModal(noRawat, tanggal, currentIdPsm, overrideStatus) {
            $('#edit_no_rawat').val(noRawat);
            $('#edit_tanggal').val(tanggal);
            $('#edit_id_psm').val(currentIdPsm);
            $('#edit_override_status').val(overrideStatus || '');
            
            // Set link for camera retake
            var cameraLink = '?act=Kamera&norawat=' + encodeURIComponent(noRawat) + '&tanggal=' + encodeURIComponent(tanggal);
            $('#btnGantiFoto').attr('href', cameraLink);
            
            var myModal = new bootstrap.Modal(document.getElementById('editModal'));
            myModal.show();
        }

        // Render Trend Chart
        const trendCtx = document.getElementById('trendChart');
        if(trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($trend_labels) ?>,
                    datasets: [{
                        label: 'Jumlah Pasien',
                        data: <?= json_encode($trend_data) ?>,
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14, 165, 233, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#0ea5e9'
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
                    plugins: { legend: { display: false } }
                }
            });
        }

    </script>
</body>
</html>
