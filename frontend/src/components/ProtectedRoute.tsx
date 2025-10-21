"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuthStore } from "@/store/authStore";
import { UserRole } from "@/types/auth";

interface ProtectedRouteProps {
  children: React.ReactNode;
  requiredRoles?: UserRole[];
  requiredPermissions?: string[];
  fallbackPath?: string;
}

export default function ProtectedRoute({ children, requiredRoles, requiredPermissions, fallbackPath = "/" }: ProtectedRouteProps) {
  const { isAuthenticated, user, isLoading, checkAuth } = useAuthStore();
  const router = useRouter();

  useEffect(() => {
    // Check authentication on mount
    if (!isAuthenticated && !isLoading) {
      checkAuth();
    }
  }, [isAuthenticated, isLoading, checkAuth]);

  useEffect(() => {
    // Redirect if not authenticated
    if (!isLoading && !isAuthenticated) {
      router.push(fallbackPath);
      return;
    }

    // Check role-based access
    if (user && requiredRoles && requiredRoles.length > 0) {
      const hasRequiredRole = user.roles?.some((role) => requiredRoles.includes(role));
      if (!hasRequiredRole) {
        router.push("/unauthorized");
        return;
      }
    }

    // Check permission-based access
    if (user && requiredPermissions && requiredPermissions.length > 0) {
      const hasRequiredPermission = user.permissions?.some((permission) => requiredPermissions.includes(permission));
      if (!hasRequiredPermission) {
        router.push("/unauthorized");
        return;
      }
    }
  }, [user, isAuthenticated, isLoading, requiredRoles, requiredPermissions, router, fallbackPath]);

  // Show loading spinner while checking authentication
  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="flex flex-col items-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          <p className="mt-4 text-gray-600">Loading...</p>
        </div>
      </div>
    );
  }

  // Don't render children if not authenticated
  if (!isAuthenticated || !user) {
    return null;
  }

  // Don't render children if role/permission check fails
  if (requiredRoles && requiredRoles.length > 0) {
    const hasRequiredRole = user.roles?.some((role) => requiredRoles.includes(role));
    if (!hasRequiredRole) {
      return null;
    }
  }

  if (requiredPermissions && requiredPermissions.length > 0) {
    const hasRequiredPermission = user.permissions?.some((permission) => requiredPermissions.includes(permission));
    if (!hasRequiredPermission) {
      return null;
    }
  }

  return <>{children}</>;
}
