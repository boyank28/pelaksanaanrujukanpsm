<?php
if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) || (empty($_SESSION['permissions_psm']['can_backup']) && !(empty($_SESSION['permissions_psm']) && $_SESSION['role_psm'] == 'Admin'))) {
    echo "<META HTTP-EQUIV='Refresh' Content='0; URL=?act=Home'>";
    exit;
}

global $db_hostname, $db_username, $db_password, $db_name, $konektor;

$message = "";

// Proses Backup
if (isset($_POST['backup'])) {
    verify_csrf();
    bukakoneksi();
    $tables = array();
    $result = mysqli_query($konektor, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }
    
    $sqlScript = "";
    foreach ($tables as $table) {
        $result = mysqli_query($konektor, "SHOW CREATE TABLE $table");
        $row = mysqli_fetch_row($result);
        $sqlScript .= "\n\n" . $row[1] . ";\n\n";
        
        $result = mysqli_query($konektor, "SELECT * FROM $table");
        $columnCount = mysqli_num_fields($result);
        
        for ($i = 0; $i < $columnCount; $i ++) {
            while ($row = mysqli_fetch_row($result)) {
                $sqlScript .= "INSERT INTO $table VALUES(";
                for ($j = 0; $j < $columnCount; $j ++) {
                    $row[$j] = $row[$j];
                    if (isset($row[$j])) {
                        $sqlScript .= '"' . mysqli_real_escape_string($konektor, $row[$j]) . '"';
                    } else {
                        $sqlScript .= '""';
                    }
                    if ($j < ($columnCount - 1)) {
                        $sqlScript .= ',';
                    }
                }
                $sqlScript .= ");\n";
            }
        }
        $sqlScript .= "\n"; 
    }
    
    if(!empty($sqlScript)) {
        $backup_file_name = $db_name . '_backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Simpan juga ke server untuk cadangan otomatis (auto-backup)
        if(!is_dir('backup')) mkdir('backup', 0777, true);
        file_put_contents('backup/'.$backup_file_name, $sqlScript);
        
        header('Content-Type: application/x-sql');
        header('Content-Disposition: attachment; filename=' . $backup_file_name);
        catat_log("Melakukan Backup Database");
        echo $sqlScript;
        exit;
    }
}

// Proses Restore
if (isset($_POST['restore'])) {
    verify_csrf();
    bukakoneksi();
    $fileName = isset($_FILES["backup_file"]["name"]) ? $_FILES["backup_file"]["name"] : '';
    $fileSize = isset($_FILES["backup_file"]["size"]) ? (int)$_FILES["backup_file"]["size"] : 0;
    if ($_FILES["backup_file"]["error"] == UPLOAD_ERR_OK && strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) === 'sql' && $fileSize <= 10485760) {
        $sqlFile = $_FILES["backup_file"]["tmp_name"];
        $sql = file_get_contents($sqlFile);
        $queries = explode(';', $sql);
        
        mysqli_query($konektor, "SET FOREIGN_KEY_CHECKS=0");
        $success = 0;
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                if(mysqli_query($konektor, $query)) {
                    $success++;
                }
            }
        }
        mysqli_query($konektor, "SET FOREIGN_KEY_CHECKS=1");
        catat_log("Melakukan Restore Database ($success query dieksekusi)");
        $message = "<div class='alert alert-success mt-3'>Restore berhasil! $success query dieksekusi.</div>";
    } else {
        $message = "<div class='alert alert-danger mt-3'>Gagal mengunggah file backup. Pastikan file berekstensi .sql dan maksimal 10 MB.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Backup & Restore</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); background: white; padding: 25px; margin-bottom: 20px;}
        .icon-large { font-size: 3rem; color: #0f766e; margin-bottom: 15px;}
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container py-4">
        <h2 class="mb-4" style="color: #0f766e; font-weight: 800;"><i class="bi bi-cloud-arrow-down-fill me-2"></i> Backup & Restore Database</h2>
        
        <?= $message ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card-custom text-center h-100">
                    <i class="bi bi-download icon-large"></i>
                    <h4 class="fw-bold text-dark">Backup Otomatis & Download</h4>
                    <p class="text-muted" style="font-size:14px;">Klik tombol ini untuk mengunduh seluruh data PSM ke komputer Anda dalam format <code>.sql</code>. Data juga akan <b>otomatis tercadang (auto-backup)</b> di dalam folder server.</p>
                    <form method="POST" action="">
                        <?= csrf_input() ?>
                        <button type="submit" name="backup" class="btn btn-primary w-100 mt-3" style="background:#0f766e; border:none; border-radius:8px; padding:12px; font-weight:bold;" onclick="setTimeout(() => Swal.fire('Berhasil!', 'Backup berhasil! File sedang diunduh dan tersimpan aman di server.', 'success'), 1500);">
                            <i class="bi bi-save-fill me-2"></i> Buat & Download Backup
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card-custom text-center h-100">
                    <i class="bi bi-upload icon-large text-warning"></i>
                    <h4 class="fw-bold text-dark">Restore Database</h4>
                    <p class="text-muted" style="font-size:14px;">Unggah file <code>.sql</code> hasil backup sebelumnya untuk memulihkan data. <strong class="text-danger">Peringatan: Ini akan menimpa data yang ada!</strong></p>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <?= csrf_input() ?>
                        <div class="mb-3 text-start">
                            <input class="form-control" type="file" name="backup_file" accept=".sql" required style="border-radius:8px;">
                        </div>
                        <button type="submit" name="restore" class="btn btn-warning w-100" style="border:none; border-radius:8px; padding:12px; font-weight:bold; color:#1e293b;" onclick="return confirm('Apakah Anda yakin ingin me-restore database? Proses ini TIDAK DAPAT DIBATALKAN!');">
                            <i class="bi bi-arrow-clockwise me-2"></i> Pulihkan Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
