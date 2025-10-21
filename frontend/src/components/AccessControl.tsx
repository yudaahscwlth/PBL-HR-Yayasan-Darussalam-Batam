"use client";

import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";

interface AccessControlProps {
  allowedRoles: string[];
  children: React.ReactNode;
  fallbackPath?: string;
}

export default function AccessControl({ allowedRoles, children, fallbackPath = "/unauthorized" }: AccessControlProps) {
  const { user, isAuthenticated } = useAuthStore();
  const router = useRouter();
  const [isAuthorized, setIsAuthorized] = useState<boolean | null>(null);

  useEffect(() => {
    console.log("=== ACCESS CONTROL ===");
    console.log("isAuthenticated:", isAuthenticated);
    console.log("user:", user);
    console.log("user roles:", user?.roles);
    console.log("allowedRoles:", allowedRoles);
    console.log("=====================");

    // Wait for auth state to be determined
    if (!isAuthenticated && user === null) {
      console.log("⏳ Waiting for auth state...");
      return;
    }

    // If not authenticated, redirect to login
    if (!isAuthenticated) {
      console.log("❌ Not authenticated, redirecting to login");
      router.push("/");
      return;
    }

    // If authenticated but no user data yet, wait
    if (isAuthenticated && !user) {
      console.log("⏳ Authenticated but no user data yet, waiting...");
      return;
    }

    // If we have user data, check roles
    if (user) {
      const hasAccess = user.roles?.some((role) => allowedRoles.includes(role));
      console.log("hasAccess:", hasAccess);

      if (!hasAccess) {
        console.log("❌ Access denied, redirecting to unauthorized");
        router.push(fallbackPath);
        return;
      }

      console.log("✅ Access granted");
      setIsAuthorized(true);
    }
  }, [isAuthenticated, user, router, allowedRoles, fallbackPath]);

  // Show loading while checking authentication
  if (isAuthorized === null) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Checking access...</p>
        </div>
      </div>
    );
  }

  // If not authorized, don't render (will redirect)
  if (!isAuthorized) {
    return null;
  }

  return <>{children}</>;
}
