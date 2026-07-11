<?php
    session_start();
    if (isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
        require_once('conf/conf.php');
        catat_log("Logout dari sistem");
    }
    
    $_SESSION = [];
    session_destroy();
    
    // Redirect to home page
    header("Location:index.php?act=Home");
?>
