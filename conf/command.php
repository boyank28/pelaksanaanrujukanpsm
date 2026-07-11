<?php
    if(strpos($_SERVER['REQUEST_URI'],"conf")){
        exit(header("Location:../index.php"));
    }
    
    function title(){
            $judul ="SIMKES Khanza --)(*!!@#$%";
            $judul = preg_replace("/[^A-Za-z0-9_\-\.\/,|]/"," ",$judul);
            $judul = str_replace(array('.','-','/',',')," ",$judul);
            $judul = trim($judul);
            echo "$judul";	
    }

    function cekSessiAdmin() {
        if (isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
            return true;
        } else {
            return false;
        }
    }


    function cekUser() {
        if (isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
            return true;
        } else {
            return false;
        }
    }

    function adminAktif() {
        if (cekSessiAdmin()) {
            return $_SESSION['ses_admin_pelaksanaanrujukanpsm'];
        }
    }

    function isGuest() {
        if (cekSessiAdmin()) {
            return false;
        } else {
            return true;
        }
    }	

    function formProtek() {
        $aksi=isset($_GET['act'])?$_GET['act']:NULL;
        if (!cekUser()) {
            $form = array ('Kamera','Kamera2','Dashboard','MasterPSM','Users','Setting','SettingTTD','SettingFee','CetakLaporan', 'Rekapitulasi', 'Amprahan', 'BackupRestore', 'LihatFoto', 'HapusRujukan', 'EditRujukan', 'Profile', 'LogAktivitas');
            foreach ($form as $page) {
                if ($aksi==$page) {
                    echo "<META HTTP-EQUIV = 'Refresh' Content = '0; URL = ?act=Home'>";
                    exit;
                    break;
                }
            }
        }
    }

    function actionPages() {
        $aksi=isset($_REQUEST['act'])?$_REQUEST['act']:NULL;
        formProtek();
        switch ($aksi) {
              case 'Home'                   : include_once('pages/index.php'); break;
              case 'Kamera'                 : include_once('pages/kamera.php'); break;
              case 'Kamera2'                : include_once('pages/kamera2.php'); break;
              case 'Dashboard'              : include_once('pages/dashboard.php'); break;
              case 'Rekapitulasi'           : include_once('pages/laporan_rekapitulasi.php'); break;
              case 'Amprahan'               : include_once('pages/amprahan.php'); break;
              case 'MasterPSM'              : include_once('pages/master_psm.php'); break;
              case 'Users'                  : include_once('pages/users.php'); break;
              case 'Setting'                : include_once('pages/setting.php'); break;
              case 'SettingTTD'             : include_once('pages/setting_ttd.php'); break;
              case 'SettingFee'             : include_once('pages/setting_fee.php'); break;
              case 'BackupRestore'          : include_once('pages/backup_restore.php'); break;
              case 'CetakLaporan'           : include_once('pages/cetak_laporan.php'); break;
              case 'LihatFoto'              : include_once('pages/lihat_foto.php'); break;
              case 'HapusRujukan'           : include_once('pages/hapus_rujukan.php'); break;
              case 'EditRujukan'            : include_once('pages/edit_rujukan.php'); break;
              case 'Profile'                : include_once('pages/profile.php'); break;
              case 'LogAktivitas'           : include_once('pages/log_aktivitas.php'); break;
              default                       : include_once('pages/index.php');
        }
    }
?>
