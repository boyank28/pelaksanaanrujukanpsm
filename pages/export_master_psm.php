<?php
    session_start();
    require_once('../conf/command.php');
    require_once('../conf/conf.php');
    
    if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
        exit("Akses ditolak.");
    }
    
    $keyword = isset($_GET['keyword']) ? validTeks4($_GET['keyword'], 50) : '';
    $whereClause = "";
    if($keyword != "") {
        $whereClause = "WHERE nama_psm LIKE '%$keyword%' OR alamat LIKE '%$keyword%' OR no_telp LIKE '%$keyword%'";
    }
    
    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Data_Master_PSM.xls");
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th, td { font-size: 12pt; font-family: Calibri, sans-serif; border: 1px solid black; padding: 5px; vertical-align: middle; }
        th { font-weight: bold; background-color: #e2e8f0; text-align: center; }
    </style>
</head>
<body>
    <h3 style="text-align: center;">DATA MASTER PSM KESELURUHAN</h3>
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama PSM</th>
                <th>Telepon</th>
                <th>Alamat</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = bukaquery2("SELECT * FROM master_psm $whereClause ORDER BY nama_psm ASC");
            if(mysqli_num_rows($query) > 0) {
                $no = 1;
                while($r = mysqli_fetch_array($query)) {
                    echo "<tr>
                            <td align='center'>".$no++."</td>
                            <td>".e($r['nama_psm'])."</td>
                            <td>".e($r['no_telp'])."</td>
                            <td>".e($r['alamat'])."</td>
                            <td align='center'>".e($r['status'])."</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5' align='center'>Tidak ada data</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
