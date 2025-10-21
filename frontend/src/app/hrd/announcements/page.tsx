"use client";

import { useState } from "react";
import { useAuthStore } from "@/store/authStore";
import BottomNavbar from "@/components/BottomNavbar";

export default function HRDAnnouncements() {
  const { user } = useAuthStore();

  return (
    <div className="min-h-screen bg-gray-100 pb-28">
      {/* Header Section */}
      <div className="bg-[#1e4d8b] px-5 pt-3 pb-16 text-white rounded-b-3xl">
        <div className="flex items-center justify-between mb-1">
          <div className="flex-1">
            <p className="text-sm opacity-90">Selamat Datang,</p>
            <h1 className="text-xl font-bold">{user?.profile_pribadi?.nama_lengkap || user?.email || "User"}</h1>
          </div>
          <div className="w-14 h-14 bg-white rounded-full flex items-center justify-center flex-shrink-0">
            <svg className="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
            </svg>
          </div>
        </div>
      </div>

      {/* Content */}
      <div className="px-5 mt-4">
        <div className="bg-white rounded-2xl shadow-md p-6">
          <h2 className="text-lg font-bold text-gray-800 mb-4">Pengumuman</h2>
          <p className="text-gray-600">Fitur pengumuman akan segera tersedia...</p>
        </div>
      </div>

      {/* Bottom Navigation */}
      <BottomNavbar />
    </div>
  );
}
