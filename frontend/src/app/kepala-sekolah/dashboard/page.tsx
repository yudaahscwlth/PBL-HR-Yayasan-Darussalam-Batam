"use client";

import { useState, useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import EmployeeDashboard from "@/app/employee/dashboard/page";

export default function KepalaSekolahDashboard() {
  const { user, isAuthenticated } = useAuthStore();
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (!isAuthenticated && user === null) {
      return;
    }

    if (!isAuthenticated) {
      router.push("/");
      return;
    }

    if (isAuthenticated && !user) {
      return;
    }

    if (user) {
      const isKepalaSekolah = user.roles?.includes("kepala sekolah");

      if (!isKepalaSekolah) {
        router.push("/unauthorized");
        return;
      }

      setIsLoading(false);
    }
  }, [isAuthenticated, user, router]);

  if (isLoading || !isAuthenticated || !user) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Loading dashboard...</p>
        </div>
      </div>
    );
  }

  return <EmployeeDashboard />;
}

