<?php
require 'conf/conf.php';
$r = bukaquery2('SHOW COLUMNS FROM master_psm');
while($row = mysqli_fetch_assoc($r)) {
    echo $row['Field'] . ' ';
}
echo PHP_EOL;
