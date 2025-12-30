"use client";

import Image from "next/image";
import { useState, useEffect, useRef, Suspense } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { apiClient } from "@/lib/api";

function VerifikasiOtpContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const email = searchParams.get("email") || "";

  const [step, setStep] = useState<"verify" | "reset">("verify");
  const [otpDigits, setOtpDigits] = useState<string[]>([
    "",
    "",
    "",
    "",
    "",
    "",
  ]);
  const otpInputRefs = useRef<(HTMLInputElement | null)[]>([]);
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [showPasswordConfirmation, setShowPasswordConfirmation] =
    useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  // Get full OTP code from digits array
  const kodeOtp = otpDigits.join("");

  useEffect(() => {
    if (!email) {
      router.push("/lupa-password");
    }
  }, [email, router]);

  // Handle OTP input change
  const handleOtpChange = (index: number, value: string) => {
    // Only allow numbers
    const numericValue = value.replace(/\D/g, "");

    if (numericValue.length > 1) {
      // Handle paste: fill multiple boxes
      const digits = numericValue.slice(0, 6).split("");
      const newOtpDigits = [...otpDigits];
      digits.forEach((digit, i) => {
        if (index + i < 6) {
          newOtpDigits[index + i] = digit;
        }
      });
      setOtpDigits(newOtpDigits);

      // Focus on the last filled box or the last box
      const nextIndex = Math.min(index + digits.length, 5);
      otpInputRefs.current[nextIndex]?.focus();
    } else {
      // Single digit input
      const newOtpDigits = [...otpDigits];
      newOtpDigits[index] = numericValue;
      setOtpDigits(newOtpDigits);

      // Auto-focus next input if value entered
      if (numericValue && index < 5) {
        otpInputRefs.current[index + 1]?.focus();
      }
    }
  };

  // Handle backspace
  const handleOtpKeyDown = (
    index: number,
    e: React.KeyboardEvent<HTMLInputElement>
  ) => {
    if (e.key === "Backspace" && !otpDigits[index] && index > 0) {
      otpInputRefs.current[index - 1]?.focus();
    }
  };

  // Handle paste
  const handleOtpPaste = (e: React.ClipboardEvent<HTMLInputElement>) => {
    e.preventDefault();
    const pastedData = e.clipboardData
      .getData("text")
      .replace(/\D/g, "")
      .slice(0, 6);
    if (pastedData) {
      const digits = pastedData.split("");
      const newOtpDigits = [...otpDigits];
      digits.forEach((digit, i) => {
        if (i < 6) {
          newOtpDigits[i] = digit;
        }
      });
      setOtpDigits(newOtpDigits);
      const nextIndex = Math.min(digits.length, 5);
      otpInputRefs.current[nextIndex]?.focus();
    }
  };

  const handleVerifyOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setSuccess("");
    setIsLoading(true);

    try {
      const response = await apiClient.auth.verifyOtp(email, kodeOtp);

      if (response.success) {
        setSuccess(
          "Kode OTP berhasil diverifikasi. Silakan masukkan password baru."
        );
        setStep("reset");
      } else {
        setError(response.message || "Kode OTP tidak valid.");
      }
    } catch (error: any) {
      console.error("Error verifying OTP:", error);
      setError(
        error.response?.data?.message ||
          "Kode OTP tidak valid. Silakan coba lagi."
      );
    } finally {
      setIsLoading(false);
    }
  };

  const handleResetPassword = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setSuccess("");

    // Validasi password
    if (password.length < 6) {
      setError("Password minimal 6 karakter.");
      return;
    }

    if (password !== passwordConfirmation) {
      setError("Password dan konfirmasi password tidak cocok.");
      return;
    }

    setIsLoading(true);

    try {
      const response = await apiClient.auth.resetPassword(
        email,
        kodeOtp,
        password,
        passwordConfirmation
      );

      if (response.success) {
        setSuccess(
          "Password berhasil direset! Mengalihkan ke halaman login..."
        );
        setTimeout(() => {
          router.push("/");
        }, 2000);
      } else {
        setError(
          response.message || "Gagal mereset password. Silakan coba lagi."
        );
      }
    } catch (error: any) {
      console.error("Error resetting password:", error);
      setError(
        error.response?.data?.message || "Terjadi kesalahan. Silakan coba lagi."
      );
    } finally {
      setIsLoading(false);
    }
  };

  const handleResendOtp = async () => {
    setError("");
    setSuccess("");
    setIsLoading(true);

    try {
      const response = await apiClient.auth.requestOtp(email);

      if (response.success) {
        setSuccess("Kode OTP baru telah dikirim ke email Anda.");
      } else {
        setError(response.message || "Gagal mengirim OTP. Silakan coba lagi.");
      }
    } catch (error: any) {
      console.error("Error resending OTP:", error);
      setError(
        error.response?.data?.message || "Terjadi kesalahan. Silakan coba lagi."
      );
    } finally {
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
            {step === "verify" ? "Verifikasi OTP" : "Reset Password"}
          </h2>
          <p className="text-gray-600 text-sm text-center mb-6">
            {step === "verify"
              ? "Masukkan kode OTP yang telah dikirim ke email Anda"
              : "Masukkan password baru untuk akun Anda"}
          </p>

          {step === "verify" ? (
            <form onSubmit={handleVerifyOtp} className="space-y-5">
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

              {/* Email Display */}
              <div className="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                <p className="text-sm text-gray-600">Email:</p>
                <p className="text-sm font-semibold text-gray-900">{email}</p>
              </div>

              {/* OTP Input - 6 Kotak Terpisah */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-3 text-center">
                  Kode OTP
                </label>
                <div className="flex justify-center gap-2 sm:gap-3 mb-3">
                  {otpDigits.map((digit, index) => (
                    <input
                      key={index}
                      ref={(el) => {
                        otpInputRefs.current[index] = el;
                      }}
                      type="text"
                      inputMode="numeric"
                      value={digit}
                      onChange={(e) => handleOtpChange(index, e.target.value)}
                      onKeyDown={(e) => handleOtpKeyDown(index, e)}
                      onPaste={index === 0 ? handleOtpPaste : undefined}
                      maxLength={1}
                      className="w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl sm:text-3xl font-bold border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-black disabled:bg-gray-100 disabled:cursor-not-allowed"
                      required
                      disabled={isLoading}
                      autoComplete="off"
                    />
                  ))}
                </div>
                <p className="text-xs text-gray-500 text-center">
                  Kode OTP berlaku selama 15 menit
                </p>
              </div>

              {/* Submit Button */}
              <button
                type="submit"
                disabled={isLoading || kodeOtp.length !== 6}
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
                    Memverifikasi...
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
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                    Verifikasi OTP
                  </>
                )}
              </button>

              {/* Resend OTP */}
              <div className="text-center">
                <button
                  type="button"
                  onClick={handleResendOtp}
                  disabled={isLoading}
                  className="text-[#0066cc] text-sm hover:underline disabled:opacity-50"
                >
                  Kirim ulang kode OTP
                </button>
              </div>

              {/* Back to Request OTP */}
              <div className="text-center">
                <button
                  type="button"
                  onClick={() => router.push("/lupa-password")}
                  className="text-[#0066cc] text-sm hover:underline"
                >
                  ← Kembali
                </button>
              </div>
            </form>
          ) : (
            <form onSubmit={handleResetPassword} className="space-y-5">
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

              {/* Password Input */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Password Baru
                </label>
                <div className="relative">
                  <input
                    type={showPassword ? "text" : "password"}
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Masukkan password baru (min. 6 karakter)"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10 text-black"
                    required
                    minLength={6}
                    disabled={isLoading}
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute inset-y-0 right-0 flex items-center px-3"
                    disabled={isLoading}
                  >
                    {showPassword ? (
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
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                        />
                      </svg>
                    ) : (
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
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                        />
                      </svg>
                    )}
                  </button>
                </div>
              </div>

              {/* Password Confirmation Input */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Konfirmasi Password
                </label>
                <div className="relative">
                  <input
                    type={showPasswordConfirmation ? "text" : "password"}
                    value={passwordConfirmation}
                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                    placeholder="Konfirmasi password baru"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10 text-black"
                    required
                    minLength={6}
                    disabled={isLoading}
                  />
                  <button
                    type="button"
                    onClick={() =>
                      setShowPasswordConfirmation(!showPasswordConfirmation)
                    }
                    className="absolute inset-y-0 right-0 flex items-center px-3"
                    disabled={isLoading}
                  >
                    {showPasswordConfirmation ? (
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
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                        />
                      </svg>
                    ) : (
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
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                        />
                      </svg>
                    )}
                  </button>
                </div>
              </div>

              {/* Submit Button */}
              <button
                type="submit"
                disabled={
                  isLoading ||
                  password.length < 6 ||
                  password !== passwordConfirmation
                }
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
                    Mereset Password...
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
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                      />
                    </svg>
                    Reset Password
                  </>
                )}
              </button>

              {/* Back to Verify OTP */}
              <div className="text-center">
                <button
                  type="button"
                  onClick={() => setStep("verify")}
                  className="text-[#0066cc] text-sm hover:underline"
                >
                  ← Kembali ke Verifikasi OTP
                </button>
              </div>
            </form>
          )}

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

export default function VerifikasiOtpPage() {
  return (
    <Suspense
      fallback={
        <div className="min-h-screen bg-white flex items-center justify-center">
          <div className="flex flex-col items-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
            <p className="text-gray-600">Memuat...</p>
          </div>
        </div>
      }
    >
      <VerifikasiOtpContent />
    </Suspense>
  );
}
