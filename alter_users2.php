<?php
require 'conf/conf.php';
$q = "ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'Aktif'";
if(bukaquery2($q)) {
    echo "Success adding status to users";
} else {
    echo "Error: " . mysqli_error($konektor);
}
