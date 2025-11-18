"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { useAuthStore } from "@/store/authStore";
import BottomNavbar from "@/components/BottomNavbar";
import AccessControl from "@/components/AccessControl";

export default function HRDProfile() {
  const router = useRouter();
  const { user, logout } = useAuthStore();
  const [activeTab, setActiveTab] = useState("profile");

  const handleLogout = async () => {
    try {
      await logout();
      router.push("/");
    } catch (error) {
      console.error("Logout error:", error);
    }
  };

  const getUserRoleDisplay = (): string => {
    if (!user?.roles) return "User";
    if (user.roles.includes("kepala hrd")) return "Kepala HRD";
    if (user.roles.includes("staff hrd")) return "Staff HRD";
    return "User";
  };

  const accountMenu = [
    {
      title: "Profile",
      icon: (
        <svg className="w-6 h-6 text-[#1e4d8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
      ),
      onClick: () => router.push("/hrd/profile/edit"),
    },
  ];

  const otherMenu = [
    {
      title: "Jabatan",
      icon: (
        <svg className="w-6 h-6 text-[#1e4d8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
          />
        </svg>
      ),
      path: "/hrd/jabatan",
      onClick: () => router.push("/hrd/jabatan"),
    },
    {
      title: "Departemen",
      icon: (
        <svg className="w-6 h-6 text-[#1e4d8b]" fill="currentColor" viewBox="0 0 24 24">
          <path d="M3 3h8v8H3V3m10 0h8v8h-8V3M3 13h8v8H3v-8m10 0h8v8h-8v-8z" />
        </svg>
      ),
      path: "/hrd/departemen",
      onClick: () => router.push("/hrd/departemen"),
    },
    {
      title: "Kantor",
      icon: (
        <svg className="w-6 h-6 text-[#1e4d8b]" fill="currentColor" viewBox="0 0 24 24">
          <path d="M3 3h18v18H3V3m2 2v14h14V5H5m2 2h10v2H7V7m0 4h10v2H7v-2m0 4h10v2H7v-2z" />
        </svg>
      ),
      path: "/hrd/kantor",
      onClick: () => router.push("/hrd/kantor"),
    },
    {
      title: "Social Media",
      icon: (
        <svg className="w-6 h-6 text-[#1e4d8b]" fill="currentColor" viewBox="0 0 24 24">
          <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z" />
        </svg>
      ),
      path: "/hrd/social-media",
      onClick: () => router.push("/hrd/social-media"),
    },
    {
      title: "Tahun Ajaran",
      icon: (
        <svg className="w-6 h-6 text-[#1e4d8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      ),
      path: "/hrd/tahun-ajaran",
      onClick: () => router.push("/hrd/tahun-ajaran"),
    },
    {
      title: "Kategori Evaluasi",
      icon: (
        <svg className="w-6 h-6 text-[#1e4d8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      ),
      path: "/hrd/kategori-evaluasi",
      onClick: () => router.push("/hrd/kategori-evaluasi"),
    },
  ];

  return (
    <AccessControl allowedRoles={["kepala hrd", "staff hrd"]}>
      <div className="min-h-screen bg-gray-100 pb-28">
        {/* Main Content */}
        <div className="px-5 py-6">
          {/* Profile Header */}
          <div className="mb-8 text-center">
            <div className="w-24 h-24 bg-blue-100 rounded-full mx-auto mb-4 flex items-center justify-center">
              <svg className="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h1 className="text-xl font-bold text-gray-800 mb-1">{user?.profile_pribadi?.nama_lengkap || user?.email}</h1>
            <p className="text-gray-600 text-sm">{getUserRoleDisplay()}</p>
          </div>

          {/* Account Section */}
          <div className="mb-8">
            <h2 className="text-sm font-bold text-gray-400 uppercase mb-4 tracking-wider">ACCOUNT</h2>
            <div className="bg-white rounded-xl overflow-hidden">
              {accountMenu.map((item, index) => (
                <button key={index} className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition-colors border-b last:border-b-0 border-gray-100" onClick={item.onClick}>
                  <div className="flex items-center gap-4">
                    {item.icon}
                    <span className="text-base text-gray-800">{item.title}</span>
                  </div>
                  <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              ))}
            </div>
          </div>

          {/* Other Section */}
          <div className="mb-8">
            <h2 className="text-sm font-bold text-gray-400 uppercase mb-4 tracking-wider">Other</h2>
            <div className="bg-white rounded-xl overflow-hidden">
              {otherMenu.map((item, index) => (
                <button
                  key={index}
                  className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition-colors border-b last:border-b-0 border-gray-100"
                  onClick={() => (item.path ? router.push(item.path) : console.log(`Clicked: ${item.title}`))}
                >
                <button key={index} className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition-colors border-b last:border-b-0 border-gray-100" onClick={item.onClick}>
                  <div className="flex items-center gap-4">
                    {item.icon}
                    <span className="text-base text-gray-800">{item.title}</span>
                  </div>
                  <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              ))}
            </div>
          </div>

          {/* Logout Section */}
          <div className="mb-8">
            <div className="bg-white rounded-xl overflow-hidden">
              <button onClick={handleLogout} className="w-full flex items-center justify-center px-5 py-4 hover:bg-red-50 transition-colors text-red-600">
                <svg className="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span className="text-base font-medium">Logout</span>
              </button>
            </div>
          </div>
        </div>

        {/* Bottom Navigation */}
        <BottomNavbar activeTab={activeTab} onTabChange={setActiveTab} />
      </div>
    </AccessControl>
  );
}
