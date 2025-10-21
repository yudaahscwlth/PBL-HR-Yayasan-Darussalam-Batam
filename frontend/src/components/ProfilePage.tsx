"use client";

import { useState, useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import BottomNavbar from "@/components/BottomNavbar";
import { User } from "@/types/auth";

interface ProfilePageProps {
  userRole: string;
  basePath: string;
}

export default function ProfilePage({ userRole, basePath }: ProfilePageProps) {
  const { user, logout, isAuthenticated } = useAuthStore();
  const router = useRouter();
  const [activeTab, setActiveTab] = useState("profile");
  const [isLoading, setIsLoading] = useState(true);

  // Redirect if not authenticated or not authorized
  useEffect(() => {
    console.log("=== PROFILE PAGE USEEFFECT ===");
    console.log("isAuthenticated:", isAuthenticated);
    console.log("user:", user);
    console.log("user roles:", user?.roles);
    console.log("userRole:", userRole);
    console.log("basePath:", basePath);
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
      const isAuthorized = checkUserRole(user, userRole);
      console.log("isAuthorized:", isAuthorized);

      if (!isAuthorized) {
        console.log("❌ Not authorized, redirecting to unauthorized");
        router.push("/unauthorized");
        return;
      }

      console.log("✅ Access granted");
      setIsLoading(false);
    }
  }, [isAuthenticated, user, router, isLoading, userRole, basePath]);

  const checkUserRole = (user: User | null, requiredRole: string): boolean => {
    if (!user?.roles) return false;

    switch (requiredRole) {
      case "admin":
        return user.roles.includes("superadmin") || user.roles.includes("kepala yayasan") || user.roles.includes("direktur pendidikan");
      case "hrd":
        return user.roles.includes("kepala hrd") || user.roles.includes("staff hrd");
      case "employee":
        return user.roles.includes("kepala departemen") || user.roles.includes("kepala sekolah") || user.roles.includes("tenaga pendidik");
      default:
        return false;
    }
  };

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

  const menuItems = [
    {
      category: "ACCOUNT",
      items: [
        {
          title: "Profile",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          ),
          onClick: () => router.push(`${basePath}/profile/edit`),
        },
      ],
    },
    {
      category: "Other",
      items: [
        {
          title: "Jabatan",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          ),
          onClick: () => router.push(`${basePath}/jabatan`),
        },
        {
          title: "Departemen",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          ),
          onClick: () => router.push(`${basePath}/departemen`),
        },
        {
          title: "Kantor",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          ),
          onClick: () => router.push(`${basePath}/kantor`),
        },
        {
          title: "Social Media",
          icon: (
            <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
            </svg>
          ),
          onClick: () => router.push(`${basePath}/social-media`),
        },
        {
          title: "Tahun Ajaran",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          ),
          onClick: () => router.push(`${basePath}/tahun-ajaran`),
        },
        {
          title: "Kategori Evaluasi",
          icon: (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
          ),
          onClick: () => router.push(`${basePath}/kategori-evaluasi`),
        },
      ],
    },
  ];

  // Show loading while checking authentication
  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Loading profile...</p>
        </div>
      </div>
    );
  }

  // If not authenticated or no user, don't render (will redirect)
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

        {/* Menu Sections */}
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
