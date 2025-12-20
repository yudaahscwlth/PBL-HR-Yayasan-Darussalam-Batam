// src/app/(whatever)/HRDDashboard.tsx
"use client";

import { useState, useEffect, useRef } from "react";
import { useAuthStore } from "@/store/authStore";
import { apiClient } from "@/lib/api";
import BottomNavbar from "@/components/BottomNavbar";
import toast from "react-hot-toast";
import AccessControl from "@/components/AccessControl";

interface DashboardItem {
  name: string;
  count: number;
  color: string;
}

interface DashboardData {
  todayAttendance: number;
  totalEmployees: number;
  todayLeave: number;
  lateEmployees: number;
  absentEmployees: number;
  jobTitleDistribution: DashboardItem[];
  departmentDistribution: DashboardItem[];
}

export default function HRDDashboard() {
  const { user } = useAuthStore();

  const [dashboardData, setDashboardData] = useState<DashboardData>({
    todayAttendance: 0,
    totalEmployees: 0,
    todayLeave: 0,
    lateEmployees: 0,
    absentEmployees: 0,
    jobTitleDistribution: [],
    departmentDistribution: [],
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

  const [isLoading, setIsLoading] = useState(false);
  const [attendanceLoading, setAttendanceLoading] = useState(false);
  const [currentLocation, setCurrentLocation] = useState<{
    latitude: number;
    longitude: number;
    accuracy?: number;
  } | null>(null);
  const [locationError, setLocationError] = useState<string | null>(null);
  const [isGettingLocation, setIsGettingLocation] = useState(false);
  const [gpsTolerance, setGpsTolerance] = useState(100); // 50 / 100 / 200 / 500 (meter)

const [lastToastTime, setLastToastTime] = useState<number>(0);

  // Refs
  const isMounted = useRef(true);

  const inFlightLocRef = useRef<
    Promise<{ latitude: number; longitude: number; accuracy?: number }> | null
  >(null);

  useEffect(() => {
    isMounted.current = true;
    return () => {
      isMounted.current = false;
};
  }, []);

  // Load dashboard data
  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        const response = await apiClient.dashboard.getStats();
        if (!cancelled && response.success && response.data) {
          const data = response.data as {
            today_attendance?: number;
            total_employees?: number;
            today_leave?: number;
            late_employees?: number;
            absent_employees?: number;
            job_title_distribution?: DashboardItem[];
            department_distribution?: DashboardItem[];
          };

          setDashboardData({
            todayAttendance: data.today_attendance || 0,
            totalEmployees: data.total_employees || 0,
            todayLeave: data.today_leave || 0,
            lateEmployees: data.late_employees || 0,
            absentEmployees: data.absent_employees || 0,
            jobTitleDistribution: data.job_title_distribution || [],
            departmentDistribution: data.department_distribution || [],
          });
        }
      } catch (error) {
        if (!cancelled) console.error("Error loading dashboard data:", error);
      }
    })();
    return () => {
      cancelled = true;
    };
  }, []);

  // Load today's attendance status
  useEffect(() => {
    let cancelled = false;
    (async () => {
      setAttendanceLoading(true);
      try {
        const response = await apiClient.attendance.getToday();
        if (!cancelled && response.success && response.data) {
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
        if (!cancelled) console.error("Error loading attendance status:", error);
      } finally {
        if (!cancelled) setAttendanceLoading(false);
      }
    })();
    return () => {
      cancelled = true;
    };
  }, []);

  /**
   * getCurrentLocation dengan strategi:
   * 1) Coba getCurrentPosition (boleh last known ≤ 60s)
   * 2) Jika belum memenuhi toleransi, aktifkan watchPosition selama window (8s) dan ambil fix terbaik.
   * 3) Cegah panggilan paralel dengan inFlightLocRef.
   */
  const getCurrentLocation = (): Promise<{
    latitude: number;
    longitude: number;
    accuracy?: number;
  }> => {
    if (inFlightLocRef.current) return inFlightLocRef.current;

    const sampleWindowMs = 8000; // 8 detik untuk “warm-up”
    let best: GeolocationPosition | null = null;
    let watchId: number | null = null;

    const p = new Promise<{
      latitude: number;
      longitude: number;
      accuracy?: number;
    }>((resolve, reject) => {
      if (!navigator.geolocation) {
        reject(new Error("Geolocation tidak didukung oleh browser ini"));
        inFlightLocRef.current = null;
        return;
      }

      const resolveWith = (pos: GeolocationPosition) => {
        const payload = {
          latitude: pos.coords.latitude,
          longitude: pos.coords.longitude,
          accuracy: pos.coords.accuracy,
        };
        if (isMounted.current) {
          setCurrentLocation(payload);
          setLocationError(null);
          setIsGettingLocation(false);
        }
        inFlightLocRef.current = null;
        resolve(payload);
      };

      const cleanup = () => {
        if (watchId !== null) {
          navigator.geolocation.clearWatch(watchId);
          watchId = null;
        }
      };

      // Langkah 1: snapshot (boleh last known ≤ 60s)
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          if (!isMounted.current) return;

          best = pos;
          const acc = pos.coords.accuracy ?? Infinity;

          // Kalau sudah <= toleransi, cukup
          if (acc <= gpsTolerance) {
            cleanup();
            resolveWith(pos);
            return;
          }

          // Lanjut ke watch untuk memburu fix lebih baik
          startWatch();
        },
        (_err) => {
          // Gagal snapshot → langsung watch
          startWatch();
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
      );

      const startWatch = () => {
        const deadline = Date.now() + sampleWindowMs;
        watchId = navigator.geolocation.watchPosition(
          (pos) => {
            if (!isMounted.current) return;

            // Simpan yang terbaik
            if (
              !best ||
              (pos.coords.accuracy ?? Infinity) <
                (best.coords.accuracy ?? Infinity)
            ) {
              best = pos;
            }

            // Jika sudah memenuhi toleransi, akhiri lebih cepat
            if ((pos.coords.accuracy ?? Infinity) <= gpsTolerance) {
              cleanup();
              resolveWith(pos);
            } else if (Date.now() >= deadline) {
              // Window habis → ambil yang terbaik
              cleanup();
              if (best) resolveWith(best);
              else {
                inFlightLocRef.current = null;
                reject(new Error("Gagal mendapatkan lokasi GPS"));
              }
            }
          },
          (err) => {
            if (Date.now() >= deadline && best) {
              cleanup();
              resolveWith(best);
            } else if (Date.now() >= deadline) {
              cleanup();
              inFlightLocRef.current = null;
              reject(
                new Error(
                  err.code === err.PERMISSION_DENIED
                    ? "Akses lokasi ditolak. Izinkan lokasi untuk melanjutkan."
                    : err.code === err.POSITION_UNAVAILABLE
                    ? "Informasi lokasi tidak tersedia."
                    : err.code === err.TIMEOUT
                    ? "Timeout saat mengambil lokasi."
                    : "Gagal mendapatkan lokasi GPS"
                )
              );
            }
          },
          { enableHighAccuracy: true, maximumAge: 0, timeout: 25000 }
        );
      };
    });

    inFlightLocRef.current = p;
    return p;
  };

  const refreshGPS = async () => {
    setIsGettingLocation(true);
    setLocationError(null);
    setCurrentLocation(null);
    try {
      await getCurrentLocation();
      if (isMounted.current) toast.success("GPS berhasil di-refresh!");
    } catch (error) {
      console.error("GPS refresh failed:", error);
      if (isMounted.current)
        toast.error("Gagal refresh GPS. Coba lagi.");
    }
  };

  const resetGPS = async () => {
    setIsGettingLocation(true);
    setLocationError(null);
    setCurrentLocation(null);
    toast("Mereset GPS... Tunggu sebentar.");
    setTimeout(async () => {
      if (!isMounted.current) return;
      try {
        await getCurrentLocation();
        if (isMounted.current) toast.success("GPS berhasil di-reset!");
      } catch (error) {
        console.error("GPS reset failed:", error);
        if (isMounted.current)
          toast.error("Gagal reset GPS. Coba lagi.");
      }
    }, 1200);
  };

  const handleCheckIn = async () => {
    setIsLoading(true);
    try {
      const location = await getCurrentLocation();

      if ((location as any).accuracy && (location as any).accuracy > gpsTolerance) {
        const accuracy = Math.round((location as any).accuracy);
        const confirmProceed = confirm(
          `📍 Akurasi GPS Rendah\n\n` +
            `Akurasi saat ini: ${accuracy}m\n` +
            `Toleransi yang diizinkan: ${gpsTolerance}m\n` +
            `Selisih: ${accuracy - gpsTolerance}m di luar toleransi\n\n` +
            `💡 Tips: Pindah ke area terbuka atau ubah toleransi GPS\n\n` +
            `Apakah Anda ingin melanjutkan absensi?`
        );
        if (!confirmProceed) {
          toast.error(
            `Absensi dibatalkan. Akurasi GPS ${accuracy}m melebihi toleransi ${gpsTolerance}m.`
          );
          setIsLoading(false);
          return;
        }
      }

      const response = await apiClient.attendance.checkIn({
        latitude_in: location.latitude,
        longitude_in: location.longitude,
      });

      if (response.success) {
        const data = response.data as { check_in_time?: string; message?: string };
        setAttendanceStatus((prev) => ({
          ...prev,
          hasCheckedIn: true,
          checkInTime: data.check_in_time || new Date().toISOString(),
        }));

        // Refresh dashboard
        const dashboardResponse = await apiClient.dashboard.getStats();
        if (dashboardResponse.success && dashboardResponse.data) {
          const d = dashboardResponse.data as {
            today_attendance?: number;
            total_employees?: number;
            today_leave?: number;
            late_employees?: number;
            absent_employees?: number;
            job_title_distribution?: DashboardItem[];
            department_distribution?: DashboardItem[];
          };
          setDashboardData({
            todayAttendance: d.today_attendance || 0,
            totalEmployees: d.total_employees || 0,
            todayLeave: d.today_leave || 0,
            lateEmployees: d.late_employees || 0,
            absentEmployees: d.absent_employees || 0,
            jobTitleDistribution: d.job_title_distribution || [],
            departmentDistribution: d.department_distribution || [],
          });
        }

        toast.success("Check-in berhasil! Lokasi telah diverifikasi.");
      } else {
        if (response.message?.includes("luar area kerja")) {
          toast.error(response.message);
        } else {
          toast.error(
            response.message || "Gagal melakukan check in. Silakan coba lagi."
          );
        }
      }
    } catch (error: any) {
      console.error("Error checking in:", error);
      console.error("Error response:", error?.response?.data);
      if (error?.message?.includes("lokasi") || error?.message?.includes("GPS")) {
        toast.error(`${error.message}`);
      } else if (error?.response?.data?.message) {
        toast.error(`Error: ${error.response.data.message}`);
      } else {
        toast.error("Gagal melakukan check in. Silakan coba lagi.");
      }
    } finally {
      setIsLoading(false);
    }
  };

  const handleCheckOut = async () => {
    setIsLoading(true);
    try {
      const location = await getCurrentLocation();

      if ((location as any).accuracy && (location as any).accuracy > gpsTolerance) {
        const accuracy = Math.round((location as any).accuracy);
        const confirmProceed = confirm(
          `📍 Akurasi GPS Rendah\n\n` +
            `Akurasi saat ini: ${accuracy}m\n` +
            `Toleransi yang diizinkan: ${gpsTolerance}m\n` +
            `Selisih: ${accuracy - gpsTolerance}m di luar toleransi\n\n` +
            `💡 Tips: Pindah ke area terbuka atau ubah toleransi GPS\n\n` +
            `Apakah Anda ingin melanjutkan absensi?`
        );
        if (!confirmProceed) {
          toast.error(
            `Absensi dibatalkan. Akurasi GPS ${accuracy}m melebihi toleransi ${gpsTolerance}m.`
          );
          setIsLoading(false);
          return;
        }
      }

      const response = await apiClient.attendance.checkOut({
        latitude_out: location.latitude,
        longitude_out: location.longitude,
      });

      if (response.success) {
        const data = response.data as { check_out_time?: string; message?: string };
        setAttendanceStatus((prev) => ({
          ...prev,
          hasCheckedOut: true,
          checkOutTime: data.check_out_time || new Date().toISOString(),
        }));

        toast.success("Check-out berhasil! Lokasi telah diverifikasi.");
      } else {
        if (response.message?.includes("luar area kerja")) {
          toast.error(response.message);
        } else {
          toast.error(
            response.message || "Gagal melakukan check out. Silakan coba lagi."
          );
        }
      }
    } catch (error: any) {
      console.error("Error checking out:", error);
      console.error("Error response:", error?.response?.data);
      if (error?.message?.includes("lokasi") || error?.message?.includes("GPS")) {
        toast.error(`${error.message}`);
      } else if (error?.response?.data?.message) {
        toast.error(`Error: ${error.response.data.message}`);
      } else {
        toast.error("Gagal melakukan check out. Silakan coba lagi.");
      }
    } finally {
      setIsLoading(false);
    }
  };

  const getCurrentDate = () => {
    const now = new Date();
    const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
    const months = [
      "Januari","Februari","Maret","April","Mei","Juni",
      "Juli","Agustus","September","Oktober","November","Desember",
    ];
    return `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
  };

  const renderPieChart = (data: DashboardItem[]) => {
    const total = data.reduce((sum, item) => sum + item.count, 0);
    let currentAngle = 0;

    return (
      <svg viewBox="0 0 200 200" className="w-full h-full">
        {total > 0 &&
          data.map((item, index) => {
            const percentage = (item.count / total) * 100;
            const angle = (percentage / 100) * 360;
            const startAngle = currentAngle;
            const endAngle = currentAngle + angle;
            currentAngle = endAngle;

            const startRad = (startAngle - 90) * (Math.PI / 180);
            const endRad = (endAngle - 90) * (Math.PI / 180);

            const x1 = 100 + 70 * Math.cos(startRad);
            const y1 = 100 + 70 * Math.sin(startRad);
            const x2 = 100 + 70 * Math.cos(endRad);
            const y2 = 100 + 70 * Math.sin(endRad);

            const largeArc = angle > 180 ? 1 : 0;

            const pathData = [
              `M 100 100`,
              `L ${x1} ${y1}`,
              `A 70 70 0 ${largeArc} 1 ${x2} ${y2}`,
              `Z`,
            ].join(" ");

            return <path key={index} d={pathData} fill={item.color} />;
          })}
        <circle cx="100" cy="100" r="45" fill="white" />
      </svg>
    );
  };

  return (
    <AccessControl allowedRoles={["kepala hrd", "staff hrd"]}>
      <div className="min-h-screen bg-gray-100 pb-8">
        {/* Header */}
        <div className="bg-[#1e4d8b] px-5 pt-3 pb-16 text-white rounded-b-3xl">
          <div className="flex items-center justify-between mb-1">
            <div className="flex-1">
              <p className="text-sm opacity-90">Selamat Datang,</p>
              <h1 className="text-xl font-bold">
                {user?.profile_pribadi?.nama_lengkap || user?.email || "User"}
              </h1>
              <div className="flex items-center mt-1">
                <svg className="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span className="text-xs">{getCurrentDate()}</span>
              </div>
            </div>
            <div className="w-12 h-12 bg-white rounded-full flex items-center justify-center overflow-hidden border-2 border-white shadow-md">
              {user?.profile_pribadi?.foto ? (
                <img
                  src={`${process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000"}/storage/${user.profile_pribadi.foto}`}
                  alt={user?.profile_pribadi?.nama_lengkap || "Profile"}
                  className="w-full h-full object-cover"
                />
              ) : (
                <svg className="w-6 h-6 text-[#1e4d8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              )}
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
                  <span
                    className={`ml-1 font-medium ${
                      currentLocation.accuracy && currentLocation.accuracy <= 10
                        ? "text-green-600"
                        : currentLocation.accuracy && currentLocation.accuracy <= 30
                        ? "text-yellow-600"
                        : "text-red-600"
                    }`}
                  >
                    {currentLocation.accuracy ? `${Math.round(currentLocation.accuracy)}m` : "N/A"}
                  </span>
                  {currentLocation.accuracy && currentLocation.accuracy <= 10 && (
                    <span className="ml-1 text-green-600">✓</span>
                  )}
                </div>
                <div className="flex items-center mt-1">
                  <span>Toleransi: </span>
                  <select
                    value={gpsTolerance}
                    onChange={(e) => setGpsTolerance(Number(e.target.value))}
                    className="ml-1 text-xs border border-gray-300 rounded px-1 py-0.5"
                  >
                    <option value={50}>50m (Ketat)</option>
                    <option value={100}>100m (Normal)</option>
                    <option value={200}>200m (Longgar)</option>
                    <option value={500}>500m (Sangat Longgar)</option>
                  </select>
                </div>
              </div>
            )}

            {locationError && (
              <div className="mt-2 text-xs text-red-500">{locationError}</div>
            )}

            <div className="mt-2 flex items-center gap-2 flex-wrap">
              <button
                onClick={refreshGPS}
                disabled={isGettingLocation}
                className="text-xs text-blue-600 hover:text-blue-800 underline disabled:opacity-50"
              >
                {isGettingLocation ? "Mengambil GPS..." : "Refresh GPS"}
              </button>
              <button
                onClick={resetGPS}
                disabled={isGettingLocation}
                className="text-xs text-orange-600 hover:text-orange-800 underline disabled:opacity-50"
              >
                Reset GPS
              </button>
              {isGettingLocation && (
                <div className="w-3 h-3 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
              )}
            </div>

            {currentLocation && currentLocation.accuracy && currentLocation.accuracy > 50 && (
              <div className="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs">
                <div className="font-medium text-yellow-800 mb-1">
                  💡 Tips Meningkatkan Akurasi GPS:
                </div>
                <ul className="text-yellow-700 space-y-0.5">
                  <li>• Pindah ke area terbuka (jangan di dalam gedung)</li>
                  <li>• Hindari area dengan banyak gedung tinggi</li>
                  <li>• Tunggu beberapa detik hingga GPS stabil</li>
                  <li>• Aktifkan Wi-Fi scanning & precise location</li>
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
                  attendanceStatus.hasCheckedIn
                    ? "bg-gray-200 border border-gray-300 cursor-not-allowed"
                    : "bg-white border border-[#1e4d8b] hover:bg-blue-50"
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
                  !attendanceStatus.hasCheckedIn || attendanceStatus.hasCheckedOut
                    ? "bg-gray-200 border border-gray-300 cursor-not-allowed"
                    : "bg-white border border-[#1e4d8b] hover:bg-blue-50"
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
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1" />
                    </svg>
                    <span className="text-[#1e4d8b] font-semibold text-sm">Absen Pulang</span>
                  </div>
                )}
              </button>
            </div>
          </div>
        </div>

        {/* Daily Statistics */}
        <div className="px-5 mb-4">
          <div className="flex items-center mb-3">
            <svg className="w-5 h-5 text-[#1e4d8b] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            <h2 className="text-base font-bold text-gray-800">Statistik Harian</h2>
          </div>

          <div className="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
            <div className="bg-white rounded-2xl shadow-md p-4 min-w-[140px] flex-shrink-0">
              <div className="flex items-center justify-center mb-2">
                <svg className="w-8 h-8 text-[#1e4d8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div className="text-3xl font-bold text-[#1e4d8b] text-center mb-1">
                {dashboardData.todayAttendance}
              </div>
              <p className="text-xs text-gray-600 text-center font-medium">Masuk Hari Ini</p>
              <p className="text-xs text-gray-400 text-center">Dari {dashboardData.totalEmployees} karyawan</p>
            </div>

            <div className="bg-white rounded-2xl shadow-md p-4 min-w-[140px] flex-shrink-0">
              <div className="flex items-center justify-center mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" className="w-8 h-8 text-[#1e4d8b]" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2">
                  <path d="M13 21H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v6.5" />
                  <path d="M16 3v4M8 3v4M4 11h16" />
                  <path d="m22 20l-5-5m0 5l5-5" />
                </svg>
              </div>
              <div className="text-3xl font-bold text-[#1e4d8b] text-center mb-1">
                {dashboardData.todayLeave}
              </div>
              <p className="text-xs text-gray-600 text-center font-medium">Cuti Hari Ini</p>
              <p className="text-xs text-gray-400 text-center">karyawan</p>
            </div>

            <div className="bg-white rounded-2xl shadow-md p-4 min-w-[140px] flex-shrink-0">
              <div className="flex items-center justify-center mb-2">
                <svg className="w-8 h-8 text-[#1e4d8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div className="text-3xl font-bold text-[#1e4d8b] text-center mb-1">
                {dashboardData.lateEmployees}
              </div>
              <p className="text-xs text-gray-600 text-center font-medium">Terlambat</p>
              <p className="text-xs text-gray-400 text-center">karyawan</p>
            </div>

            <div className="bg-white rounded-2xl shadow-md p-4 min-w-[140px] flex-shrink-0">
              <div className="flex items-center justify-center mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" className="w-8 h-8 text-[#1e4d8b]" viewBox="0 0 48 48" fill="currentColor">
                  <defs>
                    <mask id="SVGdHQ1QFib">
                      <g fill="none" stroke="#fff" strokeLinejoin="round" strokeWidth="4">
                        <path fill="#333" d="M19 20a7 7 0 1 0 0-14a7 7 0 0 0 0 14Z" />
                        <path strokeLinecap="round" d="m33 31l8 8m-8 0l8-8m-14-3h-8.2c-4.48 0-6.72 0-8.432.872a8 8 0 0 0-3.496 3.496C6 34.08 6 36.32 6 40.8V42h21" />
                      </g>
                    </mask>
                  </defs>
                  <path fill="currentColor" d="M0 0h48v48H0z" mask="url(#SVGdHQ1QFib)" />
                </svg>
              </div>
              <div className="text-3xl font-bold text-[#1e4d8b] text-center mb-1">
                {dashboardData.absentEmployees}
              </div>
              <p className="text-xs text-gray-600 text-center font-medium">Tidak Masuk</p>
              <p className="text-xs text-gray-400 text-center">karyawan</p>
            </div>
          </div>
        </div>

        {/* Employee Distribution by Job Title */}
        <div className="px-5 mb-4">
          <div className="bg-white rounded-2xl shadow-md p-5">
            <h2 className="text-base font-bold text-gray-800 mb-4">Pegawai Berdasarkan Jabatan</h2>
            <div className="flex items-center mb-4">
              <div className="w-36 h-36">{renderPieChart(dashboardData.jobTitleDistribution)}</div>
              <div className="ml-4">
                <p className="text-sm font-semibold text-gray-700">Total: {dashboardData.totalEmployees} Orang</p>
              </div>
            </div>
            <div className="space-y-2.5">
              {dashboardData.jobTitleDistribution.map((item, index) => (
                <div key={index} className="flex items-center justify-between">
                  <div className="flex items-center flex-1">
                    <div className="w-3 h-3 rounded-full mr-2.5 flex-shrink-0" style={{ backgroundColor: item.color }}></div>
                    <span className="text-sm text-gray-700">{item.name}</span>
                  </div>
                  <span className="bg-blue-500 text-white text-xs font-semibold px-2.5 py-1 rounded-full ml-2">{item.count}</span>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Employee Distribution by Department */}
        <div className="px-5 mb-28">
          <div className="bg-white rounded-2xl shadow-md p-5">
            <h2 className="text-base font-bold text-gray-800 mb-4">Pegawai Berdasarkan Departemen</h2>
            <div className="flex items-center mb-4">
              <div className="w-36 h-36">{renderPieChart(dashboardData.departmentDistribution)}</div>
              <div className="ml-4">
                <p className="text-sm font-semibold text-gray-700">Total: {dashboardData.totalEmployees} Orang</p>
              </div>
            </div>
            <div className="space-y-2.5">
              {dashboardData.departmentDistribution.map((item, index) => (
                <div key={index} className="flex items-center justify-between">
                  <div className="flex items-center flex-1">
                    <div className="w-3 h-3 rounded-full mr-2.5 flex-shrink-0" style={{ backgroundColor: item.color }}></div>
                    <span className="text-sm text-gray-700">{item.name}</span>
                  </div>
                  <span className="bg-blue-500 text-white text-xs font-semibold px-2.5 py-1 rounded-full ml-2">{item.count}</span>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Bottom Navigation */}
        <BottomNavbar />

        <style jsx>{`
          .scrollbar-hide::-webkit-scrollbar { display: none; }
          .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        `}</style>
      </div>
    </AccessControl>
  );
}
