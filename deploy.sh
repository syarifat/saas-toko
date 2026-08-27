#!/bin/bash
set -e

RUN_MIGRATE=""

# Cek argumen CLI jika diberikan
if [ "$1" == "--migrate" ] || [ "$1" == "-m" ]; then
    RUN_MIGRATE="y"
elif [ "$1" == "--no-migrate" ] || [ "$1" == "-n" ]; then
    RUN_MIGRATE="n"
fi

# Jika tanpa argumen, tampilkan pertanyaan interaktif
if [ -z "$RUN_MIGRATE" ]; then
    read -p "❓ Jalankan database migration? (y/n) [default: n]: " choice
    case "$choice" in 
      y|Y|ya|YA ) RUN_MIGRATE="y";;
      * ) RUN_MIGRATE="n";;
    esac
fi

echo ""
echo "🚀 Memulai proses deployment..."

echo "📥 1. Menarik kode terbaru dari Git (main)..."
git pull origin main

echo "🧹 2. Membersihkan cache Laravel..."
docker compose exec app php artisan optimize:clear

if [ "$RUN_MIGRATE" == "y" ]; then
    echo "🗄️ 3. Menjalankan migrasi database (migrate)..."
    docker compose exec app php artisan migrate --force
else
    echo "⏩ 3. Melewati migrasi database (tanpa migrate)..."
fi

echo "📦 4. Membuild aset frontend (NPM)..."
docker compose exec app npm run build

echo "🐳 5. Merestart container Docker..."
docker compose restart

echo ""
echo "✅ Deployment selesai dengan sukses!"
