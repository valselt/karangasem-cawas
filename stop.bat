echo 🛑 Mematikan semua container...

cd database_desa
docker compose down
cd ..

cd minio_storage
docker compose down
cd ..

cd karangasem
docker compose down
cd ..

cd karangasem_admin
docker compose down
cd ..

echo ✔ Semua container berhasil dimatikan.
pause
