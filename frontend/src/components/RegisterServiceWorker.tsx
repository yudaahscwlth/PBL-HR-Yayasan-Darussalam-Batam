// src/components/RegisterServiceWorker.tsx
"use client";

import { useEffect } from "react";

export default function RegisterServiceWorker() {
  useEffect(() => {
    if ("serviceWorker" in navigator) {
      const registerSW = async () => {
        try {
          const registration = await navigator.serviceWorker.register("/sw.js");
          console.log("SW registered: ", registration);

          // Check for updates
          registration.addEventListener("updatefound", () => {
            const newWorker = registration.installing;
            if (newWorker) {
              newWorker.addEventListener("statechange", () => {
                if (newWorker.state === "installed" && navigator.serviceWorker.controller) {
                  // New content is available, show update prompt
                  console.log("New content is available; please refresh.");
                }
              });
            }
          });

          // Listen for controller change (page refresh after update)
          navigator.serviceWorker.addEventListener("controllerchange", () => {
            window.location.reload();
          });

        } catch (error) {
          console.error("SW registration failed: ", error);
        }
      };

      // Wait for page load to register SW
      if (document.readyState === "complete") {
        registerSW();
      } else {
        window.addEventListener("load", registerSW);
      }
    }
  }, []);

  return null;
}