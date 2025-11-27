// src/app/providers.tsx
"use client";

import { useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import RegisterServiceWorker from "../components/RegisterServiceWorker";
import NetworkStatus from "@/components/NetworkStatus";

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
      {children}
    </>
  );
}
