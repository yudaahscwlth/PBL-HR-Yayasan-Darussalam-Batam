"use client";

import { useState } from "react";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";

export default function KepalaYayasanProfileEdit() {
  const { user } = useAuthStore();
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(false);

  const handleSave = async () => {
    setIsLoading(true);
    // TODO: Implement save functionality
    setTimeout(() => {
      setIsLoading(false);
      router.back();
    }, 1000);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white px-4 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <button onClick={() => router.back()} className="p-2 hover:bg-gray-100 rounded-full">
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <h1 className="text-lg font-semibold text-gray-800">Edit Profile</h1>
          <button onClick={handleSave} disabled={isLoading} className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
            {isLoading ? "Saving..." : "Save"}
          </button>
        </div>
      </div>

      {/* Content */}
      <div className="px-4 py-6">
        <div className="bg-white rounded-lg shadow-sm p-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-4">Profile Information</h2>

          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
              <input type="text" defaultValue={user?.profile_pribadi?.nama_lengkap || ""} className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input type="email" defaultValue={user?.email || ""} className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
              <input type="tel" defaultValue={user?.profile_pribadi?.no_telepon || ""} className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
              <textarea defaultValue={user?.profile_pribadi?.alamat || ""} rows={3} className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

