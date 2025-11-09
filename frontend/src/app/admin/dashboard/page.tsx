"use client";

import { useState, useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import BottomNavbar from "@/components/BottomNavbar";
import AccessControl from "@/components/AccessControl";

export default function AdminDashboard() {
  const { user } = useAuthStore();
  const router = useRouter();
  const [attendanceStatus, setAttendanceStatus] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [attendanceLoading, setAttendanceLoading] = useState(false);
  const [currentLocation, setCurrentLocation] = useState<{ latitude: number; longitude: number; accuracy?: number } | null>(null);
  const [gpsStatus, setGpsStatus] = useState<string>("");
  const [lastToastTime, setLastToastTime] = useState<number>(0);

  useEffect(() => {
    checkAttendanceStatus();
  }, []);

  const checkAttendanceStatus = async () => {
    try {
      setIsLoading(true);
      const today = new Date().toISOString().split("T")[0];
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/attendance/status?date=${today}`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
          "Content-Type": "application/json",
        },
      });

      if (response.ok) {
        const data = await response.json();
        setAttendanceStatus(data.status);
      }
    } catch (error) {
      console.error("Error checking attendance status:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const getCurrentLocation = (): Promise<{ latitude: number; longitude: number; accuracy?: number }> => {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        reject(new Error("Geolocation is not supported by this browser"));
        return;
      }

      const options = {
        enableHighAccuracy: true,
        timeout: 25000,
        maximumAge: 0,
      };

      navigator.geolocation.getCurrentPosition(
        (position) => {
          const location = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
          };
          setCurrentLocation(location);
          setGpsStatus(`GPS: ${location.accuracy?.toFixed(0)}m`);
          resolve(location);
        },
        (error) => {
          console.error("GPS Error:", error);
          setGpsStatus("GPS Error");
          reject(error);
        },
        options
      );
    });
  };

  const showToast = (message: string, type: "success" | "error" | "warning" | "info" = "info") => {
    const now = Date.now();
    if (now - lastToastTime < 2000) return; // Debounce

    setLastToastTime(now);

    const toast = document.createElement("div");
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium max-w-sm ${type === "success" ? "bg-green-500" : type === "error" ? "bg-red-500" : type === "warning" ? "bg-yellow-500" : "bg-blue-500"}`;

    const icon = type === "success" ? "✅" : type === "error" ? "❌" : type === "warning" ? "⚠️" : "ℹ️";
    toast.innerHTML = `${icon} ${message}`;

    document.body.appendChild(toast);

    setTimeout(() => {
      toast.remove();
    }, 5000);
  };

  const handleCheckIn = async () => {
    try {
      setAttendanceLoading(true);
      setGpsStatus("Mendeteksi lokasi...");

      const location = await getCurrentLocation();

      if (location.accuracy && location.accuracy > 100) {
        const confirmed = window.confirm(
          `Akurasi GPS rendah (${location.accuracy.toFixed(0)}m). ` +
            `Toleransi maksimal: 100m. ` +
            `Selisih: ${(location.accuracy - 100).toFixed(0)}m. ` +
            `\n\nTips: Pindah ke area terbuka atau dekat jendela untuk sinyal GPS yang lebih baik. ` +
            `Lanjutkan absensi?`
        );

        if (!confirmed) {
          showToast("Absensi dibatalkan karena akurasi GPS rendah", "warning");
          return;
        }
      }

      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/attendance/checkin`, {
        method: "POST",
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          latitude_in: location.latitude,
          longitude_in: location.longitude,
        }),
      });

      const data = await response.json();

      if (response.ok) {
        setAttendanceStatus("checked_in");
        showToast("Check-in berhasil!", "success");
        setGpsStatus("");
      } else {
        if (data.message?.includes("luar area kerja")) {
          showToast("Anda berada di luar area kerja. Silakan pindah ke lokasi yang tepat.", "error");
        } else {
          showToast(data.message || "Gagal melakukan check-in", "error");
        }
      }
    } catch (error) {
      console.error("Check-in error:", error);
      if (error instanceof Error && error.message.includes("GPS")) {
        showToast("Gagal mendapatkan lokasi GPS. Pastikan izin lokasi diaktifkan.", "error");
      } else {
        showToast("Gagal melakukan check-in. Silakan coba lagi.", "error");
      }
    } finally {
      setAttendanceLoading(false);
      setGpsStatus("");
    }
  };

  const handleCheckOut = async () => {
    try {
      setAttendanceLoading(true);
      setGpsStatus("Mendeteksi lokasi...");

      const location = await getCurrentLocation();

      if (location.accuracy && location.accuracy > 100) {
        const confirmed = window.confirm(
          `Akurasi GPS rendah (${location.accuracy.toFixed(0)}m). ` +
            `Toleransi maksimal: 100m. ` +
            `Selisih: ${(location.accuracy - 100).toFixed(0)}m. ` +
            `\n\nTips: Pindah ke area terbuka atau dekat jendela untuk sinyal GPS yang lebih baik. ` +
            `Lanjutkan absensi?`
        );

        if (!confirmed) {
          showToast("Absensi dibatalkan karena akurasi GPS rendah", "warning");
          return;
        }
      }

      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/attendance/checkout`, {
        method: "POST",
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          latitude_out: location.latitude,
          longitude_out: location.longitude,
        }),
      });

      const data = await response.json();

      if (response.ok) {
        setAttendanceStatus("checked_out");
        showToast("Check-out berhasil!", "success");
        setGpsStatus("");
      } else {
        if (data.message?.includes("luar area kerja")) {
          showToast("Anda berada di luar area kerja. Silakan pindah ke lokasi yang tepat.", "error");
        } else {
          showToast(data.message || "Gagal melakukan check-out", "error");
        }
      }
    } catch (error) {
      console.error("Check-out error:", error);
      if (error instanceof Error && error.message.includes("GPS")) {
        showToast("Gagal mendapatkan lokasi GPS. Pastikan izin lokasi diaktifkan.", "error");
      } else {
        showToast("Gagal melakukan check-out. Silakan coba lagi.", "error");
      }
    } finally {
      setAttendanceLoading(false);
      setGpsStatus("");
    }
  };

  const getCurrentDate = () => {
    const now = new Date();
    const options: Intl.DateTimeFormatOptions = {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    };
    return now.toLocaleDateString("id-ID", options);
  };

  return (
    <AccessControl allowedRoles={["superadmin"]}>
      <div className="min-h-screen bg-gray-100 pb-28">
        {/* Header */}
        <div className="bg-[#1e4d8b] px-5 pt-3 pb-16 text-white rounded-b-3xl">
          <div className="flex items-center justify-between mb-1">
            <div className="flex-1">
              <p className="text-sm opacity-90">Selamat Datang,</p>
              <h1 className="text-xl font-bold">{user?.profile_pribadi?.nama_lengkap || user?.email}</h1>
              <div className="flex items-center mt-2">
                <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span className="text-sm">{getCurrentDate()}</span>
              </div>
            </div>
            <div className="w-12 h-12 bg-white rounded-full flex items-center justify-center">
              <svg className="w-6 h-6 text-[#1e4d8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
          </div>
        </div>

        {/* Attendance Buttons */}
        <div className="px-5 -mt-8">
          <div className="flex gap-4">
            <button
              onClick={handleCheckIn}
              disabled={attendanceStatus === "checked_in" || attendanceLoading}
              className="flex-1 bg-white border-2 border-[#1e4d8b] rounded-lg p-4 flex items-center justify-center shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg className="w-6 h-6 text-[#1e4d8b] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              <span className="text-[#1e4d8b] font-medium">{attendanceStatus === "checked_in" ? "Sudah Masuk" : "Absen Masuk"}</span>
            </button>
            <button
              onClick={handleCheckOut}
              disabled={attendanceStatus !== "checked_in" || attendanceLoading}
              className="flex-1 bg-white border-2 border-[#1e4d8b] rounded-lg p-4 flex items-center justify-center shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg className="w-6 h-6 text-[#1e4d8b] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              <span className="text-[#1e4d8b] font-medium">{attendanceStatus === "checked_out" ? "Sudah Pulang" : "Absen Pulang"}</span>
            </button>
          </div>
        </div>

        {/* GPS Status */}
        {gpsStatus && (
          <div className="px-5 mt-4">
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-3">
              <div className="flex items-center">
                <svg className="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span className="text-blue-800 text-sm font-medium">{gpsStatus}</span>
              </div>
            </div>
          </div>
        )}

        {/* Dashboard Cards */}
        <div className="px-5 mt-6">
          <div className="grid grid-cols-2 gap-4">
            <div className="bg-white rounded-2xl shadow-md p-6">
              <div className="flex items-center mb-4">
                <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-3">
                  <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <h3 className="text-lg font-bold text-gray-800">Status Absensi</h3>
                  <p className="text-sm text-gray-600">Hari ini</p>
                </div>
              </div>
              <div className="text-2xl font-bold text-green-600">{attendanceStatus === "checked_in" ? "Masuk" : attendanceStatus === "checked_out" ? "Pulang" : "Belum Absen"}</div>
            </div>

            <div className="bg-white rounded-2xl shadow-md p-6">
              <div className="flex items-center mb-4">
                <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                  <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <div>
                  <h3 className="text-lg font-bold text-gray-800">Riwayat Absensi</h3>
                  <p className="text-sm text-gray-600">Bulan ini</p>
                </div>
              </div>
              <div className="text-2xl font-bold text-blue-600">22 Hari</div>
            </div>
          </div>
        </div>

        {/* Bottom Navigation */}
        <BottomNavbar activeTab="dashboard" onTabChange={() => {}} />
      </div>
    </AccessControl>
  );
}
