// API Configuration
export const API_CONFIG = {
  BASE_URL: process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000",
  TIMEOUT: 30000, // Increased timeout to 30 seconds
  HEADERS: {
    "Content-Type": "application/json",
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
  },
};

// App Configuration
export const APP_CONFIG = {
  NAME: process.env.NEXT_PUBLIC_APP_NAME || "HR Yayasan Darussalam",
  VERSION: process.env.NEXT_PUBLIC_APP_VERSION || "1.0.0",
};
