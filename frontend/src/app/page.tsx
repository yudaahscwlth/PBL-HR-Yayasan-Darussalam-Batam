"use client";

import Image from "next/image";
import InstallPWA from "./components/InstallPWA";
import { useState, useEffect, useRef } from "react";
import { useAuthStore, getRedirectPath } from "@/store/authStore";
import { useRouter } from "next/navigation";

export default function Home() {
  const [showPassword, setShowPassword] = useState(false);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const hasRedirectedRef = useRef(false);
  const { login, isAuthenticated, user, error, clearError, isLoading, initialize } = useAuthStore();
  const router = useRouter();

  // Initialize auth state on mount
  useEffect(() => {
    initialize();
  }, [initialize]);

  // Redirect if already authenticated
  useEffect(() => {
    console.log("=== LOGIN PAGE USEEFFECT ===");
    console.log("isAuthenticated:", isAuthenticated);
    console.log("user:", user);
    console.log("hasRedirected:", hasRedirectedRef.current);
    console.log("isLoading:", isLoading);
    console.log("Current pathname:", window.location.pathname);
    console.log("=============================");

    // Don't redirect if still loading
    if (isLoading) {
      console.log("⏳ Still loading, waiting...");
      return;
    }

    // Only redirect if authenticated and have user data
    if (isAuthenticated && user && user.roles) {
      const redirectPath = getRedirectPath(user);
      console.log("🎯 Redirect path:", redirectPath);

      // Check if we're already on the target path
      if (window.location.pathname === redirectPath) {
        console.log("✅ Already on target path, no redirect needed");
        return;
      }

      // Check if we've already attempted redirect for this path
      if (hasRedirectedRef.current) {
        console.log("⏳ Redirect already attempted, waiting for navigation...");
        return;
      }

      console.log("✅ Conditions met for redirect");
      hasRedirectedRef.current = true;
      console.log("🚀 Force redirecting to:", redirectPath);
      console.log("🔍 About to redirect, current URL:", window.location.href);

      // Use router.push instead of window.location to avoid full page reload
      router.push(redirectPath);
    } else {
      console.log("❌ Not redirecting - conditions not met:");
      console.log("  - isAuthenticated:", isAuthenticated);
      console.log("  - user exists:", !!user);
      console.log("  - user has roles:", !!user?.roles);
      console.log("  - isLoading:", isLoading);
    }
  }, [isAuthenticated, user, router, isLoading]);

  // Cleanup effect to prevent memory leaks
  useEffect(() => {
    return () => {
      console.log("Login page cleanup");
    };
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    clearError();

    try {
      console.log("🚀 Starting login process...");
      console.log("Email:", email);
      console.log("Password length:", password.length);

      await login({ email, password });

      console.log("✅ Login completed, checking auth state...");
      console.log("isAuthenticated:", isAuthenticated);
      console.log("user:", user);

      // Force check auth state after login
      setTimeout(() => {
        const currentAuthState = useAuthStore.getState();
        console.log("🔍 Auth state after timeout:", {
          isAuthenticated: currentAuthState.isAuthenticated,
          user: currentAuthState.user,
          isLoading: currentAuthState.isLoading,
        });

        if (currentAuthState.isAuthenticated && currentAuthState.user) {
          const redirectPath = getRedirectPath(currentAuthState.user);
          console.log("🎯 Force redirect from handleSubmit to:", redirectPath);

          // Check if we're already on the target path
          if (window.location.pathname === redirectPath) {
            console.log("✅ Already on target path from handleSubmit");
            return;
          }

          // Force redirect
          window.location.href = redirectPath;
        }
      }, 1000);
    } catch (error) {
      console.error("❌ Login error:", error);
    }
  };

  return (
    <>
      <InstallPWA />
      <div className="min-h-screen bg-white">
        {/* Blue Decorative Header Section */}
        {/* Header Section */}
        <div className="bg-[#1e4d8b] relative overflow-hidden">
          {/* Logo & Title */}
          <div className="flex flex-col items-center pt-8 sm:pt-12 pb-10 px-4">
            <div className="flex items-center justify-center mb-6">
              <div className="bg-white rounded-full p-3 shadow-lg">
                <Image src="/icons/logo-original.png" alt="Logo Darussalam" width={80} height={80} className="w-16 h-16 sm:w-20 sm:h-20" />
              </div>
            </div>
            <h1 className="text-white text-xl sm:text-2xl font-bold text-center">
              HR YAYASAN
              <br />
              DARUSSALAM
            </h1>
          </div>

          {/* Wave separator between header and form */}
          <div className="relative w-full -mb-1">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" className="w-full h-auto" preserveAspectRatio="none">
              <path
                fill="#ffffff"
                fillOpacity="1"
                d="M0,0L40,26.7C80,53,160,107,240,112C320,117,400,75,480,80C560,85,640,139,720,181.3C800,224,880,256,960,266.7C1040,277,1120,267,1200,218.7C1280,171,1360,85,1400,42.7L1440,0L1440,320L0,320Z"
              ></path>
            </svg>
          </div>
        </div>

        {/* Login Form */}
        <div className="flex justify-center px-4 py-8">
          <div className="w-full max-w-md">
            <h2 className="text-[#0066cc] text-2xl font-bold mb-6 text-center">Login Akun</h2>

            <form onSubmit={handleSubmit} className="space-y-5">
              {/* Error Message */}
              {error && (
                <div className="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                  <div className="flex items-center">
                    <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {error}
                  </div>
                </div>
              )}

              {/* Username (tetap menggunakan field email) */}
              <div>
                <div className="relative">
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="Email"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10 text-black"
                    required
                    disabled={isLoading}
                  />
                  <div className="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                    <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                </div>
              </div>

              {/* Password */}
              <div>
                <div className="relative">
                  <input
                    type={showPassword ? "text" : "password"}
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Password"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10 text-black"
                    required
                    disabled={isLoading}
                  />
                  <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute inset-y-0 right-0 flex items-center px-3" disabled={isLoading}>
                    {showPassword ? (
                      <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    ) : (
                      <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                        />
                      </svg>
                    )}
                  </button>
                </div>
              </div>

              {/* Forgot Password Link */}
              <div className="text-right">
                <a href="#" className="text-[#0066cc] text-sm hover:underline">
                  Lupa Password ?
                </a>
              </div>

              {/* Submit Button */}
              <button
                type="submit"
                disabled={isLoading}
                className="w-full bg-[#0066cc] text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center gap-2 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {isLoading ? (
                  <>
                    <svg className="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading...
                  </>
                ) : (
                  <>
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Login
                  </>
                )}
              </button>
            </form>

            {/* Copyright */}
            <div className="mt-6 text-center">
              <p className="text-gray-600 text-sm">© 2025 PBL 221 - Yayasan Darussalam</p>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
