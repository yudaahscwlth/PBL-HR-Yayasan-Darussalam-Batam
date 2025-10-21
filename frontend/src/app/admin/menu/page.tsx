"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import MenuPage from "@/components/MenuPage";
import { adminMenuConfig } from "@/config/menuConfig";

export default function AdminMenu() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState("menu");

  const handleTabChange = (tab: string) => {
    setActiveTab(tab);
    if (tab === "dashboard") router.push("/admin/dashboard");
    else if (tab === "notifikasi") router.push("/admin/announcements");
    else if (tab === "profile") router.push("/admin/profile");
  };

  // Add onClick handlers to menu items
  const menuSectionsWithHandlers = adminMenuConfig.map((section) => ({
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

  return <MenuPage menuSections={menuSectionsWithHandlers} activeTab={activeTab} onTabChange={handleTabChange} />;
}
