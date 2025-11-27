"use client";

import { useRouter } from "next/navigation";
import Link from "next/link";
import { useAuthStore } from "@/store/authStore";
import AccessControl from "@/components/AccessControl";
import BottomNavbar from "@/components/BottomNavbar";
import { ReactNode } from "react";

interface MenuItem {
  title: string;
  icon: ReactNode;
  path: string;
}

interface ProfileMenuProps {
  allowedRoles: string[];
  editPath: string;
  otherItems: MenuItem[];
}

export default function ProfileMenu({ allowedRoles, editPath, otherItems }: ProfileMenuProps) {
  const router = useRouter();
  const { logout } = useAuthStore();

  const handleLogout = async () => {
    try {
      console.log("🚪 Logging out...");
      await logout();
      router.push("/");
    } catch (error) {
      console.error("❌ Logout error:", error);
    }
  };

  const accountMenu = [
    {
      title: "Profile",
      icon: (
        <svg className="w-6 h-6 text-[#1e4d8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
      ),
      path: editPath,
    },
  ];

  return (
    <AccessControl allowedRoles={allowedRoles}>
      <div className="min-h-screen bg-gray-100 pb-28">
        <div className="px-5 py-6">
          {/* Account Section */}
          <div className="mb-8">
            <h2 className="text-sm font-bold text-gray-400 uppercase mb-4 tracking-wider">ACCOUNT</h2>
            <div className="bg-white rounded-xl overflow-hidden">
              {accountMenu.map((item, index) => (
                <Link
                  key={index}
                  href={item.path}
                  className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition-colors border-b last:border-b-0 border-gray-100"
                >
                  <div className="flex items-center gap-4">
                    {item.icon}
                    <span className="text-base text-gray-800">{item.title}</span>
                  </div>
                  <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </Link>
              ))}
            </div>
          </div>

          {/* Other Section */}
          {otherItems.length > 0 && (
            <div className="mb-8">
              <h2 className="text-sm font-bold text-gray-400 uppercase mb-4 tracking-wider">Other</h2>
              <div className="bg-white rounded-xl overflow-hidden">
                {otherItems.map((item, index) => (
                  <Link
                    key={index}
                    href={item.path}
                    className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition-colors border-b last:border-b-0 border-gray-100"
                  >
                    <div className="flex items-center gap-4">
                      {item.icon}
                      <span className="text-base text-gray-800">{item.title}</span>
                    </div>
                    <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                  </Link>
                ))}
              </div>
            </div>
          )}

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

        <BottomNavbar />
      </div>
    </AccessControl>
  );
}
