<?php
    if (!isset($_SESSION['ses_admin_pelaksanaanrujukanpsm'])) {
        echo "<META HTTP-EQUIV = 'Refresh' Content = '0; URL = ?act=Home'>";
        exit;
    }

    // Buat tabel jika belum ada
    bukaquery2("CREATE TABLE IF NOT EXISTS setting_ttd_amprahan (
        id INT PRIMARY KEY DEFAULT 1,
        mengetahui_nama VARCHAR(100),
        mengetahui_jabatan VARCHAR(100),
        dicek_nama VARCHAR(100),
        dicek_jabatan VARCHAR(100),
        menyetujui_nama VARCHAR(100),
        menyetujui_jabatan VARCHAR(100),
        dibuatkan_nama VARCHAR(100),
        dibuatkan_jabatan VARCHAR(100)
    )");

    // Pastikan kolom dibuatkan ada (untuk update dari versi sebelumnya)
    global $konektor;
    try {
        @mysqli_query($konektor, "ALTER TABLE setting_ttd_amprahan ADD COLUMN dibuatkan_nama VARCHAR(100) AFTER menyetujui_jabatan");
        @mysqli_query($konektor, "ALTER TABLE setting_ttd_amprahan ADD COLUMN dibuatkan_jabatan VARCHAR(100) AFTER dibuatkan_nama");
    } catch (Exception $e) {}

    // Insert default jika kosong
    $cek = bukaquery2("SELECT * FROM setting_ttd_amprahan WHERE id=1");
    if(mysqli_num_rows($cek) == 0) {
        bukaquery2("INSERT INTO setting_ttd_amprahan (id, mengetahui_nama, mengetahui_jabatan, dicek_nama, dicek_jabatan, menyetujui_nama, menyetujui_jabatan, dibuatkan_nama, dibuatkan_jabatan) 
        VALUES (1, 'Rhofiah', 'Pjs. Kabag. Marketing', 'Wulan Ria Fatmawati', 'Ka.Unit Akutansi & Keuangan', 'dr. Evameinonda, MMRS.,MQM.,FISQua', 'Direktur', '-', 'Staf Marketing')");
    }

    if(isset($_POST['btnSimpan'])) {
        bukaquery_prepared("UPDATE setting_ttd_amprahan SET mengetahui_nama=?, mengetahui_jabatan=?, dicek_nama=?, dicek_jabatan=?, menyetujui_nama=?, menyetujui_jabatan=?, dibuatkan_nama=?, dibuatkan_jabatan=? WHERE id=1", 
        "ssssssss", $_POST['mengetahui_nama'], $_POST['mengetahui_jabatan'], $_POST['dicek_nama'], $_POST['dicek_jabatan'], $_POST['menyetujui_nama'], $_POST['menyetujui_jabatan'], $_POST['dibuatkan_nama'], $_POST['dibuatkan_jabatan']);
        swal_alert('Data Master Tanda Tangan Berhasil Disimpan!', '?act=SettingTTD');
        exit;
    }

    $ttd = mysqli_fetch_array(bukaquery2("SELECT * FROM setting_ttd_amprahan WHERE id=1"));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Master Tanda Tangan Amprahan</title>
    <script src="js/jquery.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .page-title { color: #0f766e; font-weight: 800; font-size: 24px; margin-bottom: 20px; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); background: white; padding: 30px; }
        .form-label { font-size: 13px; font-weight: 600; color: #475569; }
        .form-control { border-radius: 8px; font-size: 14px; }
        .form-control:focus { border-color: #0f766e; box-shadow: 0 0 0 0.25rem rgba(15, 118, 110, 0.25); }
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container-fluid py-4" style="max-width: 900px;">
        <h2 class="page-title"><i class="bi bi-pen-fill me-2"></i> Master Tanda Tangan Amprahan</h2>
        
        <div class="card-custom">
            <div class="alert alert-info" style="border-radius: 10px; font-size: 14px;">
                <i class="bi bi-info-circle-fill me-2"></i> Atur nama dan jabatan pejabat yang berwenang untuk menandatangani dokumen Amprahan Biaya Fee PSM.
            </div>
            
            <form method="POST" action="">
                <h5 class="fw-bold mb-3 mt-4" style="color: #334155;"><i class="bi bi-person-plus-fill text-info me-2"></i>Dibuatkan Oleh</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama (Pilih dari Pengguna)</label>
                        <select name="dibuatkan_nama" class="form-control" required onchange="document.getElementById('dibuatkan_jabatan').value = this.options[this.selectedIndex].getAttribute('data-jabatan');">
                            <option value="">-- Pilih Pengguna --</option>
                            <?php 
                                $usr = bukaquery2("SELECT * FROM users ORDER BY nama_lengkap ASC");
                                while($u = mysqli_fetch_array($usr)) {
                                    $sel = (isset($ttd['dibuatkan_nama']) && $ttd['dibuatkan_nama'] == $u['nama_lengkap']) ? "selected" : "";
                                    echo "<option value='".htmlspecialchars($u['nama_lengkap'])."' data-jabatan='".htmlspecialchars($u['jabatan'])."' $sel>".htmlspecialchars($u['nama_lengkap'])."</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="dibuatkan_jabatan" id="dibuatkan_jabatan" class="form-control" value="<?= htmlspecialchars(isset($ttd['dibuatkan_jabatan']) ? $ttd['dibuatkan_jabatan'] : '') ?>" required>
                    </div>
                </div>

                <h5 class="fw-bold mb-3 mt-4" style="color: #334155;"><i class="bi bi-person-check-fill text-primary me-2"></i>Mengetahui</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama (Pilih dari Pengguna)</label>
                        <select name="mengetahui_nama" class="form-control" required onchange="document.getElementById('mengetahui_jabatan').value = this.options[this.selectedIndex].getAttribute('data-jabatan');">
                            <option value="">-- Pilih Pengguna --</option>
                            <?php 
                                mysqli_data_seek($usr, 0); // Reset pointer
                                while($u = mysqli_fetch_array($usr)) {
                                    $sel = (isset($ttd['mengetahui_nama']) && $ttd['mengetahui_nama'] == $u['nama_lengkap']) ? "selected" : "";
                                    echo "<option value='".htmlspecialchars($u['nama_lengkap'])."' data-jabatan='".htmlspecialchars($u['jabatan'])."' $sel>".htmlspecialchars($u['nama_lengkap'])."</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="mengetahui_jabatan" id="mengetahui_jabatan" class="form-control" value="<?= htmlspecialchars($ttd['mengetahui_jabatan']) ?>" required>
                    </div>
                </div>

                <h5 class="fw-bold mb-3 mt-4" style="color: #334155;"><i class="bi bi-person-lines-fill text-warning me-2"></i>Dicek Oleh</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama (Pilih dari Pengguna)</label>
                        <select name="dicek_nama" class="form-control" required onchange="document.getElementById('dicek_jabatan').value = this.options[this.selectedIndex].getAttribute('data-jabatan');">
                            <option value="">-- Pilih Pengguna --</option>
                            <?php 
                                mysqli_data_seek($usr, 0); // Reset pointer
                                while($u = mysqli_fetch_array($usr)) {
                                    $sel = (isset($ttd['dicek_nama']) && $ttd['dicek_nama'] == $u['nama_lengkap']) ? "selected" : "";
                                    echo "<option value='".htmlspecialchars($u['nama_lengkap'])."' data-jabatan='".htmlspecialchars($u['jabatan'])."' $sel>".htmlspecialchars($u['nama_lengkap'])."</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="dicek_jabatan" id="dicek_jabatan" class="form-control" value="<?= htmlspecialchars($ttd['dicek_jabatan']) ?>" required>
                    </div>
                </div>

                <h5 class="fw-bold mb-3 mt-4" style="color: #334155;"><i class="bi bi-person-badge-fill text-success me-2"></i>Menyetujui</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Nama (Pilih dari Pengguna)</label>
                        <select name="menyetujui_nama" class="form-control" required onchange="document.getElementById('menyetujui_jabatan').value = this.options[this.selectedIndex].getAttribute('data-jabatan');">
                            <option value="">-- Pilih Pengguna --</option>
                            <?php 
                                mysqli_data_seek($usr, 0); // Reset pointer
                                while($u = mysqli_fetch_array($usr)) {
                                    $sel = (isset($ttd['menyetujui_nama']) && $ttd['menyetujui_nama'] == $u['nama_lengkap']) ? "selected" : "";
                                    echo "<option value='".htmlspecialchars($u['nama_lengkap'])."' data-jabatan='".htmlspecialchars($u['jabatan'])."' $sel>".htmlspecialchars($u['nama_lengkap'])."</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="menyetujui_jabatan" id="menyetujui_jabatan" class="form-control" value="<?= htmlspecialchars($ttd['menyetujui_jabatan']) ?>" required>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" name="btnSimpan" class="btn btn-primary" style="background:#0f766e; border:none; padding:10px 25px; border-radius:8px; font-weight:600;"><i class="bi bi-save-fill me-2"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
