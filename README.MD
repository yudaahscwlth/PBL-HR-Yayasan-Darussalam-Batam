# 🏢 HR Yayasan Darussalam

Sistem Manajemen Sumber Daya Manusia (HRIS) untuk Yayasan Darussalam yang dibangun dengan teknologi modern dan responsive design.

## 📋 Deskripsi Project

HR Yayasan Darussalam adalah aplikasi web berbasis Progressive Web App (PWA) yang dirancang untuk mengelola sistem kepegawaian di lingkungan Yayasan Darussalam. Aplikasi ini menyediakan fitur-fitur lengkap untuk manajemen karyawan, absensi, cuti, penilaian kinerja, dan kuisioner.

## 👥 Tim Pengembang

### Anggota Kelompok

- **Rafles Yuda Stevenses Nababan** – 3312401062
- **Deny Riansyah** – 3312401064
- **Zidan Hamana Putra** – 3312401084
- **Zidan Masadita Pramudia** – 3312401083

### Manajer Proyek

- **Muchamad Fajri Amirul Nasrullah, S.ST., M.Sc**

## 🛠️ Teknologi yang Digunakan

### Frontend

- **Next.js 15.5.4** - React Framework dengan App Router
- **React 19.1.0** - Library JavaScript untuk UI
- **TypeScript** - Type-safe JavaScript
- **Tailwind CSS** - Utility-first CSS framework
- **PWA** - Progressive Web App capabilities

### Backend

- **Laravel 12** - PHP Framework
- **PHP 8.2+** - Server-side scripting
- **MySQL** - Database management system
- **Laravel Sanctum** - API authentication

### Tools & Environment

- **Laragon** - Local development environment
- **Composer** - PHP dependency manager
- **npm** - Node.js package manager

## ✨ Fitur Utama

### 🔐 Sistem Autentikasi

- Login multi-role (Kepala Yayasan/Dept, HRD, Pegawai)
- Session management yang aman
- Responsive login interface

### 👤 Manajemen Pegawai

- Data pegawai lengkap dengan foto
- Manajemen departemen dan jabatan
- Sistem golongan pegawai

### ⏰ Sistem Absensi

- Absensi berbasis lokasi kantor
- Tracking jam kerja
- Laporan kehadiran

### 🏖️ Manajemen Cuti

- Pengajuan cuti online
- Approval workflow
- Berbagai jenis cuti

### 📊 Penilaian Kinerja

- Sistem penilaian berkala
- Kuisioner evaluasi
- Laporan performa

### 📱 PWA Features

- Installable sebagai aplikasi native
- Offline capabilities
- Push notifications ready
- Responsive design untuk semua device

## 🚀 Instalasi dan Setup

### Prerequisites

- PHP 8.2 atau lebih tinggi
- Composer
- Node.js 18+ dan npm
- MySQL/MariaDB
- Laragon (recommended untuk Windows)

### Backend Setup (Laravel)

1. **Clone repository dan masuk ke folder backend**

   ```bash
   cd backend
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Setup environment**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi database di `.env`**

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hr_darussalam
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Migrasi dan seeding database**

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Jalankan server**
   ```bash
   php artisan serve --port=8000
   ```

### Frontend Setup (Next.js)

1. **Masuk ke folder frontend**

   ```bash
   cd frontend
   ```

2. **Install dependencies**

   ```bash
   npm install
   ```

3. **Setup environment**

   ```bash
   echo "APP_URL=http://localhost:8000/api" > .env.local
   ```

4. **Jalankan development server**
   ```bash
   npm run dev
   ```

## 🌐 Akses Aplikasi

- **Frontend**: http://localhost:3001
- **Backend API**: http://localhost:8000/api
- **Login Page**: http://localhost:3001/login

### Default Login Credentials

- **Username**: `admin` | **Password**: `password` | **Role**: Kepala Yayasan
- **Username**: `hrd` | **Password**: `password` | **Role**: HRD
- **Username**: `pegawai` | **Password**: `password` | **Role**: Pegawai

## 📱 PWA Installation

### Android (Chrome)

1. Buka aplikasi di browser Chrome
2. Tap menu (⋮) → "Add to Home screen"
3. Konfirmasi instalasi

### iOS (Safari)

1. Buka aplikasi di Safari
2. Tap Share button → "Add to Home Screen"
3. Konfirmasi instalasi

## 📁 Struktur Project

```
HR_Darussalam/
├── backend/                 # Laravel Backend
│   ├── app/
│   │   ├── Http/Controllers/
│   │   ├── Models/
│   │   └── Providers/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/
├── frontend/               # Next.js Frontend
│   ├── app/
│   │   ├── components/
│   │   ├── login/
│   │   └── api/
│   ├── public/
│   └── server/
└── README.md
```

## 🎨 Design System

### Color Palette

- **Primary**: Sky Blue (#0c4a6e)
- **Secondary**: White (#ffffff)
- **Accent**: Gray (#6b7280)
- **Success**: Green (#059669)
- **Warning**: Yellow (#f59e0b)

### Typography

- **Headings**: Geist Sans (Bold/Extrabold)
- **Body**: Geist Sans (Regular/Medium)
- **Code**: Geist Mono

## 🔧 Konfigurasi

### Environment Variables

**Backend (.env)**

```env
APP_NAME="HR Darussalam"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hr_darussalam
DB_USERNAME=root
DB_PASSWORD=
```

**Frontend (.env.local)**

```env
APP_URL=http://localhost:8000/api
NEXT_PUBLIC_APP_NAME="HR Darussalam"
```

## 📊 Database Schema

### Tabel Utama

- `users` - Data pengguna sistem
- `pegawai` - Informasi pegawai
- `departemen` - Data departemen
- `jabatan` - Data jabatan
- `absensi` - Record kehadiran
- `cuti` - Data pengajuan cuti
- `penilaian` - Hasil penilaian kinerja
- `kuisioner` - Data kuisioner evaluasi

## 🚀 Deployment

### Production Build

**Frontend**

```bash
cd frontend
npm run build
npm start
```

**Backend**

```bash
cd backend
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🤝 Contributing

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📝 License

Project ini dikembangkan untuk keperluan akademik dan internal Yayasan Darussalam.

## 📞 Support

Untuk pertanyaan atau dukungan teknis, hubungi tim pengembang:

- **Email**: support@hrdarussalam.com
- **GitHub Issues**: [Create Issue](https://github.com/your-repo/issues)

## 🙏 Acknowledgments

- Yayasan Darussalam atas kepercayaan dan dukungannya
- Dosen pembimbing atas arahan dan bimbingannya
- Tim pengembang atas dedikasi dan kerja kerasnya

---

**© 2025 PBL 221 - HR Yayasan Darussalam**

_Dikembangkan dengan ❤️ oleh Tim PBL 221_





