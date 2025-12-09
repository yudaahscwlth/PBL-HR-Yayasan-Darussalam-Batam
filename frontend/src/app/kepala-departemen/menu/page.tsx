"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { kepalaDepartemenMenuConfig } from "@/config/menuConfig";
import AccessControl from "@/components/AccessControl";
import BottomNavbar from "@/components/BottomNavbar";
import MenuCard from "@/components/MenuCard";

export default function KDMenu() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState("menu");

  const handleTabChange = (tab: string) => {
    setActiveTab(tab);
    if (tab === "dashboard") router.push("/kepala-departemen/dashboard");
    else if (tab === "notifikasi") router.push("/kepala-departemen/notifications");
    else if (tab === "profile") router.push("/kepala-departemen/profile");
  };

  const menuSectionsWithHandlers = kepalaDepartemenMenuConfig.map((section) => ({
    ...section,
    items: section.items.map((item) => ({
      ...item,
      onClick: () => {
        if (item.path) {
          router.push(item.path);
        }
      },
    })),
  }));

  return (
    <AccessControl allowedRoles={["kepala departemen"]}>
      <div className="min-h-screen bg-gray-100 pb-28">
        <div className="px-5 py-6">
          {menuSectionsWithHandlers.map((section, sectionIndex) => (
            <div key={sectionIndex} className="mb-8">
              <h2 className="text-sm font-semibold text-gray-400 uppercase mb-4">{section.category}</h2>
              <div className="grid grid-cols-2 gap-4">
                {section.items.map((item, itemIndex) => (
                  <MenuCard key={itemIndex} title={item.title} icon={item.icon} onClick={item.onClick} />
                ))}
              </div>
            </div>
          ))}
        </div>
        <BottomNavbar activeTab={activeTab} onTabChange={handleTabChange} />
      </div>
    </AccessControl>
  );
}
