# 🚀 Deployment Checklist - Railway HTTPS Fix

## ✅ Code Changes (SELESAI)

Perubahan code sudah dibuat:

- [x] **backend/app/Http/Middleware/TrustProxies.php** ← BARU
  - Middleware untuk trust Railway proxy
  - Mendeteksi HTTPS dengan benar

- [x] **backend/app/Providers/AppServiceProvider.php** ← UPDATED
  - Force HTTPS scheme di production
  - Auto redirect HTTP ke HTTPS

- [x] **backend/bootstrap/app.php** ← UPDATED
  - Aktifkan trust proxies
  - Support Railway networking

- [x] **backend/RAILWAY_ENV_VARIABLES.md** ← BARU
  - Dokumentasi environment variables
  - Panduan setup Railway

---

## 📦 Step 1: Push Code ke GitHub

```bash
# Pastikan di root project
cd "D:\GitHubPBL-HR Sem2\PBL-HR-Yayasan-Darussalam-Batam"

# Add semua perubahan
git add .

# Commit dengan pesan yang jelas
git commit -m "Fix: Enable HTTPS support for Railway deployment

- Add TrustProxies middleware
- Force HTTPS in production
- Update bootstrap/app.php for Railway proxy
- Add environment variables documentation"

# Push ke GitHub
git push origin main
```

---

## ⚙️ Step 2: Update Environment Variables di Railway

### 2.1 Login ke Railway Dashboard
1. Buka https://railway.app
2. Login dengan akun Anda
3. Pilih project **HR Darussalam** (atau nama project Anda)
4. Klik service **Laravel/Backend**
5. Klik tab **"Variables"**

### 2.2 Tambahkan Variables Berikut

**Copy paste satu per satu:**

```env
APP_ENV=production
```

```env
APP_DEBUG=false
```

```env
APP_URL=https://your-domain.up.railway.app
```
⚠️ **GANTI** `your-domain.up.railway.app` dengan domain Railway Anda!

```env
TRUST_PROXIES=*
```

```env
SESSION_SECURE_COOKIE=true
```

```env
SESSION_SAME_SITE=lax
```

### 2.3 Cara Mendapatkan Domain Railway Anda

1. Di Railway Dashboard → Laravel Service
2. Tab **"Settings"** → section **"Domains"**
3. Copy domain yang ada, contoh: `hr-production.up.railway.app`
4. Tambahkan `https://` di depannya untuk `APP_URL`

---

## 🔄 Step 3: Redeploy (Otomatis)

Railway akan **otomatis redeploy** setelah:
- Push code ke GitHub (jika connect ke GitHub)
- Update environment variables

**Pantau deployment:**
1. Di Railway Dashboard → Tab **"Deployments"**
2. Tunggu hingga status **"Success"** (hijau ✅)
3. Biasanya 2-5 menit

---

## ✅ Step 4: Verifikasi & Testing

### 4.1 Test HTTPS URL

Buka browser dan akses:
```
https://your-domain.up.railway.app
```

**Yang harus Anda lihat:**
- ✅ Ikon gembok 🔒 di address bar
- ✅ "Connection is secure" / "Secure"
- ✅ Tidak ada warning SSL

### 4.2 Test Login Form

1. Buka halaman login:
   ```
   https://your-domain.up.railway.app/login
   ```

2. Isi username & password

3. Klik "Login"

**Yang harus terjadi:**
- ❌ **TIDAK ADA** popup "Form is not secure"
- ✅ Login berhasil
- ✅ Session bekerja normal
- ✅ Redirect ke dashboard

### 4.3 Test Browser Console

Tekan **F12** → Tab **"Console"**

**Cek apakah ada error:**
- ❌ Tidak boleh ada "Mixed Content" error
- ❌ Tidak boleh ada SSL/Certificate error
- ✅ Semua resources load dengan HTTPS

---

## 🔍 Troubleshooting

### ❌ Masih Ada Warning "Form is not secure"

**Solusi:**
1. **Hard refresh browser:**
   - Windows: `Ctrl + Shift + R`
   - Mac: `Cmd + Shift + R`

2. **Clear browser cache:**
   - Chrome: Settings → Privacy → Clear browsing data
   - Atau buka **Incognito/Private mode**

3. **Cek APP_URL di Railway:**
   ```
   Pastikan: APP_URL=https://... (dengan https)
   ```

4. **Cek logs Railway:**
   ```bash
   railway logs
   ```
   Cari error terkait SSL/proxy

### ❌ Error "Too many redirects"

**Solusi:**
```env
# Pastikan di Railway Variables:
APP_ENV=production
TRUST_PROXIES=*
```

### ❌ Error "419 Page Expired"

**Solusi via Railway CLI:**
```bash
railway run php artisan config:clear
railway run php artisan cache:clear
railway run php artisan view:clear
```

### ❌ Session tidak persist / logout terus

**Solusi - Tambahkan di Railway Variables:**
```env
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=.railway.app
```

---

## 📊 Expected Final Result

### ✅ Security Indicators

**Browser Address Bar:**
```
🔒 https://your-domain.up.railway.app
     ↑ Gembok hijau = Secure!
```

**SSL Certificate:**
- Valid ✅
- Issued by: Let's Encrypt (Railway default)
- No warnings ✅

**Form Submissions:**
- No "Form is not secure" warning ✅
- All data encrypted in transit ✅

### ✅ Application Behavior

- Login works ✅
- Session persists ✅
- No CSRF errors ✅
- No mixed content errors ✅
- All redirects use HTTPS ✅

---

## 🎯 Quick Reference

### Railway CLI Commands

```bash
# View logs
railway logs

# View variables
railway variables

# Clear cache
railway run php artisan config:cache
railway run php artisan route:cache

# SSH into container
railway shell
```

### Important URLs

- Railway Dashboard: https://railway.app
- Your App (HTTPS): https://your-domain.up.railway.app
- Laravel Logs: Railway Dashboard → Deployments → View Logs

---

## 📝 Notes

- **Development (Local):** Tetap bisa pakai HTTP (http://localhost:8000)
- **Production (Railway):** Otomatis HTTPS, lebih aman
- **SSL Certificate:** Gratis dari Railway (Let's Encrypt)
- **Auto Renewal:** Railway handle certificate renewal otomatis

---

## 🎉 Success Criteria

Anda berhasil jika:

- [x] Code changes sudah di-push
- [x] Railway variables sudah ditambahkan
- [x] Deployment success (hijau)
- [x] Browser menampilkan gembok 🔒
- [x] Login tanpa warning "Form is not secure"
- [x] Session bekerja normal
- [x] Tidak ada SSL/Mixed Content error

---

**Good luck with deployment! 🚀**

Jika masih ada masalah, screenshot error dan saya akan bantu troubleshoot.

