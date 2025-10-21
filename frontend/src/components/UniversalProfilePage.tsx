"use client";

import { useState, useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import BottomNavbar from "@/components/BottomNavbar";

export default function UniversalProfilePage() {
  const { user, logout, isAuthenticated } = useAuthStore();
  const router = useRouter();
  const [activeTab, setActiveTab] = useState("profile");

  useEffect(() => {
    console.log("=== UNIVERSAL PROFILE PAGE ===");
    console.log("isAuthenticated:", isAuthenticated);
    console.log("user:", user);
    console.log("user roles:", user?.roles);
    console.log("=============================");

    // Only redirect if not authenticated
    if (isAuthenticated === false) {
      console.log("❌ Not authenticated, redirecting to login");
      router.push("/");
    }
  }, [isAuthenticated, router]);

  const getUserRoleDisplay = (user: any): string => {
    if (!user?.roles) return "User";

    if (user.roles.includes("superadmin")) return "Super Admin";
    if (user.roles.includes("kepala yayasan")) return "Kepala Yayasan";
    if (user.roles.includes("direktur pendidikan")) return "Direktur Pendidikan";
    if (user.roles.includes("kepala hrd")) return "Kepala HRD";
    if (user.roles.includes("staff hrd")) return "Staff HRD";
    if (user.roles.includes("kepala departemen")) return "Kepala Departemen";
    if (user.roles.includes("kepala sekolah")) return "Kepala Sekolah";
    if (user.roles.includes("tenaga pendidik")) return "Tenaga Pendidik";

    return "User";
  };

  const handleLogout = async () => {
    try {
      console.log("🚪 Logging out...");
      await logout();
      router.push("/");
    } catch (error) {
      console.error("❌ Logout error:", error);
    }
  };

  const handleProfileEdit = () => {
    if (!user?.roles) return;

    // Determine the correct profile edit path based on user role
    if (user.roles.includes("superadmin") || user.roles.includes("kepala yayasan") || user.roles.includes("direktur pendidikan")) {
      router.push("/admin/profile/edit");
    } else if (user.roles.includes("kepala hrd") || user.roles.includes("staff hrd")) {
      router.push("/hrd/profile/edit");
    } else if (user.roles.includes("kepala departemen") || user.roles.includes("kepala sekolah") || user.roles.includes("tenaga pendidik")) {
      router.push("/employee/profile/edit");
    }
  };

  // Show loading if not authenticated yet
  if (isAuthenticated === null) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Loading profile...</p>
        </div>
      </div>
    );
  }

  // If not authenticated, don't render (will redirect)
  if (!isAuthenticated || !user) {
    return null;
  }

  return (
    <div className="min-h-screen bg-white">
      {/* Main Content */}
      <div className="px-4 py-6">
        {/* Profile Header */}
        <div className="mb-8 text-center">
          <div className="w-24 h-24 bg-blue-100 rounded-full mx-auto mb-4 flex items-center justify-center">
            <svg className="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <h1 className="text-xl font-bold text-gray-800 mb-1">{user?.profile_pribadi?.nama_lengkap || user?.email}</h1>
          <p className="text-gray-600 text-sm">{getUserRoleDisplay(user)}</p>
        </div>

        {/* Account Section Only */}
        <div className="mb-6">
          <h2 className="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wide">ACCOUNT</h2>
          <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <button onClick={handleProfileEdit} className="w-full px-4 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
              <div className="flex items-center">
                <div className="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                  <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <span className="text-gray-800 font-medium">Profile</span>
              </div>
              <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>

        {/* Logout Button */}
        <div className="mt-8">
          <button onClick={handleLogout} className="w-full bg-red-50 border border-red-200 text-red-600 py-4 px-4 rounded-lg flex items-center justify-center hover:bg-red-100 transition-colors">
            <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Logout
          </button>
        </div>
      </div>

      {/* Bottom Navigation */}
      <BottomNavbar activeTab={activeTab} onTabChange={setActiveTab} />

      {/* Add bottom padding to account for fixed navigation */}
      <div className="h-24"></div>
    </div>
  );
}
