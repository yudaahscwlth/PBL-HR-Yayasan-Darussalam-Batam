"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import MenuPage from "@/components/MenuPage";
import { employeeMenuConfig } from "@/config/menuConfig";

export default function EmployeeMenu() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState("menu");

  const handleTabChange = (tab: string) => {
    setActiveTab(tab);
    if (tab === "dashboard") router.push("/employee/dashboard");
    else if (tab === "notifikasi") router.push("/employee/announcements");
    else if (tab === "profile") router.push("/employee/profile");
  };

  // Add onClick handlers to menu items
  const menuSectionsWithHandlers = employeeMenuConfig.map((section) => ({
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
