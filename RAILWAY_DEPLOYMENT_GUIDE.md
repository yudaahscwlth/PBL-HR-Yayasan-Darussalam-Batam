# Panduan Deployment Railway - Tanpa Auto Migration

## Cara Menonaktifkan Migrasi Otomatis

Railway by default menjalankan migrasi otomatis saat deploy. Berikut cara menonaktifkannya:

### Metode 1: Menggunakan nixpacks.toml (✅ Sudah Dikonfigurasi)

File `nixpacks.toml` sudah dibuat di root project. File ini mengontrol proses build Railway dan **TIDAK** menjalankan migrasi otomatis.

**Yang dilakukan:**
- ✅ Install dependencies
- ✅ Cache config, route, dan view
- ❌ **TIDAK** menjalankan `php artisan migrate`

### Metode 2: Menggunakan Environment Variable di Railway Dashboard

1. Buka Railway Dashboard
2. Pilih service Anda
3. Pergi ke tab **Variables**
4. Tambahkan environment variable:
   ```
   NIXPACKS_NO_MUSL=1
   ```

### Metode 3: Menggunakan railway.json (Alternatif)

Jika `nixpacks.toml` tidak bekerja, buat file `railway.json` di root project:

```json
{
  "build": {
    "builder": "NIXPACKS",
    "buildCommand": "cd backend && composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache && php artisan view:cache"
  },
  "deploy": {
    "startCommand": "cd backend && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}",
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  }
}
```

## Cara Menjalankan Migrasi Manual di Railway

Jika Anda perlu menjalankan migrasi, gunakan Railway CLI:

### 1. Install Railway CLI
```bash
npm i -g @railway/cli
```

### 2. Login ke Railway
```bash
railway login
```

### 3. Link ke Project
```bash
railway link
```

### 4. Jalankan Migrasi
```bash
railway run php artisan migrate
```

### 5. Atau masuk ke Shell Railway
```bash
railway shell
cd backend
php artisan migrate
```

## Troubleshooting

### Railway masih menjalankan migrasi?

Jika Railway masih menjalankan migrasi otomatis:

1. **Periksa composer.json** - Pastikan tidak ada `post-install-cmd` yang menjalankan migrasi
2. **Periksa Environment Variables** - Pastikan tidak ada variable yang trigger migrasi
3. **Deploy Ulang** - Setelah menambahkan `nixpacks.toml`, deploy ulang project

### Cara melihat logs Railway

```bash
railway logs
```

## Rekomendasi untuk Production

### ✅ **Yang Sebaiknya Dilakukan:**
1. Deploy aplikasi tanpa auto migration
2. Verifikasi aplikasi berjalan dengan baik
3. Jalankan migrasi manual menggunakan Railway CLI
4. Monitor logs untuk memastikan tidak ada error

### ❌ **Yang Sebaiknya Dihindari:**
1. Auto migration di production (bisa menyebabkan downtime)
2. Running migration tanpa backup database terlebih dahulu
3. Deploy tanpa test di local/staging terlebih dahulu

## Backup Database Sebelum Migrasi

Selalu backup database sebelum menjalankan migrasi di production:

```bash
# Dari Railway shell
railway shell
cd backend
php artisan db:backup  # jika ada package backup
# atau export manual dari Railway dashboard
```

## Alternative: Import Database Langsung

Jika Anda sudah memiliki database yang sudah siap (dari `db/dummyhr.sql`):

1. Buka Railway Dashboard
2. Pilih PostgreSQL/MySQL service
3. Klik **Data** tab
4. Import file SQL langsung
5. Deploy aplikasi tanpa menjalankan migrasi

## Environment Variables yang Diperlukan

Pastikan environment variables berikut sudah diset di Railway:

```env
APP_NAME=HR-Darussalam
APP_ENV=production
APP_KEY=base64:... # Generate dengan php artisan key:generate
APP_DEBUG=false
APP_URL=https://your-app.railway.app

DB_CONNECTION=mysql # atau pgsql
DB_HOST=... # Auto set oleh Railway
DB_PORT=... # Auto set oleh Railway
DB_DATABASE=... # Auto set oleh Railway
DB_USERNAME=... # Auto set oleh Railway
DB_PASSWORD=... # Auto set oleh Railway

# Optional
LOG_CHANNEL=stack
LOG_LEVEL=error
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

## Kesimpulan

Dengan konfigurasi `nixpacks.toml` yang sudah dibuat, Railway **TIDAK AKAN** menjalankan migrasi otomatis. Anda memiliki kontrol penuh kapan migrasi dijalankan menggunakan Railway CLI.

**Next Steps:**
1. ✅ Commit file `nixpacks.toml`
2. ✅ Push ke repository
3. ✅ Railway akan deploy tanpa migrasi otomatis
4. ✅ Jalankan migrasi manual jika diperlukan

