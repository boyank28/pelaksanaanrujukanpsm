<?php
    if(strpos($_SERVER['REQUEST_URI'],"pages")){
        exit(header("Location:../index.php"));
    }

    // Check Permissions
    if(empty($_SESSION['permissions_psm']['can_manage_users']) && !(empty($_SESSION['permissions_psm']) && $_SESSION['role_psm'] == 'Admin')) {
        swal_alert('Anda tidak memiliki hak akses!', '?act=Dashboard');
        exit;
    }

    // Daftar modul untuk hak akses (tambahkan disini jika ada menu/modul baru)
    $daftar_modul = [
        'can_manage_rujukan'       => 'Kelola Data Rujukan',
        'can_view_laporan'         => 'Lihat Laporan & Amprahan',
        'can_manage_fee'           => 'Pengaturan Fee PSM',
        'can_manage_users'         => 'Pengaturan Pengguna',
        'can_backup'               => 'Backup & Restore',
        'can_view_log'             => 'Lihat Log Aktivitas',
        'can_approve_mengetahui'   => 'Validasi Amprahan (Mengetahui)',
        'can_approve_dicek'        => 'Validasi Amprahan (Dicek Oleh)',
        'can_approve_menyetujui'   => 'Validasi Amprahan (Menyetujui)'
    ];

    $aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        
        if ($aksi == 'hapus') {
            $id = validTeks4($_POST['id'], 11);
            bukaquery_prepared("DELETE FROM users WHERE id_user=?", "i", $id);
        } else {
            $username = validTeks4($_POST['username'], 50);
            $role     = validTeks4($_POST['role'], 20);
            $nama     = validTeks6($_POST['nama_lengkap'], 150);
            $jabatan  = validTeks6($_POST['jabatan'], 100);
            $status   = validTeks4($_POST['status'], 20);
            
            // Build permissions array
            $perms = [];
            foreach ($daftar_modul as $key => $label) {
                $perms[$key] = isset($_POST['perm_'.$key]) ? true : false;
            }
            $perms_json = json_encode($perms);
            
            if ($aksi == 'simpan') {
                $password = password_psm_hash(validTeks4($_POST['password'], 50));
                bukaquery_prepared("INSERT INTO users (username, password, role, nama_lengkap, jabatan, permissions, status) VALUES (?, ?, ?, ?, ?, ?, ?)", "sssssss", $username, $password, $role, $nama, $jabatan, $perms_json, $status);
            } elseif ($aksi == 'edit') {
                $id = validTeks4($_POST['id_user'], 11);
                if (!empty($_POST['password'])) {
                    $password = password_psm_hash(validTeks4($_POST['password'], 50));
                    bukaquery_prepared("UPDATE users SET username=?, password=?, role=?, nama_lengkap=?, jabatan=?, permissions=?, status=? WHERE id_user=?", "sssssssi", $username, $password, $role, $nama, $jabatan, $perms_json, $status, $id);
                } else {
                    bukaquery_prepared("UPDATE users SET username=?, role=?, nama_lengkap=?, jabatan=?, permissions=?, status=? WHERE id_user=?", "ssssssi", $username, $role, $nama, $jabatan, $perms_json, $status, $id);
                }
            }
        }
        header("Location: ?act=Users");
        exit;
    }

    $data_edit = null;
    $perms_edit = [];
    if ($aksi == 'form_edit') {
        $id = validTeks4($_GET['id'], 11);
        $res = bukaquery_prepared("SELECT * FROM users WHERE id_user=?", "i", $id);
        if($res) {
            $data_edit = mysqli_fetch_array($res);
            if (!empty($data_edit['permissions'])) {
                $perms_edit = json_decode($data_edit['permissions'], true) ?: [];
            }
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pengaturan Pengguna</title>
    <script src="js/jquery.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .table th { background: #f1f5f9; color: #0f766e; }
        .form-control { font-size: 13px; }
        .page-item.active .page-link { background-color: #0f766e; border-color: #0f766e; color: white; }
        .page-link { color: #0f766e; font-size: 13px; }
        .border-custom { border: 1px solid #e2e8f0; }
        body.dark-mode .border-custom { border-color: #475569 !important; }
    </style>
</head>
<body>
    <?php include "layout/navbar.php"; ?>
    <div class="container">
        <h4 class="mb-4" style="color: #0f766e; font-weight: 700;">Pengaturan Pengguna & Hak Akses</h4>
        
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card p-4">
                    <h6 class="fw-bold mb-3"><?= $data_edit ? 'Edit Pengguna' : 'Tambah Pengguna' ?></h6>
                    <form method="POST" action="?act=Users&aksi=<?= $data_edit ? 'edit' : 'simpan' ?>">
                        <?= csrf_input() ?>
                        <?php if($data_edit): ?>
                            <input type="hidden" name="id_user" value="<?= e($data_edit['id_user']) ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Nama Lengkap</label>
                                    <input type="text" name="nama_lengkap" class="form-control" value="<?= $data_edit ? e($data_edit['nama_lengkap']) : '' ?>">
                                </div>
                                <div class="mb-3">
                                    <label>Jabatan</label>
                                    <input type="text" name="jabatan" class="form-control" value="<?= $data_edit ? e($data_edit['jabatan']) : '' ?>">
                                </div>
                                <div class="mb-3">
                                    <label>Username</label>
                                    <input type="text" name="username" class="form-control" value="<?= $data_edit ? e($data_edit['username']) : '' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>Password <?= $data_edit ? '<small class="text-muted">(Kosongkan jika tidak diubah)</small>' : '' ?></label>
                                    <input type="password" name="password" class="form-control" <?= $data_edit ? '' : 'required' ?>>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label>Hak Akses (Role)</label>
                                            <select name="role" class="form-control">
                                                <option value="Petugas" <?= ($data_edit && $data_edit['role']=='Petugas') ? 'selected' : '' ?>>Petugas</option>
                                                <option value="Admin" <?= ($data_edit && $data_edit['role']=='Admin') ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label>Status Akun</label>
                                            <select name="status" class="form-control">
                                                <option value="Aktif" <?= ($data_edit && $data_edit['status']=='Aktif') ? 'selected' : '' ?>>Aktif</option>
                                                <option value="Nonaktif" <?= ($data_edit && $data_edit['status']=='Nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3 mt-1 p-3 rounded bg-subtotal border-custom">
                                    <label class="fw-bold mb-3" style="color:#0f766e;">Izin Akses Modul</label>
                                    <div class="row">
                                        <?php 
                                            $col_count = ceil(count($daftar_modul) / 2);
                                            $i = 0;
                                            echo '<div class="col-md-6">';
                                            foreach($daftar_modul as $key => $label) {
                                                if($i > 0 && $i % $col_count == 0) echo '</div><div class="col-md-6">';
                                                $checked = (!empty($perms_edit[$key])) ? 'checked' : '';
                                                echo '<div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="perm_'.$key.'" id="'.$key.'" value="1" '.$checked.'>
                                                        <label class="form-check-label" for="'.$key.'">'.$label.'</label>
                                                      </div>';
                                                $i++;
                                            }
                                            echo '</div>';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-12 text-end">
                                <?php if($data_edit): ?>
                                    <a href="?act=Users" class="btn btn-light me-2">Batal</a>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-primary px-4" style="background:#0f766e; border:none;">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0">Daftar Pengguna</h6>
                        <form method="GET" action="" class="d-flex" style="width: 250px;">
                            <input type="hidden" name="act" value="Users">
                            <input type="text" name="keyword" class="form-control form-control-sm me-2" placeholder="Cari nama/username..." value="<?= isset($_GET['keyword']) ? e($_GET['keyword']) : '' ?>">
                            <button type="submit" class="btn btn-sm btn-primary" style="background:#0f766e; border:none;"><i class="bi bi-search"></i></button>
                        </form>
                    </div>
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th class="no-sort">No</th>
                                <th>Nama / Jabatan</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="no-sort">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $limit = 5;
                                $halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
                                $halaman = $halaman < 1 ? 1 : $halaman;
                                $offset = ($halaman - 1) * $limit;
                                
                                $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
                                
                                if($keyword != '') {
                                    $k = "%$keyword%";
                                    $res_count = bukaquery_prepared("SELECT COUNT(*) as total FROM users WHERE nama_lengkap LIKE ? OR username LIKE ?", "ss", $k, $k);
                                    $query = bukaquery_prepared("SELECT * FROM users WHERE nama_lengkap LIKE ? OR username LIKE ? ORDER BY username ASC LIMIT ?, ?", "ssii", $k, $k, $offset, $limit);
                                } else {
                                    $res_count = bukaquery2("SELECT COUNT(*) as total FROM users");
                                    $query = bukaquery_prepared("SELECT * FROM users ORDER BY username ASC LIMIT ?, ?", "ii", $offset, $limit);
                                }
                                
                                $total_data = mysqli_fetch_array($res_count)['total'];
                                $total_halaman = ceil($total_data / $limit);
                                $no = $offset + 1;
                                
                                if(mysqli_num_rows($query) > 0) {
                                    while($r = mysqli_fetch_array($query)) {
                                        echo "<tr>
                                                <td>".$no++."</td>
                                                <td>
                                                    <div class='fw-bold'>".e($r['nama_lengkap'])."</div>
                                                    <div class='text-muted' style='font-size: 11px;'>".e($r['jabatan'])."</div>
                                                </td>
                                                <td class='fw-bold'>".e($r['username'])."</td>
                                                <td>".($r['role'] == 'Admin' ? "<span class='badge bg-danger'>Admin</span>" : "<span class='badge bg-primary'>Petugas</span>")."</td>
                                                <td>".(isset($r['status']) && $r['status'] == 'Nonaktif' ? "<span class='badge bg-secondary'>Nonaktif</span>" : "<span class='badge bg-success'>Aktif</span>")."</td>
                                                <td>
                                                    <a href='?act=Users&aksi=form_edit&id=".urlencode($r['id_user'])."' class='btn btn-sm btn-outline-primary'><i class='bi bi-pencil'></i></a>
                                                    <form method='POST' action='?act=Users&aksi=hapus' style='display:inline;' onsubmit='return confirm(\"Yakin ingin menghapus?\")'>
                                                        ".csrf_input()."
                                                        <input type='hidden' name='id' value='".e($r['id_user'])."'>
                                                        <button type='submit' class='btn btn-sm btn-outline-danger'><i class='bi bi-trash'></i></button>
                                                    </form>
                                                </td>
                                              </tr>";
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                    
                    <?php if($total_halaman > 1): ?>
                    <nav aria-label="Page navigation" class="mt-3">
                        <ul class="pagination justify-content-end mb-0">
                            <?php
                                $qs = '';
                                if($keyword != '') $qs = '&keyword='.urlencode($keyword);
                            ?>
                            <li class="page-item <?= ($halaman <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?act=Users&halaman=<?= $halaman - 1 . $qs ?>">Sebelumnya</a>
                            </li>
                            
                            <?php for($i = 1; $i <= $total_halaman; $i++): ?>
                            <li class="page-item <?= ($halaman == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?act=Users&halaman=<?= $i . $qs ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?act=Users&halaman=<?= $halaman + 1 . $qs ?>">Selanjutnya</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
