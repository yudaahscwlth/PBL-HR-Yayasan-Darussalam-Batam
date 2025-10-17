"use client";

import { useEffect } from "react";

export default function RegisterServiceWorker() {
  useEffect(() => {
    if ("serviceWorker" in navigator) {
      navigator.serviceWorker
        .register("/sw.js")
        .then((registration) => {
          console.log(
            "Service Worker registered successfully:",
            registration.scope
          );

          // Check for updates
          registration.update();
        })
        .catch((error) => {
          console.log("Service Worker registration failed:", error);
        });

      // Listen for updates
      navigator.serviceWorker.addEventListener("controllerchange", () => {
        console.log("Service Worker updated");
      });
    }
  }, []);

  return null;
}

