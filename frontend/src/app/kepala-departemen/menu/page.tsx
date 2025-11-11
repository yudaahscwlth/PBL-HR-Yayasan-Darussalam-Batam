"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import MenuPage from "@/components/MenuPage";
import { kepalaDepartemenMenuConfig } from "@/config/menuConfig";

export default function KepalaDepartemenMenu() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState("menu");

  const handleTabChange = (tab: string) => {
    setActiveTab(tab);
    if (tab === "dashboard") router.push("/kepala-departemen/dashboard");
    else if (tab === "notifikasi") router.push("/kepala-departemen/announcements");
    else if (tab === "profile") router.push("/kepala-departemen/dashboard"); // Profile page tidak ada, redirect ke dashboard
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

  return <MenuPage menuSections={menuSectionsWithHandlers} activeTab={activeTab} onTabChange={handleTabChange} />;
}
