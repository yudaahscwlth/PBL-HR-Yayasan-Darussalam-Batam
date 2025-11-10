import type { Metadata } from "next";
import "./globals.css";
import Providers from "./providers";
import { Poppins } from "next/font/google";

const poppins = Poppins({
  subsets: ["latin"],
  weight: ["300", "400", "500", "600", "700"],
});



export const metadata: Metadata = {
  title: "HR Yayasan Darussalam",
  description: "Human Resources Management System for Yayasan Darussalam",
  manifest: "/manifest.json",
  themeColor: "#1e4d8b",
  viewport: "width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no",
  appleWebApp: {
    capable: true,
    statusBarStyle: "default",
    title: "HR Darussalam",
  },
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="id" suppressHydrationWarning>
      <head>
        <link rel="icon" href="/favicon.ico" />
        <link rel="apple-touch-icon" href="/icons/logo-original.png" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="apple-mobile-web-app-title" content="HR Darussalam" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="msapplication-TileColor" content="#1e4d8b" />
        <meta name="msapplication-tap-highlight" content="no" />
      </head>
         <body className={`antialiased ${poppins.className}`} suppressHydrationWarning>
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}
