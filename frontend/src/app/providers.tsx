// src/app/providers.tsx
"use client";

import { useEffect, useState } from "react";
import { useAuthStore } from "@/store/authStore";
import RegisterServiceWorker from "../components/RegisterServiceWorker";
import OfflineDetector from "@/components/OfflineDetector";
import { Toaster } from "react-hot-toast";

export default function Providers({ children }: { children: React.ReactNode }) {
  const { checkAuth } = useAuthStore();
  const [isMounted, setIsMounted] = useState(false);

  useEffect(() => {
    // Check authentication status on app load
    checkAuth();
    setIsMounted(true);
  }, [checkAuth]);

  return (
    <OfflineDetector>
      <RegisterServiceWorker />
      {isMounted && <Toaster position="top-right" />}
      {children}
    </OfflineDetector>
  );
}
