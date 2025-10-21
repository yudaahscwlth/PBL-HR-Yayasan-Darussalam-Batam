# HR Yayasan Darussalam - PWA System

Sistem Manajemen Sumber Daya Manusia (HR) untuk Yayasan Darussalam yang dibangun dengan teknologi modern menggunakan Laravel sebagai backend dan Next.js sebagai frontend dengan dukungan PWA (Progressive Web App).

## 🚀 Fitur Utama

### Authentication & Authorization

- ✅ **Role-based Authentication** - Login berdasarkan role pengguna
- ✅ **JWT Token Management** - Menggunakan Laravel Sanctum
- ✅ **Protected Routes** - Akses halaman berdasarkan role
- ✅ **Auto Token Refresh** - Refresh token otomatis

### User Management

- ✅ **Multi-role System** - 8 role berbeda (superadmin, kepala yayasan, direktur pendidikan, kepala hrd, staff hrd, kepala departemen, kepala sekolah, tenaga pendidik)
- ✅ **Profile Management** - Manajemen profil pribadi dan pekerjaan
- ✅ **Permission System** - Sistem permission berdasarkan role

### Dashboard

- ✅ **Admin Dashboard** - Dashboard untuk superadmin, kepala yayasan, direktur pendidikan
- ✅ **HRD Dashboard** - Dashboard untuk kepala hrd dan staff hrd
- ✅ **Employee Dashboard** - Dashboard untuk pegawai

### PWA Features

- ✅ **Installable** - Bisa diinstall di home screen
- ✅ **Offline Capable** - Bekerja offline dengan service worker
- ✅ **Responsive Design** - Desktop dan mobile friendly
- ✅ **Push Notifications** - Infrastructure ready

## 🛠️ Teknologi yang Digunakan

### Backend

- **Laravel 11** - PHP Framework
- **Laravel Sanctum** - API Authentication
- **Spatie Permission** - Role & Permission Management
- **MySQL** - Database
- **RESTful API** - API Architecture

### Frontend

- **Next.js 15** - React Framework
- **TypeScript** - Type Safety
- **Tailwind CSS** - Styling
- **Zustand** - State Management
- **Axios** - HTTP Client
- **PWA** - Progressive Web App

## 📱 Role & Permission

### Admin Roles

- **superadmin** - Full system access
- **kepala yayasan** - Yayasan head access
- **direktur pendidikan** - Education director access

### HRD Roles

- **kepala hrd** - HRD head access
- **staff hrd** - HRD staff access

### Employee Roles

- **kepala departemen** - Department head access
- **kepala sekolah** - School principal access
- **tenaga pendidik** - Teacher access

## 🚀 Quick Start

### Prerequisites

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL/MariaDB

### Backend Setup

```bash
cd backend
composer install
cp env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve --host=0.0.0.0 --port=8000
```

### Frontend Setup

```bash
cd frontend
npm install
cp env.example .env.local
npm run dev
```

## 📋 Test Accounts

### Admin

- **Superadmin**: `superadmin_kantor_1@gmail.com` / `password`
- **Kepala Yayasan**: `kepalayayasan_kantor_1@gmail.com` / `password`

### HRD

- **Kepala HRD**: `kepalahrd_kantor_1@gmail.com` / `password`
- **Staff HRD**: `staffhrd_kantor_1@gmail.com` / `password`

### Employee

- **Kepala Departemen**: `kepaladepartemen_kantor_1@gmail.com` / `password`
- **Tenaga Pendidik**: `tenagapendidik_kantor_1@gmail.com` / `password`

## 🔗 API Endpoints

### Authentication

- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/me` - Get current user

### Users

- `GET /api/users` - Get all users
- `GET /api/users/{id}` - Get user by ID

### Profile

- `GET /api/profile/personal` - Get personal profile
- `PUT /api/profile/personal` - Update personal profile

### Attendance

- `POST /api/attendance/checkin` - Check in
- `POST /api/attendance/checkout` - Check out

### Leave

- `GET /api/leave` - Get leave requests
- `POST /api/leave` - Create leave request

## 📱 PWA Features

### Installation

1. Open app in Chrome/Edge
2. Click install button in address bar
3. Or use "Add to Home Screen" on mobile

### Offline Support

- Service worker caches essential files
- Offline page for no connection
- Auto-reload when online

### Mobile Experience

- Responsive design
- Touch-friendly interface
- Native app-like experience

## 🏗️ Project Structure

```
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   ├── Models/
│   │   └── Policies/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/api.php
├── frontend/               # Next.js PWA
│   ├── src/
│   │   ├── app/
│   │   ├── components/
│   │   ├── lib/
│   │   ├── store/
│   │   └── types/
│   └── public/
└── docs/                   # Documentation
```

## 🔧 Development

### Backend Development

```bash
cd backend
php artisan serve --host=0.0.0.0 --port=8000
php artisan migrate:fresh --seed
```

### Frontend Development

```bash
cd frontend
npm run dev
npm run build
npm start
```

### Testing

```bash
# Backend tests
cd backend
php artisan test

# Frontend tests
cd frontend
npm test
```

## 📚 Documentation

- [Setup Guide](SETUP.md) - Detailed setup instructions
- [Integration Test](INTEGRATION_TEST.md) - Testing guide
- [PWA Setup](frontend/PWA-SETUP.md) - PWA configuration

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## 📄 License

This project is licensed under the MIT License.

## 👥 Team

- **Backend**: Laravel API Development
- **Frontend**: Next.js PWA Development
- **Database**: MySQL Schema Design
- **DevOps**: Deployment & CI/CD

## 📞 Support

For issues and questions, please contact the development team.

---

**Built with ❤️ for Yayasan Darussalam**

