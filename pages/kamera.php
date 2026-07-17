<?php
    global $db_name_sik;
    if(strpos($_SERVER['REQUEST_URI'],"pages")){
        exit(header("Location:../index.php"));
    }
    
    $namars        = getOne("select setting.nama_instansi from {$db_name_sik}.setting");
    // Ambil jam server dari database
    $_sql_time = bukaquery2("SELECT NOW() as waktu");
    if($_data_time = mysqli_fetch_array($_sql_time)) {
        $tanggal = $_data_time['waktu'];
    } else {
        $tanggal = date('Y-m-d H:i:s');
    }
    
    $norawat       = "";
    
    if (isset($_REQUEST['no_rkm_medis']) && !empty($_REQUEST['no_rkm_medis'])) {
        $no_rkm_medis_input = substr(preg_replace('/[^a-zA-Z0-9\-\s]/', '', $_REQUEST['no_rkm_medis']), 0, 30);
        $_sql_get = "select no_rawat from {$db_name_sik}.reg_periksa where no_rkm_medis=? order by tgl_registrasi desc, jam_reg desc limit 1";
        $hasil_get = bukaquery_prepared($_sql_get, "s", $no_rkm_medis_input);
        if ($data_get = mysqli_fetch_array($hasil_get)) {
            $norawat = $data_get['no_rawat'];
        }
        if (isset($_REQUEST['tanggal']) && !empty($_REQUEST['tanggal'])) {
            $tanggal = substr(preg_replace('/[^0-9\-\:\s]/', '', $_REQUEST['tanggal']), 0, 20);
        }
    } else if (isset($_REQUEST['norawat']) && !empty($_REQUEST['norawat'])) {
        // ijinkan huruf, angka, spasi, strip(-), dan garis miring(/)
        $norawat = substr(preg_replace('/[^a-zA-Z0-9\/\-\s]/', '', $_REQUEST['norawat']), 0, 30);
        if (isset($_REQUEST['tanggal']) && !empty($_REQUEST['tanggal'])) {
            $tanggal = substr(preg_replace('/[^0-9\-\:\s]/', '', $_REQUEST['tanggal']), 0, 20);
        }
    } else {
        $_sql          = "select * from antri_rujukan_psm" ;  
        $hasil         = bukaquery2($_sql);
        while ($data = mysqli_fetch_array ($hasil)){
            $tanggal  = $data['tanggal'];
            $norawat  = $data['no_rawat'];
        }
    }
    
    // Jika No Rawat kosong, tampilkan form input
    if(empty($norawat)) {
        echo "<!DOCTYPE html><html><head>
              <title>Pilih Pasien</title>
              <link rel='stylesheet' href='css/bootstrap.min.css' />
              <link href='https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
              <style>body{background:#f8fafc; font-family:'Plus Jakarta Sans', sans-serif;}</style>
              </head><body>";
        include "layout/navbar.php";
        echo "<div class='container mt-5' style='background:#fff; padding:40px; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.05); max-width:500px;'>
                <h4 class='text-center mb-4' style='color:#0f766e; font-weight:700;'>Pilih Pasien</h4>
                <form method='GET' action=''>
                    <input type='hidden' name='act' value='Kamera'>
                    <div class='mb-3'>
                        <label class='fw-bold mb-2'>Masukkan No. RM</label>
                        <input type='text' name='no_rkm_medis' class='form-control' placeholder='Contoh: 000001' required autofocus>
                    </div>
                    <button type='submit' class='btn btn-primary w-100' style='background:#0f766e; border:none;'>Cari Pasien</button>
                    <a href='?act=Dashboard' class='btn btn-light w-100 mt-2'>Kembali ke Dashboard</a>
                </form>
              </div></body></html>";
        exit;
    }
    
    
    $no_rkm_medis = "";
    $nm_pasien    = "";
    $jk           = "";
    $umur         = "";
    $tgl_lahir    = "";
    $alamat       = "";
    $no_tlp       = "";
    
    $_sql2 = "select reg_periksa.no_rawat,pasien.no_rkm_medis,pasien.nm_pasien,pasien.no_tlp,if(pasien.jk='L','LAKI-LAKI','PEREMPUAN') as jk,
               pasien.umur,DATE_FORMAT(pasien.tgl_lahir,'%d-%m-%Y') as tgl_lahir,concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab) as alamat, 
               IFNULL((SELECT CONCAT(IF(reg_periksa.status_lanjut='Ralan', CONCAT(pl.nm_poli, ' - '), IF(bsl.nm_bangsal IS NOT NULL, CONCAT(bsl.nm_bangsal, ' - '), '')), 'OK: ', po.nm_perawatan) FROM {$db_name_sik}.operasi op INNER JOIN {$db_name_sik}.paket_operasi po ON op.kode_paket=po.kode_paket WHERE op.no_rawat=reg_periksa.no_rawat LIMIT 1), IFNULL(bsl.nm_bangsal, pl.nm_poli)) as nm_bangsal,
               IF(reg_periksa.status_lanjut = 'Ranap', 'Rawat Inap', 'Rawat Jalan') as status_ranap
               from {$db_name_sik}.reg_periksa reg_periksa 
               inner join {$db_name_sik}.pasien pasien on reg_periksa.no_rkm_medis=pasien.no_rkm_medis 
               left join {$db_name_sik}.kelurahan kelurahan on pasien.kd_kel=kelurahan.kd_kel
               left join {$db_name_sik}.kecamatan kecamatan on pasien.kd_kec=kecamatan.kd_kec 
               left join {$db_name_sik}.kabupaten kabupaten on pasien.kd_kab=kabupaten.kd_kab
               left join {$db_name_sik}.kamar_inap ki on reg_periksa.no_rawat=ki.no_rawat and ki.stts_pulang='-'
               left join {$db_name_sik}.kamar k on ki.kd_kamar=k.kd_kamar
               left join {$db_name_sik}.bangsal bsl on k.kd_bangsal=bsl.kd_bangsal
               left join {$db_name_sik}.permintaan_ranap pr on reg_periksa.no_rawat=pr.no_rawat
               left join {$db_name_sik}.poliklinik pl on reg_periksa.kd_poli=pl.kd_poli
               where reg_periksa.no_rawat=?" ;  
    $hasil2 = bukaquery_prepared($_sql2, "s", $norawat);
    while ($data2  = mysqli_fetch_array ($hasil2)){
        $no_rkm_medis = $data2['no_rkm_medis'];
        $nm_pasien    = $data2['nm_pasien'];
        $jk           = $data2['jk'];
        $umur         = $data2['umur'];
        $tgl_lahir    = $data2['tgl_lahir'];
        $alamat       = $data2['alamat'];
        $no_tlp       = $data2['no_tlp'];
        $nm_bangsal   = $data2['nm_bangsal'];
        $status_ranap = $data2['status_ranap'];
    }

    if(empty($nm_pasien)) {
        echo "<!DOCTYPE html><html><head>
              <title>Akses Ditolak</title>
              <link rel='stylesheet' href='css/bootstrap.min.css' />
              <link href='https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
              <style>body{background:#f8fafc; font-family:'Plus Jakarta Sans', sans-serif;}</style>
              </head><body>";
        include "layout/navbar.php";
        echo "<div class='container mt-5'>
                <div class='alert alert-danger text-center' style='padding:40px; border:1px solid #f5c6cb; border-radius:10px; background:#f8d7da; color:#721c24;'>
                    <h4 style='margin-bottom:15px;'>Akses Ditolak!</h4>
                    <p style='margin-bottom:20px;'>Pasien dengan No. Rawat <b>".e($norawat)."</b> tidak ditemukan,<br/>Atau pasien tersebut belum memiliki data <b>Kamar Inap</b> / <b>Permintaan Ranap</b>.</p>
                    <a href='?act=Kamera' class='btn btn-light' style='padding:8px 20px; margin-right:10px;'>Cari Ulang</a>
                    <a href='?act=Dashboard' class='btn btn-secondary' style='padding:8px 20px;'>Kembali ke Dashboard</a>
                </div>
              </div></body></html>";
        exit;
    }
    
    $keterangan_diberikan_pada  = isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) ? $_SESSION['ses_admin_pelaksanaanrujukanpsm'] : 'Petugas';
    $tanggalrujukan             = date('d-m-Y');
    
    $notif_psm = "";
    $_sql_cek = "select master_psm.nama_psm from pelaksanaan_rujukan_psm inner join master_psm on pelaksanaan_rujukan_psm.id_psm = master_psm.id_psm where pelaksanaan_rujukan_psm.no_rawat=?";
    $hasil_cek = bukaquery_prepared($_sql_cek, "s", $norawat);
    if ($data_cek = mysqli_fetch_array($hasil_cek)) {
        $notif_psm = $data_cek['nama_psm'];
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>SIMKES Khanza</title>
    <script src="js/jquery.min.js"></script>
    <script src="js/webcam.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
            <style type="text/css">
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            color: #334155 !important;
            padding-top: 20px;
            padding-bottom: 40px;
            font-size: 13px !important;
        }
        .content-wrapper {
            background: #ffffff !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
            padding: 25px !important;
            max-width: 1100px !important;
            border: 1px solid #e2e8f0 !important;
            margin: auto;
        }
        h5, .text-dark {
            color: #1e293b !important;
        }
        h7 {
            font-size: 13px !important;
            font-weight: 500 !important;
            line-height: 1.6 !important;
            color: #334155 !important;
            display: inline-block !important;
        }
        table {
            border: 1px solid #cbd5e1 !important;
            border-collapse: collapse !important;
            width: 100% !important;
            margin-bottom: 15px !important;
        }
        tr, td {
            border: 1px solid #cbd5e1 !important;
        }
        td {
            padding: 6px 10px !important;
            font-size: 13px !important;
        }
        td[colspan="4"][style*="font-size:20px"] {
            font-size: 20px !important;
            font-weight: 800 !important;
            color: #0f766e !important;
            padding: 12px 0 !important;
            text-align: center !important;
            border-bottom: 2px solid #cbd5e1 !important;
            background: #f8fafc !important;
        }
        td[colspan="4"][style*="font-weight:bold"] {
            background: #f1f5f9 !important;
            color: #0f766e !important;
            font-weight: 700 !important;
            font-size: 14px !important;
            padding: 10px 12px !important;
            border-bottom: 1px solid #cbd5e1 !important;
        }
        table table {
            border: 1px solid #cbd5e1 !important;
            border-collapse: collapse !important;
            background-color: #f8fafc !important;
            margin-top: 5px !important;
            margin-bottom: 5px !important;
            width: 100% !important;
        }
        table table td {
            border: 1px solid #cbd5e1 !important;
            padding: 5px 8px !important;
        }
        h5 button {
            margin-bottom: 15px !important;
        }
        input[type="text"], select {
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 4px 8px !important;
            outline: none !important;
            transition: all 0.2s ease !important;
            font-family: inherit !important;
            font-size: 13px !important;
            background-color: #ffffff !important;
            color: #1e293b !important;
        }
        input[type="text"]:focus, select:focus {
            border-color: #0f766e !important;
            box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.15) !important;
        }
        #my_camera, #results {
            border-radius: 12px !important;
            overflow: hidden !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
            border: 2px solid #e2e8f0 !important;
            margin: 10px auto !important;
        }
        #results {
            background: #f8fafc !important;
            border-style: dashed !important;
            border-color: #cbd5e1 !important;
            min-height: 200px !important;
            width: 100% !important;
            max-width: 980px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        #results h7 {
            color: #059669 !important;
            font-weight: 600 !important;
        }
        .btn-secondary {
            background-color: #e2e8f0 !important;
            color: #475569 !important;
            border: 1px solid #cbd5e1 !important;
            font-weight: 600 !important;
            padding: 6px 14px !important;
            border-radius: 8px !important;
            transition: all 0.2s !important;
            font-size: 13px !important;
        }
        .btn-secondary:hover {
            background-color: #cbd5e1 !important;
            color: #1e293b !important;
        }
        .btn-warning {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;
            color: white !important;
            font-weight: 700 !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.2) !important;
            transition: all 0.2s !important;
            font-size: 14px !important;
        }
        .btn-warning:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 10px 15px -3px rgba(2, 132, 199, 0.3) !important;
        }
        .btn-danger {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: white !important;
            font-weight: 700 !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2) !important;
            transition: all 0.2s !important;
            font-size: 14px !important;
        }
        .btn-danger:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.3) !important;
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 34px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 6px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 32px !important;
            font-size: 13px !important;
            color: #1e293b !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 32px !important;
        }
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container content-wrapper mt-4">
        <?php if(!empty($notif_psm)) { ?>
            <div class="alert alert-warning text-center" style="border-radius: 10px; font-weight: 600;">
                <i class="bi bi-exclamation-triangle-fill"></i> Perhatian: Pasien ini sudah ditambahkan sebelumnya oleh PSM <b><?=e($notif_psm)?></b>.
            </div>
        <?php } ?>
        <h5 class="text-dark"><center><button class="btn btn-secondary" onclick="window.location.reload();">Refresh</button><br/><br/>PENGAMBILAN BUKTI PELAKSANAAN RUJUKAN PSM <br>NO. RM <?=e($no_rkm_medis);?></center></h5>
        <h7 class="text-dark"><center>Tanggal <?=e($tanggalrujukan);?></center></h7><br/>
        <form id="formFoto" method="POST" action="pages/storeImage.php" onsubmit="if(document.getElementById('TxtIsi1').value==''){alert('Silahkan klik Ya, Gabungkan Foto & Tanda Tangan terlebih dahulu!');return false;}" enctype=multipart/form-data>
            <?= csrf_input() ?>
            <input type="hidden" name="norawat" value="<?=e($norawat);?>">
            <input type="hidden" name="tanggal" value="<?=e($tanggal);?>">
            <h7 class="text-dark">
                Saya yang dibawah ini :
            </h7>
            <table class="default" width="98%" border="0" align="center" cellpadding="3px" cellspacing="0px">
                <tr class="text-dark">
                    <td width="25%">PSM Pengantar (Pilih)</td>
                    <td width="70%">: 
                        <select name="id_psm" class="form-control" style="width: 250px; display: inline-block;" required>
                            <option value="">-- Pilih PSM --</option>
                            <?php
                                $query_psm = bukaquery2("SELECT * FROM master_psm WHERE status='Aktif'");
                                while($psm = mysqli_fetch_array($query_psm)) {
                                    echo "<option value='".e($psm['id_psm'])."'>".e($psm['nama_psm'])."</option>";
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr class="text-dark">
                    <td width="25%">Petugas Verifikasi</td>
                    <td width="70%">: <?=e($keterangan_diberikan_pada);?></td>
                </tr>
            </table>
            <br/>
            <h7 class="text-dark">
                Dari pasien <?=e($namars)?> dengan :
            </h7>
             <table class="default" width="98%" border="0" align="center" cellpadding="3px" cellspacing="0px">
                <tr class="text-dark">
                    <td width="25%">Nama Pasien</td>
                    <td width="70%">: <?=e($nm_pasien);?></td>
                </tr>
                <tr class="text-dark">
                    <td width="25%">Nomor Rekam Medis</td>
                    <td width="75%">: <?=e($no_rkm_medis);?></td>
                </tr>
                <tr class="text-dark">
                    <td width="25%">Jenis Kelamin</td>
                    <td width="75%">: <?=e($jk);?></td>
                </tr>
                <tr class="text-dark">
                    <td width="25%">Tanggal Lahir</td>
                    <td width="75%">: <?=e($tgl_lahir);?></td>
                </tr>
                <tr class="text-dark">
                    <td width="25%">Kamar Inap</td>
                    <td width="75%">: <b><?=e($nm_bangsal);?></b></td>
                </tr>
                <tr class="text-dark">
                    <td width="25%">Status Permintaan</td>
                    <td width="75%">: <b><?=e($status_ranap);?></b></td>
                </tr>
            </table>
            <br/>
            <h7 class="text-dark">
                Bahwa pasien ini benar telah diantar oleh PSM terkait ke <?=e($namars)?>. Demikian pernyataan ini dibuat dalam keadaan penuh kesadaran untuk digunakan sebagaimana mestinya.
            </h7>
            <br/>
            <br/>
            <h7 class="text-dark"><center>Yang Membuat Pernyataan</center></h7>
                        <div class="row">
                <!-- Kolom Kiri: Kamera -->
                <div class="col-md-6 text-center">
                    <span class="text-dark font-weight-bold" style="font-size: 13px;">1. Ambil Foto Wajah / KTP</span><br/>
                    <div id="my_camera" style="margin: 10px auto;"></div>
                    <input type="hidden" name="image" class="image-tag" onkeydown="setDefault(this, document.getElementById('MsgIsi1'));" id="TxtIsi1">
                </div>
                <!-- Kolom Kanan: Tanda Tangan Manual -->
                <div class="col-md-6 text-center">
                    <span class="text-dark font-weight-bold" style="font-size: 13px;">2. Tanda Tangan Manual di Bawah Ini</span><br/>
                    <div style="position: relative; margin: 10px auto; width: 490px; height: 200px; background-color: #ffffff; border: 2px solid #cbd5e1; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-top: 95px;">
                        <canvas id="signature-pad" width="490" height="200" style="cursor: crosshair; display: block;"></canvas>
                        <button type="button" class="btn btn-secondary btn-sm" id="clear-signature" style="position: absolute; right: 10px; bottom: 10px; z-index: 10; padding: 4px 10px; font-size: 11px;">Hapus TTD</button>
                    </div>
                </div>
                
                <!-- Hasil Penggabungan -->
                <div class="col-md-12 text-center" style="margin-top: 20px;">
                    <span class="text-dark font-weight-bold" style="font-size: 13px;">3. Hasil Gabungan untuk Bukti Persetujuan</span><br/>
                    <div id="results" style="margin: 10px auto;">
                        <h7 class="text-success"><center>Foto dan Tanda Tangan akan digabung setelah Anda klik tombol di bawah</center></h7>
                    </div>
                    <span id="MsgIsi1" style="color:#CC0000; font-size:10px;"></span>
                </div>
                
                <div class="col-md-12 text-center" style="margin-top: 15px;">
                    <input type="button" class="btn btn-warning" value="Ya, Gabungkan Foto & Tanda Tangan" onClick="take_snapshot()">
                    <button class="btn btn-danger">Simpan</button>
                </div>
            </div>
        </form>
    </div>
    
            <script language="JavaScript">
        Webcam.set({
            width: 490,
            height: 390,
            image_format: 'jpeg',
            jpeg_quality: 90
        });

        Webcam.attach( '#my_camera' );

        // Setup Canvas Signature Pad
        var canvas = document.getElementById('signature-pad');
        var ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        var drawing = false;
        var lastX = 0;
        var lastY = 0;

        function getMousePos(canvasDom, touchOrMouseEvent) {
            var rect = canvasDom.getBoundingClientRect();
            var clientX = touchOrMouseEvent.clientX || (touchOrMouseEvent.touches && touchOrMouseEvent.touches[0].clientX);
            var clientY = touchOrMouseEvent.clientY || (touchOrMouseEvent.touches && touchOrMouseEvent.touches[0].clientY);
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function draw(e) {
            if (!drawing) return;
            var pos = getMousePos(canvas, e);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            lastX = pos.x;
            lastY = pos.y;
        }

        // Mouse Events
        canvas.addEventListener('mousedown', function(e) {
            drawing = true;
            var pos = getMousePos(canvas, e);
            lastX = pos.x;
            lastY = pos.y;
        });
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', function() { drawing = false; });
        canvas.addEventListener('mouseout', function() { drawing = false; });

        // Touch Events (for Mobile/Tablet support)
        canvas.addEventListener('touchstart', function(e) {
            e.preventDefault();
            drawing = true;
            var pos = getMousePos(canvas, e);
            lastX = pos.x;
            lastY = pos.y;
        }, { passive: false });
        canvas.addEventListener('touchmove', function(e) {
            e.preventDefault();
            draw(e);
        }, { passive: false });
        canvas.addEventListener('touchend', function() { drawing = false; });

        // Clear button
        document.getElementById('clear-signature').addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });

        function take_snapshot() {
            Webcam.snap(function(data_uri) {
                // Create an image object from the webcam data URI
                var webcamImg = new Image();
                webcamImg.onload = function() {
                    // Create an off-screen canvas for merging (stacked vertically)
                    var mergeCanvas = document.createElement('canvas');
                    mergeCanvas.width = 490; // single column width
                    mergeCanvas.height = 590; // webcam 390 + signature 200
                    var mctx = mergeCanvas.getContext('2d');

                    // Draw a white background first to avoid transparent canvas background in JPEG
                    mctx.fillStyle = "#ffffff";
                    mctx.fillRect(0, 0, mergeCanvas.width, mergeCanvas.height);

                    // Draw webcam image on top
                    mctx.drawImage(webcamImg, 0, 0, 490, 390);

                    // Draw signature canvas at the bottom
                    mctx.drawImage(canvas, 0, 390, 490, 200);

                    // Draw a subtle horizontal divider line between them
                    mctx.strokeStyle = "#cbd5e1";
                    mctx.lineWidth = 2;
                    mctx.beginPath();
                    mctx.moveTo(0, 390);
                    mctx.lineTo(490, 390);
                    mctx.stroke();

                    // Convert to base64 JPEG
                    var mergedDataUri = mergeCanvas.toDataURL("image/jpeg", 0.9);

                    // Set it to hidden field and show preview
                    $(".image-tag").val(mergedDataUri);
                    document.getElementById('results').innerHTML = '<img src="' + mergedDataUri + '" style="max-width: 490px; width: 100%; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);"/>';
                };
                webcamImg.src = data_uri;
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('select[name="id_psm"]').select2({
                width: '250px'
            });
        });
    </script>
</body>
</html>
