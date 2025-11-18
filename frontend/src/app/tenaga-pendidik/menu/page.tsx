"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import MenuPage from "@/components/MenuPage";
import { tenagaPendidikMenuConfig } from "@/config/menuConfig";

export default function TenagaPendidikMenu() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState("menu");

  const handleTabChange = (tab: string) => {
    setActiveTab(tab);
    if (tab === "dashboard") router.push("/tenaga-pendidik/dashboard");
    else if (tab === "notifikasi") router.push("/tenaga-pendidik/announcements");
    else if (tab === "profile") router.push("/tenaga-pendidik/dashboard"); // Profile page tidak ada, redirect ke dashboard
  };

  const menuSectionsWithHandlers = tenagaPendidikMenuConfig.map((section) => ({
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

