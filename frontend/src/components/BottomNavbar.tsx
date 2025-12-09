"use client";

import { useMemo, useState, useEffect } from "react";
import { usePathname, useRouter } from "next/navigation";
import { useAuthStore } from "@/store/authStore";

interface BottomNavbarProps {
  activeTab?: string;
  onTabChange?: (tab: string) => void;
}

export default function BottomNavbar({ activeTab, onTabChange }: BottomNavbarProps) {
  const router = useRouter();
  const pathname = usePathname();
  const { user } = useAuthStore();
  const [isTransitioning, setIsTransitioning] = useState(false);
  const [currentTab, setCurrentTab] = useState(activeTab);
  const [notificationCount, setNotificationCount] = useState(0);

  // Fetch notification count
  useEffect(() => {
    const fetchNotificationCount = async () => {
      try {
        const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/api/notifications`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            'Accept': 'application/json',
          },
        });
        if (response.ok) {
          const data = await response.json();
          if (data.success && data.data) {
            // Get read notifications from localStorage
            const readNotifications = JSON.parse(localStorage.getItem('readNotifications') || '[]');
            // Count unread notifications
            const unreadCount = data.data.filter((n: any) => !readNotifications.includes(n.id)).length;
            setNotificationCount(unreadCount);
          }
        }
      } catch (error) {
        console.error('Error fetching notification count:', error);
      }
    };

    if (user) {
      fetchNotificationCount();
      // Refresh count every 30 seconds
      const interval = setInterval(fetchNotificationCount, 30000);
      return () => clearInterval(interval);
    }
  }, [user]);

  // Get role-based paths
  const getRoleBasedPaths = () => {
    if (!user?.roles) return { dashboard: "/", menu: "/", profile: "/", notifikasi: "/" };

    const isAdmin = user.roles.includes("superadmin");
    const isKepalaYayasan = user.roles.includes("kepala yayasan");
    const isDirekturPendidikan = user.roles.includes("direktur pendidikan");
    const isHRD = user.roles.includes("kepala hrd") || user.roles.includes("staff hrd");
    const isKepalaDepartemen = user.roles.includes("kepala departemen");
    const isKepalaSekolah = user.roles.includes("kepala sekolah");
    const isTenagaPendidik = user.roles.includes("tenaga pendidik");

    if (isAdmin) {
      return {
        dashboard: "/admin/dashboard",
        menu: "/admin/menu",
        profile: "/admin/profile",
        notifikasi: "/admin/notifications",
      };
    } else if (isKepalaYayasan) {
      return {
        dashboard: "/kepala-yayasan/dashboard",
        menu: "/kepala-yayasan/menu",
        profile: "/kepala-yayasan/profile",
        notifikasi: "/kepala-yayasan/notifications",
      };
    } else if (isDirekturPendidikan) {
      return {
        dashboard: "/direktur-pendidikan/dashboard",
        menu: "/direktur-pendidikan/menu",
        profile: "/direktur-pendidikan/profile",
        notifikasi: "/direktur-pendidikan/notifications",
      };
    } else if (isHRD) {
      return {
        dashboard: "/hrd/dashboard",
        menu: "/hrd/menu",
        profile: "/hrd/profile",
        notifikasi: "/hrd/notifications",
      };
    } else if (isKepalaDepartemen) {
      return {
        dashboard: "/kepala-departemen/dashboard",
        menu: "/kepala-departemen/menu",
        profile: "/kepala-departemen/profile",
        notifikasi: "/kepala-departemen/notifications",
      };
    } else if (isKepalaSekolah) {
      return {
        dashboard: "/kepala-sekolah/dashboard",
        menu: "/kepala-sekolah/menu",
        profile: "/kepala-sekolah/profile",
        notifikasi: "/kepala-sekolah/notifications",
      };
    } else if (isTenagaPendidik) {
      return {
        dashboard: "/tenaga-pendidik/dashboard",
        menu: "/tenaga-pendidik/menu",
        profile: "/tenaga-pendidik/profile",
        notifikasi: "/tenaga-pendidik/notifications",
      };
    }

    return { dashboard: "/", menu: "/", profile: "/", notifikasi: "/" };
  };

  const rolePaths = getRoleBasedPaths();

  const navigationItems = [
    {
      id: "dashboard",
      label: "Dashboard",
      icon: (
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
          <path fill="currentColor" d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m0 16H5V5h14zM9 17H7v-5h2zm4 0h-2V7h2zm4 0h-2v-7h2z" />
        </svg>
      ),
      path: rolePaths.dashboard,
    },
    {
      id: "menu",
      label: "Menu",
      icon: (
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
          <path
            fill="none"
            stroke="currentColor"
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth="1.5"
            d="M8.557 2.75H4.682A1.93 1.93 0 0 0 2.75 4.682v3.875a1.94 1.94 0 0 0 1.932 1.942h3.875a1.94 1.94 0 0 0 1.942-1.942V4.682A1.94 1.94 0 0 0 8.557 2.75m10.761 0h-3.875a1.94 1.94 0 0 0-1.942 1.932v3.875a1.943 1.943 0 0 0 1.942 1.942h3.875a1.94 1.94 0 0 0 1.932-1.942V4.682a1.93 1.93 0 0 0-1.932-1.932m0 10.75h-3.875a1.94 1.94 0 0 0-1.942 1.933v3.875a1.94 1.94 0 0 0 1.942 1.942h3.875a1.94 1.94 0 0 0 1.932-1.942v-3.875a1.93 1.93 0 0 0-1.932-1.932M8.557 13.5H4.682a1.943 1.943 0 0 0-1.932 1.943v3.875a1.93 1.93 0 0 0 1.932 1.932h3.875a1.94 1.94 0 0 0 1.942-1.932v-3.875a1.94 1.94 0 0 0-1.942-1.942"
          />
        </svg>
      ),
      path: rolePaths.menu,
    },
    {
      id: "notifikasi",
      label: "Notifikasi",
      icon: (
        <div className="relative">
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
            />
          </svg>
          {notificationCount > 0 && (
            <span className="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
              {notificationCount > 99 ? '99+' : notificationCount}
            </span>
          )}
        </div>
      ),
      path: rolePaths.notifikasi,
    },
    {
      id: "profile",
      label: "Profile",
      icon: (
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
      ),
      path: rolePaths.profile,
    },
  ];

  const activeIndex = useMemo(() => {
    // Check by pathname first, then by activeTab
    const currentPath = pathname;
    const indexByPath = navigationItems.findIndex((item) => item.path === currentPath);

    if (indexByPath !== -1) return indexByPath;

    // Fallback to activeTab
    if (activeTab) {
      const indexByTab = navigationItems.findIndex((item) => item.id === activeTab);
      if (indexByTab !== -1) return indexByTab;
    }

    return 0;
  }, [pathname, activeTab]);

  useEffect(() => {
    setIsTransitioning(true);
    const timer = setTimeout(() => {
      setIsTransitioning(false);
    }, 800);
    return () => clearTimeout(timer);
  }, [activeIndex]);

  const handleNavigation = (item: { id: string; path: string }) => {
    // Navigate to the page
    router.push(item.path);

    // Update local state
    setCurrentTab(item.id);

    // Call callback if provided
    if (onTabChange) {
      onTabChange(item.id);
    }
  };

  return (
    <div className="fixed bottom-0 left-0 right-0 bg-[#1e4d8b] text-white z-50 shadow-2xl">
      <div className="flex justify-around items-center h-20 relative">
        {/* Sliding Active Indicator with Smart Animate */}
        <div
          className="absolute bg-white rounded-full shadow-xl transition-all ease-out"
          style={{
            width: "64px",
            height: "64px",
            top: "-32px",
            left: `calc(${(activeIndex / navigationItems.length) * 100}% + ${100 / navigationItems.length / 2}% - 32px)`,
            transitionDuration: "800ms",
            transitionTimingFunction: "cubic-bezier(0.34, 1.56, 0.64, 1)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            transform: isTransitioning ? "scale(1.1) translateY(-4px)" : "scale(1) translateY(0)",
          }}
        >
          <div
            className="text-[#1e4d8b] transition-all"
            style={{
              transitionDuration: "800ms",
              transitionTimingFunction: "cubic-bezier(0.34, 1.56, 0.64, 1)",
              transform: isTransitioning ? "rotate(360deg) scale(1.1)" : "rotate(0deg) scale(1)",
            }}
          >
            {navigationItems[activeIndex]?.icon}
          </div>
        </div>

        {/* Navigation Items */}
        {navigationItems.map((item, index) => {
          const active = index === activeIndex;
          return (
            <button
              key={item.id}
              onClick={() => handleNavigation(item)}
              className="flex flex-col items-center justify-center flex-1 h-full relative z-10 transition-all duration-300"
              style={{
                paddingTop: active ? "8px" : "0",
              }}
            >
              {/* Icon - hidden when active */}
              <div
                className="transition-all ease-out"
                style={{
                  opacity: active ? 0 : 1,
                  transform: active ? "scale(0.8) translateY(-10px)" : "scale(1) translateY(0)",
                  transitionDuration: "800ms",
                  transitionTimingFunction: "cubic-bezier(0.34, 1.56, 0.64, 1)",
                }}
              >
                {item.icon}
              </div>

              {/* Label */}
              <span
                className="text-xs mt-1 transition-all ease-out"
                style={{
                  fontWeight: active ? 600 : 400,
                  opacity: active ? 1 : 0.8,
                  transform: active ? "translateY(0)" : "translateY(0)",
                  transitionDuration: "800ms",
                  transitionTimingFunction: "cubic-bezier(0.34, 1.56, 0.64, 1)",
                }}
              >
                {item.label}
              </span>
            </button>
          );
        })}
      </div>
    </div>
  );
}
