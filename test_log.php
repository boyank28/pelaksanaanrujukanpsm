<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['ses_admin_pelaksanaanrujukanpsm'] = 'admin';
$_SESSION['role_psm'] = 'Admin';
$_GET['act'] = 'LogAktivitas';

require_once 'conf/conf.php';
require_once 'pages/log_aktivitas.php';
