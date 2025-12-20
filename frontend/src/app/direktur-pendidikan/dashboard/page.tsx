// src/app/(whatever)/HRDDashboard.tsx
"use client";

import { useState, useEffect, useRef } from "react";
import { useAuthStore } from "@/store/authStore";
import { apiClient } from "@/lib/api";
import BottomNavbar from "@/components/BottomNavbar";
import AccessControl from "@/components/AccessControl";

// Interface untuk data cuti
interface LeaveRequest {
  id: number;
  id_user?: number;
  tanggal_mulai: string;
  tanggal_selesai: string;
  tipe_cuti: string;
  status_pengajuan: string;
  alasan_pendukung: string;
  file_pendukung?: string;
  komentar?: string;
  created_at: string;
  user?: {
    id: number;
    email: string;
    profile_pribadi?: {
      nama_lengkap: string;
    };
  };
}

interface DashboardData {
  todayAttendance: number;
  totalEmployees: number;
  todayLeave: number;
  lateEmployees: number;
  absentEmployees: number;
}

export default function HRDDashboard() {
  const { user } = useAuthStore();

  const [dashboardData, setDashboardData] = useState<DashboardData>({
    todayAttendance: 0,
    totalEmployees: 0,
    todayLeave: 0,
    lateEmployees: 0,
    absentEmployees: 0,
  });

  const [leaveRequests, setLeaveRequests] = useState<LeaveRequest[]>([]);
  const [isLoadingLeave, setIsLoadingLeave] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [searchTerm, setSearchTerm] = useState("");
  const [entriesPerPage, setEntriesPerPage] = useState(10);
  const [sortField, setSortField] = useState<string>("created_at");
  const [sortOrder, setSortOrder] = useState<"asc" | "desc">("desc");

  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [selectedRequest, setSelectedRequest] = useState<LeaveRequest | null>(null);
  const [actionType, setActionType] = useState<"approve" | "reject" | null>(null);
  const [komentar, setKomentar] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);
  
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

  const [toastMessage, setToastMessage] = useState<{
    type: "success" | "error" | "warning" | "info";
    message: string;
    show: boolean;
  }>({ type: "info", message: "", show: false });
  const [lastToastTime, setLastToastTime] = useState<number>(0);

  // Refs
  const isMounted = useRef(true);
  const toastTimeoutRef = useRef<number | null>(null);
  const inFlightLocRef = useRef<
    Promise<{ latitude: number; longitude: number; accuracy?: number }> | null
  >(null);

  useEffect(() => {
    isMounted.current = true;
    return () => {
      isMounted.current = false;
      if (toastTimeoutRef.current) {
        clearTimeout(toastTimeoutRef.current);
        toastTimeoutRef.current = null;
      }
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
          };

          setDashboardData({
            todayAttendance: data.today_attendance || 0,
            totalEmployees: data.total_employees || 0,
            todayLeave: data.today_leave || 0,
            lateEmployees: data.late_employees || 0,
            absentEmployees: data.absent_employees || 0,
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

  // Load leave requests
  useEffect(() => {
    loadLeaveRequests();
  }, [currentPage, entriesPerPage, searchTerm, sortField, sortOrder]);

  const loadLeaveRequests = async () => {
    setIsLoadingLeave(true);
    try {
      const response = await apiClient.leave.getAll();
      
      if (response.success && response.data) {
       const allRequests = response.data as LeaveRequest[];
        
        // Filter berdasarkan status yang diinginkan
        const filteredRequests = allRequests.filter((request) => {
          const status = request.status_pengajuan.toLowerCase();
          return (
            status === "ditinjau direktur pendidikan" ||
            status === "disetujui hrd menunggu tinjauan dirpen" ||
            status === "ditolak direktur pendidikan"
          );
        });
        
        // Apply sorting
        const sortedRequests = [...filteredRequests].sort((a, b) => {
          let aValue: any = a;
          let bValue: any = b;
          
          // Handle nested property for nama
          if (sortField === "user.profilePribadi.nama_lengkap") {
            aValue = a.user?.profile_pribadi?.nama_lengkap || "";
            bValue = b.user?.profile_pribadi?.nama_lengkap || "";
          } else {
            aValue = a[sortField as keyof LeaveRequest] || "";
            bValue = b[sortField as keyof LeaveRequest] || "";
          }
          
          if (sortOrder === "asc") {
            return aValue > bValue ? 1 : -1;
          } else {
            return aValue < bValue ? 1 : -1;
          }
        });
        
        // Apply pagination
        const total = sortedRequests.length;
        const pages = Math.ceil(total / entriesPerPage);
        const paginatedData = sortedRequests.slice(
          (currentPage - 1) * entriesPerPage,
          currentPage * entriesPerPage
        );
        
        setLeaveRequests(paginatedData);
        setTotalPages(pages);
      }
    } catch (error) {
      console.error("Error loading leave requests:", error);
      showToast("error", "Gagal memuat data pengajuan cuti");
    } finally {
      setIsLoadingLeave(false);
    }
  };

  const handleConfirmAction = async () => {
    if (!selectedRequest || !actionType) return;

    // Validate komentar for reject
    if (actionType === "reject" && !komentar.trim()) {
      showToast("error", "Alasan penolakan wajib diisi");
      return;
    }

    setIsSubmitting(true);

    try {
      let response;

      if (actionType === "approve") {
        // Gunakan endpoint approve khusus direktur pendidikan
        response = await apiClient.leave.approveDirpen(
          selectedRequest.id,
          komentar.trim() || undefined
        );
      } else {
        // Reject: gunakan endpoint reject khusus direktur pendidikan
        response = await apiClient.leave.rejectDirpen(
          selectedRequest.id,
          komentar.trim()
        );
      }

      if (response.success) {
        showToast(
          "success",
          `✅ Cuti berhasil ${
            actionType === "approve" ? "disetujui" : "ditolak"
          }!`
        );
        setShowConfirmModal(false);
        setSelectedRequest(null);
        setActionType(null);
        setKomentar("");
        // Reload leave requests to show updated status
        await loadLeaveRequests();
      } else {
        showToast(
          "error",
          response.message ||
            `❌ Gagal ${
              actionType === "approve" ? "menyetujui" : "menolak"
            } cuti`
        );
      }
    } catch (error: any) {
      console.error("Error confirming leave request:", error);
      const errorMessage =
        error.response?.data?.message ||
        error.response?.data?.error ||
        error.message ||
        `❌ Gagal ${actionType === "approve" ? "menyetujui" : "menolak"} cuti`;
      showToast("error", errorMessage);
    } finally {
      setIsSubmitting(false);
    }
  };

  const openConfirmModal = (
    request: LeaveRequest,
    type: "approve" | "reject"
  ) => {
    setSelectedRequest(request);
    setActionType(type);
    setKomentar("");
    setShowConfirmModal(true);
  };

  const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
    setSearchTerm(e.target.value);
    setCurrentPage(1); // Reset ke halaman pertama saat mencari
  };

  const handleEntriesChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    setEntriesPerPage(Number(e.target.value));
    setCurrentPage(1);
  };

  const handleSort = (field: string) => {
    if (sortField === field) {
      setSortOrder(sortOrder === "asc" ? "desc" : "asc");
    } else {
      setSortField(field);
      setSortOrder("asc");
    }
  };

  const getStatusBadge = (status: string) => {
    const statusLower = status.toLowerCase();
    
    if (statusLower === "ditinjau direktur pendidikan") {
      return "bg-yellow-100 text-yellow-800 border border-yellow-200";
    } else if (statusLower === "disetujui hrd menunggu tinjauan dirpen") {
      return "bg-blue-100 text-blue-800 border border-blue-200";
    } else if (statusLower === "ditolak direktur pendidikan") {
      return "bg-red-100 text-red-800 border border-red-200";
    } else if (statusLower.includes("disetujui")) {
      return "bg-green-100 text-green-800 border border-green-200";
    } else if (statusLower.includes("ditolak")) {
      return "bg-red-100 text-red-800 border border-red-200";
    } else {
      return "bg-yellow-100 text-yellow-800 border border-yellow-200";
    }
  };

  const getStatusText = (status: string) => {
    const statusLower = status.toLowerCase();
    
    const statusMap: { [key: string]: string } = {
      "ditinjau direktur pendidikan": "Ditinjau Direktur Pendidikan",
      "disetujui hrd menunggu tinjauan dirpen": "disetujui hrd menunggu tinjauan dirpen",
      "ditolak direktur pendidikan": "Ditolak Direktur Pendidikan",
      "disetujui direktur pendidikan": "Disetujui Direktur Pendidikan",
    };
    
    return statusMap[statusLower] || status;
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  };

  const calculateDays = (startDate: string, endDate: string) => {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end.getTime() - start.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    return diffDays;
  };

  /** Toast helper */
  const showToast = (
    type: "success" | "error" | "warning" | "info",
    message: string
  ) => {
    if (!isMounted.current) return;
    const now = Date.now();
    if (now - lastToastTime < 2000) return;

    setLastToastTime(now);
    setToastMessage({ type, message, show: true });

    if (toastTimeoutRef.current) clearTimeout(toastTimeoutRef.current);
    toastTimeoutRef.current = window.setTimeout(() => {
      if (isMounted.current) {
        setToastMessage((prev) => ({ ...prev, show: false }));
      }
      toastTimeoutRef.current = null;
    }, 5000);
  };

  const getCurrentLocation = (): Promise<{
    latitude: number;
    longitude: number;
    accuracy?: number;
  }> => {
    if (inFlightLocRef.current) return inFlightLocRef.current;

    const sampleWindowMs = 8000;
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

      navigator.geolocation.getCurrentPosition(
        (pos) => {
          if (!isMounted.current) return;

          best = pos;
          const acc = pos.coords.accuracy ?? Infinity;

          if (acc <= gpsTolerance) {
            cleanup();
            resolveWith(pos);
            return;
          }

          startWatch();
        },
        (_err) => {
          startWatch();
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
      );

      const startWatch = () => {
        const deadline = Date.now() + sampleWindowMs;
        watchId = navigator.geolocation.watchPosition(
          (pos) => {
            if (!isMounted.current) return;

            if (
              !best ||
              (pos.coords.accuracy ?? Infinity) <
                (best.coords.accuracy ?? Infinity)
            ) {
              best = pos;
            }

            if ((pos.coords.accuracy ?? Infinity) <= gpsTolerance) {
              cleanup();
              resolveWith(pos);
            } else if (Date.now() >= deadline) {
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
      if (isMounted.current) showToast("success", "GPS berhasil di-refresh!");
    } catch (error) {
      console.error("GPS refresh failed:", error);
      if (isMounted.current)
        showToast("error", "Gagal refresh GPS. Coba lagi.");
    }
  };

  const resetGPS = async () => {
    setIsGettingLocation(true);
    setLocationError(null);
    setCurrentLocation(null);
    showToast("info", "🔄 Mereset GPS... Tunggu sebentar.");
    setTimeout(async () => {
      if (!isMounted.current) return;
      try {
        await getCurrentLocation();
        if (isMounted.current) showToast("success", "✅ GPS berhasil di-reset!");
      } catch (error) {
        console.error("GPS reset failed:", error);
        if (isMounted.current)
          showToast("error", "❌ Gagal reset GPS. Coba lagi.");
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
          showToast(
            "warning",
            `⚠️ Absensi dibatalkan. Akurasi GPS ${accuracy}m melebihi toleransi ${gpsTolerance}m.`
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

        const dashboardResponse = await apiClient.dashboard.getStats();
        if (dashboardResponse.success && dashboardResponse.data) {
          const d = dashboardResponse.data as {
            today_attendance?: number;
            total_employees?: number;
            today_leave?: number;
            late_employees?: number;
            absent_employees?: number;
          };
          setDashboardData({
            todayAttendance: d.today_attendance || 0,
            totalEmployees: d.total_employees || 0,
            todayLeave: d.today_leave || 0,
            lateEmployees: d.late_employees || 0,
            absentEmployees: d.absent_employees || 0,
          });
        }

        showToast("success", "✅ Check-in berhasil! Lokasi telah diverifikasi.");
      } else {
        if (response.message?.includes("luar area kerja")) {
          showToast(
            "error",
            `🚫 Anda berada di luar area kerja yang diizinkan!\n\n${response.message}`
          );
        } else {
          showToast(
            "error",
            response.message || "❌ Gagal melakukan check in. Silakan coba lagi."
          );
        }
      }
    } catch (error: any) {
      console.error("Error checking in:", error);
      console.error("Error response:", error?.response?.data);
      if (error?.message?.includes("lokasi") || error?.message?.includes("GPS")) {
        showToast("error", `📍 ${error.message}`);
      } else if (error?.response?.data?.message) {
        showToast("error", `⚠️ Error: ${error.response.data.message}`);
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
          showToast(
            "warning",
            `⚠️ Absensi dibatalkan. Akurasi GPS ${accuracy}m melebihi toleransi ${gpsTolerance}m.`
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

        showToast(
          "success",
          "✅ Check-out berhasil! Lokasi telah diverifikasi."
        );
      } else {
        if (response.message?.includes("luar area kerja")) {
          showToast(
            "error",
            `🚫 Anda berada di luar area kerja yang diizinkan untuk check-out!\n\n${response.message}`
          );
        } else {
          showToast(
            "error",
            response.message || "❌ Gagal melakukan check out. Silakan coba lagi."
          );
        }
      }
    } catch (error: any) {
      console.error("Error checking out:", error);
      console.error("Error response:", error?.response?.data);
      if (error?.message?.includes("lokasi") || error?.message?.includes("GPS")) {
        showToast("error", `📍 ${error.message}`);
      } else if (error?.response?.data?.message) {
        showToast("error", `⚠️ Error: ${error.response.data.message}`);
      } else {
        showToast("error", "❌ Gagal melakukan check out. Silakan coba lagi.");
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

  return (
    <AccessControl allowedRoles={["direktur pendidikan"]}>
      <div className="min-h-screen bg-gray-100 pb-20">
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

        {/* Verifikasi Cuti - Menggantikan bagian statistik */}
        <div className="px-5 mb-4">
          <div className="flex items-center mb-3">
            <svg className="w-5 h-5 text-[#1e4d8b] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h2 className="text-base font-bold text-gray-800">Verifikasi Cuti</h2>
          </div>

          <div className="bg-white rounded-2xl shadow-md p-5">
            {/* Search and filter controls */}
            <div className="flex flex-col md:flex-row md:items-center justify-between mb-4 gap-3">
              <div className="flex items-center">
                <label className="text-sm text-gray-600 mr-2">Show</label>
                <select
                  value={entriesPerPage}
                  onChange={handleEntriesChange}
                  className="border border-gray-300 rounded-lg text-sm px-2 py-1.5 mr-2"
                >
                  <option value={10}>10</option>
                  <option value={25}>25</option>
                  <option value={50}>50</option>
                  <option value={100}>100</option>
                </select>
                <span className="text-sm text-gray-600">entries</span>
              </div>
              
              <div className="flex items-center">
                <svg className="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                  type="text"
                  placeholder="Search..."
                  value={searchTerm}
                  onChange={handleSearch}
                  className="border border-gray-300 rounded-lg text-sm px-3 py-1.5 w-full md:w-64"
                />
              </div>
            </div>

            {/* Table */}
            <div className="overflow-x-auto">
              <table className="w-full border-collapse">
                <thead>
                  <tr className="bg-gray-50 border-b border-gray-200">
                    <th 
                      className="text-left py-3 px-4 text-sm font-semibold text-gray-700 cursor-pointer"
                      onClick={() => handleSort("created_at")}
                    >
                      <div className="flex items-center">
                        Tanggal Pengajuan
                        {sortField === "created_at" && (
                          <svg className={`w-4 h-4 ml-1 ${sortOrder === "asc" ? "transform rotate-180" : ""}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                          </svg>
                        )}
                      </div>
                    </th>
                    <th 
                      className="text-left py-3 px-4 text-sm font-semibold text-gray-700 cursor-pointer"
                      onClick={() => handleSort("user.profilePribadi.nama_lengkap")}
                    >
                      <div className="flex items-center">
                        Nama
                        {sortField === "user.profilePribadi.nama_lengkap" && (
                          <svg className={`w-4 h-4 ml-1 ${sortOrder === "asc" ? "transform rotate-180" : ""}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                          </svg>
                        )}
                      </div>
                    </th>
                    <th className="text-left py-3 px-4 text-sm font-semibold text-gray-700">Tipe</th>
                    <th className="text-left py-3 px-4 text-sm font-semibold text-gray-700">Durasi</th>
                    <th className="text-left py-3 px-4 text-sm font-semibold text-gray-700">Alasan</th>
                    <th className="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                  </tr>
                </thead>
                <tbody>
                  {isLoadingLeave ? (
                    <tr>
                      <td colSpan={6} className="py-8 text-center">
                        <div className="flex justify-center">
                          <div className="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                        </div>
                      </td>
                    </tr>
                  ) : leaveRequests.length === 0 ? (
                    <tr>
                      <td colSpan={6} className="py-8 text-center text-gray-500">
                        Tidak ada pengajuan cuti yang perlu diverifikasi
                      </td>
                    </tr>
                  ) : (
                    leaveRequests.map((request) => (
                      <tr key={request.id} className="border-b border-gray-100 hover:bg-gray-50">
                        <td className="py-3 px-4 text-sm text-gray-700">
                          {formatDate(request.created_at)}
                        </td>
                        <td className="py-3 px-4 text-sm text-gray-700">
                          {request.user?.profile_pribadi?.nama_lengkap || "N/A"}
                        </td>
                        <td className="py-3 px-4 text-sm text-gray-700 capitalize">
                          {request.tipe_cuti}
                        </td>
                        <td className="py-3 px-4 text-sm text-gray-700">
                          {calculateDays(request.tanggal_mulai, request.tanggal_selesai)} hari
                          <br />
                          <span className="text-xs text-gray-500">
                            ({formatDate(request.tanggal_mulai)} - {formatDate(request.tanggal_selesai)})
                          </span>
                        </td>
                        <td className="py-3 px-4 text-sm text-gray-700 max-w-xs truncate" title={request.alasan_pendukung}>
                          {request.alasan_pendukung || "-"}
                        </td>
                        <td className="py-3 px-4">
                          <span className={`inline-block px-3 py-1 text-xs font-medium rounded-full ${getStatusBadge(request.status_pengajuan)}`}>
                            {getStatusText(request.status_pengajuan)}
                          </span>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>

            {/* Pagination */}
            {totalPages > 1 && (
              <div className="flex flex-col md:flex-row md:items-center justify-between mt-4 pt-4 border-t border-gray-200">
                <div className="text-sm text-gray-600 mb-2 md:mb-0">
                  Showing page {currentPage} of {totalPages}
                </div>
                <div className="flex items-center gap-1">
                  <button
                    onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                    disabled={currentPage === 1}
                    className="px-3 py-1.5 text-sm border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                  >
                    Previous
                  </button>
                  {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                    let pageNum;
                    if (totalPages <= 5) {
                      pageNum = i + 1;
                    } else if (currentPage <= 3) {
                      pageNum = i + 1;
                    } else if (currentPage >= totalPages - 2) {
                      pageNum = totalPages - 4 + i;
                    } else {
                      pageNum = currentPage - 2 + i;
                    }
                    
                    return (
                      <button
                        key={i}
                        onClick={() => setCurrentPage(pageNum)}
                        className={`px-3 py-1.5 text-sm border rounded-lg ${
                          currentPage === pageNum
                            ? "bg-[#1e4d8b] text-white border-[#1e4d8b]"
                            : "border-gray-300 text-gray-700 hover:bg-gray-50"
                        }`}
                      >
                        {pageNum}
                      </button>
                    );
                  })}
                  <button
                    onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                    disabled={currentPage === totalPages}
                    className="px-3 py-1.5 text-sm border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                  >
                    Next
                  </button>
                </div>
              </div>
            )}

            {/* Status Legend */}
            <div className="flex items-center gap-4 mt-6 pt-4 border-t border-gray-200">
              <div className="flex items-center gap-2">
                <div className="w-3 h-3 rounded bg-green-500"></div>
                <span className="text-sm text-gray-600">Disetujui</span>
              </div>
              <div className="flex items-center gap-2">
                <div className="w-3 h-3 rounded bg-red-500"></div>
                <span className="text-sm text-gray-600">Ditolak</span>
              </div>
              <div className="flex items-center gap-2">
                <div className="w-3 h-3 rounded bg-yellow-500"></div>
                <span className="text-sm text-gray-600">Menunggu</span>
              </div>
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

        <style jsx>{`
          .scrollbar-hide::-webkit-scrollbar { display: none; }
          .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        `}</style>
      </div>
    </AccessControl>
  );
}
