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

  const handleCheckIn = async () => {
    try {
      await apiClient.attendance.checkIn();
      loadDashboardData();
    } catch (error) {
      console.error("Check-in error:", error);
    }
  };

  const handleCheckOut = async () => {
    try {
      await apiClient.attendance.checkOut();
      loadDashboardData();
    } catch (error) {
      console.error("Check-out error:", error);
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

  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Loading dashboard...</p>
        </div>
      </div>
    );
  }

  if (!isAuthenticated || !user) {
    return null;
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

      {/* Attendance Buttons - Overlapping */}
      <div className="px-5 -mt-8">
        <div className="flex gap-4">
          <button onClick={handleCheckIn} className="flex-1 bg-white border-2 border-[#1e4d8b] rounded-lg p-4 flex items-center justify-center shadow-md">
            <svg className="w-6 h-6 text-[#1e4d8b] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            <span className="text-[#1e4d8b] font-medium">Absen Masuk</span>
          </button>
          <button onClick={handleCheckOut} className="flex-1 bg-white border-2 border-[#1e4d8b] rounded-lg p-4 flex items-center justify-center shadow-md">
            <svg className="w-6 h-6 text-[#1e4d8b] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            <span className="text-[#1e4d8b] font-medium">Absen Pulang</span>
          </button>
        </div>
      </div>

      {/* Informasi Cuti Card */}
      <div className="px-5 mt-4">
        <div className="bg-white rounded-2xl shadow-md p-6">
          <div className="flex items-center mb-4">
            <svg className="w-6 h-6 text-[#1e4d8b] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <h2 className="text-lg font-bold text-gray-800">Informasi Cuti</h2>
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

      {/* Bottom Navigation */}
      <BottomNavbar />
    </div>
  );
}
