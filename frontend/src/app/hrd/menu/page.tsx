"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import MenuPage from "@/components/MenuPage";
import { hrdMenuConfig } from "@/config/menuConfig";
import AccessControl from "@/components/AccessControl";

export default function HRDMenu() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState("menu");

  const handleTabChange = (tab: string) => {
    setActiveTab(tab);
    if (tab === "dashboard") router.push("/hrd/dashboard");
    else if (tab === "notifikasi") router.push("/hrd/announcements");
    else if (tab === "profile") router.push("/hrd/profile");
  };

  // Add onClick handlers to menu items
  const menuSectionsWithHandlers = hrdMenuConfig.map((section) => ({
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
    <AccessControl allowedRoles={["kepala hrd", "staff hrd"]}>
      <MenuPage menuSections={menuSectionsWithHandlers} activeTab={activeTab} onTabChange={handleTabChange} />
    </AccessControl>
  );
}
