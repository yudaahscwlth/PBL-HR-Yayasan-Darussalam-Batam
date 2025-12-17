"use client";

import React from "react";

interface FormSkeletonProps {
  fields?: number;
  showButtons?: boolean;
  className?: string;
}

export default function FormSkeleton({
  fields = 6,
  showButtons = true,
  className = "",
}: FormSkeletonProps) {
  return (
    <div className={`animate-pulse ${className}`} role="status" aria-label="Memuat form...">
      <div className="space-y-6">
        {/* Form fields skeleton */}
        {Array.from({ length: fields }).map((_, idx) => (
          <div key={idx} className="space-y-2">
            {/* Label skeleton */}
            <div className="h-4 bg-gray-200 rounded w-1/4"></div>
            
            {/* Input skeleton */}
            <div className="h-10 bg-gray-200 rounded-lg w-full"></div>
          </div>
        ))}
        
        {/* Action buttons skeleton */}
        {showButtons && (
          <div className="flex space-x-4 pt-4">
            <div className="h-10 bg-blue-200 rounded-lg w-32"></div>
            <div className="h-10 bg-gray-200 rounded-lg w-24"></div>
          </div>
        )}
      </div>
      
      {/* Screen reader only text */}
      <span className="sr-only">Memuat form...</span>
    </div>
  );
}
