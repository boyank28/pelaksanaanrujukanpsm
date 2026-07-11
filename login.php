<?php
    include_once "conf/command.php";
    require_once('conf/conf.php');
    
    $url = "index.php?act=Home";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user = validTeks4($_POST['usere'], 50);
        $plainPass = validTeks4($_POST['passwordte'], 50);
    }

    if (isset($user) && isset($plainPass)) {
        $query = "SELECT * FROM users WHERE username=?";
        $hasil = bukaquery_prepared($query, "s", $user);
        if ($hasil && mysqli_num_rows($hasil) > 0) {
            $data = mysqli_fetch_array($hasil);
            if (!password_psm_verify($plainPass, $data['password'])) {
                swal_alert('Login Gagal! Username atau Password salah.', 'index.php?act=Home');
                exit;
            }
            if ($data['status'] === 'Nonaktif') {
                swal_alert('Akun Anda dinonaktifkan. Silakan hubungi Admin.', 'index.php?act=Home');
                exit;
            }
            session_start();
            $_SESSION['ses_admin_pelaksanaanrujukanpsm'] = $data['username'];
            $_SESSION['role_psm'] = $data['role'];
            $_SESSION['nama_lengkap_psm'] = $data['nama_lengkap'];
            $_SESSION['jabatan_psm'] = $data['jabatan'];
            $_SESSION['permissions_psm'] = json_decode($data['permissions'], true) ?: [];
            $_SESSION['last_action'] = time();
            csrf_token();
            if (password_psm_needs_rehash($data['password'])) {
                $newHash = password_psm_hash($plainPass);
                bukaquery_prepared("UPDATE users SET password=? WHERE id_user=?", "si", $newHash, $data['id_user']);
            }
            catat_log("Login ke dalam sistem");
            if ($data['role'] === 'PSM') {
                $url = "index.php?act=Kamera";
            } else {
                $url = "index.php?act=Dashboard";
            }
        } else {
            swal_alert('Login Gagal! Username atau Password salah.', 'index.php?act=Home');
            exit;
        }
    }
    
    header("Location:".$url);
?>
