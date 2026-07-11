<?php
    if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) || (empty($_SESSION['permissions_psm']['can_view_laporan']) && !empty($_SESSION['permissions_psm']))) {
        echo "<META HTTP-EQUIV = 'Refresh' Content = '0; URL = ?act=Dashboard'>";
        exit;
    }
    global $db_name_sik, $konektor;

    $bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
    $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

    // Query untuk mengambil data total pasien per PSM pada bulan dan tahun terpilih
    $sql = "SELECT psm.nama_psm, COUNT(p.no_rawat) as total_pasien 
            FROM master_psm psm 
            LEFT JOIN pelaksanaan_rujukan_psm p ON psm.id_psm = p.id_psm 
                AND MONTH(p.tanggal) = ? AND YEAR(p.tanggal) = ?
            GROUP BY psm.id_psm 
            ORDER BY total_pasien DESC LIMIT 10";
            
    $hasil = bukaquery_prepared($sql, "ii", $bulan, $tahun);
    
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
    @mysqli_query($konektor, "ALTER TABLE setting_fee_psm ADD COLUMN fee_ralan_op DOUBLE AFTER fee_ralan");
    $master_fee = mysqli_fetch_array(bukaquery2("SELECT * FROM setting_fee_psm WHERE id=1"));
    if(!$master_fee) $master_fee = ['fee_ranap_op'=>75000, 'fee_ranap'=>50000, 'fee_ralan'=>25000];
    $f_ranap_op = (double)$master_fee['fee_ranap_op'];
    $f_ranap = (double)$master_fee['fee_ranap'];
    $f_ralan = (double)$master_fee['fee_ralan'];
    $f_ralan_op = isset($master_fee['fee_ralan_op']) ? (double)$master_fee['fee_ralan_op'] : 125000;

    // Query untuk mengambil detail pasien
    $sql_detail = "SELECT 
        DATE(r.tgl_registrasi) as tgl_masuk,
        IFNULL(MAX(ki.tgl_keluar), '-') as tgl_pulang,
        r.no_rkm_medis as no_rm,
        ps.nm_pasien,
        IF(r.status_lanjut='Ranap', IF(op.no_rawat IS NOT NULL, 'RANAP-OP', 'RANAP'), IF(op.no_rawat IS NOT NULL, 'RALAN-OP', 'RALAN')) as jenis_perawatan,
        pj.png_jawab as jaminan,
        mpsm.nama_psm,
        concat(ps.alamat, ', ', kel.nm_kel, ', ', kec.nm_kec) as alamat,
        ps.no_tlp as no_hp,
        IFNULL((SELECT totalpiutang FROM {$db_name_sik}.piutang_pasien WHERE no_rawat=r.no_rawat LIMIT 1), 0) as billing,
        IF(r.status_lanjut='Ranap', IF(op.no_rawat IS NOT NULL, {$f_ranap_op}, {$f_ranap}), IF(op.no_rawat IS NOT NULL, {$f_ralan_op}, {$f_ralan})) as fee
    FROM pelaksanaan_rujukan_psm p
    INNER JOIN master_psm mpsm ON p.id_psm = mpsm.id_psm
    INNER JOIN {$db_name_sik}.reg_periksa r ON p.no_rawat = r.no_rawat
    INNER JOIN {$db_name_sik}.pasien ps ON r.no_rkm_medis = ps.no_rkm_medis
    INNER JOIN {$db_name_sik}.penjab pj ON r.kd_pj = pj.kd_pj
    LEFT JOIN {$db_name_sik}.kelurahan kel ON ps.kd_kel = kel.kd_kel
    LEFT JOIN {$db_name_sik}.kecamatan kec ON ps.kd_kec = kec.kd_kec
    LEFT JOIN {$db_name_sik}.kamar_inap ki ON r.no_rawat = ki.no_rawat AND ki.stts_pulang <> 'Pindah Kamar'
    LEFT JOIN (SELECT no_rawat FROM {$db_name_sik}.operasi GROUP BY no_rawat) op ON r.no_rawat = op.no_rawat
    WHERE MONTH(p.tanggal) = ? AND YEAR(p.tanggal) = ?
    GROUP BY p.no_rawat
    ORDER BY r.tgl_registrasi ASC";
    $hasil_detail = bukaquery_prepared($sql_detail, "ii", $bulan, $tahun);
    $detail_rows = "";
    $no_detail = 1;
    $total_fee = 0;
    if ($hasil_detail && mysqli_num_rows($hasil_detail) > 0) {
        while ($r_det = mysqli_fetch_array($hasil_detail)) {
            $fee = (float)$r_det['fee'];
            $total_fee += $fee;
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
            </tr>";
        }
        $detail_rows .= "<tr class='fw-bold bg-subtotal'><td colspan='11' class='text-end'>TOTAL FEE:</td><td class='text-end'>".number_format($total_fee, 0, ',', '.')."</td></tr>";
    } else {
        $detail_rows = "<tr><td colspan='12' class='text-center text-muted'>Tidak ada data pasien pada bulan ini</td></tr>";
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
    <div class="container-fluid py-4" style="max-width: 1200px;">
        <h2 class="page-title"><i class="bi bi-bar-chart-fill me-2"></i> Laporan Rekapitulasi PSM</h2>
        
        <div class="card-custom">
            <form method="GET" action="index.php" class="row g-3 align-items-end">
                <input type="hidden" name="act" value="Rekapitulasi">
                <div class="col-md-3">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size: 13px;">Bulan</label>
                    <select name="bulan" class="form-select" style="border-radius: 8px; border: 1.5px solid #cbd5e1;">
                        <?php 
                        $bulans = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                        foreach($bulans as $index => $nama_bulan) {
                            $val = str_pad($index + 1, 2, "0", STR_PAD_LEFT);
                            $sel = ($val == $bulan) ? "selected" : "";
                            echo "<option value='$val' $sel>$nama_bulan</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size: 13px;">Tahun</label>
                    <select name="tahun" class="form-select" style="border-radius: 8px; border: 1.5px solid #cbd5e1;">
                        <?php 
                        $thn_skrg = date('Y');
                        for($t = $thn_skrg; $t >= 2023; $t--) {
                            $sel = ($t == $tahun) ? "selected" : "";
                            echo "<option value='$t' $sel>$t</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-filter w-100 py-2"><i class="bi bi-funnel-fill me-1"></i> Tampilkan</button>
                </div>
            </form>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card-custom">
                    <h5 class="fw-bold mb-4" style="color: #334155;">Grafik Kinerja PSM</h5>
                    <canvas id="psmChart" height="120"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-custom">
                    <h5 class="fw-bold mb-3" style="color: #334155;"><i class="bi bi-trophy-fill text-warning me-2"></i> Peringkat PSM</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th width="10%">#</th>
                                    <th>Nama PSM</th>
                                    <th class="text-center">Total Pasien</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?= $table_data ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card-custom">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold m-0" style="color: #334155;"><i class="bi bi-file-earmark-spreadsheet-fill text-success me-2"></i> DATA REKAP PASIEN RUJUKAN PSM</h5>
                        <div>
                            <button class="btn btn-outline-primary btn-sm me-2" onclick="exportExcel('tableRekap', 'Rekap_Pasien_PSM_<?= $bulan ?>_<?= $tahun ?>')"><i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel</button>
                            <button class="btn btn-outline-success btn-sm" onclick="cetakArea('areaRekap', 'DATA REKAP PASIEN RUJUKAN PSM')"><i class="bi bi-printer-fill me-1"></i> Cetak</button>
                        </div>
                    </div>
                    <div class="table-responsive" id="areaRekap">
                        <table id="tableRekap" class="table table-bordered table-hover table-custom" style="font-size: 12px; white-space: nowrap;">
                            <thead class="text-center align-middle">
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
                                <?= $detail_rows ?>
                            </tbody>
                        </table>
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
</body>
</html>
