"use client";

import { useOfflineSync } from "@/hooks/useOfflineSync";

export default function PWADashboard() {
  const {
    isOnline,
    pendingActions,
    lastSyncTime,
    storageUsage,
    isSyncing,
    syncData,
    clearCache,
    refreshData,
  } = useOfflineSync();

  const formatBytes = (bytes: number) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
  };

  const handleClearCache = async () => {
    if (confirm("Apakah Anda yakin ingin menghapus semua data offline?")) {
      await clearCache();
      alert("Data offline telah dihapus");
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-4xl mx-auto px-4">
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <h1 className="text-2xl font-bold text-gray-900 mb-6">Status PWA</h1>
          
          {/* Connection Status */}
          <div className="mb-8">
            <h2 className="text-lg font-semibold text-gray-900 mb-4">Status Koneksi</h2>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className={`p-4 rounded-lg border-2 ${
                isOnline 
                  ? "border-green-500 bg-green-50" 
                  : "border-red-500 bg-red-50"
              }`}>
                <div className="flex items-center gap-3">
                  <div className={`w-3 h-3 rounded-full ${
                    isOnline ? "bg-green-500" : "bg-red-500"
                  }`}></div>
                  <div>
                    <p className="font-medium text-gray-900">
                      {isOnline ? "Online" : "Offline"}
                    </p>
                    <p className="text-sm text-gray-600">
                      {isOnline ? "Terkoneksi dengan server" : "Tidak ada koneksi internet"}
                    </p>
                  </div>
                </div>
              </div>

              <div className="p-4 rounded-lg border-2 border-blue-500 bg-blue-50">
                <div className="flex items-center gap-3">
                  <div className="w-3 h-3 rounded-full bg-blue-500"></div>
                  <div>
                    <p className="font-medium text-gray-900">
                      {pendingActions} Data Pending
                    </p>
                    <p className="text-sm text-gray-600">
                      Menunggu sinkronisasi
                    </p>
                  </div>
                </div>
              </div>

              <div className="p-4 rounded-lg border-2 border-purple-500 bg-purple-50">
                <div className="flex items-center gap-3">
                  <div className="w-3 h-3 rounded-full bg-purple-500"></div>
                  <div>
                    <p className="font-medium text-gray-900">
                      {isSyncing ? "Sinkronisasi..." : "Idle"}
                    </p>
                    <p className="text-sm text-gray-600">
                      Status sinkronisasi
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Storage Information */}
          <div className="mb-8">
            <h2 className="text-lg font-semibold text-gray-900 mb-4">Informasi Penyimpanan</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="p-4 rounded-lg border border-gray-200">
                <h3 className="font-medium text-gray-900 mb-2">Penggunaan Storage</h3>
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-gray-600">Terpakai:</span>
                    <span className="font-medium">{formatBytes(storageUsage.used)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Tersedia:</span>
                    <span className="font-medium">{formatBytes(storageUsage.available)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Persentase:</span>
                    <span className="font-medium">
                      {storageUsage.available > 0 
                        ? Math.round((storageUsage.used / storageUsage.available) * 100)
                        : 0}%
                    </span>
                  </div>
                </div>
              </div>

              <div className="p-4 rounded-lg border border-gray-200">
                <h3 className="font-medium text-gray-900 mb-2">Terakhir Sinkronisasi</h3>
                <p className="text-gray-600">
                  {lastSyncTime || "Belum ada sinkronisasi"}
                </p>
              </div>
            </div>
          </div>

          {/* Actions */}
          <div>
            <h2 className="text-lg font-semibold text-gray-900 mb-4">Aksi</h2>
            <div className="flex flex-col sm:flex-row gap-4">
              <button
                onClick={syncData}
                disabled={!isOnline || isSyncing}
                className="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {isSyncing ? "M Sinkronisasi..." : "Sinkronkan Sekarang"}
              </button>
              
              <button
                onClick={refreshData}
                className="px-6 py-3 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 transition-colors"
              >
                🔄 Refresh Status
              </button>
              
              <button
                onClick={() => window.location.href = "/pwa/offline"}
                className="px-6 py-3 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors"
              >
                📱 Lihat Mode Offline
              </button>
              
              <button
                onClick={handleClearCache}
                className="px-6 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors"
              >
                🗑️ Hapus Cache
              </button>
            </div>
          </div>
        </div>

        {/* Service Worker Status */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Service Worker Status</h2>
          <div className="space-y-4">
            <div className="flex items-center justify-between p-3 bg-gray-50 rounded">
              <span className="text-gray-700">Service Worker</span>
              <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                navigator.serviceWorker?.controller 
                  ? "bg-green-100 text-green-800" 
                  : "bg-yellow-100 text-yellow-800"
              }`}>
                {navigator.serviceWorker?.controller ? "Aktif" : "Tidak Aktif"}
              </span>
            </div>
            
            <div className="flex items-center justify-between p-3 bg-gray-50 rounded">
              <span className="text-gray-700">Push Notifications</span>
              <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                "Notification" in window 
                  ? "bg-green-100 text-green-800" 
                  : "bg-red-100 text-red-800"
              }`}>
                {"Notification" in window ? "Didukung" : "Tidak Didukung"}
              </span>
            </div>
            
            <div className="flex items-center justify-between p-3 bg-gray-50 rounded">
              <span className="text-gray-700">Background Sync</span>
              <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                "serviceWorker" in navigator && "SyncManager" in window 
                  ? "bg-green-100 text-green-800" 
                  : "bg-yellow-100 text-yellow-800"
              }`}>
                {"serviceWorker" in navigator && "SyncManager" in window ? "Didukung" : "Tidak Didukung"}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
