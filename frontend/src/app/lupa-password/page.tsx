"use client";

import Image from "next/image";
import { useState } from "react";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/api";

export default function LupaPasswordPage() {
  const [email, setEmail] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const router = useRouter();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setSuccess("");

    // Validasi format email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      setError("Format email tidak valid. Silakan masukkan email yang benar.");
      return;
    }

    setIsLoading(true);

    try {
      const response = await apiClient.auth.requestOtp(email);

      if (response.success) {
        setSuccess(response.message || "Kode OTP telah dikirim ke email Anda.");
        // Redirect ke halaman verifikasi setelah 2 detik
        setTimeout(() => {
          router.push(
            `/lupa-password/verifikasi?email=${encodeURIComponent(email)}`
          );
        }, 2000);
      } else {
        // Response dari backend dengan success: false (misalnya email tidak terdaftar)
        setError(response.message || "Gagal mengirim OTP. Silakan coba lagi.");
        setIsLoading(false);
      }
    } catch (error: any) {
      console.error("Error requesting OTP:", error);
      // Handle network error atau error lainnya
      const errorMessage =
        error.response?.data?.message ||
        error.message ||
        "Terjadi kesalahan. Silakan coba lagi.";
      setError(errorMessage);
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-white">
      {/* Blue Decorative Header Section */}
      <div className="bg-[#1e4d8b] relative overflow-hidden">
        {/* Logo & Title */}
        <div className="flex flex-col items-center pt-8 sm:pt-12 pb-10 px-4">
          <div className="flex items-center justify-center mb-6">
            <div className="bg-white rounded-full p-3 shadow-lg">
              <Image
                src="/icons/logo-original.png"
                alt="Logo Darussalam"
                width={80}
                height={80}
                className="w-16 h-16 sm:w-20 sm:h-20"
              />
            </div>
          </div>
          <h1 className="text-white text-xl sm:text-2xl font-bold text-center">
            HR YAYASAN
            <br />
            DARUSSALAM
          </h1>
        </div>

        {/* Wave separator */}
        <div className="relative w-full -mb-1">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 1440 320"
            className="w-full h-auto"
            preserveAspectRatio="none"
          >
            <path
              fill="#ffffff"
              fillOpacity="1"
              d="M0,0L40,26.7C80,53,160,107,240,112C320,117,400,75,480,80C560,85,640,139,720,181.3C800,224,880,256,960,266.7C1040,277,1120,267,1200,218.7C1280,171,1360,85,1400,42.7L1440,0L1440,320L0,320Z"
            ></path>
          </svg>
        </div>
      </div>

      {/* Form Section */}
      <div className="flex justify-center px-4 py-8">
        <div className="w-full max-w-md">
          <h2 className="text-[#0066cc] text-2xl font-bold mb-2 text-center">
            Lupa Password
          </h2>
          <p className="text-gray-600 text-sm text-center mb-6">
            Masukkan email Anda untuk menerima kode OTP
          </p>

          <form onSubmit={handleSubmit} className="space-y-5">
            {/* Error Message */}
            {error && (
              <div className="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                <div className="flex items-center">
                  <svg
                    className="w-5 h-5 mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  {error}
                </div>
              </div>
            )}

            {/* Success Message */}
            {success && (
              <div className="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg">
                <div className="flex items-center">
                  <svg
                    className="w-5 h-5 mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  {success}
                </div>
              </div>
            )}

            {/* Email Input */}
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
                  <svg
                    className="w-5 h-5 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                    />
                  </svg>
                </div>
              </div>
            </div>

            {/* Submit Button */}
            <button
              type="submit"
              disabled={isLoading}
              className="w-full bg-[#0066cc] text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center gap-2 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isLoading ? (
                <>
                  <svg
                    className="animate-spin w-5 h-5"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      className="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      strokeWidth="4"
                    ></circle>
                    <path
                      className="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                  </svg>
                  Mengirim...
                </>
              ) : (
                <>
                  <svg
                    className="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                    />
                  </svg>
                  Kirim Kode OTP
                </>
              )}
            </button>

            {/* Back to Login */}
            <div className="text-center">
              <button
                type="button"
                onClick={() => router.push("/")}
                className="text-[#0066cc] text-sm hover:underline"
              >
                ← Kembali ke Login
              </button>
            </div>
          </form>

          {/* Copyright */}
          <div className="mt-6 text-center">
            <p className="text-gray-600 text-sm">
              © 2025 PBL 221 - Yayasan Darussalam
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
