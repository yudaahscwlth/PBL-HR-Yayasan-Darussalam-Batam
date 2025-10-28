"use client";

import { useState, useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/api";
import BottomNavbar from "@/components/BottomNavbar";

interface AttendanceData {
  jam_masuk?: string;
  jam_keluar?: string;
  status?: string;
}

interface WorkAnniversary {
  years: number;
  months: number;
  days: number;
}

interface LeaveRequest {
  tanggal_mulai: string;
  tanggal_selesai: string;
  jenis_cuti: string;
  status: string;
}

interface EmployeeDashboardData {
  todayAttendance: AttendanceData | null;
  monthlyStats: Record<string, number>;
  workAnniversary: WorkAnniversary | null;
  recentLeaves: LeaveRequest[];
}

export default function EmployeeDashboard() {
  const { user, isAuthenticated } = useAuthStore();
  const router = useRouter();
  const [activeTab, setActiveTab] = useState("dashboard");
  const [isLoading, setIsLoading] = useState(true);
  const [dashboardData, setDashboardData] = useState<EmployeeDashboardData>({
    todayAttendance: null,
    monthlyStats: {},
    workAnniversary: null,
    recentLeaves: [],
  });

  const [attendanceStatus, setAttendanceStatus] = useState<{
    hasCheckedIn: boolean;
    hasCheckedOut: boolean;
    checkInTime: string | null;
    checkOutTime: string | null;
  }>({
    hasCheckedIn: false,
    hasCheckedOut: false,
    checkInTime: null,
    checkOutTime: null,
  });

  const [attendanceLoading, setAttendanceLoading] = useState(false);
  const [currentLocation, setCurrentLocation] = useState<{
    latitude: number;
    longitude: number;
    accuracy?: number;
  } | null>(null);
  const [locationError, setLocationError] = useState<string | null>(null);
  const [isGettingLocation, setIsGettingLocation] = useState(false);
  const [gpsTolerance, setGpsTolerance] = useState(100); // Default 100m tolerance
  const [toastMessage, setToastMessage] = useState<{
    type: "success" | "error" | "warning" | "info";
    message: string;
    show: boolean;
  }>({ type: "info", message: "", show: false });
  const [lastToastTime, setLastToastTime] = useState<number>(0);

  // Redirect if not authenticated or not Employee
  useEffect(() => {
    console.log("=== EMPLOYEE DASHBOARD USEEFFECT ===");
    console.log("isAuthenticated:", isAuthenticated);
    console.log("user:", user);
    console.log("isLoading:", isLoading);
    console.log("================================");

    // Wait for auth state to be determined
    if (!isAuthenticated && user === null) {
      console.log("⏳ Waiting for auth state...");
      return;
    }

    // If not authenticated, redirect to login
    if (!isAuthenticated) {
      console.log("❌ Not authenticated, redirecting to login");
      router.push("/");
      return;
    }

    // If authenticated but no user data yet, wait
    if (isAuthenticated && !user) {
      console.log("⏳ Authenticated but no user data yet, waiting...");
      return;
    }

    // If we have user data, check roles
    if (user) {
      const isEmployee = user.roles?.includes("kepala departemen") || user.roles?.includes("kepala sekolah") || user.roles?.includes("tenaga pendidik");
      console.log("isEmployee:", isEmployee);

      if (!isEmployee) {
        console.log("❌ Not employee, redirecting to unauthorized");
        router.push("/unauthorized");
        return;
      }

      console.log("✅ Employee access granted");
      setIsLoading(false);
    }
  }, [isAuthenticated, user, router, isLoading]);

  const getCurrentDate = () => {
    const now = new Date();
    const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
    const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

    return `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
  };

  // Fetch today's attendance status
  useEffect(() => {
    const loadAttendanceStatus = async () => {
      if (!isAuthenticated || !user) return;
      
      setAttendanceLoading(true);
      try {
        const response = await apiClient.attendance.getToday();

        if (response.success && response.data) {
          const data = response.data as {
            has_checked_in?: boolean;
            has_checked_out?: boolean;
            check_in_time?: string;
            check_out_time?: string;
          };

          setAttendanceStatus({
            hasCheckedIn: data.has_checked_in || false,
            hasCheckedOut: data.has_checked_out || false,
            checkInTime: data.check_in_time || null,
            checkOutTime: data.check_out_time || null,
          });
        }
      } catch (error) {
        console.error("Error loading attendance status:", error);
      } finally {
        setAttendanceLoading(false);
      }
    };

    loadAttendanceStatus();
  }, [isAuthenticated, user]);

  const getCurrentLocation = (): Promise<{ latitude: number; longitude: number }> => {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        reject(new Error("Geolocation tidak didukung oleh browser ini"));
        return;
      }

      // Try multiple times with different settings for better accuracy
      let attempts = 0;
      const maxAttempts = 3;

      const tryGetLocation = (options: PositionOptions) => {
        attempts++;
        console.log(`GPS Attempt ${attempts}/${maxAttempts}`);

        navigator.geolocation.getCurrentPosition(
          (position) => {
            const location = {
              latitude: position.coords.latitude,
              longitude: position.coords.longitude,
              accuracy: position.coords.accuracy,
            };

            // Check accuracy
            const accuracy = position.coords.accuracy;
            console.log(`GPS Accuracy: ${accuracy}m`);

            // If accuracy is too poor (>20m), try again with different settings
            if (accuracy > 20 && attempts < maxAttempts) {
              console.log(`GPS accuracy too poor (${accuracy}m), trying again...`);
              const newOptions = {
                enableHighAccuracy: true,
                timeout: 25000, // Increase timeout for better accuracy
                maximumAge: 0,
              };
              setTimeout(() => tryGetLocation(newOptions), 3000); // Wait longer between attempts
              return;
            }

            setCurrentLocation(location);
            setLocationError(null);
            setIsGettingLocation(false);
            resolve(location);
          },
          (error) => {
            console.log(`GPS Error on attempt ${attempts}:`, error);

            // If timeout and we have attempts left, try again
            if (error.code === error.TIMEOUT && attempts < maxAttempts) {
              console.log("GPS timeout, trying again...");
              const newOptions = {
                enableHighAccuracy: true,
                timeout: 20000,
                maximumAge: 0,
              };
              setTimeout(() => tryGetLocation(newOptions), 2000);
              return;
            }

            let errorMessage = "Gagal mendapatkan lokasi GPS";
            switch (error.code) {
              case error.PERMISSION_DENIED:
                errorMessage = "Akses lokasi ditolak. Silakan izinkan akses lokasi untuk melakukan absensi.";
                break;
              case error.POSITION_UNAVAILABLE:
                errorMessage = "Informasi lokasi tidak tersedia. Pastikan GPS aktif dan sinyal baik.";
                break;
              case error.TIMEOUT:
                errorMessage = "Timeout saat mengambil lokasi. Coba pindah ke area dengan sinyal GPS yang lebih baik.";
                break;
            }
            setLocationError(errorMessage);
            setIsGettingLocation(false);
            reject(new Error(errorMessage));
          },
          options
        );
      };

      // Start with high accuracy settings
      const initialOptions = {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 0,
      };

      tryGetLocation(initialOptions);
    });
  };

  const showToast = (type: "success" | "error" | "warning" | "info", message: string) => {
    const now = Date.now();
    // Prevent duplicate messages within 2 seconds
    if (now - lastToastTime < 2000) {
      return;
    }

    setLastToastTime(now);
    setToastMessage({ type, message, show: true });
    setTimeout(() => {
      setToastMessage((prev) => ({ ...prev, show: false }));
    }, 5000); // Auto hide after 5 seconds
  };

  const refreshGPS = async () => {
    setIsGettingLocation(true);
    setLocationError(null);
    setCurrentLocation(null); // Clear previous location
    try {
      await getCurrentLocation();
      showToast("success", "GPS berhasil di-refresh!");
    } catch (error) {
      console.error("GPS refresh failed:", error);
      showToast("error", "Gagal refresh GPS. Coba lagi.");
    }
  };

  const resetGPS = async () => {
    setIsGettingLocation(true);
    setLocationError(null);
    setCurrentLocation(null);
    showToast("info", "🔄 Mereset GPS... Tunggu sebentar.");

    // Wait a bit before getting new location
    setTimeout(async () => {
      try {
        await getCurrentLocation();
        showToast("success", "✅ GPS berhasil di-reset!");
      } catch (error) {
        console.error("GPS reset failed:", error);
        showToast("error", "❌ Gagal reset GPS. Coba lagi.");
      }
    }, 2000);
  };

  const handleCheckIn = async () => {
    setIsLoading(true);
    try {
      // Get current GPS location
      const location = await getCurrentLocation();

      // Check GPS accuracy before proceeding
      const locationWithAccuracy = location as { latitude: number; longitude: number; accuracy?: number };
      if (locationWithAccuracy.accuracy && locationWithAccuracy.accuracy > gpsTolerance) {
        const accuracy = Math.round(locationWithAccuracy.accuracy);
        const confirmProceed = confirm(
          `📍 Akurasi GPS Rendah\n\n` +
            `Akurasi saat ini: ${accuracy}m\n` +
            `Toleransi yang diizinkan: ${gpsTolerance}m\n` +
            `Selisih: ${accuracy - gpsTolerance}m di luar toleransi\n\n` +
            `💡 Tips: Pindah ke area terbuka atau ubah toleransi GPS\n\n` +
            `Apakah Anda ingin melanjutkan absensi?`
        );

        if (!confirmProceed) {
          showToast("warning", `⚠️ Absensi dibatalkan. Akurasi GPS ${accuracy}m melebihi toleransi ${gpsTolerance}m.`);
          setIsLoading(false);
          return;
        }
      }

      // Call API with GPS coordinates
      const response = await apiClient.attendance.checkIn({
        latitude_in: location.latitude,
        longitude_in: location.longitude,
      });

      if (response.success) {
        const data = response.data as {
          check_in_time?: string;
          message?: string;
        };

        setAttendanceStatus({
          ...attendanceStatus,
          hasCheckedIn: true,
          checkInTime: data.check_in_time || new Date().toISOString(),
        });

      loadDashboardData();
        showToast("success", "✅ Check-in berhasil! Lokasi telah diverifikasi.");
      } else {
        // Handle specific error messages
        if (response.message?.includes("luar area kerja")) {
          showToast("error", `🚫 Anda berada di luar area kerja yang diizinkan!\n\n${response.message}`);
        } else {
          showToast("error", response.message || "❌ Gagal melakukan check in. Silakan coba lagi.");
        }
      }
    } catch (error: unknown) {
      const err = error as { message?: string; response?: { data?: { message?: string } } };
      console.error("Error checking in:", error);
      console.error("Error response:", err.response?.data);

      if (err.message && (err.message.includes("lokasi") || err.message.includes("GPS"))) {
        showToast("error", `📍 ${err.message}`);
      } else if (err.response?.data?.message) {
        showToast("error", `⚠️ Error: ${err.response.data.message}`);
      } else {
        showToast("error", "❌ Gagal melakukan check in. Silakan coba lagi.");
      }
    } finally {
      setIsLoading(false);
    }
  };

  const handleCheckOut = async () => {
    setIsLoading(true);
    try {
      // Get current GPS location
      const location = await getCurrentLocation();

      // Check GPS accuracy before proceeding
      const locationWithAccuracy = location as { latitude: number; longitude: number; accuracy?: number };
      if (locationWithAccuracy.accuracy && locationWithAccuracy.accuracy > gpsTolerance) {
        const accuracy = Math.round(locationWithAccuracy.accuracy);
        const confirmProceed = confirm(
          `📍 Akurasi GPS Rendah\n\n` +
            `Akurasi saat ini: ${accuracy}m\n` +
            `Toleransi yang diizinkan: ${gpsTolerance}m\n` +
            `Selisih: ${accuracy - gpsTolerance}m di luar toleransi\n\n` +
            `💡 Tips: Pindah ke area terbuka atau ubah toleransi GPS\n\n` +
            `Apakah Anda ingin melanjutkan absensi?`
        );

        if (!confirmProceed) {
          showToast("warning", `⚠️ Absensi dibatalkan. Akurasi GPS ${accuracy}m melebihi toleransi ${gpsTolerance}m.`);
          setIsLoading(false);
          return;
        }
      }

      // Call API with GPS coordinates
      const response = await apiClient.attendance.checkOut({
        latitude_out: location.latitude,
        longitude_out: location.longitude,
      });

      if (response.success) {
        const data = response.data as {
          check_out_time?: string;
          message?: string;
        };

        setAttendanceStatus({
          ...attendanceStatus,
          hasCheckedOut: true,
          checkOutTime: data.check_out_time || new Date().toISOString(),
        });

        showToast("success", "✅ Check-out berhasil! Lokasi telah diverifikasi.");
      } else {
        // Handle specific error messages
        if (response.message?.includes("luar area kerja")) {
          showToast("error", `🚫 Anda berada di luar area kerja yang diizinkan untuk check-out!\n\n${response.message}`);
        } else {
          showToast("error", response.message || "❌ Gagal melakukan check out. Silakan coba lagi.");
        }
      }
    } catch (error: unknown) {
      const err = error as { message?: string; response?: { data?: { message?: string } } };
      console.error("Error checking out:", error);
      console.error("Error response:", err.response?.data);

      if (err.message && (err.message.includes("lokasi") || err.message.includes("GPS"))) {
        showToast("error", `📍 ${err.message}`);
      } else if (err.response?.data?.message) {
        showToast("error", `⚠️ Error: ${err.response.data.message}`);
      } else {
        showToast("error", "❌ Gagal melakukan check out. Silakan coba lagi.");
      }
    } finally {
      setIsLoading(false);
    }
  };

  const loadDashboardData = async () => {
    try {
      console.log("Loading personal dashboard data...");

      // Load personal dashboard statistics
      const personalResponse = await apiClient.dashboard.getPersonal();
      if (personalResponse.success) {
        console.log("Personal dashboard data loaded:", personalResponse.data);
        const data = personalResponse.data as {
          today_attendance?: AttendanceData | null;
          monthly_stats?: Record<string, number>;
          work_anniversary?: WorkAnniversary | null;
          recent_leaves?: LeaveRequest[];
        };
        setDashboardData({
          todayAttendance: data.today_attendance || null,
          monthlyStats: data.monthly_stats || {},
          workAnniversary: data.work_anniversary || null,
          recentLeaves: data.recent_leaves || [],
        });
      }
    } catch (error) {
      console.error("Error loading personal dashboard data:", error);
      // Set fallback data if API fails
      setDashboardData({
        todayAttendance: null,
        monthlyStats: {},
        workAnniversary: null,
        recentLeaves: [],
      });
    }
  };

  useEffect(() => {
    if (isAuthenticated && user) {
      loadDashboardData();
    }
  }, [isAuthenticated, user]);

  if (isLoading || !isAuthenticated || !user) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Loading dashboard...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-100 pb-28">
      {/* Header Section */}
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

      {/* GPS Status */}
      <div className="px-5 -mt-12 mb-4">
        <div className="bg-white rounded-2xl shadow-lg p-4 mb-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center">
              <svg className="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span className="text-sm font-medium text-gray-700">Status GPS:</span>
            </div>
            <div className="flex items-center">
              {currentLocation ? (
                <>
                  <div className="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                  <span className="text-xs text-green-600 font-medium">Terhubung</span>
                </>
              ) : locationError ? (
                <>
                  <div className="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                  <span className="text-xs text-red-600 font-medium">Error</span>
                </>
              ) : (
                <>
                  <div className="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                  <span className="text-xs text-yellow-600 font-medium">Menunggu</span>
                </>
              )}
            </div>
          </div>
          {currentLocation && (
            <div className="mt-2 text-xs text-gray-500">
              <div>Lat: {currentLocation.latitude.toFixed(6)}</div>
              <div>Lng: {currentLocation.longitude.toFixed(6)}</div>
              <div className="flex items-center mt-1">
                <span>Akurasi: </span>
                <span className={`ml-1 font-medium ${currentLocation.accuracy && currentLocation.accuracy <= 10 ? "text-green-600" : currentLocation.accuracy && currentLocation.accuracy <= 30 ? "text-yellow-600" : "text-red-600"}`}>
                  {currentLocation.accuracy ? `${Math.round(currentLocation.accuracy)}m` : "N/A"}
                </span>
                {currentLocation.accuracy && currentLocation.accuracy <= 10 && <span className="ml-1 text-green-600">✓</span>}
              </div>
              <div className="flex items-center mt-1">
                <span>Toleransi: </span>
                <select value={gpsTolerance} onChange={(e) => setGpsTolerance(Number(e.target.value))} className="ml-1 text-xs border border-gray-300 rounded px-1 py-0.5">
                  <option value={50}>50m (Ketat)</option>
                  <option value={100}>100m (Normal)</option>
                  <option value={200}>200m (Longgar)</option>
                  <option value={500}>500m (Sangat Longgar)</option>
                </select>
              </div>
            </div>
          )}
          {locationError && <div className="mt-2 text-xs text-red-500">{locationError}</div>}
          <div className="mt-2 flex items-center gap-2 flex-wrap">
            <button onClick={refreshGPS} disabled={isGettingLocation} className="text-xs text-blue-600 hover:text-blue-800 underline disabled:opacity-50">
              {isGettingLocation ? "Mengambil GPS..." : "Refresh GPS"}
            </button>
            <button onClick={resetGPS} disabled={isGettingLocation} className="text-xs text-orange-600 hover:text-orange-800 underline disabled:opacity-50">
              Reset GPS
            </button>
            {isGettingLocation && <div className="w-3 h-3 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>}
          </div>
          {currentLocation && currentLocation.accuracy && currentLocation.accuracy > 50 && (
            <div className="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs">
              <div className="font-medium text-yellow-800 mb-1">💡 Tips Meningkatkan Akurasi GPS:</div>
              <ul className="text-yellow-700 space-y-0.5">
                <li>• Pindah ke area terbuka (jangan di dalam gedung)</li>
                <li>• Hindari area dengan banyak gedung tinggi</li>
                <li>• Tunggu beberapa detik hingga GPS stabil</li>
                <li>• Pastikan sinyal GPS aktif di pengaturan device</li>
              </ul>
            </div>
          )}
        </div>
      </div>

      {/* Attendance Buttons */}
      <div className="px-5 mb-4">
        <div className="bg-white rounded-2xl shadow-lg p-4">
          <div className="flex gap-3">
            <button
              onClick={handleCheckIn}
              disabled={attendanceStatus.hasCheckedIn || isLoading}
              className={`flex-1 rounded-xl py-3 flex items-center justify-center transition-all ${
                attendanceStatus.hasCheckedIn ? "bg-gray-200 border border-gray-300 cursor-not-allowed" : "bg-white border border-[#1e4d8b] hover:bg-blue-50"
              }`}
            >
              {isLoading ? (
                <div className="flex items-center">
                  <svg className="animate-spin h-5 w-5 text-[#1e4d8b] mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span className="text-[#1e4d8b] font-semibold text-sm">Loading...</span>
                </div>
              ) : attendanceStatus.hasCheckedIn ? (
                <div className="flex items-center">
                  <svg className="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="text-green-600 font-semibold text-sm">Sudah Absen Masuk</span>
                </div>
              ) : (
                <div className="flex items-center">
                  <svg className="w-5 h-5 text-[#1e4d8b] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
                  <span className="text-[#1e4d8b] font-semibold text-sm">Absen Masuk</span>
                </div>
              )}
          </button>
            <button
              onClick={handleCheckOut}
              disabled={!attendanceStatus.hasCheckedIn || attendanceStatus.hasCheckedOut || isLoading}
              className={`flex-1 rounded-xl py-3 flex items-center justify-center transition-all ${
                !attendanceStatus.hasCheckedIn || attendanceStatus.hasCheckedOut ? "bg-gray-200 border border-gray-300 cursor-not-allowed" : "bg-white border border-[#1e4d8b] hover:bg-blue-50"
              }`}
            >
              {isLoading ? (
                <div className="flex items-center">
                  <svg className="animate-spin h-5 w-5 text-[#1e4d8b] mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span className="text-[#1e4d8b] font-semibold text-sm">Loading...</span>
                </div>
              ) : attendanceStatus.hasCheckedOut ? (
                <div className="flex items-center">
                  <svg className="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="text-green-600 font-semibold text-sm">Sudah Absen Pulang</span>
                </div>
              ) : !attendanceStatus.hasCheckedIn ? (
                <div className="flex items-center">
                  <svg className="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                  </svg>
                  <span className="text-gray-400 font-semibold text-sm">Absen Pulang</span>
                </div>
              ) : (
                <div className="flex items-center">
                  <svg className="w-5 h-5 text-[#1e4d8b] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
                  <span className="text-[#1e4d8b] font-semibold text-sm">Absen Pulang</span>
                </div>
              )}
          </button>
          </div>
        </div>
      </div>

      {/* Informasi Cuti Card */}
      <div className="px-5 mt-4">
        <div className="bg-white rounded-2xl shadow-md p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="flex items-center">
              <svg className="w-6 h-6 text-[#1e4d8b] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <h2 className="text-lg font-bold text-gray-800">Informasi Cuti</h2>
            </div>
            <button
              onClick={() => router.push("/employee/pengajuan-cuti")}
              className="p-2 bg-[#1e4d8b] text-white rounded-lg hover:bg-blue-700 transition-colors"
              title="Ajukan Cuti"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
              </svg>
            </button>
          </div>

          <div className="mb-4">
            <div className="flex justify-between items-center mb-2">
              <span className="text-sm text-gray-600">Sisa Cuti Tahunan</span>
              <span className="text-sm text-gray-600">Total Cuti</span>
            </div>
            <div className="flex justify-between items-center mb-3">
              <span className="text-2xl font-bold text-[#1e4d8b]">9 hari</span>
              <span className="text-lg font-semibold text-gray-800">12 hari</span>
            </div>

            {/* Progress Bar */}
            <div className="w-full bg-gray-200 rounded-full h-2 mb-2">
              <div className="bg-[#1e4d8b] h-2 rounded-full" style={{ width: "25%" }}></div>
            </div>

            <p className="text-sm text-gray-600">Anda telah menggunakan 3 hari cuti tahun ini</p>
          </div>

          {/* Recent Leave Requests */}
          {dashboardData.recentLeaves && dashboardData.recentLeaves.length > 0 && (
            <div className="mt-4 pt-4 border-t border-gray-200">
              <h3 className="text-sm font-semibold text-gray-700 mb-3">Pengajuan Cuti Terbaru</h3>
              <div className="space-y-2">
                {dashboardData.recentLeaves.slice(0, 3).map((leave: any, index: number) => {
                  const leaveType = leave.tipe_cuti || leave.jenis_cuti || "Cuti";
                  const status = leave.status_pengajuan || leave.status || "pending";
                  const statusLower = status.toLowerCase();
                  
                  return (
                    <div key={index} className="flex items-center justify-between py-2">
                      <div className="flex-1">
                        <p className="text-sm font-medium text-gray-800 capitalize">
                          {leaveType}
                        </p>
                        <p className="text-xs text-gray-500">
                          {new Date(leave.tanggal_mulai).toLocaleDateString("id-ID", { day: "numeric", month: "short" })} - {new Date(leave.tanggal_selesai).toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" })}
                        </p>
                      </div>
                      <span className={`px-2 py-1 text-xs rounded-full ${
                        statusLower.includes("disetujui") || statusLower.includes("approved")
                          ? "bg-green-100 text-green-800"
                          : statusLower.includes("ditolak") || statusLower.includes("rejected")
                          ? "bg-red-100 text-red-800"
                          : "bg-yellow-100 text-yellow-800"
                      }`}>
                        {statusLower.includes("disetujui") ? "Disetujui" : statusLower.includes("ditolak") ? "Ditolak" : "Sedang Ditinjau"}
                      </span>
                    </div>
                  );
                })}
              </div>
            </div>
          )}

          <div className="mt-4 pt-3 border-t border-gray-200">
            <button 
              onClick={() => router.push("/employee/pengajuan-cuti")}
              className="w-full text-[#1e4d8b] text-sm font-medium hover:underline text-center"
            >
              Ajukan Cuti Baru
            </button>
          </div>
        </div>
      </div>

      {/* Riwayat Absensi Card */}
      <div className="px-5 mt-4">
        <div className="bg-white rounded-2xl shadow-md p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="flex items-center">
              <svg className="w-6 h-6 text-[#1e4d8b] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <h2 className="text-lg font-bold text-gray-800">Riwayat Absensi</h2>
            </div>
          </div>

          <div className="space-y-3">
            {/* Sample attendance records */}
            <div className="flex items-center justify-between py-2">
              <div className="flex items-center">
                <div className="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                <div>
                  <p className="font-medium text-gray-800">12 Okt 2025</p>
                  <p className="text-sm text-gray-600">Masuk: 08:00, Pulang: 17:00</p>
                </div>
              </div>
              <span className="px-2 py-1 bg-gray-800 text-white text-xs rounded-full">Tepat Waktu</span>
            </div>

            <div className="flex items-center justify-between py-2">
              <div className="flex items-center">
                <div className="w-2 h-2 bg-red-500 rounded-full mr-3"></div>
                <div>
                  <p className="font-medium text-gray-800">11 Okt 2025</p>
                  <p className="text-sm text-gray-600">Masuk: 08:15, Pulang: 17:05</p>
                </div>
              </div>
              <span className="px-2 py-1 bg-red-500 text-white text-xs rounded-full">Terlambat</span>
            </div>

            <div className="flex items-center justify-between py-2">
              <div className="flex items-center">
                <div className="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                <div>
                  <p className="font-medium text-gray-800">10 Okt 2025</p>
                  <p className="text-sm text-gray-600">Masuk: 07:55, Pulang: 17:00</p>
                </div>
              </div>
              <span className="px-2 py-1 bg-gray-800 text-white text-xs rounded-full">Tepat Waktu</span>
            </div>

            <div className="flex items-center justify-between py-2">
              <div className="flex items-center">
                <div className="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                <div>
                  <p className="font-medium text-gray-800">09 Okt 2025</p>
                  <p className="text-sm text-gray-600">Masuk: 08:00, Pulang: 17:00</p>
                </div>
              </div>
              <span className="px-2 py-1 bg-gray-800 text-white text-xs rounded-full">Tepat Waktu</span>
            </div>

            <div className="flex items-center justify-between py-2">
              <div className="flex items-center">
                <div className="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                <div>
                  <p className="font-medium text-gray-800">08 Okt 2025</p>
                  <p className="text-sm text-gray-600">Masuk: -, Pulang: -</p>
                </div>
              </div>
              <span className="px-2 py-1 bg-gray-400 text-white text-xs rounded-full">Cuti</span>
            </div>
          </div>

          <div className="mt-4 pt-3 border-t border-gray-200">
            <button className="text-[#1e4d8b] text-sm font-medium hover:underline">Lihat Semua Riwayat</button>
          </div>
        </div>
      </div>

      {/* Toast Notification */}
      {toastMessage.show && (
        <div className="fixed top-4 right-4 z-50 max-w-sm">
          <div
            className={`p-4 rounded-lg shadow-lg border-l-4 ${
              toastMessage.type === "success"
                ? "bg-green-50 border-green-500 text-green-800"
                : toastMessage.type === "error"
                ? "bg-red-50 border-red-500 text-red-800"
                : toastMessage.type === "warning"
                ? "bg-yellow-50 border-yellow-500 text-yellow-800"
                : "bg-blue-50 border-blue-500 text-blue-800"
            }`}
          >
            <div className="flex items-start">
              <div className="flex-shrink-0">
                {toastMessage.type === "success" && <span className="text-green-500 text-xl">✅</span>}
                {toastMessage.type === "error" && <span className="text-red-500 text-xl">❌</span>}
                {toastMessage.type === "warning" && <span className="text-yellow-500 text-xl">⚠️</span>}
                {toastMessage.type === "info" && <span className="text-blue-500 text-xl">ℹ️</span>}
              </div>
              <div className="ml-3 flex-1">
                <p className="text-sm font-medium whitespace-pre-line">{toastMessage.message}</p>
              </div>
              <div className="ml-4 flex-shrink-0">
                <button
                  onClick={() => setToastMessage((prev) => ({ ...prev, show: false }))}
                  className={`inline-flex rounded-md p-1.5 ${
                    toastMessage.type === "success"
                      ? "text-green-500 hover:bg-green-100"
                      : toastMessage.type === "error"
                      ? "text-red-500 hover:bg-red-100"
                      : toastMessage.type === "warning"
                      ? "text-yellow-500 hover:bg-yellow-100"
                      : "text-blue-500 hover:bg-blue-100"
                  }`}
                >
                  <span className="sr-only">Close</span>
                  <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      fillRule="evenodd"
                      d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                      clipRule="evenodd"
                    />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Bottom Navigation */}
      <BottomNavbar />
    </div>
  );
}
