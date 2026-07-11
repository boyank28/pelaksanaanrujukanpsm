<?php
    if(strpos($_SERVER['REQUEST_URI'],"pages")){
        exit(header("Location:../index.php"));
    }

    $aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $nama_psm = validTeks4($_POST['nama_psm'], 100);
        $no_telp  = validTeks4($_POST['no_telp'], 20);
        $alamat   = validTeks4($_POST['alamat'], 500);
        $status   = validTeks4($_POST['status'], 20);
        
        $foto_name = "";
        if(isset($_POST['foto_base64']) && !empty($_POST['foto_base64'])){
            $img = $_POST['foto_base64'];
            if (strpos($img, 'data:image/jpeg;base64,') === 0 || strpos($img, 'data:image/png;base64,') === 0) {
                $img = preg_replace('/^data:image\/(jpeg|png);base64,/', '', $img);
                $img = str_replace(' ', '+', $img);
                $data = base64_decode($img);
                
                // MIME check for base64 decode
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->buffer($data);
                if ($mime == 'image/jpeg' || $mime == 'image/png') {
                    $ext = ($mime == 'image/png') ? '.png' : '.jpg';
                    $foto_name = "psm_".time().$ext;
                    if(!is_dir('foto_psm')) mkdir('foto_psm', 0777, true);
                    file_put_contents('foto_psm/'.$foto_name, $data);
                } else {
                    swal_alert('Gagal! Tipe file tidak didukung.', '?act=MasterPSM'); exit;
                }
            }
        } elseif(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){
            $tmp_name = $_FILES['foto']['tmp_name'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmp_name);
            if ($mime == 'image/jpeg' || $mime == 'image/png') {
                $ext = ($mime == 'image/png') ? '.png' : '.jpg';
                $foto_name = "psm_".time().$ext;
                if(!is_dir('foto_psm')) mkdir('foto_psm', 0777, true);
                move_uploaded_file($tmp_name, 'foto_psm/'.$foto_name);
            } else {
                swal_alert('Gagal! Tipe file upload tidak didukung (harus JPG/PNG).', '?act=MasterPSM'); exit;
            }
        }
        
        if ($aksi == 'simpan') {
            $cek = bukaquery_prepared("SELECT id_psm FROM master_psm WHERE nama_psm=?", "s", $nama_psm);
            if(mysqli_num_rows($cek) > 0) {
                swal_alert("Gagal! Nama PSM \"".$nama_psm."\" sudah terdaftar.", "?act=MasterPSM"); exit;
            }
            bukaquery_prepared("INSERT INTO master_psm (nama_psm, no_telp, alamat, status, foto) VALUES (?, ?, ?, ?, ?)", "sssss", $nama_psm, $no_telp, $alamat, $status, $foto_name);
        } elseif ($aksi == 'edit') {
            $id = validTeks4($_POST['id_psm'], 11);
            $cek = bukaquery_prepared("SELECT id_psm FROM master_psm WHERE nama_psm=? AND id_psm!=?", "si", $nama_psm, $id);
            if(mysqli_num_rows($cek) > 0) {
                swal_alert("Gagal! Nama PSM \"".$nama_psm."\" sudah digunakan oleh data lain.", "?act=MasterPSM&aksi=form_edit&id=".$id); exit;
            }
            if($foto_name != "") {
                $q_old = bukaquery_prepared("SELECT foto FROM master_psm WHERE id_psm=?", "i", $id);
                if($r_old = mysqli_fetch_array($q_old)) {
                    $abs_old = __DIR__ . '/../foto_psm/' . $r_old['foto'];
                    if($r_old['foto'] != "" && file_exists($abs_old)) {
                        @unlink($abs_old);
                    }
                }
                bukaquery_prepared("UPDATE master_psm SET nama_psm=?, no_telp=?, alamat=?, status=?, foto=? WHERE id_psm=?", "sssssi", $nama_psm, $no_telp, $alamat, $status, $foto_name, $id);
            } else {
                bukaquery_prepared("UPDATE master_psm SET nama_psm=?, no_telp=?, alamat=?, status=? WHERE id_psm=?", "ssssi", $nama_psm, $no_telp, $alamat, $status, $id);
            }
        }
        header("Location: ?act=MasterPSM");
        exit;
    }

    if ($aksi == 'hapus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $id = validTeks4($_POST['id'], 11);
        
        $q_foto = bukaquery_prepared("SELECT foto FROM master_psm WHERE id_psm=?", "i", $id);
        if($d_foto = mysqli_fetch_array($q_foto)) {
            $img = $d_foto['foto'];
            $abs_img = __DIR__ . '/../foto_psm/' . $img;
            if(!empty($img) && file_exists($abs_img)) {
                @unlink($abs_img);
            }
        }
        
        bukaquery_prepared("DELETE FROM master_psm WHERE id_psm=?", "i", $id);
        header("Location: ?act=MasterPSM");
        exit;
    }

    $data_edit = null;
    if ($aksi == 'form_edit') {
        $id = validTeks4($_GET['id'], 11);
        $res = bukaquery_prepared("SELECT * FROM master_psm WHERE id_psm=?", "i", $id);
        if($res) $data_edit = mysqli_fetch_array($res);
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Master PSM</title>
    <script src="js/jquery.min.js"></script>
    <script src="js/webcam.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
        }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .table th { background: #f1f5f9; color: #0f766e; }
        .form-control { font-size: 13px; }
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container-fluid mb-5 pb-4 px-4">
        <div class="d-flex align-items-center mb-4 mt-2">
            <div style="background: rgba(15, 118, 110, 0.1); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                <i class="bi bi-person-badge-fill" style="color: #0f766e; font-size: 20px;"></i>
            </div>
            <h4 class="mb-0" style="color: #1e293b; font-weight: 800; letter-spacing: -0.5px;">Data Master PSM</h4>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-12">
                <div class="card h-100" style="border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
                        <h6 class="fw-bold mb-0" style="color: #334155;"><i class="bi <?= $data_edit ? 'bi-pencil-square text-warning' : 'bi-person-plus-fill text-primary' ?> me-2"></i><?= $data_edit ? 'Edit Data PSM' : 'Tambah PSM Baru' ?></h6>
                    </div>
                    <div class="card-body px-4 pb-4 pt-3">
                        <form method="POST" action="?act=MasterPSM&aksi=<?= $data_edit ? 'edit' : 'simpan' ?>" enctype="multipart/form-data">
                            <?= csrf_input() ?>
                            <?php if($data_edit): ?>
                                <input type="hidden" name="id_psm" value="<?= e($data_edit['id_psm']) ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Nama PSM</label>
                                <input type="text" name="nama_psm" class="form-control" value="<?= $data_edit ? e($data_edit['nama_psm']) : '' ?>" placeholder="Masukkan nama lengkap PSM" required style="border-radius: 8px;">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">No. Telepon</label>
                                <input type="text" name="no_telp" class="form-control" value="<?= $data_edit ? e($data_edit['no_telp']) : '' ?>" placeholder="Contoh: 08123456789" style="border-radius: 8px;">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Alamat</label>
                                <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap domisili" style="border-radius: 8px; resize: none;"><?= $data_edit ? e($data_edit['alamat']) : '' ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Status</label>
                                <select name="status" class="form-select" style="border-radius: 8px;">
                                    <option value="Aktif" <?= ($data_edit && $data_edit['status']=='Aktif') ? 'selected' : '' ?>>Aktif</option>
                                    <option value="Nonaktif" <?= ($data_edit && $data_edit['status']=='Nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Foto PSM</label>
                                <?php if($data_edit && $data_edit['foto']): ?>
                                    <div class="mb-2 d-flex align-items-center gap-3 p-2 border rounded-3 bg-light">
                                        <img src="foto_psm/<?= e($data_edit['foto']) ?>" width="40" height="40" style="border-radius:50%; object-fit:cover; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                                        <span style="font-size: 12px;" class="text-secondary">Foto saat ini</span>
                                    </div>
                                <?php endif; ?>
                                <ul class="nav nav-pills nav-fill mb-3" id="fotoTabs" role="tablist" style="background: #f1f5f9; border-radius: 8px; padding: 4px;">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active py-1" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab" onclick="stopWebcam()" style="font-size:12px; font-weight:600; border-radius: 6px;">Upload File</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link py-1" id="kamera-tab" data-bs-toggle="tab" data-bs-target="#kamera" type="button" role="tab" onclick="startWebcam()" style="font-size:12px; font-weight:600; border-radius: 6px;">Kamera</button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="fotoTabsContent">
                                    <div class="tab-pane fade show active" id="upload" role="tabpanel">
                                        <input type="file" name="foto" class="form-control" accept="image/*" style="border-radius: 8px;">
                                        <small class="text-muted mt-2 d-block"><i class="bi bi-info-circle me-1"></i>Format yang didukung: JPG, PNG.</small>
                                    </div>
                                    <div class="tab-pane fade" id="kamera" role="tabpanel">
                                        <div id="my_camera" style="margin: 0 auto; border-radius: 8px; overflow: hidden; border: 1px solid #cbd5e1; width: 100%; max-width: 100%; min-height: 200px; background:#e2e8f0;"></div>
                                        <input type="hidden" name="foto_base64" id="foto_base64">
                                        <div class="text-center mt-3" id="kamera_controls">
                                            <button type="button" class="btn btn-info text-white w-100 rounded-pill shadow-sm" onclick="take_snapshot()"><i class="bi bi-camera me-2"></i> Ambil Foto</button>
                                        </div>
                                        <div id="results" class="mt-3 text-center" style="display:none;"></div>
                                        <div class="text-center mt-3" id="kamera_retake" style="display:none;">
                                            <button type="button" class="btn btn-outline-secondary w-100 rounded-pill" onclick="retake_snapshot()"><i class="bi bi-arrow-counterclockwise me-2"></i> Ulangi Foto</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary rounded-pill shadow-sm" style="background:#0f766e; border:none; font-weight: 600;"><i class="bi <?= $data_edit ? 'bi-save2' : 'bi-plus-circle' ?> me-2"></i><?= $data_edit ? 'Simpan Perubahan' : 'Simpan PSM' ?></button>
                                <?php if($data_edit): ?>
                                    <a href="?act=MasterPSM" class="btn btn-light rounded-pill border" style="font-weight: 600;">Batal Edit</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8 col-md-12">
                <div class="card h-100" style="border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h6 class="fw-bold mb-0" style="color:#334155;"><i class="bi bi-list-ul text-info me-2"></i>Daftar PSM Terdaftar</h6>
                        <div class="d-flex align-items-center gap-2">
                            <a href="pages/export_master_psm.php?keyword=<?= isset($_GET['keyword']) ? urlencode($_GET['keyword']) : '' ?>" class="btn btn-light text-success btn-sm px-3 rounded-pill border" title="Export Excel Semua Data"><i class="bi bi-file-earmark-excel-fill me-1"></i> Excel</a>
                            <a href="pages/cetak_master_psm.php?keyword=<?= isset($_GET['keyword']) ? urlencode($_GET['keyword']) : '' ?>" target="_blank" class="btn btn-light text-primary btn-sm px-3 rounded-pill border" title="Cetak Semua Data"><i class="bi bi-printer-fill me-1"></i> Cetak</a>
                            <form method="GET" action="index.php" class="d-flex mb-0 ms-2">
                                <input type="hidden" name="act" value="MasterPSM">
                                <div class="input-group input-group-sm shadow-sm" style="border-radius: 20px; overflow: hidden;">
                                    <input type="text" name="keyword" class="form-control border-0 bg-light" placeholder="Cari nama/alamat..." value="<?= isset($_GET['keyword']) ? e($_GET['keyword']) : '' ?>" style="box-shadow: none;">
                                    <button type="submit" class="btn btn-light border-0 bg-light text-primary"><i class="bi bi-search"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-4 pt-0">
                        <div id="areaCetakPSM" class="table-responsive">
                            <table id="tableMasterPSM" class="table table-hover table-custom align-middle" style="min-width: 600px;">
                                <thead style="border-bottom: 2px solid #e2e8f0;">
                                    <tr>
                                        <th class="no-sort text-center px-3 py-3" style="width: 50px;">No</th>
                                        <th class="no-sort text-center py-3" style="width: 70px;">Foto</th>
                                        <th class="py-3">Nama Lengkap</th>
                                        <th class="py-3">No. Telepon</th>
                                        <th class="py-3">Alamat Domisili</th>
                                        <th class="text-center py-3" style="width: 100px;">Status</th>
                                        <th class="no-sort text-center py-3" style="width: 120px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody style="border-top: none;">
                                    <?php
                                        $batas = 7; 
                                        $halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
                                        $halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;
                                        
                                        $keyword = isset($_GET['keyword']) ? validTeks4($_GET['keyword'], 50) : '';
                                        $whereClause = "";
                                        if($keyword != "") {
                                            $whereClause = "WHERE nama_psm LIKE '%$keyword%' OR alamat LIKE '%$keyword%' OR no_telp LIKE '%$keyword%'";
                                        }
                                        
                                        $query_semua = bukaquery2("SELECT id_psm FROM master_psm $whereClause");
                                        $jumlah_data = mysqli_num_rows($query_semua);
                                        $total_halaman = ceil($jumlah_data / $batas);
                                        
                                        $no = $halaman_awal + 1;
                                        $query = bukaquery2("SELECT * FROM master_psm $whereClause ORDER BY nama_psm ASC LIMIT $halaman_awal, $batas");
                                        if(mysqli_num_rows($query) > 0) {
                                            while($r = mysqli_fetch_array($query)) {
                                                echo "<tr>
                                                        <td class='text-center text-muted'>".$no++."</td>
                                                        <td class='text-center'>".($r['foto'] ? "<img src='foto_psm/".e($r['foto'])."' width='42' height='42' style='border-radius:50%; object-fit:cover; border:2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>" : "<div style='width:42px; height:42px; border-radius:50%; background:#f1f5f9; display:inline-flex; align-items:center; justify-content:center; border:2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05);'><i class='bi bi-person text-slate-400' style='font-size:22px; color:#94a3b8;'></i></div>")."</td>
                                                        <td class='fw-bold' style='color:#334155;'>".e($r['nama_psm'])."</td>
                                                        <td><span class='text-muted'><i class='bi bi-telephone me-1' style='font-size:11px;'></i>".(empty($r['no_telp']) ? '-' : e($r['no_telp']))."</span></td>
                                                        <td><span class='text-muted text-truncate d-inline-block' style='max-width:200px;' title='".e($r['alamat'])."'>".(empty($r['alamat']) ? '-' : e($r['alamat']))."</span></td>
                                                        <td class='text-center'>".($r['status'] == 'Aktif' ? "<span class='badge bg-success-subtle text-success px-2 py-1 rounded-pill border border-success-subtle' style='font-weight:600;'>Aktif</span>" : "<span class='badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill border border-secondary-subtle' style='font-weight:600;'>Nonaktif</span>")."</td>
                                                        <td class='text-center'>
                                                            <div class='btn-group shadow-sm'>
                                                                <a href='?act=MasterPSM&aksi=form_edit&id=".urlencode($r['id_psm'])."' class='btn btn-sm btn-light border text-primary' title='Edit'><i class='bi bi-pencil-square'></i></a>
                                                                <form method='POST' action='?act=MasterPSM&aksi=hapus' style='display:inline;' onsubmit='return confirm(\"Yakin ingin menghapus data ".e($r['nama_psm'])."?\")'>
                                                                    ".csrf_input()."
                                                                    <input type='hidden' name='id' value='".e($r['id_psm'])."'>
                                                                    <button type='submit' class='btn btn-sm btn-light border text-danger' title='Hapus'><i class='bi bi-trash3'></i></button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                      </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='7' class='text-center py-5 text-muted'>
                                                <i class='bi bi-inboxes text-secondary' style='font-size: 40px; opacity: 0.5;'></i><br>
                                                <span class='d-block mt-2'>Data PSM tidak ditemukan.</span>
                                            </td></tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if($total_halaman > 1): ?>
                        <nav class="mt-4 d-flex justify-content-between align-items-center">
                            <span class="text-muted" style="font-size:12px;">Menampilkan halaman <?= $halaman ?> dari <?= $total_halaman ?></span>
                            <ul class="pagination pagination-sm mb-0">
                                <?php 
                                    $keyword_param = isset($_GET['keyword']) ? '&keyword='.urlencode($_GET['keyword']) : '';
                                ?>
                                <li class="page-item <?= ($halaman <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link shadow-sm border-0 bg-white" href="?act=MasterPSM<?= $keyword_param ?>&halaman=<?= $halaman - 1 ?>" style="border-radius:20px 0 0 20px;"><i class="bi bi-chevron-left"></i></a>
                                </li>
                                <?php for($x=1; $x<=$total_halaman; $x++): ?>
                                    <li class="page-item <?= ($halaman == $x) ? 'active' : '' ?>">
                                        <a class="page-link shadow-sm border-0 <?= ($halaman == $x) ? 'bg-primary text-white' : 'bg-white' ?>" href="?act=MasterPSM<?= $keyword_param ?>&halaman=<?= $x ?>" style="<?= ($halaman == $x) ? 'background-color:#0f766e !important;' : '' ?>"><?= $x ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                                    <a class="page-link shadow-sm border-0 bg-white" href="?act=MasterPSM<?= $keyword_param ?>&halaman=<?= $halaman + 1 ?>" style="border-radius:0 20px 20px 0;"><i class="bi bi-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let webcamActive = false;
        function startWebcam() {
            if(!webcamActive) {
                Webcam.set({
                    width: 320,
                    height: 240,
                    image_format: 'jpeg',
                    jpeg_quality: 90
                });
                Webcam.attach('#my_camera');
                webcamActive = true;
                
                document.getElementById('foto_base64').value = '';
                document.getElementById('results').style.display = 'none';
                document.getElementById('my_camera').style.display = 'block';
                document.getElementById('kamera_controls').style.display = 'block';
                document.getElementById('kamera_retake').style.display = 'none';
            }
        }
        function stopWebcam() {
            if(webcamActive) {
                Webcam.reset();
                webcamActive = false;
                document.getElementById('foto_base64').value = '';
                document.getElementById('results').style.display = 'none';
            }
        }
        function take_snapshot() {
            Webcam.snap(function(data_uri) {
                document.getElementById('foto_base64').value = data_uri;
                document.getElementById('results').innerHTML = '<h6 class="text-success mt-2" style="font-size:12px;">Foto berhasil diambil!</h6><img src="'+data_uri+'" width="100%" style="max-width:320px; border-radius:8px; border:2px solid #059669;"/>';
                document.getElementById('results').style.display = 'block';
                document.getElementById('my_camera').style.display = 'none';
                document.getElementById('kamera_controls').style.display = 'none';
                document.getElementById('kamera_retake').style.display = 'block';
            });
        }
        function retake_snapshot() {
            document.getElementById('foto_base64').value = '';
            document.getElementById('results').style.display = 'none';
            document.getElementById('my_camera').style.display = 'block';
            document.getElementById('kamera_controls').style.display = 'block';
            document.getElementById('kamera_retake').style.display = 'none';
        }

        function cetakArea(areaId, title) {
            let content = document.getElementById(areaId).innerHTML;
            let win = window.open('', '_blank');
            win.document.write(`
                <html>
                <head>
                    <title>Cetak Laporan</title>
                    <link rel="stylesheet" href="css/bootstrap.min.css" />
                    <style>
                        body { padding: 20px; font-family: sans-serif; font-size: 12px; }
                        table { width: 100%; border-collapse: collapse !important; margin-bottom: 20px; }
                        table, th, td { border: 1px solid #000 !important; padding: 6px !important; }
                        th { background-color: #f8f9fa !important; text-align: center; }
                        .text-center { text-align: center; }
                        .fw-bold { font-weight: bold; }
                    </style>
                </head>
                <body>
                    <h4 class="text-center fw-bold mb-4">${title}</h4>
                    ${content}
                </body>
                </html>
            `);
            win.document.close();
            win.focus();
            setTimeout(function(){ win.print(); win.close(); }, 500);
        }

        function exportExcel(tableID, filename = ''){
            var tableSelect = document.getElementById(tableID);
            var modifiedTableHTML = tableSelect.outerHTML.replace(/<table/g, '<table border="1"');
            var css = '<style> table { border-collapse: collapse; } th, td { font-size: 12pt !important; font-family: Calibri, sans-serif !important; padding: 5px; border: 1px solid #000000; vertical-align: middle; } th { font-weight: bold; background-color: #e2e8f0; text-align: center; } </style>';
            var tableHTML = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8">' + css + '</head><body>' + modifiedTableHTML + '</body></html>';
            filename = filename ? filename + '.xls' : 'excel_data.xls';
            var blob = new Blob([tableHTML], { type: 'application/vnd.ms-excel' });
            var downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);
            if(navigator.msSaveOrOpenBlob){ navigator.msSaveOrOpenBlob(blob, filename); } else {
                var url = URL.createObjectURL(blob); downloadLink.href = url; downloadLink.download = filename; downloadLink.click(); URL.revokeObjectURL(url);
            }
            document.body.removeChild(downloadLink);
        }
    </script>
</body>
</html>
