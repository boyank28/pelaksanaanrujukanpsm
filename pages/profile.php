<?php
    if(strpos($_SERVER['REQUEST_URI'],"pages")){
        exit(header("Location:../index.php"));
    }

    if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
        swal_alert('Sesi Anda telah berakhir. Silakan login kembali.', '?act=Home');
        exit;
    }

    $current_username = $_SESSION['ses_admin_pelaksanaanrujukanpsm'];
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        
        $username = validTeks4($_POST['username'], 50);
        $nama     = validTeks4($_POST['nama_lengkap'], 150);
        $id       = validTeks4($_POST['id_user'], 11);

        // Check if new username is already taken by someone else
        $cek = bukaquery_prepared("SELECT username FROM users WHERE username = ? AND id_user != ?", "si", $username, $id);
        if(mysqli_num_rows($cek) > 0) {
            swal_alert('Username sudah digunakan oleh akun lain! Silakan pilih username lain.', 'back');
            exit;
        }

        if (!empty($_POST['password'])) {
            $password = password_psm_hash(validTeks4($_POST['password'], 50));
            bukaquery_prepared("UPDATE users SET username=?, password=?, nama_lengkap=? WHERE id_user=?", "sssi", $username, $password, $nama, $id);
        } else {
            bukaquery_prepared("UPDATE users SET username=?, nama_lengkap=? WHERE id_user=?", "ssi", $username, $nama, $id);
        }
        
        // Update session
        $_SESSION['ses_admin_pelaksanaanrujukanpsm'] = $username;
        $_SESSION['nama_lengkap_psm'] = $nama;

        swal_alert('Profil berhasil diperbarui!', '?act=Profile');
        exit;
    }

    // Fetch user details
    $res = bukaquery_prepared("SELECT * FROM users WHERE username=?", "s", $current_username);
    $data_user = mysqli_fetch_array($res);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profil Saya</title>
    <script src="js/jquery.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #334155; }
        .page-title { color: #0f766e; font-weight: 800; font-size: 24px; margin-bottom: 20px; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); background: white; padding: 25px; margin-bottom: 20px;}
        .form-control { border-radius: 8px; border: 1.5px solid #cbd5e1; font-family: inherit; }
        .form-control:focus { border-color: #0f766e; box-shadow: 0 0 0 0.2rem rgba(15, 118, 110, 0.15); }
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container py-4" style="max-width: 800px;">
        <h2 class="page-title"><i class="bi bi-person-fill-gear me-2"></i> Profil Saya</h2>
        
        <div class="card-custom">
            <form method="POST" action="">
                <?= csrf_input() ?>
                <input type="hidden" name="id_user" value="<?= e($data_user['id_user']) ?>">
                
                <div class="mb-3">
                    <label class="fw-bold mb-2">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" value="<?= e($data_user['nama_lengkap']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="fw-bold mb-2">Jabatan (Hanya Admin yang dapat mengubah)</label>
                    <input type="text" class="form-control" value="<?= e($data_user['jabatan']) ?>" readonly style="background:#f1f5f9;">
                </div>
                
                <div class="mb-3">
                    <label class="fw-bold mb-2">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= e($data_user['username']) ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="fw-bold mb-2">Password <small class="text-muted">(Kosongkan jika tidak ingin mengubah password)</small></label>
                    <input type="password" name="password" class="form-control">
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" style="background:#0f766e; border:none; border-radius:8px;">
                        <i class="bi bi-floppy-fill me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
