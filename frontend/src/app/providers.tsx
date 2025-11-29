// src/app/providers.tsx
"use client";

import { useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import RegisterServiceWorker from "../components/RegisterServiceWorker";
import NetworkStatus from "@/components/NetworkStatus";
import { Toaster } from "react-hot-toast";

export default function Providers({ children }: { children: React.ReactNode }) {
  const { checkAuth } = useAuthStore();

  useEffect(() => {
    // Check authentication status on app load
    checkAuth();
  }, [checkAuth]);

  return (
    <>
      <NetworkStatus />
      <RegisterServiceWorker />
      <Toaster position="top-right" />
      {children}
    </>
  );
}
