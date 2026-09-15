<?php
    if(strpos($_SERVER['REQUEST_URI'],"pages")){
        exit(header("Location:../index.php"));
    }

    if(!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) || $_SESSION['role_psm'] != 'Admin') {
        swal_alert('Anda tidak memiliki hak akses!', '?act=Dashboard');
        exit;
    }

    // Ensure table setting_aplikasi exists and has logo column
    bukaquery2("CREATE TABLE IF NOT EXISTS setting_aplikasi (id INT PRIMARY KEY DEFAULT 1, nama_instansi VARCHAR(100), logo VARCHAR(255))");
    global $konektor;
    try {
        @mysqli_query($konektor, "ALTER TABLE setting_aplikasi ADD COLUMN logo VARCHAR(255) DEFAULT NULL");
    } catch(Exception $e) {}

    $setting = null;
    $res = bukaquery2("SELECT * FROM setting_aplikasi WHERE id=1");
    if($res) $setting = mysqli_fetch_array($res);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        
        // Handle Delete Logo action
        if (isset($_POST['action']) && $_POST['action'] === 'delete_logo') {
            if ($setting && !empty($setting['logo'])) {
                $old_file = __DIR__ . '/../images/' . $setting['logo'];
                if (file_exists($old_file)) {
                    @unlink($old_file);
                }
                bukaquery_prepared("UPDATE setting_aplikasi SET logo=NULL WHERE id=1");
                catat_log("Menghapus logo instansi");
            }
            swal_alert('Logo berhasil dihapus!', '?act=Setting');
            exit;
        }

        $nama_instansi = validTeks4($_POST['nama_instansi'], 100);
        $logo_filename = $setting ? $setting['logo'] : null;

        // Handle File Upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp  = $_FILES['logo']['tmp_name'];
            $file_name = $_FILES['logo']['name'];
            $file_size = $_FILES['logo']['size'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            if (!in_array($ext, $allowed_ext)) {
                swal_alert('Gagal! Format file logo harus berupa gambar (JPG, PNG, GIF, WEBP, SVG).', '?act=Setting');
                exit;
            }

            if ($file_size > 2 * 1024 * 1024) { // 2MB max
                swal_alert('Gagal! Ukuran file logo maksimal 2MB.', '?act=Setting');
                exit;
            }

            $target_dir = __DIR__ . '/../images/';
            if (!file_exists($target_dir)) {
                @mkdir($target_dir, 0777, true);
            }

            $new_logo_name = "logo_" . time() . "." . $ext;
            $target_path = $target_dir . $new_logo_name;

            if (move_uploaded_file($file_tmp, $target_path)) {
                // Delete previous logo file if exists
                if (!empty($setting['logo']) && file_exists($target_dir . $setting['logo'])) {
                    @unlink($target_dir . $setting['logo']);
                }
                $logo_filename = $new_logo_name;
            } else {
                swal_alert('Gagal mengupload logo!', '?act=Setting');
                exit;
            }
        }

        if ($setting) {
            bukaquery_prepared("UPDATE setting_aplikasi SET nama_instansi=?, logo=? WHERE id=1", "ss", $nama_instansi, $logo_filename);
        } else {
            bukaquery_prepared("INSERT INTO setting_aplikasi (id, nama_instansi, logo) VALUES (1, ?, ?)", "ss", $nama_instansi, $logo_filename);
        }

        catat_log("Memperbarui pengaturan aplikasi (Nama: $nama_instansi)");
        swal_alert('Pengaturan aplikasi berhasil disimpan!', '?act=Setting');
        exit;
    }
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
    <div class="container py-4">
        <h4 class="mb-4" style="color: #0f766e; font-weight: 700;"><i class="bi bi-sliders me-2"></i>Setting Aplikasi</h4>
        
        <div class="row">
            <div class="col-md-7">
                <div class="card p-4">
                    <form method="POST" action="?act=Setting" enctype="multipart/form-data">
                        <?= csrf_input() ?>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Nama Rumah Sakit / Instansi</label>
                            <input type="text" name="nama_instansi" class="form-control" value="<?= $setting ? e($setting['nama_instansi']) : '' ?>" required placeholder="Contoh: RSUD Dr. Soetomo">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Logo Instansi / Aplikasi</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <small class="text-muted">Format: PNG, JPG, WEBP, SVG (Maks. 2MB). Disarankan berlatar transparan.</small>
                            
                            <?php if ($setting && !empty($setting['logo']) && file_exists(__DIR__ . '/../images/' . $setting['logo'])): ?>
                                <div class="mt-3 p-3 bg-light rounded d-flex align-items-center justify-content-between border">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="images/<?= e($setting['logo']) ?>" alt="Logo Instansi" style="max-height: 60px; max-width: 120px; object-fit: contain;" class="bg-white p-1 rounded border">
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 12px;">Logo Terpasang</div>
                                            <div class="text-muted" style="font-size: 11px;"><?= e($setting['logo']) ?></div>
                                        </div>
                                    </div>
                                    <button type="submit" name="action" value="delete_logo" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin ingin menghapus logo saat ini?')">
                                        <i class="bi bi-trash me-1"></i>Hapus Logo
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 rounded-pill" style="background:#0f766e; border:none; font-weight: 600;"><i class="bi bi-save me-2"></i>Simpan Pengaturan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
