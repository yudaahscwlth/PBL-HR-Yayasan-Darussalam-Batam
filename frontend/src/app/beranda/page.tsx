"use client";

import Image from "next/image";
import { useState } from "react";

export default function BerandaPage() {
  const [user] = useState({
    name: "Kepala HRD",
    email: "kepalahrd@darussalam.com",
    role: "Kepala HRD",
  });

  return (
    <div className="min-h-screen bg-[#1e4d8b] relative overflow-hidden">
      {/* Background Wave Decoration */}
      <div className="absolute bottom-0 left-0 right-0 h-[45%]">
        <svg
          viewBox="0 0 1440 320"
          className="absolute bottom-0 w-full h-full"
          preserveAspectRatio="none"
        >
          <path
            fill="#ffffff"
            fillOpacity="1"
            d="M0,160L48,149.3C96,139,192,117,288,128C384,139,480,181,576,181.3C672,181,768,139,864,122.7C960,107,1056,117,1152,133.3C1248,149,1344,171,1392,181.3L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
          ></path>
        </svg>
      </div>

      {/* Header Section */}
      <div className="relative z-10 flex flex-col items-center pt-8 sm:pt-12 px-4">
        <div className="flex items-center justify-center mb-6">
          <div className="bg-white rounded-full p-3 shadow-lg">
            <Image
              src="/icons/logo-original.png"
              alt="Logo Darussalam"
              width={80}
              height={80}
              className="w-16 h-16 sm:w-20 sm:h-20"
            />
          </div>
        </div>
        <h1 className="text-white text-xl sm:text-2xl font-bold text-center mb-2">
          HR YAYASAN
          <br />
          DARUSSALAM
        </h1>
        <p className="text-white/80 text-sm text-center mb-8">
          Selamat Datang, {user.name}
        </p>
      </div>

      {/* Main Content - Menu Grid */}
      <div className="relative z-10 flex-1 flex items-start justify-center px-4 pb-8 mt-8">
        <div className="w-full max-w-5xl">
          <div className="bg-white rounded-2xl shadow-2xl p-6 sm:p-8">
            {/* Stats Summary */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
              <div className="text-center p-4 bg-blue-50 rounded-lg">
                <div className="text-3xl font-bold text-[#0066cc]">248</div>
                <div className="text-sm text-gray-600 mt-1">Total Pegawai</div>
              </div>
              <div className="text-center p-4 bg-blue-50 rounded-lg">
                <div className="text-3xl font-bold text-[#0066cc]">235</div>
                <div className="text-sm text-gray-600 mt-1">Hadir Hari Ini</div>
              </div>
              <div className="text-center p-4 bg-blue-50 rounded-lg">
                <div className="text-3xl font-bold text-[#0066cc]">12</div>
                <div className="text-sm text-gray-600 mt-1">Pengajuan Cuti</div>
              </div>
              <div className="text-center p-4 bg-blue-50 rounded-lg">
                <div className="text-3xl font-bold text-[#0066cc]">8</div>
                <div className="text-sm text-gray-600 mt-1">
                  Evaluasi Pending
                </div>
              </div>
            </div>

            {/* Menu Grid */}
            <div className="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
              {/* Absensi */}
              <a
                href="/absensi"
                className="flex flex-col items-center justify-center p-6 bg-[#0066cc] rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group"
              >
                <div className="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                  <svg
                    className="w-8 h-8 text-[#0066cc]"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                    />
                  </svg>
                </div>
                <h3 className="text-white font-semibold text-center">
                  Absensi
                </h3>
              </a>

              {/* Pengajuan Cuti */}
              <a
                href="/cuti"
                className="flex flex-col items-center justify-center p-6 bg-[#0066cc] rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group"
              >
                <div className="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                  <svg
                    className="w-8 h-8 text-[#0066cc]"
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
                </div>
                <h3 className="text-white font-semibold text-center">
                  Pengajuan Cuti
                </h3>
              </a>

              {/* Evaluasi Pegawai */}
              <a
                href="/evaluasi"
                className="flex flex-col items-center justify-center p-6 bg-[#0066cc] rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group"
              >
                <div className="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                  <svg
                    className="w-8 h-8 text-[#0066cc]"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                    />
                  </svg>
                </div>
                <h3 className="text-white font-semibold text-center">
                  Evaluasi Pegawai
                </h3>
              </a>

              {/* Data Pegawai */}
              <a
                href="/pegawai"
                className="flex flex-col items-center justify-center p-6 bg-[#0066cc] rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group"
              >
                <div className="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                  <svg
                    className="w-8 h-8 text-[#0066cc]"
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
                <h3 className="text-white font-semibold text-center">
                  Data Pegawai
                </h3>
              </a>

              {/* Laporan */}
              <a
                href="/laporan"
                className="flex flex-col items-center justify-center p-6 bg-[#0066cc] rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group"
              >
                <div className="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                  <svg
                    className="w-8 h-8 text-[#0066cc]"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                    />
                  </svg>
                </div>
                <h3 className="text-white font-semibold text-center">
                  Laporan
                </h3>
              </a>

              {/* Pengaturan */}
              <a
                href="/pengaturan"
                className="flex flex-col items-center justify-center p-6 bg-[#0066cc] rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group"
              >
                <div className="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                  <svg
                    className="w-8 h-8 text-[#0066cc]"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                    />
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                  </svg>
                </div>
                <h3 className="text-white font-semibold text-center">
                  Pengaturan
                </h3>
              </a>
            </div>

            {/* Logout Button */}
            <button
              onClick={() => {
                window.location.href = "/";
              }}
              className="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold transition-colors flex items-center justify-center gap-2 shadow-lg"
            >
              <svg
                className="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                />
              </svg>
              Logout
            </button>

            {/* Footer */}
            <div className="mt-6 text-center">
              <p className="text-gray-600 text-sm">
                © 2025 PBL 221 - Yayasan Darussalam
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
