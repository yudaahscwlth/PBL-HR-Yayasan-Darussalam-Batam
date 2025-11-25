"use client";

import { useEffect, useState } from "react";
import { useAuthStore } from "@/store/authStore";

export default function OfflinePage() {
  const { user } = useAuthStore();
  const [isOnline, setIsOnline] = useState(false);
  const [lastSyncTime, setLastSyncTime] = useState<string>("");

  useEffect(() => {
    // Check online status
    setIsOnline(navigator.onLine);
    
    // Get last sync time from localStorage
    const lastSync = localStorage.getItem("lastSyncTime");
    if (lastSync) {
      setLastSyncTime(new Date(lastSync).toLocaleString("id-ID"));
    }

    const handleOnline = () => setIsOnline(true);
    const handleOffline = () => setIsOnline(false);

    window.addEventListener("online", handleOnline);
    window.addEventListener("offline", handleOffline);

    return () => {
      window.removeEventListener("online", handleOnline);
      window.removeEventListener("offline", handleOffline);
    };
  }, []);

  const handleRefresh = () => {
    window.location.reload();
  };

  const handleBackToHome = () => {
    window.location.href = "/";
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center w-20 h-20 bg-blue-600 rounded-full mb-4">
            <svg className="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
            </svg>
          </div>
          <h1 className="text-3xl font-bold text-gray-900 mb-2">HR Darussalam</h1>
          <div className="flex items-center justify-center gap-2">
            <div className={`w-3 h-3 rounded-full ${isOnline ? "bg-green-500" : "bg-red-500"}`}></div>
            <span className="text-lg font-medium text-gray-700">
              {isOnline ? "Online" : "Offline Mode"}
            </span>
          </div>
        </div>

        {/* User Info */}
        {user && (
          <div className="max-w-4xl mx-auto mb-8">
            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-4">Informasi Pengguna</h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p className="text-sm text-gray-500">Email</p>
                  <p className="font-medium text-gray-900">{user.email}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-500">Nama</p>
                  <p className="font-medium text-gray-900">{user.profile_pribadi?.nama_lengkap || 'Tidak tersedia'}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-500">Role</p>
                  <p className="font-medium text-gray-900 capitalize">{user.roles?.join(', ') || 'Tidak tersedia'}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-500">Terakhir Sync</p>
                  <p className="font-medium text-gray-900">{lastSyncTime || "Belum ada data"}</p>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* App Information */}
        <div className="max-w-4xl mx-auto">
          <div className="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-4">Tentang Aplikasi</h2>
            <div className="space-y-4">
              <div>
                <h3 className="font-medium text-gray-900 mb-2">Sistem Manajemen HR Darussalam</h3>
                <p className="text-gray-600">
                  Aplikasi HR Darussalam adalah sistem manajemen sumber daya manusia yang dirancang khusus untuk Yayasan Darussalam. 
                  Aplikasi ini membantu mengelola berbagai aspek HR termasuk absensi, cuti, evaluasi kinerja, dan data pegawai.
                </p>
              </div>
              
              <div>
                <h3 className="font-medium text-gray-900 mb-2">Fitur Utama</h3>
                <ul className="list-disc list-inside text-gray-600 space-y-1">
                  <li>Absensi check-in/check-out</li>
                  <li>Pengajuan dan verifikasi cuti</li>
                  <li>Evaluasi kinerja pegawai</li>
                  <li>Manajemen data pegawai</li>
                  <li>Manajemen departemen dan jabatan</li>
                  <li>Laporan dan rekapitulasi</li>
                </ul>
              </div>

              <div>
                <h3 className="font-medium text-gray-900 mb-2">Mode Offline</h3>
                <p className="text-gray-600">
                  Saat offline, Anda masih dapat mengakses data yang telah disimpan secara lokal. 
                  Beberapa fitur mungkin terbatas hingga koneksi internet tersedia kembali.
                </p>
              </div>
            </div>
          </div>

          {/* Quick Access */}
          <div className="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-4">Akses Cepat</h2>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              <button
                onClick={() => window.location.href = "/absensi"}
                className="p-4 bg-blue-50 rounded-lg text-center hover:bg-blue-100 transition-colors"
              >
                <div className="text-2xl mb-2">📅</div>
                <p className="text-sm font-medium text-gray-900">Absensi</p>
              </button>
              <button
                onClick={() => window.location.href = "/cuti"}
                className="p-4 bg-green-50 rounded-lg text-center hover:bg-green-100 transition-colors"
              >
                <div className="text-2xl mb-2">🏖️</div>
                <p className="text-sm font-medium text-gray-900">Cuti</p>
              </button>
              <button
                onClick={() => window.location.href = "/dashboard"}
                className="p-4 bg-purple-50 rounded-lg text-center hover:bg-purple-100 transition-colors"
              >
                <div className="text-2xl mb-2">📊</div>
                <p className="text-sm font-medium text-gray-900">Dashboard</p>
              </button>
              <button
                onClick={() => window.location.href = "/profile"}
                className="p-4 bg-orange-50 rounded-lg text-center hover:bg-orange-100 transition-colors"
              >
                <div className="text-2xl mb-2">👤</div>
                <p className="text-sm font-medium text-gray-900">Profile</p>
              </button>
            </div>
          </div>

          {/* Action Buttons */}
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <button
              onClick={handleRefresh}
              className="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors"
            >
              🔄 Refresh Halaman
            </button>
            {isOnline && (
              <button
                onClick={handleBackToHome}
                className="px-6 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors"
              >
                🏠 Kembali ke Beranda
              </button>
            )}
          </div>
        </div>

        {/* Footer */}
        <div className="text-center mt-12 text-gray-500 text-sm">
          <p>© 2025 Yayasan Darussalam - Sistem HR Management</p>
          <p className="mt-1">Versi 1.0.0</p>
        </div>
      </div>
    </div>
  );
}
