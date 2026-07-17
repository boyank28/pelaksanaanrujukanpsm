<?php
    session_start();
    require_once('../conf/conf.php');
    require_once('../conf/command.php');
    if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
        http_response_code(403);
        die("Akses ditolak.");
    }
    verify_csrf();
    $norawat = substr(preg_replace('/[^a-zA-Z0-9\/\-\s]/', '', $_POST["norawat"]), 0, 30);
    $tanggal = substr(preg_replace('/[^0-9\-\:\s]/', '', $_POST["tanggal"]), 0, 20);
    $id_psm  = validTeks4($_POST["id_psm"],11);
    $fileName       = str_replace("/","",$norawat).str_replace(":","",str_replace("-","",str_replace(" ","",$tanggal))).".jpeg";
    $folderPath     = __DIR__ . "/upload/";
    if (!is_dir($folderPath)) {
        @mkdir($folderPath, 0777, true);
    }
    $file           = $folderPath.$fileName;

    if(file_exists($file)){
        @unlink($file);
    }

    $img            = $_POST["image"];
    $image_parts    = explode(";base64,", $img);
    if(count($image_parts) != 2 || (strpos($image_parts[0], 'image/jpeg') === false && strpos($image_parts[0], 'image/png') === false)) {
        die("Invalid image format.");
    }
    $image_base64   = base64_decode($image_parts[1]);
    
    // MIME check for base64 decode
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($image_base64);
    if ($mime != 'image/jpeg' && $mime != 'image/png') {
        die("Invalid file type detected.");
    }

    if (file_put_contents($file, $image_base64) === false) {
        echo renderPage('error', 'Gagal', 'Gagal menyimpan file gambar. Periksa hak akses direktori upload server.', $urlKembali);
        exit;
    }

    $urlKembali = isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) ? '../index.php?act=Dashboard' : '../index.php?act=Home';
    
    function renderPage($type, $title, $message, $url) {
        $icon = $type == 'success' ? 'bi-check-circle-fill text-success' : ($type == 'warning' ? 'bi-exclamation-triangle-fill text-warning' : 'bi-x-circle-fill text-danger');
        $bg = $type == 'success' ? '#f0fdf4' : ($type == 'warning' ? '#fffbeb' : '#fef2f2');
        $border = $type == 'success' ? '#bbf7d0' : ($type == 'warning' ? '#fde68a' : '#fecaca');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Status Rujukan</title>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <link rel='stylesheet' href='../css/bootstrap.min.css'/>
            <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css'>
            <link href='https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
            <style>
                body {
                    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                    font-family: 'Plus Jakarta Sans', sans-serif;
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0;
                }
                .status-card {
                    background: white;
                    padding: 40px;
                    border-radius: 20px;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
                    text-align: center;
                    max-width: 450px;
                    width: 90%;
                    border: 1px solid rgba(0,0,0,0.05);
                    animation: slideUp 0.5s ease-out;
                }
                .icon-wrapper {
                    font-size: 60px;
                    margin-bottom: 20px;
                    display: inline-block;
                    background: {$bg};
                    width: 100px;
                    height: 100px;
                    line-height: 100px;
                    border-radius: 50%;
                    border: 2px solid {$border};
                }
                .btn-kembali {
                    background: #0f766e;
                    color: white;
                    border-radius: 10px;
                    padding: 10px 25px;
                    font-weight: 600;
                    text-decoration: none;
                    display: inline-block;
                    margin-top: 25px;
                    transition: all 0.3s;
                    border: none;
                    width: 100%;
                }
                .btn-kembali:hover {
                    background: #0d9488;
                    color: white;
                    transform: translateY(-2px);
                }
                @keyframes slideUp {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
            </style>
        </head>
        <body>
            <div class='status-card'>
                <div class='icon-wrapper'>
                    <i class='bi {$icon}'></i>
                </div>
                <h3 class='fw-bold mb-3' style='color:#1e293b;'>{$title}</h3>
                <p style='color:#64748b; font-size:15px; line-height:1.6;'>{$message}</p>
                <a href='{$url}' class='btn-kembali'><i class='bi bi-arrow-left-circle me-2'></i>Kembali ke Dashboard</a>
            </div>
            <script>
                // Auto redirect after 3 seconds for success
                " . ($type == 'success' ? "setTimeout(function(){ window.location.href = '{$url}'; }, 3000);" : "") . "
            </script>
        </body>
        </html>";
    }

    $petugas = isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) ? $_SESSION['ses_admin_pelaksanaanrujukanpsm'] : 'Petugas';
    
    try {
        // Insert new record since we're not relying on existing data anymore
        bukaquery_prepared("INSERT INTO pelaksanaan_rujukan_psm (no_rawat, tanggal, id_psm, keterangan_diberikan_pada, diberikan_pada, materi_rujukan) 
                    VALUES (?, ?, ?, ?, 'Pasien', '-') 
                    ON DUPLICATE KEY UPDATE id_psm=?, keterangan_diberikan_pada=?", "ssssss", $norawat, $tanggal, $id_psm, $petugas, $id_psm, $petugas);
        
        $bukti_path = "pages/upload/".$fileName;
        bukaquery_prepared("INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE bukti=?", "ssss", $norawat, $tanggal, $bukti_path, $bukti_path);
        
        catat_log("Menyimpan foto bukti dan rujukan PSM dengan no rawat $norawat (ID PSM: $id_psm)");
        echo renderPage('success', 'Berhasil!', 'Dokumen rujukan berhasil difoto dan disimpan ke dalam sistem.', $urlKembali);
        
    } catch (mysqli_sql_exception $e) {
        if($e->getCode() == 1062){
            echo renderPage('warning', 'Peringatan', 'Foto bukti rujukan PSM untuk pasien ini kemungkinan sudah pernah diambil sebelumnya.', $urlKembali);
        } else {
            echo renderPage('error', 'Gagal', 'Terjadi kesalahan sistem. Silakan coba beberapa saat lagi.', $urlKembali);
        }
    }
?>
