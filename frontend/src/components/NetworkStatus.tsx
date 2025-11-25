"use client";

import { useEffect, useState } from "react";
import { useOfflineSync } from "@/hooks/useOfflineSync";

export default function NetworkStatus() {
  const [showOfflineAlert, setShowOfflineAlert] = useState(false);
  const { isOnline, pendingActions, isSyncing, syncData } = useOfflineSync();

  useEffect(() => {
    if (!isOnline) {
      setShowOfflineAlert(true);
    } else {
      setShowOfflineAlert(false);
    }
  }, [isOnline]);

  const handleOfflineClick = () => {
    window.location.href = "/pwa/offline";
  };

  const handleSyncNow = async () => {
    await syncData();
  };

  if (!showOfflineAlert && isOnline && pendingActions === 0) return null;

  return (
    <div className={`fixed top-0 left-0 right-0 z-50 text-white px-4 py-3 shadow-lg transition-colors ${
      isOnline ? "bg-blue-500" : "bg-orange-500"
    }`}>
      <div className="max-w-7xl mx-auto flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="flex-shrink-0">
            {isOnline ? (
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            ) : (
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            )}
          </div>
          <div>
            <p className="font-medium">
              {isOnline ? "Koneksi Tersedia" : "Koneksi Terputus"}
            </p>
            <p className="text-sm text-blue-100">
              {isOnline 
                ? pendingActions > 0 
                  ? `${pendingActions} data menunggu sinkronisasi`
                  : "Aplikasi tersinkronisasi"
                : "Anda sedang offline. Beberapa fitur mungkin tidak tersedia."
              }
            </p>
          </div>
        </div>
        <div className="flex items-center gap-2">
          {isOnline && pendingActions > 0 && (
            <button
              onClick={handleSyncNow}
              disabled={isSyncing}
              className="bg-white text-blue-500 px-4 py-2 rounded-md font-medium text-sm hover:bg-blue-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isSyncing ? "M Sinkronisasi..." : "Sinkronkan Sekarang"}
            </button>
          )}
          {!isOnline && (
            <button
              onClick={handleOfflineClick}
              className="bg-white text-orange-500 px-4 py-2 rounded-md font-medium text-sm hover:bg-orange-50 transition-colors"
            >
              Lihat Mode Offline
            </button>
          )}
          <button
            onClick={() => setShowOfflineAlert(false)}
            className="text-white hover:text-blue-200 text-xl"
          >
            ✕
          </button>
        </div>
      </div>
    </div>
  );
}
