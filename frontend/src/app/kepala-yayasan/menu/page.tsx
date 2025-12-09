"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import AccessControl from "@/components/AccessControl";
import BottomNavbar from "@/components/BottomNavbar";
import MenuCard from "@/components/MenuCard";

export default function KepalaYayasanMenu() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState("menu");

  const handleTabChange = (tab: string) => {
    setActiveTab(tab);
    if (tab === "dashboard") router.push("/kepala-yayasan/dashboard");
    else if (tab === "notifikasi") router.push("/kepala-yayasan/notifications");
    else if (tab === "profile") router.push("/kepala-yayasan/profile");
  };

  const menuItems = [
    {
      title: "Absensi Hari Ini",
      icon: (
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
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
          />
        </svg>
      ),
      path: "/kepala-yayasan/absensi-hari-ini",
    },
    {
      title: "Pegawai",
      icon: (
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
      ),
      path: "/kepala-yayasan/kelola/pegawai",
    },
  ];

  return (
    <AccessControl allowedRoles={["kepala yayasan"]}>
      <div className="min-h-screen bg-gray-100 pb-28">
        {/* Main Content */}
        <div className="px-5 py-6">
          {/* Menu Section */}
          <div className="mb-8">
            <h2 className="text-sm font-semibold text-gray-400 uppercase mb-4">
              Menu
            </h2>
            <div className="grid grid-cols-2 gap-4">
              {menuItems.map((item, itemIndex) => (
                <MenuCard
                  key={itemIndex}
                  title={item.title}
                  icon={item.icon}
                  onClick={() => {
                    if (item.path) {
                      router.push(item.path);
                    }
                  }}
                />
              ))}
            </div>
          </div>
        </div>

        {/* Bottom Navigation */}
        <BottomNavbar activeTab={activeTab} onTabChange={handleTabChange} />
      </div>
    </AccessControl>
  );
}


