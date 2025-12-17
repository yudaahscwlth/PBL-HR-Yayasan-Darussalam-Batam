"use client";

import React from "react";

interface CardSkeletonProps {
  count?: number;
  variant?: "default" | "notification" | "profile";
  className?: string;
}

export default function CardSkeleton({
  count = 3,
  variant = "default",
  className = "",
}: CardSkeletonProps) {
  // Default card skeleton
  const DefaultCard = () => (
    <div className="bg-white rounded-lg shadow-md p-6 animate-pulse">
      <div className="h-6 bg-gray-200 rounded w-1/2 mb-4"></div>
      <div className="space-y-3">
        <div className="h-4 bg-gray-200 rounded"></div>
        <div className="h-4 bg-gray-200 rounded w-5/6"></div>
        <div className="h-4 bg-gray-200 rounded w-4/6"></div>
      </div>
    </div>
  );

  // Notification card skeleton
  const NotificationCard = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 animate-pulse">
      <div className="flex items-start space-x-3">
        {/* Avatar skeleton */}
        <div className="flex-shrink-0">
          <div className="h-10 w-10 bg-gray-200 rounded-full"></div>
        </div>
        
        {/* Content skeleton */}
        <div className="flex-1 space-y-2">
          <div className="h-4 bg-gray-200 rounded w-3/4"></div>
          <div className="h-3 bg-gray-200 rounded w-full"></div>
          <div className="h-3 bg-gray-200 rounded w-1/3"></div>
        </div>
      </div>
    </div>
  );

  // Profile card skeleton
  const ProfileCard = () => (
    <div className="bg-white rounded-lg shadow-md overflow-hidden animate-pulse">
      {/* Header/Banner skeleton */}
      <div className="h-24 bg-gradient-to-r from-blue-200 to-sky-200"></div>
      
      {/* Profile content */}
      <div className="px-6 pb-6">
        {/* Avatar skeleton */}
        <div className="-mt-12 mb-4">
          <div className="h-24 w-24 bg-gray-200 rounded-full border-4 border-white"></div>
        </div>
        
        {/* Info skeleton */}
        <div className="space-y-3">
          <div className="h-6 bg-gray-200 rounded w-2/3"></div>
          <div className="h-4 bg-gray-200 rounded w-1/2"></div>
          <div className="h-4 bg-gray-200 rounded w-3/4"></div>
        </div>
      </div>
    </div>
  );

  const renderCard = () => {
    switch (variant) {
      case "notification":
        return <NotificationCard />;
      case "profile":
        return <ProfileCard />;
      default:
        return <DefaultCard />;
    }
  };

  return (
    <div className={`space-y-4 ${className}`} role="status" aria-label="Memuat data...">
      {Array.from({ length: count }).map((_, idx) => (
        <div key={idx}>{renderCard()}</div>
      ))}
      
      {/* Screen reader only text */}
      <span className="sr-only">Memuat data...</span>
    </div>
  );
}
