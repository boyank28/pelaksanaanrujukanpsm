<style>
    body { padding-left: 260px !important; padding-top: 80px !important; padding-bottom: 60px !important; background: #f0fdfa !important; }
    .sidebar-custom {
        position: fixed; top: 0; left: 0; width: 260px; height: 100vh;
        background: #115e59; z-index: 1040; padding: 25px 0; color: white;
        box-shadow: 4px 0 15px rgba(0,0,0,0.05); overflow-y: auto;
    }
    .sidebar-brand { font-size: 22px; font-weight: 800; padding: 0 25px; margin-bottom: 40px; display: flex; align-items: center; color: white !important; text-decoration: none; letter-spacing: 0.5px; }
    .sidebar-brand i { font-size: 28px; margin-right: 10px; color: #ccfbf1; }
    .sidebar-nav { list-style: none; padding: 0 15px; margin: 0; }
    .sidebar-link {
        display: flex; align-items: center; padding: 12px 20px; color: rgba(255,255,255,0.7); text-decoration: none;
        font-weight: 500; transition: 0.3s; border-radius: 12px; margin-bottom: 5px;
    }
    .sidebar-link i { font-size: 18px; margin-right: 15px; }
    .sidebar-link:hover { color: white; background: rgba(255,255,255,0.05); text-decoration: none;}
    .sidebar-link.active { background: #0f766e; color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .topbar-custom {
        position: fixed; top: 0; left: 260px; right: 0; height: 80px;
        background: rgba(240, 253, 250, 0.9); backdrop-filter: blur(12px);
        z-index: 1030; display: flex; align-items: center; justify-content: space-between;
        padding: 0 40px; border-bottom: 1px solid rgba(15, 118, 110, 0.1);
    }
    .topbar-welcome { color: #0f766e; }
    .topbar-welcome h4 { font-weight: 800; margin: 0; color: #1e293b; letter-spacing: -0.5px; }
    .topbar-welcome span { font-size: 14px; font-weight: 500; color: #64748b; }
    .topbar-actions { display: flex; align-items: center; gap: 20px; }
    .sidebar-submenu { list-style: none; padding-left: 42px; margin: 0; padding-bottom: 10px; }
    .submenu-link {
        display: block; padding: 8px 12px; color: rgba(255,255,255,0.7); text-decoration: none;
        font-size: 13px; font-weight: 500; transition: 0.3s; border-radius: 8px; margin-bottom: 3px;
    }
    .submenu-link:hover, .submenu-link.active { color: white; background: rgba(255,255,255,0.1); text-decoration: none; }
    .sidebar-link[aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }
    .bi-chevron-down { transition: transform 0.3s; }
    
    @media (max-width: 991px) {
        body { padding-left: 0 !important; padding-top: 130px !important; }
        .sidebar-custom { display: none; }
        .topbar-custom { left: 0; padding: 0 20px; flex-direction: column; height: auto; padding-top: 15px; padding-bottom: 15px; }
    }
    
    /* Dark Mode Global Styles */
    body.dark-mode { background: #0f172a !important; color: #f8fafc !important; }
    body.dark-mode .topbar-custom {
        background: rgba(15, 23, 42, 0.9) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
    }
    body.dark-mode .topbar-welcome h4 { color: #f8fafc !important; }
    body.dark-mode .topbar-welcome span { color: #94a3b8 !important; }
    
    body.dark-mode .card-custom, body.dark-mode .content-wrapper, body.dark-mode .card { background: #1e293b !important; color: #f8fafc !important; border-color: #334155 !important; }
    body.dark-mode .card-header { background: #1e293b !important; }
    body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 { color: #f8fafc !important; }
    body.dark-mode p { color: #cbd5e1 !important; }
    body.dark-mode .table-custom th, body.dark-mode th { background: #334155 !important; color: #f8fafc !important; border-color: #475569 !important; }
    body.dark-mode .table-custom td, body.dark-mode td { color: #f8fafc !important; border-color: #475569 !important; }
    body.dark-mode .table-custom tbody tr:hover, body.dark-mode table tr:hover { background: #334155 !important; }
    body.dark-mode .form-control, body.dark-mode .form-select { background: #0f172a !important; color: #f8fafc !important; border-color: #475569 !important; }
    body.dark-mode .form-label, body.dark-mode label { color: #cbd5e1 !important; }
    body.dark-mode span:not(.badge):not(.navbar-toggler-icon) { color: #cbd5e1 !important; }
    body.dark-mode .btn-nav { background-color: #334155 !important; color: #f8fafc !important; border-color: #475569 !important; }
    body.dark-mode .btn-nav:hover { background-color: #475569 !important; }
    body.dark-mode .modal-content { background: #1e293b !important; }
    body.dark-mode .modal-header, body.dark-mode .modal-body, body.dark-mode .modal-footer { background: #1e293b !important; border-color: #334155 !important; color: #f8fafc !important;}
    body.dark-mode .input-group-text { background: #334155 !important; color: #f8fafc !important; border-color: #475569 !important;}
    body.dark-mode .search-box { background: #0f172a !important; color: #f8fafc !important; border-color: #475569 !important; }
    body.dark-mode .list-group-item { background: #1e293b !important; color: #cbd5e1 !important; border-color: #334155 !important; }
    body.dark-mode .text-dark { color: #f8fafc !important; }
    body.dark-mode .text-muted { color: #cbd5e1 !important; } /* Lighter muted text */
    body.dark-mode .list-group-item, body.dark-mode .list-group-item *:not(.badge) { color: #f8fafc !important; }
    body.dark-mode .bg-light, body.dark-mode .bg-white { background-color: #1e293b !important; }
    
    /* Pagination & Tabs */
    body.dark-mode .page-link { background-color: #1e293b !important; color: #cbd5e1 !important; border-color: #475569 !important; }
    body.dark-mode .page-item.active .page-link { background-color: #0f766e !important; color: #ffffff !important; border-color: #0f766e !important; }
    body.dark-mode .page-item.disabled .page-link { background-color: #0f172a !important; color: #475569 !important; border-color: #475569 !important; }
    body.dark-mode .nav-tabs { border-bottom-color: #475569 !important; }
    body.dark-mode .nav-tabs .nav-link { color: #cbd5e1 !important; }
    body.dark-mode .nav-tabs .nav-link:hover { border-color: #475569 #475569 #1e293b !important; }
    body.dark-mode .nav-tabs .nav-link.active { background-color: #1e293b !important; color: #f8fafc !important; border-color: #475569 #475569 #1e293b !important; }
    
    .bg-subtotal { background-color: #f1f5f9 !important; }
    .bg-total { background-color: #e2e8f0 !important; }
    body.dark-mode .bg-subtotal { background-color: #334155 !important; }
    body.dark-mode .bg-total { background-color: #1e293b !important; }
    
    /* Preloader */
    #preloader {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: #f8fafc; z-index: 99999;
        display: flex; justify-content: center; align-items: center;
        transition: opacity 0.5s ease;
    }
    body.dark-mode #preloader { background: #0f172a; }
    .spinner {
        width: 50px; height: 50px;
        border: 5px solid #e2e8f0; border-top-color: #0f766e;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }
</style>
<div id="preloader"><div class="spinner"></div></div>
<script>
window.addEventListener('load', function() {
    var preloader = document.getElementById('preloader');
    if(preloader) {
        preloader.style.opacity = '0';
        setTimeout(function() { preloader.style.display = 'none'; }, 500);
    }
});
</script>
<div class="sidebar-custom d-none d-lg-block">
    <a href="?act=Dashboard" class="sidebar-brand">
        <i class="bi bi-hospital"></i>
        <div>
            <div style="font-size: 12px; font-weight: 600; opacity: 0.8; line-height: 1;">SISTEM RUJUKAN</div>
            <div style="line-height: 1;">PSM</div>
        </div>
    </a>
    
    <?php 
        $perms_nav = isset($_SESSION['permissions_psm']) ? $_SESSION['permissions_psm'] : [];
        $is_admin_nav = (isset($_SESSION['role_psm']) && $_SESSION['role_psm'] == 'Admin');
        if (empty($perms_nav)) { 
            $perms_nav = [
                'can_view_laporan' => true, 'can_manage_fee' => $is_admin_nav,
                'can_manage_users' => $is_admin_nav, 'can_backup' => $is_admin_nav,
                'can_view_log' => $is_admin_nav
            ];
        }
        $active_page = isset($_GET['act']) ? $_GET['act'] : 'Dashboard';
    ?>
    
    <ul class="sidebar-nav">
        <li>
            <a class="sidebar-link <?= in_array($active_page, ['Dashboard','Kamera']) ? 'active' : '' ?>" href="?act=Dashboard">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
        </li>
        
        <?php if(!empty($perms_nav['can_view_laporan'])): ?>
        <li class="nav-item">
            <a class="sidebar-link <?= in_array($active_page, ['Rekapitulasi','Amprahan']) ? '' : 'collapsed' ?>" href="#laporanCollapse" data-bs-toggle="collapse" role="button" aria-expanded="<?= in_array($active_page, ['Rekapitulasi','Amprahan']) ? 'true' : 'false' ?>">
                <i class="bi bi-file-earmark-text"></i> <span style="flex-grow:1;">Laporan</span> <i class="bi bi-chevron-down" style="margin-right:0; font-size:12px;"></i>
            </a>
            <div class="collapse <?= in_array($active_page, ['Rekapitulasi','Amprahan']) ? 'show' : '' ?>" id="laporanCollapse">
                <ul class="sidebar-submenu">
                    <li><a class="submenu-link <?= $active_page == 'Rekapitulasi' ? 'active' : '' ?>" href="?act=Rekapitulasi"><i class="bi bi-pie-chart me-2"></i> Rekapitulasi</a></li>
                    <li><a class="submenu-link <?= $active_page == 'Amprahan' ? 'active' : '' ?>" href="?act=Amprahan"><i class="bi bi-cash-stack me-2"></i> Amprahan Fee</a></li>
                </ul>
            </div>
        </li>
        <?php endif; ?>
        
        <li class="nav-item">
            <a class="sidebar-link <?= in_array($active_page, ['MasterPSM','Users','Setting','SettingTTD','SettingFee','BackupRestore','LogAktivitas']) ? '' : 'collapsed' ?>" href="#settingsCollapse" data-bs-toggle="collapse" role="button" aria-expanded="<?= in_array($active_page, ['MasterPSM','Users','Setting','SettingTTD','SettingFee','BackupRestore','LogAktivitas']) ? 'true' : 'false' ?>">
                <i class="bi bi-gear"></i> <span style="flex-grow:1;">Settings</span> <i class="bi bi-chevron-down" style="margin-right:0; font-size:12px;"></i>
            </a>
            <div class="collapse <?= in_array($active_page, ['MasterPSM','Users','Setting','SettingTTD','SettingFee','BackupRestore','LogAktivitas']) ? 'show' : '' ?>" id="settingsCollapse">
                <ul class="sidebar-submenu">
                    <li><a class="submenu-link <?= $active_page == 'MasterPSM' ? 'active' : '' ?>" href="?act=MasterPSM"><i class="bi bi-person-badge me-2"></i> Master PSM</a></li>
                    <?php if(!empty($perms_nav['can_manage_users'])): ?><li><a class="submenu-link <?= $active_page == 'Users' ? 'active' : '' ?>" href="?act=Users"><i class="bi bi-people me-2"></i> Kelola Pengguna</a></li><?php endif; ?>
                    <?php if(!empty($perms_nav['can_manage_fee'])): ?><li><a class="submenu-link <?= $active_page == 'SettingFee' ? 'active' : '' ?>" href="?act=SettingFee"><i class="bi bi-cash-coin me-2"></i> Master Fee PSM</a></li><?php endif; ?>
                    <?php if($is_admin_nav): ?>
                    <li><a class="submenu-link <?= $active_page == 'SettingTTD' ? 'active' : '' ?>" href="?act=SettingTTD"><i class="bi bi-pen me-2"></i> Tanda Tangan</a></li>
                    <li><a class="submenu-link <?= $active_page == 'Setting' ? 'active' : '' ?>" href="?act=Setting"><i class="bi bi-sliders me-2"></i> Setting Aplikasi</a></li>
                    <?php endif; ?>
                    <?php if(!empty($perms_nav['can_backup'])): ?><li><a class="submenu-link <?= $active_page == 'BackupRestore' ? 'active' : '' ?>" href="?act=BackupRestore"><i class="bi bi-cloud-arrow-down me-2"></i> Backup</a></li><?php endif; ?>
                    <?php if(!empty($perms_nav['can_view_log']) || $is_admin_nav): ?><li><a class="submenu-link <?= $active_page == 'LogAktivitas' ? 'active' : '' ?>" href="?act=LogAktivitas"><i class="bi bi-journal-text me-2"></i> Log Aktivitas</a></li><?php endif; ?>
                </ul>
            </div>
        </li>
    </ul>
    
    <div style="position: absolute; bottom: 30px; width: 100%; padding: 0 15px;">
        <a class="sidebar-link" href="#" id="darkModeToggle" onclick="toggleDarkMode(event)">
            <i class="bi bi-moon-stars-fill text-info" id="darkModeIcon"></i> <span id="darkModeText">Mode Gelap</span>
        </a>
        <a class="sidebar-link" href="#" data-bs-toggle="modal" data-bs-target="#aboutModal">
            <i class="bi bi-info-circle"></i> Review Aplikasi
        </a>
    </div>
</div>

<div class="topbar-custom">
    <div class="topbar-welcome">
        <span>Selamat Datang,</span>
        <h4><?= isset($_SESSION['nama_lengkap_psm']) && !empty($_SESSION['nama_lengkap_psm']) ? e($_SESSION['nama_lengkap_psm']) : (isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) ? ucfirst($_SESSION['ses_admin_pelaksanaanrujukanpsm']) : 'User') ?></h4>
    </div>
    <div class="topbar-actions">
        <div class="dropdown">
            <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                <?php $avatar_name = isset($_SESSION['nama_lengkap_psm']) && !empty($_SESSION['nama_lengkap_psm']) ? urlencode($_SESSION['nama_lengkap_psm']) : (isset($_SESSION['ses_admin_pelaksanaanrujukanpsm']) ? urlencode($_SESSION['ses_admin_pelaksanaanrujukanpsm']) : 'User'); ?>
                <img src="images/logo.png" onerror="this.src='https://ui-avatars.com/api/?name=<?= $avatar_name ?>&background=0D8ABC&color=fff'" alt="User Avatar" width="40" height="40" class="rounded-circle border">
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow text-small" aria-labelledby="dropdownUser">
                <li><a class="dropdown-item" href="?act=Profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Review Aplikasi Modal -->
<div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true" style="z-index: 9999;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.2); overflow: hidden;">
      <div class="modal-header" style="background: #ffffff; border-bottom: 1px solid #f1f5f9; padding: 20px 25px;">
        <h5 class="modal-title fw-bold" id="aboutModalLabel" style="color: #0f766e;"><i class="bi bi-star-fill text-warning me-2"></i>Review Aplikasi</h5>
      </div>
      <div class="modal-body text-center" style="padding: 30px 25px; background: #ffffff;">
        <div style="font-size: 50px; margin-bottom: 15px;">
            <i class="bi bi-rocket-takeoff-fill" style="color: #0ea5e9;"></i>
        </div>
        <h4 class="fw-bold" style="color: #1e293b; margin-bottom: 10px;">Pelaksanaan Rujukan PSM</h4>
        <p class="text-muted mb-4" style="font-size: 14px; line-height: 1.6;">Sistem Cerdas Terintegrasi SIMKES Khanza untuk mempermudah pencatatan, pemantauan, dan pelaporan pasien rujukan PSM dengan desain modern & responsif.</p>
        
        <div class="p-4 mb-3 rounded-4" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fde68a;">
            <p class="mb-3" style="font-size: 14.5px; color: #92400e; font-weight: 600;">Terbantu dengan aplikasi ini? Yuk, dukung pengembangannya agar semakin canggih! ☕</p>
            <a href="https://saweria.co/boyank28" target="_blank" class="btn fw-bold w-100 shadow-sm" style="background: linear-gradient(to right, #f59e0b, #ea580c); color: white; border-radius: 12px; padding: 12px; font-size: 15px; border: none; transition: transform 0.2s;">
                <img src="https://saweria.co/favicon.ico" width="20" class="me-2" style="border-radius: 50%; background: white; padding: 2px;"> Traktir Kopi via Saweria
            </a>
        </div>
      </div>
      <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #f1f5f9; padding: 15px 25px; justify-content: space-between;">
        <span style="font-size: 12px; color: #94a3b8; font-weight: 500;">&copy; 2026 Developed by boyank28</span>
        <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal" style="border-radius: 8px; padding: 8px 20px; background: #cbd5e1; color: #475569; border: none;">Tutup</button>
      </div>
    </div>
  </div>
</div>
<!-- Include Bootstrap JS for dropdowns to work -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Footer -->
<footer style="position: fixed; bottom: 0; width: 100%; background: #0f766e; color: white; padding: 10px 0; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); z-index: 999;">
    <div class="container d-flex justify-content-between align-items-center">
        <span style="font-size: 14px;">&copy; <?= date('Y') ?> SIMKES Khanza - Pelaksanaan Rujukan PSM</span>
        <div class="d-flex align-items-center px-3 py-1 rounded" style="background: #064e3b; color: #ffffff; border: 1px solid #0f766e;">
            <i class="bi bi-clock-history me-2 text-warning"></i>
            <span id="liveClock" class="fw-bold" style="letter-spacing: 1px;">Loading...</span>
        </div>
    </div>
</footer>

<script>
    function updateClock() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateStr = now.toLocaleDateString('id-ID', options);
        
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        const liveClockEl = document.getElementById('liveClock');
        if(liveClockEl) {
            liveClockEl.innerHTML = `${dateStr} &nbsp;|&nbsp; ${hours}:${minutes}:${seconds}`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
    
    function applyDarkMode(isDark) {
        if(isDark) {
            document.body.classList.add('dark-mode');
            const icon = document.getElementById('darkModeIcon');
            if (icon) {
                icon.classList.replace('bi-sun-fill', 'bi-moon-stars-fill');
                icon.classList.replace('text-warning', 'text-info');
            }
            const text = document.getElementById('darkModeText');
            if (text) text.innerText = 'Mode Gelap';
        } else {
            document.body.classList.remove('dark-mode');
            const icon = document.getElementById('darkModeIcon');
            if (icon) {
                icon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
                icon.classList.replace('text-info', 'text-warning');
            }
            const text = document.getElementById('darkModeText');
            if (text) text.innerText = 'Mode Terang';
        }
    }

    function toggleDarkMode(e) {
        e.preventDefault();
        const isDark = !document.body.classList.contains('dark-mode');
        localStorage.setItem('psmDarkMode', isDark ? '1' : '0');
        applyDarkMode(isDark);
    }

    // Initialize
    if (localStorage.getItem('psmDarkMode') === '1') {
        applyDarkMode(true);
    }

    // Dynamic Table Sorting
    $(document).ready(function() {
        $('th').each(function(col) {
            var header = $(this);
            // Skip action columns or explicitly no-sort
            if(header.closest('table').hasClass('table-custom') && !header.hasClass('no-sort') && header.text().trim() !== 'AKSI') {
                header.css('cursor', 'pointer').attr('title', 'Klik untuk mengurutkan');
                
                if(header.find('.sort-icon').length === 0) {
                    header.append(' <i class="bi bi-arrow-down-up sort-icon text-muted" style="font-size: 10px; margin-left: 5px; opacity: 0.5;"></i>');
                }
                
                header.click(function() {
                    var table = header.closest('table');
                    var tbody = table.find('tbody');
                    if(tbody.find('tr').length <= 1) return; // Skip if empty or 1 row
                    
                    var isAsc = header.hasClass('asc');
                    table.find('th').removeClass('asc desc');
                    table.find('.sort-icon').removeClass('text-primary').css('opacity', '0.5');
                    
                    header.addClass(isAsc ? 'desc' : 'asc');
                    header.find('.sort-icon').addClass('text-primary').css('opacity', '1');
                    
                    var rows = tbody.find('tr').toArray().sort(function(a, b) {
                        var valA = $(a).find('td').eq(col).text().trim();
                        var valB = $(b).find('td').eq(col).text().trim();
                        
                        // Parse numbers if possible (handling Rupiah/dot format)
                        var numA = valA.replace(/\./g, '');
                        var numB = valB.replace(/\./g, '');
                        if(numA !== '' && numB !== '' && !isNaN(numA) && !isNaN(numB)) {
                            valA = parseFloat(numA);
                            valB = parseFloat(numB);
                        } else {
                            valA = valA.toLowerCase();
                            valB = valB.toLowerCase();
                        }
                        
                        if(valA < valB) return isAsc ? 1 : -1;
                        if(valA > valB) return isAsc ? -1 : 1;
                        return 0;
                    });
                    
                    $.each(rows, function(index, row) {
                        tbody.append(row);
                    });
                });
            }
        });
    });
</script>
