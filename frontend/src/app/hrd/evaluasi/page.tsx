"use client";

import AccessControl from "@/components/AccessControl";
import { useRouter } from "next/navigation";

export default function HRDEvaluasiPage() {
  const router = useRouter();

  return (
    <AccessControl allowedRoles={["kepala hrd", "staff hrd"]}>
      <div className="min-h-screen bg-zinc-100 px-4 py-8 md:px-8">
        <div className="max-w-3xl mx-auto bg-white rounded-2xl shadow-md border border-black/10 p-6 md:p-8">
          <div className="flex items-center gap-3 mb-6">
            <button
              onClick={() => router.back()}
              className="w-9 h-9 flex items-center justify-center rounded-full border border-zinc-200 text-zinc-600 hover:bg-zinc-50"
              aria-label="Kembali"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 18l-6-6 6-6" />
              </svg>
            </button>
            <div>
              <p className="text-xs text-zinc-500 uppercase tracking-wide">Menu HRD</p>
              <h1 className="text-xl font-semibold text-zinc-900">Evaluasi</h1>
            </div>
          </div>

          <div className="space-y-4 text-sm text-zinc-600 leading-relaxed">
            <p>Halaman evaluasi HRD masih dalam pengembangan.</p>
            <p>Silakan kembali lagi nanti atau hubungi tim pengembang jika membutuhkan akses segera.</p>
          </div>
        </div>
      </div>
    </AccessControl>
  );
}

