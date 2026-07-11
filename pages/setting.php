<?php
    if(strpos($_SERVER['REQUEST_URI'],"pages")){
        exit(header("Location:../index.php"));
    }

    if(!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) || $_SESSION['role_psm'] != 'Admin') {
        swal_alert('Anda tidak memiliki hak akses!', '?act=Dashboard');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $nama_instansi = validTeks4($_POST['nama_instansi'], 100);
        bukaquery_prepared("UPDATE setting_aplikasi SET nama_instansi=? WHERE id=1", "s", $nama_instansi);
        swal_alert('Pengaturan berhasil disimpan!', '?act=Setting');
        exit;
    }

    $setting = null;
    $res = bukaquery2("SELECT * FROM setting_aplikasi WHERE id=1");
    if($res) $setting = mysqli_fetch_array($res);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setting Aplikasi</title>
    <script src="js/jquery.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container">
        <h4 class="mb-4" style="color: #0f766e; font-weight: 700;">Setting Aplikasi</h4>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card p-4">
                    <form method="POST" action="?act=Setting">
                        <?= csrf_input() ?>
                        <div class="mb-3">
                            <label class="fw-bold text-muted">Nama Rumah Sakit / Instansi</label>
                            <input type="text" name="nama_instansi" class="form-control" value="<?= $setting ? e($setting['nama_instansi']) : '' ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary" style="background:#0f766e; border:none;">Simpan Pengaturan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
