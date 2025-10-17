# 📱 PWA Setup - HR Darussalam

## ✅ Apa yang Sudah Diimplementasikan

### 1. **Manifest.json** ✓

- File konfigurasi PWA di `/public/manifest.json`
- Metadata aplikasi (nama, deskripsi, icons, theme)
- App shortcuts untuk akses cepat
- Screenshots placeholder

### 2. **Service Worker** ✓

- File `/public/sw.js` untuk offline capability
- Caching strategy (cache-first)
- Offline fallback page
- Push notification support (future)

### 3. **App Icons** ✓

- Logo Darussalam sudah di-copy ke `/public/icons/`
- Placeholder icons berbagai ukuran (72x72 sampai 512x512)
- **PERLU OPTIMASI:** Generate icon proper dari logo original

### 4. **Metadata PWA** ✓

- PWA metadata di `layout.tsx`
- Theme color: #1e40af (biru Darussalam)
- Apple touch icons
- Viewport configuration

### 5. **Install Prompt** ✓

- Component `InstallPWA.tsx` untuk prompt install
- Auto-detect sudah install atau belum
- Tombol "Install Sekarang" dengan UI menarik

### 6. **Offline Page** ✓

- Halaman `/offline.html` untuk saat tidak ada koneksi
- Auto reload ketika online kembali

---

## 🚀 Cara Menjalankan

### Development Mode

```bash
cd frontend
npm run dev
```

Buka browser: `http://localhost:3000`

### Production Build

```bash
cd frontend
npm run build
npm start
```

---

## 📱 Cara Test PWA

### Di Desktop (Chrome/Edge):

1. Jalankan aplikasi (`npm run dev`)
2. Buka DevTools (F12)
3. Tab **Application** → **Manifest**
4. Klik **"Install"** di address bar (icon ➕)
5. Atau klik tombol **"Install Sekarang"** di popup

### Di Mobile (Chrome Android):

1. Akses aplikasi via browser Chrome
2. Popup "Add to Home Screen" akan muncul
3. Atau Menu → **"Add to Home Screen"**
4. Icon aplikasi akan muncul di home screen

### Verifikasi PWA:

1. DevTools → Application → Service Workers (check registered)
2. DevTools → Application → Manifest (check valid)
3. Lighthouse → Progressive Web App (run audit)

---

## 🎨 Icon Optimization (RECOMMENDED)

### Icons saat ini menggunakan logo yang di-resize browser.

Untuk hasil optimal, generate proper icons:

### **Opsi 1: Online Tool (Paling Mudah)**

1. Buka: https://www.pwabuilder.com/imageGenerator
2. Upload `/public/icons/logo-original.png`
3. Download hasil generation
4. Replace files di `/public/icons/`

### **Opsi 2: CLI Tool**

```bash
npm install -g pwa-asset-generator
pwa-asset-generator public/icons/logo-original.png public/icons --icon-only
```

### **Opsi 3: Manual (Photoshop/Canva)**

Generate ukuran:

- 72x72, 96x96, 128x128, 144x144
- 152x152, 192x192, 384x384, 512x512

---

## 🔧 Konfigurasi

### Ubah Theme Color

Edit `frontend/src/app/layout.tsx`:

```typescript
export const viewport: Viewport = {
  themeColor: "#1e40af", // ← Ubah warna di sini
  // ...
};
```

### Ubah App Name

Edit `/public/manifest.json`:

```json
{
  "name": "HR Darussalam", // ← Nama lengkap
  "short_name": "HR Darussalam" // ← Nama pendek
  // ...
}
```

---

## 📊 PWA Features Checklist

- ✅ **Installable** - Bisa install di home screen
- ✅ **Responsive** - Desktop & mobile friendly
- ✅ **Offline Capable** - Service worker + cache
- ✅ **Fast Loading** - Caching strategy
- ✅ **App-like** - Standalone display mode
- ✅ **Secure** - HTTPS required (production)
- ⏳ **Push Notifications** - Infrastructure ready, need API
- ⏳ **Background Sync** - Future enhancement

---

## 🔗 Integrasi dengan Laravel API

### Setup API Endpoint

Laravel backend sudah ada di `../backend` dengan:

- **Sanctum** untuk authentication
- **RESTful API** endpoints

### Hubungkan Next.js dengan Laravel:

1. Update `next.config.ts`:

```typescript
const nextConfig: NextConfig = {
  async rewrites() {
    return [
      {
        source: "/api/:path*",
        destination: "http://localhost:8000/api/:path*",
      },
    ];
  },
};
```

2. Buat API client di Next.js:

```typescript
// src/lib/api.ts
const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

export async function apiClient(endpoint: string, options = {}) {
  const response = await fetch(`${API_URL}${endpoint}`, {
    credentials: "include", // untuk Sanctum cookies
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    ...options,
  });
  return response.json();
}
```

---

## 🐛 Troubleshooting

### Service Worker tidak register?

- Check console untuk error
- Pastikan running di localhost atau HTTPS
- Clear cache dan reload

### Icon tidak muncul?

- Check `/public/icons/` folder ada files
- Verify manifest.json path benar
- Check icon sizes (minimal 192x192 & 512x512)

### Install prompt tidak muncul?

- Chrome: Check sudah memenuhi PWA criteria
- Buka DevTools → Application → Manifest (check warnings)
- Sudah install sebelumnya? Uninstall dulu

---

## 📚 Resources

- [Next.js PWA Documentation](https://nextjs.org/docs/app/building-your-application/configuring/progressive-web-apps)
- [PWA Builder](https://www.pwabuilder.com/)
- [Web.dev PWA Guide](https://web.dev/progressive-web-apps/)
- [Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)

---

## 👨‍💻 Next Steps

1. ✅ PWA sudah bisa diinstall
2. 🎨 **Optimize icons** (gunakan PWA Builder)
3. 📸 **Add screenshots** untuk app store like experience
4. 🔔 **Implement push notifications** (dengan Laravel backend)
5. 🔄 **Background sync** untuk offline actions
6. 🧪 **Testing** di berbagai devices
7. 🚀 **Deploy** ke production dengan HTTPS

---

**Happy Coding! 🎉**

