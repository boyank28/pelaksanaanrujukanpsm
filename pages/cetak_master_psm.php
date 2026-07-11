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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cetak Data Master PSM</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <style>
        body { padding: 20px; font-family: sans-serif; font-size: 13px; }
        table { width: 100%; border-collapse: collapse !important; margin-bottom: 20px; }
        table, th, td { border: 1px solid #000 !important; padding: 8px !important; }
        th { background-color: #f8f9fa !important; text-align: center; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="text-end mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm">Cetak Sekarang</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm">Tutup</button>
    </div>
    <h4 class="text-center fw-bold mb-4">DATA MASTER PSM KESELURUHAN</h4>
    <table class="table table-bordered">
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
                            <td class='text-center'>".$no++."</td>
                            <td>".e($r['nama_psm'])."</td>
                            <td>".e($r['no_telp'])."</td>
                            <td>".e($r['alamat'])."</td>
                            <td class='text-center'>".e($r['status'])."</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>Tidak ada data</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
