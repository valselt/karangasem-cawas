<?php
include 'koneksi.php';
$msg = "";

if (isset($_POST['daftar'])) {
    // Ambil data dari form & sanitasi
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    
    // Data Baru
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $jenis_kelamin = $_POST['jenis_kelamin']; // P atau L
    $rw = $_POST['rw']; // 1-10
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    
    // Cek checkbox punya whatsapp (jika dicentang = 1, tidak = 0)
    $punya_whatsapp = isset($_POST['punya_whatsapp']) ? 1 : 0;

    $level = 'user'; 

    // Cek Username Duplikat
    $cek = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($cek->num_rows == 0) {
        // Query Insert Data Lengkap
        $sql = "INSERT INTO users (nama_lengkap, alamat, jenis_kelamin, rw, no_hp, punya_whatsapp, username, password, level) 
                VALUES ('$nama', '$alamat', '$jenis_kelamin', '$rw', '$no_hp', '$punya_whatsapp', '$username', '$password', '$level')";
        
        if($conn->query($sql)){
            echo "<script>alert('Pendaftaran berhasil! Silakan login.'); window.location='login.php';</script>";
        } else {
            $msg = "Terjadi kesalahan sistem: " . $conn->error;
        }
    } else {
        $msg = "Username sudah digunakan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar - Desa Karangasem</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Stack+Sans+Headline:wght@200..700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <link rel="stylesheet" href="style.css">
    <style>
        body { 
            margin: 0; padding: 0; height: 100vh; width: 100vw; overflow: hidden; 
            font-family: var(--main-font);
            background-image: url('https://cdn.ivanaldorino.web.id/karangasem/websiteutama/register.png');
            background-size: cover; background-position: center; background-repeat: no-repeat;
            display: flex; justify-content: flex-end; 
        }
        .brand-logo {
            position: absolute; bottom: 30px; left: 40px; width: 120px; 
            height: auto; z-index: 10; filter: none; 
        }
        .right-panel {
            width: 35%; /* Sedikit diperlebar untuk form panjang */
            height: 100vh; padding: 3rem; box-sizing: border-box;
            display: flex; flex-direction: column; z-index: 2;
        }
        .auth-box { 
            background: rgba(255, 255, 255, 0.95); /* Lebih solid dikit biar teks jelas */
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            width: 100%; height: 100%; border-radius: 24px; 
            box-shadow: 0 0 40px rgba(0,0,0,0.15); 
            display: flex; flex-direction: column; justify-content: center; align-items: center; 
            padding: 2rem; box-sizing: border-box; overflow-y: auto; /* Scrollable */
        }
        /* Scrollbar styling */
        .auth-box::-webkit-scrollbar { width: 6px; }
        .auth-box::-webkit-scrollbar-thumb { background-color: #ccc; border-radius: 4px; }

        .auth-content-inner { width: 100%; max-width: 400px; text-align: center; padding-top: 20px; padding-bottom: 20px;}
        .auth-header h2 { margin-bottom: 5px; color: var(--primary-color); font-size: 1.8rem; }
        .auth-header p { color: var(--text-muted); margin-bottom: 20px; font-size: 0.9rem; }

        .input-group { position: relative; margin-bottom: 15px; text-align: left; }
        .input-group label { margin-bottom: 6px; display: block; font-weight: 600; font-size: 0.85rem; color: var(--text-color); }
        .input-wrapper { position: relative; }
        .input-wrapper .material-symbols-rounded {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
            color: #95a5a6; font-size: 20px; pointer-events: none;
        }
        .form-control-icon {
            width: 100%; padding: 12px 12px 12px 45px !important; 
            border: 2px solid #f0f0f0; background: #fcfcfc; border-radius: 12px;
            font-size: 0.95rem; transition: all 0.3s; box-sizing: border-box; font-family: var(--main-font);
        }
        .form-control-icon:focus {
            border-color: var(--accent-color); background: #fff; outline: none;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.1);
        }
        
        /* Grid untuk RW & JK */
        .row-grid { display: flex; gap: 15px; }
        .col-half { flex: 1; }

        .btn-primary {
            background: var(--accent-color); color: white; font-weight: 700; font-size: 1rem;
            padding: 14px; border-radius: 12px; box-shadow: 0 10px 20px -5px rgba(52, 152, 219, 0.3);
            transition: 0.3s; border: none; cursor: pointer; width: 100%;
            display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 10px;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 15px 25px -5px rgba(52, 152, 219, 0.4); filter: brightness(1.1); }

        @media (max-width: 768px) {
            body { justify-content: center; background-position: left center; }
            .right-panel { width: 100%; padding: 1rem; }
            .brand-logo { width: 90px; bottom: 20px; left: 50%; transform: translateX(-50%); }
        }
    </style>
</head>
<body>
    <img src="https://cdn.ivanaldorino.web.id/karangasem/websiteutama/karangasem-white.png" alt="Logo Karangasem" class="brand-logo">

    <div class="right-panel">
        <div class="auth-box">
            <div class="auth-content-inner">
                <div class="auth-header">
                    <h2>Pendaftaran Akun</h2>
                    <p>Lengkapi data diri warga Desa Karangasem</p>
                </div>
                
                <?php if($msg): ?>
                    <div style="color:#e74c3c; background: #fdecea; padding: 10px; border-radius: 10px; font-size: 0.85rem; margin-bottom: 20px; display:flex; align-items:center; gap:8px; text-align:left; border:1px solid #fadbd8;">
                        <span class="material-symbols-rounded" style="font-size:18px">warning</span> 
                        <span><?= $msg ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group">
                        <label>Nama Lengkap</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-rounded">badge</span>
                            <input type="text" name="nama" class="form-control-icon" placeholder="Nama Lengkap Anda" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Alamat Lengkap</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-rounded">home</span>
                            <input type="text" name="alamat" class="form-control-icon" placeholder="Jalan, RT/RW, Dusun" required>
                        </div>
                    </div>

                    <div class="row-grid">
                        <div class="col-half">
                            <div class="input-group">
                                <label>Jenis Kelamin</label>
                                <div class="input-wrapper">
                                    <span class="material-symbols-rounded">wc</span>
                                    <select name="jenis_kelamin" class="form-control-icon" required style="appearance: none;">
                                        <option value="" disabled selected>Pilih</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-half">
                            <div class="input-group">
                                <label>RW</label>
                                <div class="input-wrapper">
                                    <span class="material-symbols-rounded">map</span>
                                    <select name="rw" class="form-control-icon" required style="appearance: none;">
                                        <option value="" disabled selected>Pilih</option>
                                        <?php for($i=1; $i<=10; $i++): ?>
                                            <option value="<?= $i ?>">RW <?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Nomor HP</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-rounded">call</span>
                            <input type="number" name="no_hp" class="form-control-icon" placeholder="08xxxxx" required>
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px; margin-top:-10px;">
                        <input type="checkbox" name="punya_whatsapp" id="chk-wa" value="1" style="width:18px; height:18px; cursor:pointer;">
                        <label for="chk-wa" style="margin:0; font-size:0.9rem; cursor:pointer; color:var(--text-color);">Apakah Nomor ini terhubung dengan WhatsApp?</label>
                    </div>

                    <div class="input-group">
                        <label>Username</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-rounded">person</span>
                            <input type="text" name="username" class="form-control-icon" placeholder="Buat Username" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-rounded">lock</span>
                            <input type="password" name="password" class="form-control-icon" placeholder="Buat Password" required>
                        </div>
                    </div>

                    <button type="submit" name="daftar" class="btn btn-primary">
                        <span class="material-symbols-rounded">how_to_reg</span> Daftar Sekarang
                    </button>
                </form>
                
                <p style="margin-top:20px; font-size:0.85rem; color: var(--text-muted);">
                    Sudah punya akun? <a href="login.php" style="color: var(--accent-color); font-weight: 700; text-decoration:none;">Login</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>