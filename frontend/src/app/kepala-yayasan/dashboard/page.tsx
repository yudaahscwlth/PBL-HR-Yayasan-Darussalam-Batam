// src/app/kepala-yayasan/dashboard/page.tsx
"use client";

import { useState, useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import { apiClient } from "@/lib/api";
import BottomNavbar from "@/components/BottomNavbar";
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

export default function KepalaYayasanDashboard() {
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

  const [isLoading, setIsLoading] = useState(false);

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

  const getCurrentDate = () => {
    const now = new Date();
    const days = [
      "Minggu",
      "Senin",
      "Selasa",
      "Rabu",
      "Kamis",
      "Jumat",
      "Sabtu",
    ];
    const months = [
      "Januari",
      "Februari",
      "Maret",
      "April",
      "Mei",
      "Juni",
      "Juli",
      "Agustus",
      "September",
      "Oktober",
      "November",
      "Desember",
    ];
    return `${days[now.getDay()]}, ${now.getDate()} ${
      months[now.getMonth()]
    } ${now.getFullYear()}`;
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
    <AccessControl allowedRoles={["kepala yayasan"]}>
      <div className="min-h-screen bg-gray-100 pb-8">
        {/* Header */}
        <div className="bg-[#1e4d8b] px-5 pt-3 pb-3 text-white rounded-b-3xl">
          <div className="flex items-center justify-between mb-1">
            <div className="flex-1">
              <p className="text-sm opacity-90">Selamat Datang,</p>
              <h1 className="text-xl font-bold">
                {user?.profile_pribadi?.nama_lengkap || user?.email || "User"}
              </h1>
              <div className="flex items-center mt-1">
                <svg
                  className="w-4 h-4 mr-1.5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
                <span className="text-xs">{getCurrentDate()}</span>
              </div>
            </div>
            <div className="w-14 h-14 bg-white rounded-full flex items-center justify-center flex-shrink-0">
              <svg
                className="w-8 h-8 text-gray-400"
                fill="currentColor"
                viewBox="0 0 24 24"
              >
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
              </svg>
            </div>
          </div>
        </div>

        {/* Daily Statistics */}
        <div className="px-5 mb-4">
          <div className="flex items-center mb-3 mt-4">
            <svg
              className="w-5 h-5 text-[#1e4d8b] mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
              />
            </svg>
            <h2 className="text-base font-bold text-gray-800">
              Statistik Harian
            </h2>
          </div>

          <div className="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
            <div className="bg-white rounded-2xl shadow-md p-4 min-w-[140px] flex-shrink-0">
              <div className="flex items-center justify-center mb-2">
                <svg
                  className="w-8 h-8 text-[#1e4d8b]"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                  />
                </svg>
              </div>
              <div className="text-3xl font-bold text-[#1e4d8b] text-center mb-1">
                {dashboardData.todayAttendance}
              </div>
              <p className="text-xs text-gray-600 text-center font-medium">
                Masuk Hari Ini
              </p>
              <p className="text-xs text-gray-400 text-center">
                Dari {dashboardData.totalEmployees} karyawan
              </p>
            </div>

            <div className="bg-white rounded-2xl shadow-md p-4 min-w-[140px] flex-shrink-0">
              <div className="flex items-center justify-center mb-2">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  className="w-8 h-8 text-[#1e4d8b]"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth="2"
                >
                  <path d="M13 21H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v6.5" />
                  <path d="M16 3v4M8 3v4M4 11h16" />
                  <path d="m22 20l-5-5m0 5l5-5" />
                </svg>
              </div>
              <div className="text-3xl font-bold text-[#1e4d8b] text-center mb-1">
                {dashboardData.todayLeave}
              </div>
              <p className="text-xs text-gray-600 text-center font-medium">
                Cuti Hari Ini
              </p>
              <p className="text-xs text-gray-400 text-center">karyawan</p>
            </div>

            <div className="bg-white rounded-2xl shadow-md p-4 min-w-[140px] flex-shrink-0">
              <div className="flex items-center justify-center mb-2">
                <svg
                  className="w-8 h-8 text-[#1e4d8b]"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <div className="text-3xl font-bold text-[#1e4d8b] text-center mb-1">
                {dashboardData.lateEmployees}
              </div>
              <p className="text-xs text-gray-600 text-center font-medium">
                Terlambat
              </p>
              <p className="text-xs text-gray-400 text-center">karyawan</p>
            </div>

            <div className="bg-white rounded-2xl shadow-md p-4 min-w-[140px] flex-shrink-0">
              <div className="flex items-center justify-center mb-2">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  className="w-8 h-8 text-[#1e4d8b]"
                  viewBox="0 0 48 48"
                  fill="currentColor"
                >
                  <defs>
                    <mask id="SVGdHQ1QFib">
                      <g
                        fill="none"
                        stroke="#fff"
                        strokeLinejoin="round"
                        strokeWidth="4"
                      >
                        <path
                          fill="#333"
                          d="M19 20a7 7 0 1 0 0-14a7 7 0 0 0 0 14Z"
                        />
                        <path
                          strokeLinecap="round"
                          d="m33 31l8 8m-8 0l8-8m-14-3h-8.2c-4.48 0-6.72 0-8.432.872a8 8 0 0 0-3.496 3.496C6 34.08 6 36.32 6 40.8V42h21"
                        />
                      </g>
                    </mask>
                  </defs>
                  <path
                    fill="currentColor"
                    d="M0 0h48v48H0z"
                    mask="url(#SVGdHQ1QFib)"
                  />
                </svg>
              </div>
              <div className="text-3xl font-bold text-[#1e4d8b] text-center mb-1">
                {dashboardData.absentEmployees}
              </div>
              <p className="text-xs text-gray-600 text-center font-medium">
                Tidak Masuk
              </p>
              <p className="text-xs text-gray-400 text-center">karyawan</p>
            </div>
          </div>
        </div>

        {/* Employee Distribution by Job Title */}
        <div className="px-5 mb-4">
          <div className="bg-white rounded-2xl shadow-md p-5">
            <h2 className="text-base font-bold text-gray-800 mb-4">
              Pegawai Berdasarkan Jabatan
            </h2>
            <div className="flex items-center mb-4">
              <div className="w-36 h-36">
                {renderPieChart(dashboardData.jobTitleDistribution)}
              </div>
              <div className="ml-4">
                <p className="text-sm font-semibold text-gray-700">
                  Total: {dashboardData.totalEmployees} Orang
                </p>
              </div>
            </div>
            <div className="space-y-2.5">
              {dashboardData.jobTitleDistribution.map((item, index) => (
                <div key={index} className="flex items-center justify-between">
                  <div className="flex items-center flex-1">
                    <div
                      className="w-3 h-3 rounded-full mr-2.5 flex-shrink-0"
                      style={{ backgroundColor: item.color }}
                    ></div>
                    <span className="text-sm text-gray-700">{item.name}</span>
                  </div>
                  <span className="bg-blue-500 text-white text-xs font-semibold px-2.5 py-1 rounded-full ml-2">
                    {item.count}
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Employee Distribution by Department */}
        <div className="px-5 mb-28">
          <div className="bg-white rounded-2xl shadow-md p-5">
            <h2 className="text-base font-bold text-gray-800 mb-4">
              Pegawai Berdasarkan Departemen
            </h2>
            <div className="flex items-center mb-4">
              <div className="w-36 h-36">
                {renderPieChart(dashboardData.departmentDistribution)}
              </div>
              <div className="ml-4">
                <p className="text-sm font-semibold text-gray-700">
                  Total: {dashboardData.totalEmployees} Orang
                </p>
              </div>
            </div>
            <div className="space-y-2.5">
              {dashboardData.departmentDistribution.map((item, index) => (
                <div key={index} className="flex items-center justify-between">
                  <div className="flex items-center flex-1">
                    <div
                      className="w-3 h-3 rounded-full mr-2.5 flex-shrink-0"
                      style={{ backgroundColor: item.color }}
                    ></div>
                    <span className="text-sm text-gray-700">{item.name}</span>
                  </div>
                  <span className="bg-blue-500 text-white text-xs font-semibold px-2.5 py-1 rounded-full ml-2">
                    {item.count}
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Bottom Navigation */}
        <BottomNavbar />

        <style jsx>{`
          .scrollbar-hide::-webkit-scrollbar {
            display: none;
          }
          .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
          }
        `}</style>
      </div>
    </AccessControl>
  );
}
