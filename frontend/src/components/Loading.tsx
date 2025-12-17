"use client";

import React from "react";

export type LoadingVariant = "fullscreen" | "spinner" | "inline";
export type LoadingSize = "sm" | "md" | "lg";

interface LoadingProps {
  variant?: LoadingVariant;
  size?: LoadingSize;
  text?: string;
  className?: string;
}

export default function Loading({
  variant = "fullscreen",
  size = "md",
  text = "Memuat...",
  className = "",
}: LoadingProps) {
  // Size mapping for spinner
  const sizeClasses = {
    sm: "h-5 w-5 border-2",
    md: "h-12 w-12 border-2",
    lg: "h-16 w-16 border-3",
  };

  const textSizeClasses = {
    sm: "text-sm",
    md: "text-base",
    lg: "text-lg",
  };

  // Spinner element
  const Spinner = () => (
    <div
      className={`animate-spin rounded-full border-blue-600 border-t-transparent ${sizeClasses[size]}`}
      role="status"
      aria-label="Loading"
    />
  );

  // Fullscreen variant (for routes, auth, etc.)
  if (variant === "fullscreen") {
    return (
      <div 
        className={`min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-sky-50 ${className}`}
        role="status"
        aria-live="polite"
        aria-label={text}
      >
        <div className="flex flex-col items-center space-y-4">
          <div className="relative">
            {/* Outer ring */}
            <div className="absolute inset-0 rounded-full border-4 border-blue-200 opacity-25" />
            {/* Spinning ring */}
            <div className={`animate-spin rounded-full border-blue-600 border-t-transparent ${sizeClasses[size]}`} />
          </div>
          {text && (
            <p className={`text-gray-700 font-medium ${textSizeClasses[size]} animate-pulse`}>
              {text}
            </p>
          )}
        </div>
      </div>
    );
  }

  // Spinner only variant (for buttons, small sections)
  if (variant === "spinner") {
    return (
      <div className={`inline-flex items-center justify-center ${className}`}>
        <Spinner />
      </div>
    );
  }

  // Inline variant (for content sections)
  if (variant === "inline") {
    return (
      <div 
        className={`flex items-center justify-center py-8 ${className}`}
        role="status"
        aria-live="polite"
        aria-label={text}
      >
        <div className="flex flex-col items-center space-y-3">
          <Spinner />
          {text && (
            <p className={`text-gray-600 ${textSizeClasses[size]}`}>
              {text}
            </p>
          )}
        </div>
      </div>
    );
  }

  return null;
}
