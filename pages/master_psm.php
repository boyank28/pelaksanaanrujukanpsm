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
    <div class="container mb-5 pb-4">
        <h4 class="mb-4" style="color: #0f766e; font-weight: 700;">Data Master PSM</h4>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card p-3">
                    <h6 class="fw-bold mb-2"><?= $data_edit ? 'Edit PSM' : 'Tambah PSM Baru' ?></h6>
                    <form method="POST" action="?act=MasterPSM&aksi=<?= $data_edit ? 'edit' : 'simpan' ?>" enctype="multipart/form-data">
                        <?= csrf_input() ?>
                        <?php if($data_edit): ?>
                            <input type="hidden" name="id_psm" value="<?= e($data_edit['id_psm']) ?>">
                        <?php endif; ?>
                        
                        <div class="mb-2">
                            <label style="font-size: 12px; font-weight: 600; color:#475569;">Nama PSM</label>
                            <input type="text" name="nama_psm" class="form-control form-control-sm" value="<?= $data_edit ? e($data_edit['nama_psm']) : '' ?>" required>
                        </div>
                        <div class="mb-2">
                            <label style="font-size: 12px; font-weight: 600; color:#475569;">No. Telepon</label>
                            <input type="text" name="no_telp" class="form-control form-control-sm" value="<?= $data_edit ? e($data_edit['no_telp']) : '' ?>">
                        </div>
                        <div class="mb-2">
                            <label style="font-size: 12px; font-weight: 600; color:#475569;">Alamat</label>
                            <textarea name="alamat" class="form-control form-control-sm" rows="2"><?= $data_edit ? e($data_edit['alamat']) : '' ?></textarea>
                        </div>
                        <div class="mb-2">
                            <label style="font-size: 12px; font-weight: 600; color:#475569;">Status</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="Aktif" <?= ($data_edit && $data_edit['status']=='Aktif') ? 'selected' : '' ?>>Aktif</option>
                                <option value="Nonaktif" <?= ($data_edit && $data_edit['status']=='Nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label style="font-size: 12px; font-weight: 600; color:#475569;">Foto PSM</label>
                            <?php if($data_edit && $data_edit['foto']): ?>
                                <div class="mb-1">
                                    <img src="foto_psm/<?= e($data_edit['foto']) ?>" width="60" style="border-radius:6px; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                                </div>
                            <?php endif; ?>
                            <ul class="nav nav-tabs mb-2" id="fotoTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab" onclick="stopWebcam()" style="font-size:12px;">Upload File</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="kamera-tab" data-bs-toggle="tab" data-bs-target="#kamera" type="button" role="tab" onclick="startWebcam()" style="font-size:12px;">Gunakan Kamera</button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="fotoTabsContent">
                                <div class="tab-pane fade show active" id="upload" role="tabpanel">
                                    <input type="file" name="foto" class="form-control" accept="image/*">
                                    <small class="text-muted mt-1 d-block">Pilih file gambar dari perangkat Anda.</small>
                                </div>
                                <div class="tab-pane fade" id="kamera" role="tabpanel">
                                    <div id="my_camera" style="margin: 0 auto; border-radius: 8px; overflow: hidden; border: 2px solid #cbd5e1; width: 100%; max-width: 320px; min-height: 240px; background:#e2e8f0;"></div>
                                    <input type="hidden" name="foto_base64" id="foto_base64">
                                    <div class="text-center mt-2" id="kamera_controls">
                                        <button type="button" class="btn btn-sm btn-info text-white w-100" onclick="take_snapshot()"><i class="bi bi-camera"></i> Ambil Foto</button>
                                    </div>
                                    <div id="results" class="mt-2 text-center" style="display:none;"></div>
                                    <div class="text-center mt-2" id="kamera_retake" style="display:none;">
                                        <button type="button" class="btn btn-sm btn-secondary w-100" onclick="retake_snapshot()"><i class="bi bi-arrow-counterclockwise"></i> Ulangi Foto</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" style="background:#0f766e; border:none;">Simpan</button>
                        <?php if($data_edit): ?>
                            <a href="?act=MasterPSM" class="btn btn-light w-100 mt-2">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="color:#0f766e;">Daftar PSM</h6>
                        <div class="d-flex align-items-center gap-2">
                            <a href="pages/export_master_psm.php?keyword=<?= isset($_GET['keyword']) ? urlencode($_GET['keyword']) : '' ?>" class="btn btn-outline-success btn-sm px-2" title="Export Excel Semua Data"><i class="bi bi-file-earmark-excel-fill"></i></a>
                            <a href="pages/cetak_master_psm.php?keyword=<?= isset($_GET['keyword']) ? urlencode($_GET['keyword']) : '' ?>" target="_blank" class="btn btn-outline-primary btn-sm px-2" title="Cetak Semua Data"><i class="bi bi-printer-fill"></i></a>
                            <form method="GET" action="index.php" class="d-flex mb-0" style="width:220px;">
                                <input type="hidden" name="act" value="MasterPSM">
                                <input type="text" name="keyword" class="form-control form-control-sm me-1" placeholder="Cari nama PSM..." value="<?= isset($_GET['keyword']) ? e($_GET['keyword']) : '' ?>">
                                <button type="submit" class="btn btn-sm btn-primary" style="background:#0f766e; border:none;"><i class="bi bi-search"></i></button>
                            </form>
                        </div>
                    </div>
                    <div id="areaCetakPSM" class="table-responsive">
                    <table id="tableMasterPSM" class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th class="no-sort">No</th>
                                <th class="no-sort">Foto</th>
                                <th>Nama PSM</th>
                                <th>Telepon</th>
                                <th>Alamat</th>
                                <th>Status</th>
                                <th class="no-sort">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $batas = 5; 
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
                                                <td class='align-middle'>".$no++."</td>
                                                <td class='align-middle'>".($r['foto'] ? "<img src='foto_psm/".e($r['foto'])."' width='40' height='40' style='border-radius:50%; object-fit:cover; border:2px solid #cbd5e1;'>" : "<div style='width:40px; height:40px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; border:2px solid #cbd5e1;'><i class='bi bi-person text-secondary' style='font-size:20px;'></i></div>")."</td>
                                                <td class='fw-bold align-middle'>".e($r['nama_psm'])."</td>
                                                <td class='align-middle'>".e($r['no_telp'])."</td>
                                                <td class='align-middle'>".e($r['alamat'])."</td>
                                                <td class='align-middle'>".($r['status'] == 'Aktif' ? "<span class='badge bg-success'>Aktif</span>" : "<span class='badge bg-secondary'>Nonaktif</span>")."</td>
                                                <td class='align-middle'>
                                                    <a href='?act=MasterPSM&aksi=form_edit&id=".urlencode($r['id_psm'])."' class='btn btn-sm btn-outline-primary'><i class='bi bi-pencil'></i></a>
                                                    <form method='POST' action='?act=MasterPSM&aksi=hapus' style='display:inline;' onsubmit='return confirm(\"Yakin ingin menghapus?\")'>
                                                        ".csrf_input()."
                                                        <input type='hidden' name='id' value='".e($r['id_psm'])."'>
                                                        <button type='submit' class='btn btn-sm btn-outline-danger'><i class='bi bi-trash'></i></button>
                                                    </form>
                                                </td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>Belum ada data</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                    </div>

                    <?php if($total_halaman > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination pagination-sm justify-content-center">
                            <?php 
                                $keyword_param = isset($_GET['keyword']) ? '&keyword='.urlencode($_GET['keyword']) : '';
                            ?>
                            <li class="page-item <?= ($halaman <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?act=MasterPSM<?= $keyword_param ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                            </li>
                            <?php for($x=1; $x<=$total_halaman; $x++): ?>
                                <li class="page-item <?= ($halaman == $x) ? 'active' : '' ?>">
                                    <a class="page-link" href="?act=MasterPSM<?= $keyword_param ?>&halaman=<?= $x ?>"><?= $x ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?act=MasterPSM<?= $keyword_param ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
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
