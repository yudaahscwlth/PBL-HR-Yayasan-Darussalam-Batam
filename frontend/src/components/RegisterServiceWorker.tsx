// src/components/RegisterServiceWorker.tsx
"use client";

import { useEffect } from "react";

export default function RegisterServiceWorker() {
  useEffect(() => {
    if (typeof window === "undefined" || !("serviceWorker" in navigator)) {
      return;
    }

    const handleControllerChange = () => {
      window.location.reload();
    };

    const registerSW = async () => {
      try {
        const registration = await navigator.serviceWorker.register("/sw.js");
        console.log("SW registered: ", registration);

        // Check for updates
        registration.addEventListener("updatefound", () => {
          const newWorker = registration.installing;
          if (newWorker) {
            newWorker.addEventListener("statechange", () => {
              if (
                newWorker.state === "installed" &&
                navigator.serviceWorker.controller
              ) {
                // New content is available, show update prompt
                console.log("New content is available; please refresh.");
              }
            });
          }
        });

        // Wait for service worker to be ready before adding event listeners
        navigator.serviceWorker.ready
          .then(() => {
            // Only add event listener after service worker is ready
            if (navigator.serviceWorker.controller) {
              navigator.serviceWorker.addEventListener(
                "controllerchange",
                handleControllerChange
              );
            }
          })
          .catch((error) => {
            console.warn("Service worker ready check failed:", error);
          });
      } catch (error) {
        // Handle permission errors gracefully
        if (
          error instanceof Error &&
          (error.name === "NotAllowedError" ||
            error.message?.includes("permission"))
        ) {
          console.warn(
            "Service Worker registration permission denied. This may happen in certain contexts."
          );
        } else {
          console.error("SW registration failed: ", error);
        }
      }
    };

    // Wait for page load to register SW
    if (document.readyState === "complete") {
      registerSW();
    } else {
      window.addEventListener("load", registerSW);
    }

    return () => {
      if (navigator.serviceWorker) {
        navigator.serviceWorker.removeEventListener(
          "controllerchange",
          handleControllerChange
        );
      }
      window.removeEventListener("load", registerSW);
    };
  }, []);

  return null;
}
