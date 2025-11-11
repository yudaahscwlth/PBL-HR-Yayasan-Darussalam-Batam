"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import MenuPage from "@/components/MenuPage";
import { kepalaYayasanMenuConfig } from "@/config/menuConfig";

export default function KepalaYayasanMenu() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState("menu");

  const handleTabChange = (tab: string) => {
    setActiveTab(tab);
    if (tab === "dashboard") router.push("/kepala-yayasan/dashboard");
    else if (tab === "notifikasi") router.push("/kepala-yayasan/announcements");
    else if (tab === "profile") router.push("/kepala-yayasan/dashboard"); // Profile page tidak ada, redirect ke dashboard
  };

  const menuSectionsWithHandlers = kepalaYayasanMenuConfig.map((section) => ({
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
