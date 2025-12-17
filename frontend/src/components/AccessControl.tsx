"use client";

import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import Loading from "@/components/Loading";

interface AccessControlProps {
  allowedRoles: string[];
  children: React.ReactNode;
  fallbackPath?: string;
}

export default function AccessControl({ allowedRoles, children, fallbackPath = "/unauthorized" }: AccessControlProps) {
  const { user, isAuthenticated } = useAuthStore();
  const router = useRouter();
  const [isAuthorized, setIsAuthorized] = useState<boolean | null>(null);
  const [isMounted, setIsMounted] = useState(false);

  // Track when component is mounted on client to prevent hydration mismatch
  useEffect(() => {
    setIsMounted(true);
  }, []);

  useEffect(() => {
    // Wait for auth state to be determined
    if (!isAuthenticated && user === null) {
      return;
    }

    // If not authenticated, redirect to login
    if (!isAuthenticated) {
      router.push("/");
      return;
    }

    // If authenticated but no user data yet, wait
    if (isAuthenticated && !user) {
      return;
    }

    // If we have user data, check roles
    if (user) {
      const hasAccess = user.roles?.some((role) => allowedRoles.includes(role));

      if (!hasAccess) {
        router.push(fallbackPath);
        return;
      }

      setIsAuthorized(true);
    }
  }, [isAuthenticated, user, router, allowedRoles, fallbackPath]);

  // Show loading while checking authentication or during initial mount
  // Return null on server to prevent hydration mismatch
  if (!isMounted || isAuthorized === null) {
    return isMounted ? <Loading variant="fullscreen" text="Memeriksa akses..." /> : null;
  }

  // If not authorized, don't render (will redirect)
  if (!isAuthorized) {
    return null;
  }

  return <>{children}</>;
}
