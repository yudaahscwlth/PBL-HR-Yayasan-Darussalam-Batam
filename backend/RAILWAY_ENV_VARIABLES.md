# Environment Variables untuk Railway Deployment

## ⚙️ Variables yang Harus Ditambahkan di Railway Dashboard

Setelah push code, login ke **Railway Dashboard** → **Project Anda** → **Laravel Service** → **Variables**

Tambahkan/Update variables berikut:

---

## 🔒 HTTPS & Security

```env
# Application Environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.up.railway.app

# Trust Proxies (untuk HTTPS support)
TRUST_PROXIES=*

# Session Security (penting untuk HTTPS)
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

⚠️ **PENTING:** 
- Ganti `your-domain.up.railway.app` dengan **domain Railway Anda yang sebenarnya**!
- Pastikan URL menggunakan **https://** (dengan 's')

---

## 📋 Variables Lain yang Diperlukan

### Database (Biasanya sudah auto-set oleh Railway)
```env
DB_CONNECTION=mysql
DB_HOST=${MYSQLHOST}
DB_PORT=${MYSQLPORT}
DB_DATABASE=${MYSQLDATABASE}
DB_USERNAME=${MYSQLUSER}
DB_PASSWORD=${MYSQLPASSWORD}
```

### Cache & Queue
```env
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### Logging
```env
LOG_CHANNEL=stack
LOG_LEVEL=error
```

---

## 🚀 Cara Menambahkan di Railway

### Metode 1: Via Railway Dashboard (Web)

1. Buka **Railway Dashboard**
2. Pilih **Project** Anda
3. Klik **Laravel Service** (backend)
4. Klik tab **"Variables"**
5. Klik **"+ New Variable"**
6. Tambahkan satu per satu:
   - Variable name: `APP_ENV`
   - Value: `production`
   - Klik **"Add"**
7. Ulangi untuk semua variables di atas

### Metode 2: Via Railway CLI (Lebih Cepat)

```bash
# Login
railway login

# Link project
railway link

# Set variables
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false
railway variables set APP_URL=https://your-domain.up.railway.app
railway variables set TRUST_PROXIES='*'
railway variables set SESSION_SECURE_COOKIE=true
railway variables set SESSION_SAME_SITE=lax
```

---

## ✅ Verifikasi Variables

Setelah ditambahkan, verifikasi dengan:

```bash
# Via Railway CLI
railway variables

# Atau cek di Railway Dashboard → Variables tab
```

---

## 🔍 Cara Mendapatkan Domain Railway Anda

1. Buka **Railway Dashboard** → **Laravel Service**
2. Klik tab **"Settings"** atau **"Deployments"**
3. Cari bagian **"Domains"** atau **"Public Networking"**
4. Copy domain yang ditampilkan, contoh:
   ```
   hr-sem-2-pribadi-production.up.railway.app
   ```
5. Tambahkan **https://** di depannya untuk `APP_URL`:
   ```
   APP_URL=https://hr-sem-2-pribadi-production.up.railway.app
   ```

---

## ⚠️ Penting!

Setelah menambahkan/update variables:

1. **Railway akan otomatis redeploy** aplikasi
2. Tunggu hingga deployment selesai (status hijau ✅)
3. Test aplikasi dengan HTTPS URL
4. Pastikan tidak ada lagi warning "Form is not secure"

---

## 🎯 Expected Result

Setelah semua variables ditambahkan dan deployment berhasil:

✅ **URL otomatis HTTPS:**
```
https://your-domain.up.railway.app
```

✅ **Browser menampilkan:**
- Ikon gembok hijau 🔒
- "Connection is secure"
- Tidak ada warning saat submit form

✅ **Session & Cookie bekerja dengan baik:**
- Login berhasil
- Session persist
- Tidak ada error 419

---

**Status Checklist:**
- [x] Code changes sudah dibuat
- [ ] Push code ke GitHub
- [ ] Tambahkan environment variables di Railway
- [ ] Tunggu redeploy selesai
- [ ] Test dengan HTTPS

