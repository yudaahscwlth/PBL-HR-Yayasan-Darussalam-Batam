"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import MenuPage from "@/components/MenuPage";
import { kepalaSekolahMenuConfig } from "@/config/menuConfig";

export default function KepalaSekolahMenu() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState("menu");

  const handleTabChange = (tab: string) => {
    setActiveTab(tab);
    if (tab === "dashboard") router.push("/kepala-sekolah/dashboard");
    else if (tab === "notifikasi") router.push("/kepala-sekolah/announcements");
    else if (tab === "profile") router.push("/kepala-sekolah/profile");
  };

  const menuSectionsWithHandlers = kepalaSekolahMenuConfig.map((section) => ({
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
