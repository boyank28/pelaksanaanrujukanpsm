<?php
if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
    echo "<META HTTP-EQUIV='Refresh' Content='0; URL=?act=Home'>";
    exit;
}

$img = isset($_GET['img']) ? $_GET['img'] : '';
$back_url = safe_redirect_url(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?act=Dashboard');

$uploadDir = realpath(__DIR__ . '/upload');
$filePath = realpath(__DIR__ . '/../' . $img);
$allowedExt = array('jpg', 'jpeg', 'png');
$ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
if (
    empty($img) ||
    !$uploadDir ||
    !$filePath ||
    strpos($filePath, $uploadDir . DIRECTORY_SEPARATOR) !== 0 ||
    !in_array($ext, $allowedExt, true)
) {
    swal_alert('Foto tidak ditemukan!', '?act=Dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Penampil Foto - Rujukan PSM</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }
        .header-bar {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .btn-back {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 600;
        }
        .btn-back:hover {
            background: white;
            color: #0f172a;
        }
        .image-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        .photo-frame {
            max-width: 90%;
            max-height: 80vh;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 4px solid rgba(255,255,255,0.1);
            transition: transform 0.3s;
        }
        .photo-frame:hover {
            transform: scale(1.02);
            border-color: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div class="header-bar">
        <h5 class="m-0 fw-bold"><i class="bi bi-image me-2 text-info"></i>Dokumen Rujukan</h5>
        <div>
            <button onclick="window.close();" class="btn-back"><i class="bi bi-x-circle me-1"></i> Tutup</button>
        </div>
    </div>
    
    <div class="image-container">
        <img src="<?= e($img) ?>" class="photo-frame" alt="Bukti Pelaksanaan Rujukan">
    </div>
</body>
</html>
