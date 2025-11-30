@echo off
echo 🚀 [WINDOWS] Memulai Deployment Project Desa...
echo ==============================================

:: --- 1. INFRASTRUKTUR ---
echo [1/3] Menyalakan Database & Storage...

cd database_desa
docker compose up -d
cd ..

cd minio_storage
docker compose up -d
cd ..

echo.
echo ⏳ Menunggu 10 detik agar Database booting...
timeout /t 10 /nobreak >nul

:: --- 2. APLIKASI UTAMA (KARANGASEM) ---
echo.
echo [2/3] Menyalakan Aplikasi Karangasem...
cd karangasem
docker compose up -d --build

:: PERUBAHAN PENTING: Cek folder src\vendor
if not exist "src\vendor\" (
    echo    -> Folder 'src\vendor' tidak ditemukan. Menginstall AWS SDK...
    docker exec PHP-FPM composer require aws/aws-sdk-php --no-interaction
) else (
    echo    -> Folder 'src\vendor' sudah ada. Skip install.
)
cd ..

:: --- 3. APLIKASI ADMIN (KARANGASEM ADMIN) ---
echo.
echo [3/3] Menyalakan Admin Panel...
cd karangasem_admin
docker compose up -d --build

:: PERUBAHAN PENTING: Cek folder src\vendor
if not exist "src\vendor\" (
    echo    -> Folder 'src\vendor' Admin tidak ditemukan. Menginstall AWS SDK...
    docker exec ADMIN-PHP-FPM composer require aws/aws-sdk-php --no-interaction
) else (
    echo    -> Folder 'src\vendor' Admin sudah ada. Skip install.
)
cd ..

echo.
echo ✅ Deployment Selesai! Tekan tombol apa saja untuk keluar.
pause