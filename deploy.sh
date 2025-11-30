#!/bin/bash

# Warna text
GREEN='\033[0;32m'
CYAN='\033[0;36m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${CYAN}🚀 [LINUX/MAC] Memulai Deployment Project Desa...${NC}"

# --- 1. INFRASTRUKTUR ---
echo -e "${YELLOW}1. Menyalakan Database & Storage...${NC}"

cd database_desa && docker compose up -d && cd ..
cd minio_storage && docker compose up -d && cd ..

echo -e "⏳ Menunggu 10 detik agar Database siap..."
sleep 10

# --- 2. APLIKASI UTAMA (KARANGASEM) ---
echo -e "${YELLOW}2. Menyalakan Aplikasi Karangasem...${NC}"
cd karangasem
docker compose up -d --build

# PERUBAHAN PENTING: Cek folder di dalam 'src/vendor'
if [ ! -d "src/vendor" ]; then
    echo -e "${GREEN}   -> Folder 'src/vendor' tidak ditemukan. Menginstall AWS SDK...${NC}"
    # Kita panggil nama Container langsung: PHP-FPM
    docker exec PHP-FPM composer require aws/aws-sdk-php --no-interaction
else
    echo -e "   -> Folder 'src/vendor' ada. Skip install."
fi
cd ..

# --- 3. APLIKASI ADMIN (KARANGASEM ADMIN) ---
echo -e "${YELLOW}3. Menyalakan Admin Panel...${NC}"
cd karangasem_admin
docker compose up -d --build

# PERUBAHAN PENTING: Cek folder di dalam 'src/vendor'
if [ ! -d "src/vendor" ]; then
    echo -e "${GREEN}   -> Folder 'src/vendor' Admin tidak ditemukan. Menginstall AWS SDK...${NC}"
    # Kita panggil nama Container langsung: ADMIN-PHP-FPM
    docker exec ADMIN-PHP-FPM composer require aws/aws-sdk-php --no-interaction
else
    echo -e "   -> Folder 'src/vendor' Admin ada. Skip install."
fi
cd ..

echo -e "${CYAN}✅ Deployment Selesai!${NC}"