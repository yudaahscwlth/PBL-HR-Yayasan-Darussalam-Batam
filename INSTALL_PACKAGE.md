# Instalasi Package PhpSpreadsheet

## Masalah
Error 500 saat download template Excel karena package `phpoffice/phpspreadsheet` belum terinstall.

## Solusi

### 1. Install Package
Jalankan perintah berikut di terminal:

```bash
cd backend
composer require phpoffice/phpspreadsheet
```

### 2. Jika sudah ada di composer.json
Jika package sudah ada di `composer.json` tapi belum terinstall, jalankan:

```bash
cd backend
composer install
```

atau

```bash
cd backend
composer update phpoffice/phpspreadsheet
```

### 3. Restart Server Laravel
Setelah package terinstall, restart server Laravel:

```bash
# Stop server (Ctrl+C)
php artisan serve
```

### 4. Verifikasi Instalasi
Untuk memverifikasi package sudah terinstall, jalankan:

```bash
cd backend
composer show phpoffice/phpspreadsheet
```

Atau cek di file `composer.json` apakah `phpoffice/phpspreadsheet` sudah ada di bagian `require`.

## Catatan
- Pastikan PHP versi 8.2 atau lebih tinggi
- Pastikan Composer sudah terinstall
- Jika masih error, cek log Laravel di `backend/storage/logs/laravel.log`
