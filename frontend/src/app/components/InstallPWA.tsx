"use client";

import { useEffect, useState } from "react";

interface BeforeInstallPromptEvent extends Event {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: "accepted" | "dismissed" }>;
}

export default function InstallPWA() {
  const [deferredPrompt, setDeferredPrompt] =
    useState<BeforeInstallPromptEvent | null>(null);
  const [showInstallButton, setShowInstallButton] = useState(false);

  useEffect(() => {
    const handler = (e: Event) => {
      e.preventDefault();
      setDeferredPrompt(e as BeforeInstallPromptEvent);
      setShowInstallButton(true);
    };

    window.addEventListener("beforeinstallprompt", handler);

    // Check if already installed
    if (window.matchMedia("(display-mode: standalone)").matches) {
      setShowInstallButton(false);
    }

    return () => {
      window.removeEventListener("beforeinstallprompt", handler);
    };
  }, []);

  const handleInstallClick = async () => {
    if (!deferredPrompt) return;

    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;

    if (outcome === "accepted") {
      console.log("User accepted the install prompt");
      setShowInstallButton(false);
    }

    setDeferredPrompt(null);
  };

  if (!showInstallButton) return null;

  return (
    <div className="fixed bottom-4 right-4 z-50 animate-fade-in">
      <div className="bg-blue-600 text-white rounded-lg shadow-2xl p-4 max-w-sm">
        <div className="flex items-start gap-3">
          <div className="flex-shrink-0 text-3xl">📱</div>
          <div className="flex-1">
            <h3 className="font-semibold text-lg mb-1">
              Install Aplikasi HR Darussalam
            </h3>
            <p className="text-sm text-blue-100 mb-3">
              Install aplikasi untuk akses cepat dan pengalaman lebih baik
            </p>
            <div className="flex gap-2">
              <button
                onClick={handleInstallClick}
                className="bg-white text-blue-600 px-4 py-2 rounded-md font-medium text-sm hover:bg-blue-50 transition-colors"
              >
                Install Sekarang
              </button>
              <button
                onClick={() => setShowInstallButton(false)}
                className="text-white px-4 py-2 rounded-md font-medium text-sm hover:bg-blue-700 transition-colors"
              >
                Nanti
              </button>
            </div>
          </div>
          <button
            onClick={() => setShowInstallButton(false)}
            className="flex-shrink-0 text-white hover:text-blue-200 text-xl"
          >
            ✕
          </button>
        </div>
      </div>
    </div>
  );
}

