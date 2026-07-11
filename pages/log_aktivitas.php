<?php
    $is_admin = (isset($_SESSION['role_psm']) && $_SESSION['role_psm'] == 'Admin');
    $can_view_log = $is_admin || !empty($_SESSION['permissions_psm']['can_view_log']);
    if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) || !$can_view_log) {
        echo "<META HTTP-EQUIV = 'Refresh' Content = '0; URL = ?act=Home'>";
        exit;
    }

    // Pagination & Filters
    $tgl_awal = validTanggal(isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : '', date('Y-m-01'));
    $tgl_akhir = validTanggal(isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '', date('Y-m-t'));
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
    
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;

    $where = "WHERE DATE(waktu) BETWEEN ? AND ?";
    $params = [$tgl_awal, $tgl_akhir];
    $types = "ss";

    if($keyword !== '') {
        $where .= " AND (username LIKE ? OR aktivitas LIKE ? OR ip_address LIKE ?)";
        $k = "%$keyword%";
        $params[] = $k;
        $params[] = $k;
        $params[] = $k;
        $types .= "sss";
    }

    $q_count = "SELECT COUNT(*) as total FROM log_aktivitas $where";
    $res_count = bukaquery_prepared($q_count, $types, ...$params);
    $total_data = ($res_count && mysqli_num_rows($res_count) > 0) ? mysqli_fetch_array($res_count)['total'] : 0;
    $total_pages = ceil($total_data / $limit);

    $sql = "SELECT * FROM log_aktivitas $where ORDER BY waktu DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    $types .= "ii";
    $hasil = bukaquery_prepared($sql, $types, ...$params);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Log Aktivitas</title>
    <script src="js/jquery.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .page-title { color: #0f766e; font-weight: 800; font-size: 24px; margin-bottom: 20px; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); background: white; padding: 20px; margin-bottom: 20px;}
        .table-custom th { background: #f1f5f9; color: #0f766e; border-bottom: 2px solid #cbd5e1; }
        .table-custom td { vertical-align: middle; }
        .pagination .page-item.active .page-link { background-color: #0f766e; border-color: #0f766e; color: white; }
        .pagination .page-link { color: #0f766e; box-shadow: none; }
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container-fluid py-4" style="max-width: 1000px;">
        <h2 class="page-title"><i class="bi bi-journal-text me-2"></i> Log Aktivitas</h2>
        
        <div class="card-custom">
            <form method="GET" action="index.php" class="row g-2 mb-4">
                <input type="hidden" name="act" value="LogAktivitas">
                <div class="col-md-3">
                    <input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" name="keyword" id="searchInput" class="form-control" placeholder="Cari username, aktivitas, IP..." value="<?= e($keyword) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100" style="background:#0f766e; border:none;"><i class="bi bi-search me-1"></i>Cari</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-custom" style="font-size: 13px;">
                    <thead class="text-center align-middle">
                        <tr>
                            <th width="20%">WAKTU</th>
                            <th width="20%">PENGGUNA</th>
                            <th width="60%">AKTIVITAS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($hasil && mysqli_num_rows($hasil) > 0) {
                            while ($row = mysqli_fetch_array($hasil)) {
                                echo "<tr>
                                        <td class='text-center'>".date('d/m/Y H:i', strtotime($row['waktu']))."</td>
                                        <td>
                                            <span class='fw-bold' style='color:#0f766e;'>".htmlspecialchars($row['username'])."</span><br>
                                            <small class='text-muted'>IP: ".htmlspecialchars(isset($row['ip_address']) ? $row['ip_address'] : 'Tidak tercatat')."</small>
                                        </td>
                                        <td>".htmlspecialchars($row['aktivitas'])."</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='text-center text-muted'>Belum ada aktivitas terekam.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php
                        $qs = "&tgl_awal=$tgl_awal&tgl_akhir=$tgl_akhir";
                        if($keyword != '') $qs .= "&keyword=".urlencode($keyword);
                    ?>
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?act=LogAktivitas<?=$qs?>&page=<?=$page-1?>">Previous</a>
                    </li>
                    <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        for($i=$start_page; $i<=$end_page; $i++): 
                    ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?act=LogAktivitas<?=$qs?>&page=<?=$i?>"><?=$i?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?act=LogAktivitas<?=$qs?>&page=<?=$page+1?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            <div class="text-center mt-2 text-muted" style="font-size:12px;">Menampilkan halaman <?=$page?> dari <?= max(1, $total_pages) ?> (Total <?=$total_data?> Data)</div>
        </div>
    </div>
    
    <script>
        $(document).ready(function(){
            // Live search for current page data
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $(".table-custom tbody tr").filter(function() {
                    if($(this).find("td").length > 1) { // skip "Belum ada aktivitas" row
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    }
                });
            });
        });
    </script>
</body>
</html>
