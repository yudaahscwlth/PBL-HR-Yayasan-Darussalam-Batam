"use client";

import { useState, useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import BottomNavbar from "@/components/BottomNavbar";
import { User } from "@/types/auth";

export default function RoleBasedProfilePage() {
  const { user, logout, isAuthenticated } = useAuthStore();
  const router = useRouter();
  const [activeTab, setActiveTab] = useState("profile");

  useEffect(() => {
    console.log("=== ROLE-BASED PROFILE PAGE ===");
    console.log("isAuthenticated:", isAuthenticated);
    console.log("user:", user);
    console.log("user roles:", user?.roles);
    console.log("================================");

    // Only redirect if not authenticated
    if (isAuthenticated === false) {
      console.log("❌ Not authenticated, redirecting to login");
      router.push("/");
    }
  }, [isAuthenticated, router]);

  const getUserRoleDisplay = (user: User | null): string => {
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
    // Redirect to the general profile edit page for all roles
    router.push("/profile/edit");
  };

  // Get role-specific menu items with sections
  const getRoleBasedMenuItems = () => {
    if (!user?.roles) return [];

    const isAdmin = user.roles.includes("superadmin") || user.roles.includes("kepala yayasan") || user.roles.includes("direktur pendidikan");
    const isHRD = user.roles.includes("kepala hrd") || user.roles.includes("staff hrd");

    interface MenuItem {
      title: string;
      icon: React.ReactNode;
      onClick: () => void;
    }

    // Common account items for all roles
    const accountItems: MenuItem[] = [
      {
        title: "Profile",
        icon: (
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        ),
        onClick: handleProfileEdit,
      },
    ];

    let otherItems: MenuItem[] = [];

    if (isAdmin) {
      otherItems = [
        {
          title: "Jabatan",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          ),
          onClick: () => router.push("/admin/jabatan"),
        },
        {
          title: "Departemen",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          ),
          onClick: () => router.push("/admin/departemen"),
        },
        {
          title: "Kantor",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          ),
          onClick: () => router.push("/admin/kantor"),
        },
        {
          title: "Sosial Media",
          icon: (
            <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z" />
            </svg>
          ),
          onClick: () => router.push("/admin/social-media"),
        },
        {
          title: "Tahun Ajaran",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          ),
          onClick: () => router.push("/admin/tahun-ajaran"),
        },
        {
          title: "Kategori Evaluasi",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
          ),
          onClick: () => router.push("/admin/kategori-evaluasi"),
        },
      ];
    }

    if (isHRD) {
      otherItems = [
        {
          title: "Jabatan",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          ),
          onClick: () => router.push("/hrd/jabatan"),
        },
        {
          title: "Departemen",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          ),
          onClick: () => router.push("/hrd/departemen"),
        },
        {
          title: "Kantor",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          ),
          onClick: () => router.push("/hrd/kantor"),
        },
        {
          title: "Sosial Media",
          icon: (
            <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z" />
            </svg>
          ),
          onClick: () => router.push("/hrd/social-media"),
        },
        {
          title: "Tahun Ajaran",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          ),
          onClick: () => router.push("/hrd/tahun-ajaran"),
        },
        {
          title: "Kategori Evaluasi",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
          ),
          onClick: () => router.push("/hrd/kategori-evaluasi"),
        },
      ];
    }

    // Employee and other roles only have Account section (Profile only)
    // No additional items for other roles

    const sections = [{ category: "ACCOUNT", items: accountItems }];

    // Only add OTHER section if there are items
    if (otherItems.length > 0) {
      sections.push({ category: "OTHER", items: otherItems });
    }

    return sections;
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

  const menuItems = getRoleBasedMenuItems();

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

        {/* Role-based Menu Sections */}
        {menuItems.map((section, sectionIndex) => (
          <div key={sectionIndex} className="mb-6">
            <h2 className="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wide">{section.category}</h2>
            <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
              {section.items.map((item, itemIndex) => (
                <div key={itemIndex}>
                  <button onClick={item.onClick} className="w-full px-4 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <div className="flex items-center">
                      <div className="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <div className="text-blue-600">{item.icon}</div>
                      </div>
                      <span className="text-gray-800 font-medium">{item.title}</span>
                    </div>
                    <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                  {itemIndex < section.items.length - 1 && <div className="border-t border-gray-100"></div>}
                </div>
              ))}
            </div>
          </div>
        ))}

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
