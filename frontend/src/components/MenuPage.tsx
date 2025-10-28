"use client";

import { useState } from "react";
import MenuCard from "./MenuCard";

interface MenuItem {
  title: string;
  icon: React.ReactNode;
  onClick?: () => void;
}

interface MenuSection {
  category: string;
  items: MenuItem[];
}

interface MenuPageProps {
  menuSections: MenuSection[];
  activeTab?: string;
  onTabChange?: (tab: string) => void;
}

export default function MenuPage({ menuSections, activeTab = "menu", onTabChange }: MenuPageProps) {
  const navigationItems = [
    {
      id: "dashboard",
      label: "Dasbor",
      icon: (
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
      ),
    },
    {
      id: "menu",
      label: "Menu",
      icon: (
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
          />
        </svg>
      ),
    },
    {
      id: "notifikasi",
      label: "Notifikasi",
      icon: (
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
          />
        </svg>
      ),
    },
    {
      id: "profile",
      label: "Profile",
      icon: (
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
          />
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
      ),
    },
  ];

  const activeIndex = navigationItems.findIndex((item) => item.id === activeTab);

  return (
    <div className="min-h-screen bg-gray-100 pb-24">
      {/* Main Content */}
      <div className="px-5 py-6">
        {/* Menu Sections */}
        {menuSections.map((section, sectionIndex) => (
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

      {/* Bottom Navigation */}
      <div className="fixed bottom-0 left-0 right-0 bg-[#1e4d8b] text-white z-50 shadow-2xl">
        <div className="flex justify-around items-center h-20 relative">
          {/* Sliding Active Indicator */}
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
            }}
          >
            <div className="text-[#1e4d8b]">{navigationItems[activeIndex]?.icon}</div>
          </div>

          {/* Navigation Items */}
          {navigationItems.map((item, index) => {
            const active = index === activeIndex;
            return (
              <button
                key={item.id}
                onClick={() => onTabChange?.(item.id)}
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
    </div>
  );
}




