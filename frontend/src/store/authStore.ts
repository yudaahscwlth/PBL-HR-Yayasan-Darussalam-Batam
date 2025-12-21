import { create } from "zustand";
import { persist } from "zustand/middleware";
import { AuthState, User, LoginRequest, UserRole } from "@/types/auth";
import { apiClient } from "@/lib/api";
import { offlineStorage } from "@/lib/offlineStorage";

interface AuthStore extends AuthState {
  // Additional methods for role-based access
  hasRole: (role: UserRole) => boolean;
  hasAnyRole: (roles: UserRole[]) => boolean;
  hasPermission: (permission: string) => boolean;
  hasAnyPermission: (permissions: string[]) => boolean;
  isAdmin: () => boolean;
  isHRD: () => boolean;
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
        set({ isLoading: true, error: null });

        try {
          const response = await apiClient.auth.login(credentials.email, credentials.password);

          if (response.success && response.user) {
            // Store token if provided
            if (response.token) {
              localStorage.setItem("auth_token", response.token);
            }

            set({
              user: response.user,
              isAuthenticated: true,
              isLoading: false,
              error: null,
            });

            // Save last login session for offline access
            try {
              // Fetch attendance data for offline storage
              let attendanceData: any[] = [];
              try {
                const attendanceResponse = await apiClient.attendance.getHistory();
                if (attendanceResponse.success && attendanceResponse.data) {
                  attendanceData = Array.isArray(attendanceResponse.data) 
                    ? attendanceResponse.data 
                    : [];
                }
              } catch (attendanceError) {
                console.warn("⚠️ Failed to fetch attendance data during login:", attendanceError);
              }

              await offlineStorage.saveLastLoginSession(response.user, attendanceData);
            } catch (error) {
              console.warn("⚠️ Failed to save last login session:", error);
            }
          } else {
            set({
              user: null,
              isAuthenticated: false,
              isLoading: false,
              error: response.message || "Login failed",
            });
          }
        } catch (error: unknown) {
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
    }),
    {
      name: "auth-storage",
      partialize: (state) => ({
        user: state.user,
        isAuthenticated: state.isAuthenticated,
      }),
      onRehydrateStorage: () => (state) => {
        if (state) {
          // Reset loading state after rehydration
          state.isLoading = false;
        }
      },
    }
  )
);

// Helper function to get redirect path based on user role
export const getRedirectPath = (user: User | null): string => {
  if (!user || !user.roles) {
    return "/";
  }

  const roles = user.roles;

  // Admin roles (only superadmin)
  if (roles.includes("superadmin")) {
    return "/admin/dashboard";
  }

  // Kepala Yayasan
  if (roles.includes("kepala yayasan")) {
    return "/kepala-yayasan/dashboard";
  }

  // Direktur Pendidikan
  if (roles.includes("direktur pendidikan")) {
    return "/direktur-pendidikan/dashboard";
  }

  // HRD roles
  if (roles.includes("kepala hrd") || roles.includes("staff hrd")) {
    return "/hrd/dashboard";
  }

  // Kepala Departemen
  if (roles.includes("kepala departemen")) {
    return "/kepala-departemen/dashboard";
  }

  // Kepala Sekolah
  if (roles.includes("kepala sekolah")) {
    return "/kepala-sekolah/dashboard";
  }

  // Tenaga Pendidik
  if (roles.includes("tenaga pendidik")) {
    return "/tenaga-pendidik/dashboard";
  }

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
