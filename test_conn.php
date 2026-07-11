<?php
$k = @mysqli_connect('localhost', 'root', '', 'psmdb');
if(!$k) {
    echo "ERROR: " . mysqli_connect_error();
} else {
    echo "Connected!";
}
?>
