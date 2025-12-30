"use client";

import { useEffect, useState } from "react";

interface OfflineDetectorProps {
  children: React.ReactNode;
}

export default function OfflineDetector({ children }: OfflineDetectorProps) {
  const [isOnline, setIsOnline] = useState(true);
  const [isRedirecting, setIsRedirecting] = useState(false);

  useEffect(() => {
    // Set initial state
    setIsOnline(navigator.onLine);

    const handleOffline = () => {
      console.log("[OfflineDetector] Going offline - redirecting to offline page");
      setIsOnline(false);
      setIsRedirecting(true);

      // Store current path before redirecting
      if (typeof window !== "undefined") {
        localStorage.setItem("lastOnlinePath", window.location.pathname + window.location.search);
      }

      // Redirect to offline.html
      window.location.href = "/offline.html";
    };

    const handleOnline = () => {
      console.log("[OfflineDetector] Coming back online");
      setIsOnline(true);
    };

    // Check if we should redirect immediately
    if (!navigator.onLine) {
      handleOffline();
      return;
    }

    // Add event listeners
    window.addEventListener("offline", handleOffline);
    window.addEventListener("online", handleOnline);

    // Cleanup
    return () => {
      window.removeEventListener("offline", handleOffline);
      window.removeEventListener("online", handleOnline);
    };
  }, []);

  // Prevent rendering while redirecting
  if (isRedirecting || !isOnline) {
    return (
      <div style={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: 'linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #1e40af 100%)',
        color: 'white',
        fontSize: '18px',
      }}>
        Mengalihkan ke halaman offline...
      </div>
    );
  }

  return <>{children}</>;
}
