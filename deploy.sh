#!/usr/bin/env bash

# ==============================================================================
# Script Deployment Otomatis - SaaS Toko Multi-Tenant Modular
# ==============================================================================

set -e

echo "🚀 [1/7] Membangun dan menyalakan container Docker..."
docker compose down --remove-orphans
docker compose up -d --build

echo "⏳ [2/7] Menunggu koneksi database MySQL siap..."
until docker compose exec -T app php -r "
    try {
        new PDO('mysql:host=db;port=3306;dbname=saas_toko', 'root', 'root', [PDO::ATTR_TIMEOUT => 2]);
        exit(0);
    } catch (Throwable \$e) {
        exit(1);
    }
" 2>/dev/null; do
    echo "Menunggu database MySQL..."
    sleep 2
done
echo "✓ Database MySQL terhubung!"

echo "📦 [3/7] Menginstall dependency Composer & NPM..."
docker compose exec -T app composer install --no-interaction --prefer-dist --optimize-autoloader

if command -v npm &> /dev/null; then
    echo "Membangun asset frontend via host NPM..."
    npm run build
else
    echo "NPM host tidak ditemukan, melewati build host..."
fi

echo "🔗 [4/7] Membuat storage symlink & permission..."
docker compose exec -T app php artisan storage:link || true
docker compose exec -T app chmod -R 775 storage bootstrap/cache || true

echo "🗄️ [5/7] Menjalankan Database Migrations & Seeders..."
docker compose exec -T app php artisan migrate:fresh --seed --force

echo "🧪 [6/7] Menjalankan automated test suite..."
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan test --compact

echo "⚡ [7/7] Mengoptimasi cache konfigurasi dan view Laravel..."
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache

echo "=============================================================================="
echo "🎉 DEPLOYMENT SELESAI & BERHASIL 100%!"
echo "=============================================================================="
echo "🌐 URL Aplikasi     : http://localhost:8000"
echo "👤 Superadmin Email : superadmin@gmail.com"
echo "🔑 Superadmin Pass  : password"
echo "=============================================================================="
