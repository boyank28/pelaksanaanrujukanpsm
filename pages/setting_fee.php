<?php
    if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) || (empty($_SESSION['permissions_psm']['can_manage_fee']) && !(empty($_SESSION['permissions_psm']) && $_SESSION['role_psm'] == 'Admin'))) {
        echo "<META HTTP-EQUIV = 'Refresh' Content = '0; URL = ?act=Dashboard'>";
        exit;
    }

    // Buat tabel jika belum ada
    bukaquery2("CREATE TABLE IF NOT EXISTS setting_fee_psm (
        id INT PRIMARY KEY DEFAULT 1,
        fee_ranap_op DOUBLE,
        fee_ranap DOUBLE,
        fee_ralan DOUBLE
    )");
    global $konektor;
    @mysqli_query($konektor, "ALTER TABLE setting_fee_psm ADD COLUMN fee_ralan_op DOUBLE AFTER fee_ralan");

    // Insert default jika kosong
    $cek = bukaquery2("SELECT * FROM setting_fee_psm WHERE id=1");
    if(mysqli_num_rows($cek) == 0) {
        bukaquery2("INSERT INTO setting_fee_psm (id, fee_ranap_op, fee_ranap, fee_ralan) VALUES (1, 75000, 50000, 25000)");
    }

    if(isset($_POST['btnSimpan'])) {
        bukaquery_prepared("UPDATE setting_fee_psm SET fee_ranap_op=?, fee_ranap=?, fee_ralan=?, fee_ralan_op=? WHERE id=1", 
        "dddd", $_POST['fee_ranap_op'], $_POST['fee_ranap'], $_POST['fee_ralan'], $_POST['fee_ralan_op']);
        swal_alert('Data Master Fee Berhasil Disimpan!', '?act=SettingFee');
        exit;
    }

    $fee = mysqli_fetch_array(bukaquery2("SELECT * FROM setting_fee_psm WHERE id=1"));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Master Fee PSM</title>
    <script src="js/jquery.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .page-title { color: #0f766e; font-weight: 800; font-size: 24px; margin-bottom: 20px; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); background: white; padding: 30px; max-width: 600px; margin: auto; }
        .form-label { font-size: 13px; font-weight: 600; color: #475569; }
        .form-control { border-radius: 8px; font-size: 14px; }
        .form-control:focus { border-color: #0f766e; box-shadow: 0 0 0 0.25rem rgba(15, 118, 110, 0.25); }
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container-fluid py-4">
        <h2 class="page-title text-center"><i class="bi bi-cash-coin me-2"></i> Master Fee PSM</h2>
        
        <div class="card-custom mt-4">
            <div class="alert alert-info" style="border-radius: 10px; font-size: 14px;">
                <i class="bi bi-info-circle-fill me-2"></i> Atur nominal fee untuk PSM berdasarkan jenis perawatan pasien.
            </div>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label">Fee Rawat Inap + Operasi (RANAP-OP)</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: #f1f5f9; border-radius: 8px 0 0 8px;">Rp</span>
                        <input type="number" name="fee_ranap_op" class="form-control" value="<?= htmlspecialchars($fee['fee_ranap_op']) ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Fee Rawat Inap (RANAP)</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: #f1f5f9; border-radius: 8px 0 0 8px;">Rp</span>
                        <input type="number" name="fee_ranap" class="form-control" value="<?= htmlspecialchars($fee['fee_ranap']) ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Fee Rawat Jalan (RALAN)</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: #f1f5f9; border-radius: 8px 0 0 8px;">Rp</span>
                        <input type="number" name="fee_ralan" class="form-control" value="<?= htmlspecialchars($fee['fee_ralan']) ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Fee Rawat Jalan + Operasi (RALAN-OP)</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: #f1f5f9; border-radius: 8px 0 0 8px;">Rp</span>
                        <input type="number" name="fee_ralan_op" class="form-control" value="<?= isset($fee['fee_ralan_op']) ? htmlspecialchars($fee['fee_ralan_op']) : '125000' ?>" required>
                    </div>
                </div>

                <hr>
                <div class="d-grid mt-4">
                    <button type="submit" name="btnSimpan" class="btn btn-primary btn-lg" style="background:#0f766e; border:none; border-radius:8px; font-weight:600;"><i class="bi bi-save-fill me-2"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
