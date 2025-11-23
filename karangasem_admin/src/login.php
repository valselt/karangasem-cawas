<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['login_status']) && $_SESSION['login_status'] == true) {
    header("Location: index.php");
    exit;
}

$error = '';
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['login_status'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
            $_SESSION['level'] = $row['level'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $result_plain = $conn->query("SELECT * FROM users WHERE username='$username' AND password='$password'");
        if ($result_plain->num_rows > 0) {
            $row = $result_plain->fetch_assoc();
            $_SESSION['login_status'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
            $_SESSION['level'] = $row['level'];
            header("Location: index.php");
            exit;
        }
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login - Desa Karangasem</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Stack+Sans+Headline:wght@200..700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <link rel="stylesheet" href="style.css">
    <style>
        body { 
            margin: 0;
            padding: 0;
            height: 100vh; 
            width: 100vw;
            overflow: hidden; 
            font-family: var(--main-font);
            background-image: url('https://cdn.ivanaldorino.web.id/karangasem/websiteutama/login.JPG');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: flex-end; 
        }

        .brand-logo {
            position: absolute;
            bottom: 30px;
            left: 40px;
            width: 180px; 
            height: auto;
            z-index: 10;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.6));
        }

        /* --- UPDATE: WIDTH JADI 30% --- */
        .right-panel {
            width: 30%; /* PERUBAHAN DISINI */
            height: 100vh; 
            padding: 1rem; 
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            z-index: 2;
        }

        .auth-box { 
            background: #ffffff; 
            width: 100%;
            height: 100%;
            border-radius: 24px; 
            box-shadow: 0 0 40px rgba(0,0,0,0.15); 
            display: flex;
            flex-direction: column;
            justify-content: center; 
            align-items: center; 
            padding: 40px;
            box-sizing: border-box;
            overflow-y: auto; 
        }

        .auth-content-inner {
            width: 100%;
            max-width: 400px; 
            text-align: center;
        }

        .auth-header h2 { 
            margin-bottom: 10px; 
            color: var(--primary-color); 
            font-size: 2rem;
        }
        .auth-header p {
            color: var(--text-muted);
            margin-bottom: 40px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }
        .input-group label {
            margin-bottom: 8px;
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-color);
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper .material-symbols-rounded {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
            font-size: 22px;
            pointer-events: none;
        }
        .form-control-icon {
            width: 100%;
            padding: 16px 16px 16px 50px !important; 
            border: 2px solid #f0f0f0;
            background: #fcfcfc;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            box-sizing: border-box;
            font-family: var(--main-font);
        }
        .form-control-icon:focus {
            border-color: var(--accent-color);
            background: #fff;
            outline: none;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.1);
        }

        .btn-primary {
            background: var(--accent-color);
            color: white;
            font-weight: 700;
            font-size: 1rem;
            padding: 18px;
            border-radius: 12px;
            box-shadow: 0 10px 20px -5px rgba(52, 152, 219, 0.3);
            transition: 0.3s;
            border: none;
            cursor: pointer;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(52, 152, 219, 0.4);
            filter: brightness(1.1);
        }

        /* --- RESPONSIVE: HP TETAP FULL WIDTH --- */
        @media (max-width: 768px) {
            body {
                justify-content: center; 
                background-position: left center; 
            }
            .right-panel {
                width: 100%; /* Di HP Tetap Full Width */
                padding: 1rem; 
            }
            .brand-logo {
                width: 120px;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
            }
        }
    </style>
</head>
<body>
    <img src="https://cdn.ivanaldorino.web.id/karangasem/websiteutama/karangasem-white.png" alt="Logo Karangasem" class="brand-logo">

    <div class="right-panel">
        <div class="auth-box">
            <div class="auth-content-inner">
                <div class="auth-header">
                    <h2>Selamat Datang</h2>
                    <p>Masuk untuk mengelola data desa</p>
                </div>
                
                <?php if($error): ?>
                    <div style="color:#e74c3c; background: #fdecea; padding: 12px; border-radius: 10px; font-size: 0.9rem; margin-bottom: 25px; display:flex; align-items:center; gap:10px; text-align:left; border:1px solid #fadbd8;">
                        <span class="material-symbols-rounded">error</span> 
                        <span><?= $error ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group">
                        <label>Username</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-rounded">person</span>
                            <input type="text" name="username" class="form-control-icon" placeholder="Masukkan username" required>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-rounded">lock</span>
                            <input type="password" name="password" class="form-control-icon" placeholder="Masukkan password" required>
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn btn-primary">
                        Masuk <span class="material-symbols-rounded">arrow_forward</span>
                    </button>
                </form>
                
                <p style="margin-top:30px; font-size:0.9rem; color: var(--text-muted);">
                    Belum punya akun? <a href="register.php" style="color: var(--accent-color); font-weight: 700; text-decoration:none;">Daftar Sekarang</a>
                </p>
            </div> 
        </div>
    </div>
</body>
</html>