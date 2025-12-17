"use client";

import { ArrowLeft } from "lucide-react";
import { useRouter } from "next/navigation";

interface BottomBackProps {
  onClick?: () => void;
  variant?: "fixed" | "inline";
  className?: string;
}

export default function BottomBack({
  onClick,
  variant = "fixed",
  className = "",
}: BottomBackProps) {
  const router = useRouter();

  const handleClick = () => {
    if (onClick) {
      onClick();
      return;
    }
    router.back();
  };

  if (variant === "inline") {
    return (
      <button
        onClick={handleClick}
        className={`inline-flex items-center justify-center  hover:bg-gray-200 text-gray-700 p-2 rounded-lg transition-colors ${className}`.trim()}
      >
        <ArrowLeft className="w-6 h-6 text-gray-700" />
      </button>
    );
  }

  return (
    <div className={`fixed inset-x-0 bottom-0 z-40 bg-white/95 border-t border-gray-200 shadow-lg backdrop-blur ${className}`.trim()}>
      <div className="max-w-5xl mx-auto px-4 py-3">
        <button
          onClick={handleClick}
          className="w-full inline-flex items-center justify-center bg-sky-800 hover:bg-sky-700 text-white p-3 rounded-xl transition-colors"
        >
          <ArrowLeft className="w-6 h-6" />
        </button>
      </div>
    </div>
  );
}