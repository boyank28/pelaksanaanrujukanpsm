<?php
    if(strpos($_SERVER['REQUEST_URI'],"pages")){
        exit(header("Location:../index.php"));
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pelaksanaan Rujukan PSM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; 
            padding: 0; 
            background-color: #f8fafc;
        }
        .login-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            width: 100%;
            height: 100vh;
            z-index: 9999;
            background: #f8fafc;
        }
        .login-brand {
            flex: 1.2;
            background: linear-gradient(135deg, #0f766e 0%, #115e59 50%, #042f2e 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        .login-brand::before {
            content: '';
            position: absolute;
            top: -20%; left: -10%;
            width: 500px; height: 500px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            filter: blur(60px);
        }
        .login-brand::after {
            content: '';
            position: absolute;
            bottom: -20%; right: -10%;
            width: 400px; height: 400px;
            background: rgba(20, 184, 166, 0.15);
            border-radius: 50%;
            filter: blur(60px);
        }
        .brand-icon {
            font-size: 90px;
            margin-bottom: 25px;
            color: #ccfbf1;
            text-shadow: 0 10px 25px rgba(0,0,0,0.3);
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        .brand-title {
            font-weight: 800;
            font-size: 38px;
            margin-bottom: 15px;
            z-index: 1;
            text-align: center;
            letter-spacing: -0.5px;
        }
        .brand-subtitle {
            font-size: 17px;
            font-weight: 300;
            color: #99f6e4;
            z-index: 1;
            text-align: center;
            max-width: 80%;
            line-height: 1.6;
        }
        .login-form-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
            padding: 40px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            margin-bottom: 40px;
        }
        .login-header h3 {
            font-weight: 800;
            color: #0f172a;
            font-size: 32px;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }
        .login-header p {
            color: #64748b;
            font-size: 16px;
        }
        
        .input-group {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: white;
            overflow: hidden;
        }
        .input-group:focus-within {
            border-color: #0f766e;
            box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.1);
        }
        .input-group-text {
            border: none;
            background: transparent;
            color: #94a3b8;
            padding-right: 0;
            padding-left: 1.2rem;
            font-size: 18px;
            transition: all 0.3s ease;
        }
        .input-group:focus-within .input-group-text {
            color: #0f766e;
        }
        .form-control.with-icon {
            border: none;
            box-shadow: none !important;
            padding: 1rem;
            font-size: 15px;
            background: transparent;
            color: #334155;
            font-weight: 500;
        }
        .form-control.with-icon::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 700;
            font-size: 16px;
            color: white;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 8px 20px -6px rgba(15, 118, 110, 0.5);
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -6px rgba(15, 118, 110, 0.6);
            color: white;
        }
        .btn-login i {
            transition: transform 0.3s ease;
        }
        .btn-login:hover i {
            transform: translateX(5px);
        }
        
        /* Preloader */
        #preloader {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: #ffffff; z-index: 99999;
            display: flex; justify-content: center; align-items: center;
            transition: opacity 0.5s ease;
        }
        .spinner {
            width: 50px; height: 50px;
            border: 4px solid #f1f5f9; border-top-color: #0f766e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        
        @media (max-width: 768px) {
            .login-brand { display: none; }
            .login-form-container { background: #f8fafc; }
            .login-card { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        }
    </style>
</head>
<body>
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
    
    <div class="login-container">
        <div class="login-brand">
            <i class="bi bi-hospital brand-icon"></i>
            <h1 class="brand-title">SIMKES Khanza</h1>
            <p class="brand-subtitle">Sistem Informasi Manajemen<br>Pelaksanaan Rujukan Pekerja Sosial Masyarakat (PSM)</p>
        </div>
        
        <div class="login-form-container">
            <div class="login-card">
                <div class="login-header">
                    <h3>Selamat Datang 👋</h3>
                    <p>Silakan login ke akun Anda untuk masuk ke sistem.</p>
                </div>
                
                <form action="login.php" method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold" style="color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="usere" class="form-control with-icon" placeholder="Masukkan username" required autofocus autocomplete="off">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold" style="color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                            <input type="password" name="passwordte" class="form-control with-icon" placeholder="Masukkan password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        Login ke Sistem <i class="bi bi-arrow-right-short ms-2 fs-5"></i>
                    </button>
                    
                    <div class="mt-5 text-center" style="font-size: 13px; color: #94a3b8;">
                        &copy; 2026 SIMKES Khanza <br> Modul Pelaksanaan Rujukan PSM
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>