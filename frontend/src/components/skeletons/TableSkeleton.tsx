"use client";

import React from "react";

interface TableSkeletonProps {
  rows?: number;
  columns?: number;
  showHeader?: boolean;
  className?: string;
}

export default function TableSkeleton({
  rows = 5,
  columns = 6,
  showHeader = true,
  className = "",
}: TableSkeletonProps) {
  return (
    <div className={`animate-pulse ${className}`} role="status" aria-label="Memuat data tabel...">
      <div className="overflow-x-auto">
        <table className="min-w-full bg-white rounded-lg overflow-hidden">
          {/* Table Header Skeleton */}
          {showHeader && (
            <thead className="bg-gradient-to-r from-[#1e4d8b] to-[#2563eb]">
              <tr>
                {Array.from({ length: columns }).map((_, idx) => (
                  <th key={idx} className="px-6 py-4">
                    <div className="h-4 bg-blue-300 rounded w-3/4"></div>
                  </th>
                ))}
              </tr>
            </thead>
          )}
          
          {/* Table Body Skeleton */}
          <tbody className="divide-y divide-gray-200">
            {Array.from({ length: rows }).map((_, rowIdx) => (
              <tr key={rowIdx} className="hover:bg-gray-50 transition-colors">
                {Array.from({ length: columns }).map((_, colIdx) => (
                  <td key={colIdx} className="px-6 py-4">
                    <div 
                      className="h-4 bg-gray-200 rounded"
                      style={{ 
                        width: `${Math.random() * 40 + 60}%` // Random width between 60-100%
                      }}
                    ></div>
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      
      {/* Screen reader only text */}
      <span className="sr-only">Memuat data tabel...</span>
    </div>
  );
}
