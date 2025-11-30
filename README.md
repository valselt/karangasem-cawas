# 📊 Website Desa Karangasem

Repositori Website, Website Utama, Database, dan Object Storage yang digunakan untuk memenuhi Program Kerja Pribadi GIAT UNNES 13 di Desa Karangasem, Kecamatan Cawas, Kabupaten Klaten.

Website Utama dan Website Admin dapat diakses secara online melalui [desakarangasem.web.id](https://desakarangasem.web.id/) dan [portal.desakarangasem.web.id](https://portal.desakarangasem.web.id/)

<div align="center">
  <img src="https://img.shields.io/badge/Docker-%232496ED?style=for-the-badge&logo=docker&logoColor=white">
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white">
  <img src="https://img.shields.io/badge/CSS-%23663399?style=for-the-badge&logo=css&logoColor=white">
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black">
  <img src="https://img.shields.io/badge/PHP-%23777BB4?style=for-the-badge&logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/phpmyadmin-%233BABC3?style=for-the-badge&logo=phpmyadmin&logoColor=white">
  <img src="https://img.shields.io/badge/MYSQL-%234479A1?style=for-the-badge&logo=mysql&logoColor=white">
  <img src="https://img.shields.io/badge/minio-C72E49?style=for-the-badge&logo=minio&logoColor=white">
</div>

## ✨ Pratinjau Aplikasi

Berikut adalah preview dari Website Desa Karangasem dan Website Admin Desa Karangasem.

![Tampilan Halaman Website Desa Karangasem](https://i.postimg.cc/d0PYJtf6/websiteutama.png)
*Gambar 1: Tampilan Halaman Website Desa Karangasem.*

![Tampilan Halaman Website Admin Desa Karangasem](https://i.postimg.cc/KYDvVMYD/websiteadmin-foto.png)
*Gambar 2: Tampilan Halaman Website Admin Desa Karangasem.*

---

## 🚀 Instalasi

1.  **Download & Install Docker Desktop**  
    <a href="https://www.docker.com/products/docker-desktop/">
      <img src="https://i.postimg.cc/vZmNGz0w/docker-download.png" alt="Docker Desktop" style="height: 40px;">
    </a>
2.  **Clone Repositori Ini**  
    Buka terminal (Git Bash, Command Prompt, atau PowerShell) dan jalankan perintah berikut:
    ```bash
    git clone https://github.com/valselt/karangasem-cawas.git
    ```

3.  **Masuk Ke Direktori anda melalui terminal dengan menggunakan `cd`**  
    ```bash
    cd karangasem-cawas
    ```

4.  **Jalankan File Deploy**  
    Untuk Windows, Jalankan Ini
    ```bash
    deploy.bat
    ```
    Untuk MacOS/Linux, Jalankan Ini
    ```bash
    chmod +x deploy.sh
    ./deploy.sh
    ```
5.  **Masukkan Database**  
    Jangan Lupa Untuk Import Database [karangasem-fix.sql](https://github.com/valselt/karangasem-cawas/blob/main/karangasem-fix.sql) kedalam MySQL


## ⚡ Cara Menjalankan

### Docker  
1. Buka Terminal
2. Masuk Ke Direktori anda melalui terminal dengan menggunakan `cd` 
3. Ketik Perintah ini ketika Ingin menjalankan Container Docker  
    Untuk Windows, Jalankan Ini
    ```bash
    deploy.bat
    ```
    Untuk MacOS/Linux, Jalankan Ini
    ```bash
    chmod +x deploy.sh
    ./deploy.sh
    ```

4.  Dan Ketika Ingin Stop Container Docker
    Untuk Windows, Jalankan Ini
    ```bash
    stop.bat
    ```
    
    Untuk MacOS/Linux, Jalankan Ini
    ```bash
    sh stop.sh
    ```

### Website   
Pastikan Semua Kontainer Telah Berjalan dengan Sempurna.


1. Jalankan ini di browser untuk menjalankan Website Utama
    ```bash
    http://localhost:7891/
    ```

2. Jalankan ini di browser untuk menjalankan Website Admin
    ```bash
    http://localhost:7892/
    ```