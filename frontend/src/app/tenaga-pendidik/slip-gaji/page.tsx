"use client";

import AccessControl from "@/components/AccessControl";
import BottomNavbar from "@/components/BottomNavbar";

export default function TPSlipGaji() {
  return (
    <AccessControl allowedRoles={["tenaga pendidik"]}>
      <div className="min-h-screen bg-gray-100 pb-28">
        <div className="px-5 py-6">
          <h1 className="text-2xl font-bold text-gray-800 mb-4">Slip Gaji Tenaga Pendidik</h1>
          <div className="bg-white p-6 rounded-xl shadow-sm">
            <p className="text-gray-600">Halaman Slip Gaji sedang dalam pengembangan.</p>
          </div>
        </div>
        <BottomNavbar />
      </div>
    </AccessControl>
  );
}
