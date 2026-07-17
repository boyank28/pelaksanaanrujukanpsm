<?php
    if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
        echo "<META HTTP-EQUIV = 'Refresh' Content = '0; URL = ?act=Home'>";
        exit;
    }
    global $db_name_sik, $konektor;

    $bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
    $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

    // Pastikan tabel TTD ada dan ambil datanya
    bukaquery2("CREATE TABLE IF NOT EXISTS setting_ttd_amprahan (id INT PRIMARY KEY DEFAULT 1, mengetahui_nama VARCHAR(100), mengetahui_jabatan VARCHAR(100), dicek_nama VARCHAR(100), dicek_jabatan VARCHAR(100), menyetujui_nama VARCHAR(100), menyetujui_jabatan VARCHAR(100), dibuatkan_nama VARCHAR(100), dibuatkan_jabatan VARCHAR(100))");
    
    // Pastikan kolom dibuatkan ada
    global $konektor;
    try {
        @mysqli_query($konektor, "ALTER TABLE setting_ttd_amprahan ADD COLUMN dibuatkan_nama VARCHAR(100) AFTER menyetujui_jabatan");
        @mysqli_query($konektor, "ALTER TABLE setting_ttd_amprahan ADD COLUMN dibuatkan_jabatan VARCHAR(100) AFTER dibuatkan_nama");
    } catch (Exception $e) {}

    $ttd = mysqli_fetch_array(bukaquery2("SELECT * FROM setting_ttd_amprahan WHERE id=1"));
    if(!$ttd) {
        $ttd = [
            'mengetahui_nama' => '-', 'mengetahui_jabatan' => '-',
            'dicek_nama' => '-', 'dicek_jabatan' => '-',
            'menyetujui_nama' => '-', 'menyetujui_jabatan' => '-',
            'dibuatkan_nama' => '-', 'dibuatkan_jabatan' => '-'
        ];
    }
    
    $pembuat_nama = !empty($ttd['dibuatkan_nama']) ? $ttd['dibuatkan_nama'] : '-';
    $pembuat_jabatan = !empty($ttd['dibuatkan_jabatan']) ? $ttd['dibuatkan_jabatan'] : '-';

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

    bukaquery2("CREATE TABLE IF NOT EXISTS amprahan_keterangan (bulan INT, tahun INT, id_psm INT, keterangan TEXT, PRIMARY KEY(bulan, tahun, id_psm))");
    bukaquery2("CREATE TABLE IF NOT EXISTS log_aktivitas (id INT AUTO_INCREMENT PRIMARY KEY, waktu DATETIME, username VARCHAR(100), ip_address VARCHAR(50), aktivitas TEXT)");

    // Query Amprahan Biaya Fee
    $sql_amprahan = "SELECT 
        IFNULL(kec.nm_kec, 'LAINNYA') as kecamatan,
        mpsm.id_psm,
        mpsm.nama_psm,
        SUM(IF(r.status_lanjut='Ranap', IF(op.no_rawat IS NOT NULL, {$f_ranap_op}, {$f_ranap}), IF(op.no_rawat IS NOT NULL, {$f_ralan_op}, {$f_ralan}))) as total_fee,
        ak.keterangan
    FROM pelaksanaan_rujukan_psm p
    INNER JOIN master_psm mpsm ON p.id_psm = mpsm.id_psm
    INNER JOIN {$db_name_sik}.reg_periksa r ON p.no_rawat = r.no_rawat
    INNER JOIN {$db_name_sik}.pasien ps ON r.no_rkm_medis = ps.no_rkm_medis
    LEFT JOIN {$db_name_sik}.kecamatan kec ON ps.kd_kec = kec.kd_kec
    LEFT JOIN (SELECT no_rawat FROM {$db_name_sik}.operasi GROUP BY no_rawat) op ON r.no_rawat = op.no_rawat
    LEFT JOIN amprahan_keterangan ak ON mpsm.id_psm = ak.id_psm AND ak.bulan = ? AND ak.tahun = ?
    WHERE MONTH(p.tanggal) = ? AND YEAR(p.tanggal) = ?
    GROUP BY kec.nm_kec, mpsm.id_psm
    ORDER BY kec.nm_kec ASC, mpsm.nama_psm ASC";
    
    $hasil_amprahan = bukaquery_prepared($sql_amprahan, "iiii", $bulan, $tahun, $bulan, $tahun);
    $amprahan_data = [];
    $total_keseluruhan = 0;
    if ($hasil_amprahan && mysqli_num_rows($hasil_amprahan) > 0) {
        while ($r_amp = mysqli_fetch_array($hasil_amprahan)) {
            $kec = strtoupper($r_amp['kecamatan']);
            $fee = (float)$r_amp['total_fee'];
            if(!isset($amprahan_data[$kec])) $amprahan_data[$kec] = [];
            $amprahan_data[$kec][] = ['id_psm' => $r_amp['id_psm'], 'nama' => $r_amp['nama_psm'], 'fee' => $fee, 'keterangan' => $r_amp['keterangan']];
            $total_keseluruhan += $fee;
        }
    }
    
    // Ambil data approval
    $approvals = [];
    // Buat tabel jika belum ada (jaga-jaga)
    bukaquery2("CREATE TABLE IF NOT EXISTS amprahan_approval (bulan INT, tahun INT, tipe_approval VARCHAR(50), username VARCHAR(100), nama_lengkap VARCHAR(150), waktu_approval DATETIME, PRIMARY KEY(bulan, tahun, tipe_approval))");
    
    $res_app = bukaquery_prepared("SELECT * FROM amprahan_approval WHERE bulan=? AND tahun=?", "ii", $bulan, $tahun);
    if($res_app) {
        while($row_app = mysqli_fetch_array($res_app)) {
            $approvals[$row_app['tipe_approval']] = $row_app;
        }
    }
    
    $perms = isset($_SESSION['permissions_psm']) ? $_SESSION['permissions_psm'] : [];
    $is_admin = isset($_SESSION['role_psm']) && $_SESSION['role_psm'] == 'Admin';
    $can_mengetahui = $is_admin || !empty($perms['can_approve_mengetahui']);
    $can_dicek = $is_admin || !empty($perms['can_approve_dicek']);
    $can_menyetujui = $is_admin || !empty($perms['can_approve_menyetujui']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Amprahan Fee PSM</title>
    <script src="js/jquery.min.js"></script>
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
        @media print {
            body { background: white !important; }
            .card-custom { box-shadow: none !important; padding: 0 !important; }
            form, .btn, .navbar, footer { display: none !important; }
            .print-only { display: block !important; }
            .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            .signature-block { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container-fluid py-4" style="max-width: 1000px;">
        <h2 class="page-title d-print-none"><i class="bi bi-cash-stack me-2"></i> Amprahan Biaya Fee PSM</h2>
        
        <div class="card-custom d-print-none">
            <form method="GET" action="index.php" class="row g-3 align-items-end">
                <input type="hidden" name="act" value="Amprahan">
                <div class="col-md-4">
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
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <button type="submit" class="btn btn-filter w-100 py-2"><i class="bi bi-funnel-fill me-1"></i> Tampilkan</button>
                </div>
            </form>
        </div>

        <div class="card-custom">
            <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
                <h5 class="fw-bold m-0" style="color: #334155;">AMPRAHAN BIAYA FEE PSM</h5>
                <div>
                    <button class="btn btn-outline-success btn-sm me-2" onclick="exportExcel('tableAmprahan', 'Amprahan_Fee_PSM_<?= $bulan ?>_<?= $tahun ?>')"><i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel</button>
                    <button class="btn btn-outline-primary btn-sm" onclick="window.print()"><i class="bi bi-printer-fill me-1"></i> Cetak</button>
                </div>
            </div>
            
            <h5 class="fw-bold m-0 text-center mb-3 d-none d-print-block" style="color: #000;">AMPRAHAN BIAYA FEE PSM WILAYAH CIKAMPEK DAN SEKITARNYA BULAN <?= strtoupper($bulans[$bulan-1]) ?> TH <?= $tahun ?></h5>

            <div class="table-responsive">
                <table id="tableAmprahan" class="table table-bordered table-custom" style="font-size: 13px;">
                    <thead class="text-center align-middle" style="background-color: #e2e8f0;">
                        <tr>
                            <th width="25%" class="no-sort">KECAMATAN</th>
                            <th width="35%" class="no-sort">NAMA PSM</th>
                            <th width="20%" class="no-sort">FEE PSM</th>
                            <th width="20%" class="no-sort">KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(empty($amprahan_data)) {
                            echo "<tr><td colspan='4' class='text-center text-muted'>Tidak ada data</td></tr>";
                        } else {
                            foreach($amprahan_data as $kec => $psms) {
                                $rowspan = count($psms);
                                $subtotal = 0;
                                $first = true;
                                foreach($psms as $psm) {
                                    $subtotal += $psm['fee'];
                                    echo "<tr>";
                                    if($first) {
                                        echo "<td rowspan='".($rowspan+1)."' class='align-middle fw-bold'>".e($kec)."</td>";
                                        $first = false;
                                    }
                                    echo "<td>".e($psm['nama'])."</td>";
                                    echo "<td class='text-end'>".number_format($psm['fee'], 0, ',', '.')."</td>";
                                    echo "<td contenteditable='true' style='cursor:text; outline:none;' title='Klik untuk mengisi keterangan' onblur='saveKet(".$psm['id_psm'].", this.innerText, this)'>".e($psm['keterangan'])."</td>";
                                    echo "</tr>";
                                }
                                echo "<tr class='fw-bold bg-subtotal'>";
                                echo "<td>TOTAL</td>";
                                echo "<td class='text-end'>".number_format($subtotal, 0, ',', '.')."</td>";
                                echo "<td></td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                        <tr class="fw-bold bg-total" style="font-size:14px;">
                            <td colspan="2" class="text-center py-3">TOTAL KESELURUHAN PSM</td>
                            <td class="text-end py-3"><?= number_format($total_keseluruhan, 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="row mt-5 text-center signature-block" style="font-size: 13px; color:#000;">
                <div class="col-3">
                    <p class="mb-2">Dibuatkan Oleh :</p>
                    <div style="min-height: 80px;" class="d-flex flex-column justify-content-center align-items-center">
                        <?php 
                        $qr_pembuat = urlencode("Telah dibuatkan oleh " . $pembuat_nama . " secara digital pada " . date('d/m/Y'));
                        ?>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=<?= $qr_pembuat ?>" class="mb-1" alt="QR Code" />
                        <span class="text-success fw-bold" style="font-size: 11px;"><i class="bi bi-check-circle-fill"></i> Dibuatkan digital</span>
                    </div>
                    <p class="fw-bold m-0 mt-2"><?= e($pembuat_nama) ?></p>
                    <p class="text-muted"><?= e($pembuat_jabatan) ?></p>
                </div>
                <div class="col-3">
                    <p class="mb-2">Mengetahui,</p>
                    <div id="box-mengetahui" style="min-height: 80px;" class="d-flex flex-column justify-content-center align-items-center">
                        <?php if(isset($approvals['mengetahui'])): 
                            $qr_text = urlencode("Telah disetujui digital oleh " . $approvals['mengetahui']['nama_lengkap'] . " pada " . date('d/m/Y H:i', strtotime($approvals['mengetahui']['waktu_approval'])));
                        ?>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=<?= $qr_text ?>" class="mb-1" alt="QR Code" />
                            <span class="text-success fw-bold" style="font-size: 11px;"><i class="bi bi-check-circle-fill"></i> Disetujui digital</span>
                            <span class="text-muted mb-2" style="font-size: 10px;"><?= date('d/m/Y H:i', strtotime($approvals['mengetahui']['waktu_approval'])) ?></span>
                            <?php if($can_mengetahui): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 d-print-none" style="font-size: 10px;" onclick="toggleApproval('mengetahui')">Batal</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if($can_mengetahui): ?>
                                <button type="button" class="btn btn-sm btn-primary d-print-none" style="background:#0f766e; border:none;" onclick="toggleApproval('mengetahui')">Validasi</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <p class="fw-bold m-0 mt-2"><?= isset($approvals['mengetahui']) ? e($approvals['mengetahui']['nama_lengkap']) : e($ttd['mengetahui_nama']) ?></p>
                    <p class="text-muted"><?= e($ttd['mengetahui_jabatan']) ?></p>
                </div>
                <div class="col-3">
                    <p class="mb-2">Dicek Oleh :</p>
                    <div id="box-dicek" style="min-height: 80px;" class="d-flex flex-column justify-content-center align-items-center">
                        <?php if(isset($approvals['dicek'])): 
                            $qr_text2 = urlencode("Telah disetujui digital oleh " . $approvals['dicek']['nama_lengkap'] . " pada " . date('d/m/Y H:i', strtotime($approvals['dicek']['waktu_approval'])));
                        ?>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=<?= $qr_text2 ?>" class="mb-1" alt="QR Code" />
                            <span class="text-success fw-bold" style="font-size: 11px;"><i class="bi bi-check-circle-fill"></i> Disetujui digital</span>
                            <span class="text-muted mb-2" style="font-size: 10px;"><?= date('d/m/Y H:i', strtotime($approvals['dicek']['waktu_approval'])) ?></span>
                            <?php if($can_dicek): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 d-print-none" style="font-size: 10px;" onclick="toggleApproval('dicek')">Batal</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if($can_dicek): ?>
                                <button type="button" class="btn btn-sm btn-primary d-print-none" style="background:#0f766e; border:none;" onclick="toggleApproval('dicek')">Validasi</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <p class="fw-bold m-0 mt-2"><?= isset($approvals['dicek']) ? e($approvals['dicek']['nama_lengkap']) : e($ttd['dicek_nama']) ?></p>
                    <p class="text-muted"><?= e($ttd['dicek_jabatan']) ?></p>
                </div>
                <div class="col-3">
                    <p class="mb-2">Menyetujui :</p>
                    <div id="box-menyetujui" style="min-height: 80px;" class="d-flex flex-column justify-content-center align-items-center">
                        <?php if(isset($approvals['menyetujui'])): 
                            $qr_text3 = urlencode("Telah disetujui digital oleh " . $approvals['menyetujui']['nama_lengkap'] . " pada " . date('d/m/Y H:i', strtotime($approvals['menyetujui']['waktu_approval'])));
                        ?>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=<?= $qr_text3 ?>" class="mb-1" alt="QR Code" />
                            <span class="text-success fw-bold" style="font-size: 11px;"><i class="bi bi-check-circle-fill"></i> Disetujui digital</span>
                            <span class="text-muted mb-2" style="font-size: 10px;"><?= date('d/m/Y H:i', strtotime($approvals['menyetujui']['waktu_approval'])) ?></span>
                            <?php if($can_menyetujui): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 d-print-none" style="font-size: 10px;" onclick="toggleApproval('menyetujui')">Batal</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if($can_menyetujui): ?>
                                <button type="button" class="btn btn-sm btn-primary d-print-none" style="background:#0f766e; border:none;" onclick="toggleApproval('menyetujui')">Validasi</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <p class="fw-bold m-0 mt-2"><?= isset($approvals['menyetujui']) ? e($approvals['menyetujui']['nama_lengkap']) : e($ttd['menyetujui_nama']) ?></p>
                    <p class="text-muted"><?= e($ttd['menyetujui_jabatan']) ?></p>
                </div>
            </div>
        </div>
    </div>
    <script>
    function saveKet(id_psm, ket, element) {
        let originalBg = element.style.backgroundColor;
        element.style.backgroundColor = "#fef3c7";
        $.post('pages/save_keterangan.php', {
            bulan: <?= $bulan ?>,
            tahun: <?= $tahun ?>,
            id_psm: id_psm,
            keterangan: ket
        }, function(res) {
            element.style.backgroundColor = originalBg;
            showToast("Keterangan berhasil disimpan!");
        });
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

    function toggleApproval(tipe) {
        $.post('pages/approve_amprahan.php', {
            bulan: <?= $bulan ?>,
            tahun: <?= $tahun ?>,
            tipe: tipe
        }, function(res) {
            try {
                let data = JSON.parse(res);
                if(data.status === 'success') {
                    window.location.reload();
                } else {
                    Swal.fire("Info", data.message, "info");
                }
            } catch(e) {
                Swal.fire("Error", "Terjadi kesalahan sistem.", "error");
            }
        });
    }

    function showToast(msg) {
        let toast = $('<div class="position-fixed bottom-0 end-0 p-3 d-print-none" style="z-index: 1100"><div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true"><div class="d-flex"><div class="toast-body"><i class="bi bi-check-circle me-2"></i>'+msg+'</div></div></div></div>');
        $('body').append(toast);
        let bsToast = new bootstrap.Toast(toast.find('.toast')[0], {delay: 2000});
        bsToast.show();
        setTimeout(() => toast.remove(), 2500);
    }
    </script>
</body>
</html>
