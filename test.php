<?php
require 'conf/conf.php';
$r = mysqli_query($konektor, "SELECT status_lanjut FROM reg_periksa WHERE no_rkm_medis='455327' ORDER BY tgl_registrasi DESC LIMIT 1");
var_dump(mysqli_fetch_assoc($r));
