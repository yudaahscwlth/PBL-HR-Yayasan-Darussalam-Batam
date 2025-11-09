import { create } from "zustand";
import { persist } from "zustand/middleware";
import { AuthState, User, LoginRequest, UserRole } from "@/types/auth";
import { apiClient } from "@/lib/api";

interface AuthStore extends AuthState {
  // Additional methods for role-based access
  hasRole: (role: UserRole) => boolean;
  hasAnyRole: (roles: UserRole[]) => boolean;
  hasPermission: (permission: string) => boolean;
  hasAnyPermission: (permissions: string[]) => boolean;
  isAdmin: () => boolean;
  isHRD: () => boolean;
  isEmployee: () => boolean;
  initialize: () => void;
}

export const useAuthStore = create<AuthStore>()(
  persist(
    (set, get) => ({
      // Initial state
      user: null,
      isAuthenticated: false,
      isLoading: true, // Start with loading true to prevent premature redirects
      error: null,

      // Login method
      login: async (credentials: LoginRequest) => {
        console.log("Login attempt started:", credentials.email);
        set({ isLoading: true, error: null });

        try {
          console.log("Calling API client login...");
          const response = await apiClient.auth.login(credentials.email, credentials.password);
          console.log("API response:", response);
          console.log("Response success:", response.success);
          console.log("Response user:", response.user);
          console.log("Response token:", response.token);

          if (response.success && response.user) {
            // Store token if provided
            if (response.token) {
              localStorage.setItem("auth_token", response.token);
              console.log("Token stored:", response.token);
            }

            console.log("🔧 Setting auth state...");
            set({
              user: response.user,
              isAuthenticated: true,
              isLoading: false,
              error: null,
            });
            console.log("✅ Login successful, user set:", response.user);
            console.log("✅ Auth state updated - isAuthenticated: true");

            // Verify state was set
            const currentState = get();
            console.log("🔍 Current auth state after login:", {
              isAuthenticated: currentState.isAuthenticated,
              user: currentState.user,
              isLoading: currentState.isLoading,
            });

            // Force trigger re-render
            console.log("🔄 Forcing state update...");
            setTimeout(() => {
              const delayedState = get();
              console.log("⏰ Delayed auth state check:", {
                isAuthenticated: delayedState.isAuthenticated,
                user: delayedState.user,
                isLoading: delayedState.isLoading,
              });
            }, 100);
          } else {
            console.log("Login failed:", response.message);
            set({
              user: null,
              isAuthenticated: false,
              isLoading: false,
              error: response.message || "Login failed",
            });
          }
        } catch (error: unknown) {
          console.error("Login error:", error);
          const axiosError = error as { response?: { data?: { message?: string } }; message?: string };
          const errorMessage = axiosError.response?.data?.message || axiosError.message || "Login failed";
          set({
            user: null,
            isAuthenticated: false,
            isLoading: false,
            error: errorMessage,
          });
        }
      },

      // Logout method
      logout: async () => {
        try {
          await apiClient.auth.logout();
        } catch {
          // Ignore logout errors
        } finally {
          // Clear local storage
          localStorage.removeItem("auth_token");
          localStorage.removeItem("user_data");

          set({
            user: null,
            isAuthenticated: false,
            isLoading: false,
            error: null,
          });
        }
      },

      // Clear error method
      clearError: () => {
        set({ error: null });
      },

      // Check authentication method
      checkAuth: async () => {
        const token = localStorage.getItem("auth_token");

        if (!token) {
          set({
            user: null,
            isAuthenticated: false,
            isLoading: false,
          });
          return;
        }

        set({ isLoading: true });

        try {
          const response = await apiClient.auth.me();

          if (response.success && response.data) {
            set({
              user: response.data as User,
              isAuthenticated: true,
              isLoading: false,
              error: null,
            });
          } else {
            // Token is invalid, clear it
            localStorage.removeItem("auth_token");
            set({
              user: null,
              isAuthenticated: false,
              isLoading: false,
              error: null,
            });
          }
        } catch (error: unknown) {
          // Token is invalid, clear it
          console.error("Auth check error:", error);
          localStorage.removeItem("auth_token");
          set({
            user: null,
            isAuthenticated: false,
            isLoading: false,
            error: null,
          });
        }
      },

      // Initialize auth state
      initialize: () => {
        const token = localStorage.getItem("auth_token");
        if (!token) {
          set({
            user: null,
            isAuthenticated: false,
            isLoading: false,
            error: null,
          });
        }
      },

      // Role-based access control methods
      hasRole: (role: UserRole) => {
        const { user } = get();
        return user?.roles?.includes(role) || false;
      },

      hasAnyRole: (roles: UserRole[]) => {
        const { user } = get();
        return user?.roles?.some((role) => roles.includes(role)) || false;
      },

      hasPermission: (permission: string) => {
        const { user } = get();
        return user?.permissions?.includes(permission) || false;
      },

      hasAnyPermission: (permissions: string[]) => {
        const { user } = get();
        return user?.permissions?.some((permission) => permissions.includes(permission)) || false;
      },

      // Convenience methods for common role checks
      isAdmin: () => {
        const { hasAnyRole } = get();
        return hasAnyRole(["superadmin"]);
      },

      isHRD: () => {
        const { hasAnyRole } = get();
        return hasAnyRole(["kepala hrd", "staff hrd"]);
      },

      isEmployee: () => {
        const { hasAnyRole } = get();
        return hasAnyRole(["kepala departemen", "kepala sekolah", "tenaga pendidik"]);
      },
    }),
    {
      name: "auth-storage",
      partialize: (state) => ({
        user: state.user,
        isAuthenticated: state.isAuthenticated,
      }),
      onRehydrateStorage: () => (state) => {
        console.log("Auth store rehydrated:", state);
        if (state) {
          console.log("Rehydrated state - isAuthenticated:", state.isAuthenticated, "user:", state.user);
          // Reset loading state after rehydration
          state.isLoading = false;
        }
      },
    }
  )
);

// Helper function to get redirect path based on user role
export const getRedirectPath = (user: User | null): string => {
  console.log("getRedirectPath called with user:", user);

  if (!user || !user.roles) {
    console.log("No user or roles, returning /");
    return "/";
  }

  const roles = user.roles;
  console.log("User roles:", roles);

  // Admin roles (only superadmin)
  if (roles.includes("superadmin")) {
    console.log("Admin role detected, redirecting to /admin/dashboard");
    return "/admin/dashboard";
  }

  // HRD roles
  if (roles.includes("kepala hrd") || roles.includes("staff hrd")) {
    console.log("HRD role detected, redirecting to /hrd/dashboard");
    return "/hrd/dashboard";
  }

  // Kepala Departemen
  if (roles.includes("kepala departemen")) {
    console.log("Kepala Departemen role detected, redirecting to /kepala-departemen/dashboard");
    return "/kepala-departemen/dashboard";
  }

  // Kepala Sekolah
  if (roles.includes("kepala sekolah")) {
    console.log("Kepala Sekolah role detected, redirecting to /kepala-sekolah/dashboard");
    return "/kepala-sekolah/dashboard";
  }

  // Tenaga Pendidik
  if (roles.includes("tenaga pendidik")) {
    console.log("Tenaga Pendidik role detected, redirecting to /tenaga-pendidik/dashboard");
    return "/tenaga-pendidik/dashboard";
  }

  console.log("No matching role, returning /dashboard");
  return "/dashboard";
};

// Helper function to check if user can access a route
export const canAccessRoute = (user: User | null, requiredRoles?: UserRole[], requiredPermissions?: string[]): boolean => {
  if (!user) return false;

  // Check roles
  if (requiredRoles && requiredRoles.length > 0) {
    const hasRequiredRole = user.roles?.some((role) => requiredRoles.includes(role));
    if (!hasRequiredRole) return false;
  }

  // Check permissions
  if (requiredPermissions && requiredPermissions.length > 0) {
    const hasRequiredPermission = user.permissions?.some((permission) => requiredPermissions.includes(permission));
    if (!hasRequiredPermission) return false;
  }

  return true;
};
