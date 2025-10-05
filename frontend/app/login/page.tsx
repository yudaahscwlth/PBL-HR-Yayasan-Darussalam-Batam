"use client";

import { useState } from "react";
import LogoDisplay from "../components/LogoDisplay";

export default function LoginPage() {
  const [selectedRole, setSelectedRole] = useState("");
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    console.log("Login attempt:", { selectedRole, username, password });
  };

  return (
    <div className="min-h-screen bg-sky-800">
      {/* Mobile Layout - Vertical */}
      <div className="md:hidden flex flex-col min-h-screen">
        {/* Header Section */}
        <div className="bg-sky-800 pt-12 pb-16 px-6 text-white flex-shrink-0 safe-area-top">
          <div className="flex items-center justify-center gap-4 mt-4">
            <LogoDisplay width={90} height={90} className="drop-shadow-lg flex-shrink-0" logoPath="/logo-drs.png" />
            <h1 className="text-xl font-extrabold leading-tight">
              HR YAYASAN
              <br />
              DARUSSALAM
            </h1>
          </div>
        </div>

        {/* Main Content - Mobile */}
        <div className="flex-1 bg-white rounded-t-[40px] px-6 pt-8 pb-8 min-h-0 -mt-4 safe-area-bottom">
          <h2 className="text-sky-600 text-xl font-bold mb-6 text-center">Login Akun</h2>

          <form onSubmit={handleSubmit} className="flex flex-col gap-5">
            {/* Role */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Login sebagai</label>
              <select
                value={selectedRole}
                onChange={(e) => setSelectedRole(e.target.value)}
                className="w-full border border-gray-300 rounded-lg px-3 py-3 text-base focus:outline-none focus:ring-2 focus:ring-sky-600 focus:border-transparent bg-gray-50"
              >
                <option value="">Pilih Role</option>
                <option value="kepala">Kepala Yayasan/Dept</option>
                <option value="hrd">HRD</option>
                <option value="pegawai">Pegawai</option>
              </select>
            </div>

            {/* Username */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
              <input
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                className="w-full border border-gray-300 rounded-lg px-3 py-3 text-base focus:outline-none focus:ring-2 focus:ring-sky-600 focus:border-transparent bg-gray-50"
                placeholder="Masukkan username"
              />
            </div>

            {/* Password */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full border border-gray-300 rounded-lg px-3 py-3 text-base focus:outline-none focus:ring-2 focus:ring-sky-600 focus:border-transparent bg-gray-50"
                placeholder="Masukkan password"
              />
            </div>

            {/* Forgot Password & Login Button */}
            <div className="flex flex-col gap-3">
              <div className="text-right">
                <button type="button" className="text-sky-600 text-sm font-medium hover:underline">
                  Lupa Password?
                </button>
              </div>
              <button type="submit" className="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-lg py-3 text-base transition-colors shadow-lg active:scale-95 transform">
                Login
              </button>
            </div>
          </form>

          {/* Footer */}
          <div className="mt-4 text-center text-xs text-gray-400">© 2025 PBL 221 - Yayasan Darussalam</div>
        </div>
      </div>

      {/* Desktop Layout - Horizontal */}
      <div className="hidden md:flex min-h-screen">
        {/* Left Side - Logo & Branding */}
        <div className="flex-1 bg-sky-800 flex items-center justify-center p-12">
          <div className="text-center text-white max-w-md">
            <div className="flex justify-center mb-8">
              <LogoDisplay width={150} height={150} className="drop-shadow-2xl" logoPath="/logo-drs.png" />
            </div>
            <h1 className="text-4xl font-extrabold leading-tight mb-4">
              HR YAYASAN
              <br />
              DARUSSALAM
            </h1>
            <p className="text-sky-100 text-lg">Sistem Manajemen Sumber Daya Manusia</p>
          </div>
        </div>

        {/* Right Side - Login Form */}
        <div className="flex-1 bg-white flex items-center justify-center p-12">
          <div className="w-full max-w-md">
            <h2 className="text-sky-600 text-3xl font-bold mb-8 text-center">Login Akun</h2>

            <form onSubmit={handleSubmit} className="flex flex-col gap-6">
              {/* Role */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Login sebagai</label>
                <select
                  value={selectedRole}
                  onChange={(e) => setSelectedRole(e.target.value)}
                  className="w-full border border-gray-300 rounded-xl px-4 py-4 text-base focus:outline-none focus:ring-2 focus:ring-sky-600 focus:border-transparent bg-gray-50"
                >
                  <option value="">Pilih Role</option>
                  <option value="kepala">Kepala Yayasan/Dept</option>
                  <option value="hrd">HRD</option>
                  <option value="pegawai">Pegawai</option>
                </select>
              </div>

              {/* Username */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <input
                  type="text"
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  className="w-full border border-gray-300 rounded-xl px-4 py-4 text-base focus:outline-none focus:ring-2 focus:ring-sky-600 focus:border-transparent bg-gray-50"
                  placeholder="Masukkan username"
                />
              </div>

              {/* Password */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full border border-gray-300 rounded-xl px-4 py-4 text-base focus:outline-none focus:ring-2 focus:ring-sky-600 focus:border-transparent bg-gray-50"
                  placeholder="Masukkan password"
                />
              </div>

              {/* Forgot Password & Login Button */}
              <div className="flex flex-col gap-4">
                <div className="text-right">
                  <button type="button" className="text-sky-600 text-sm font-medium hover:underline">
                    Lupa Password?
                  </button>
                </div>
                <button type="submit" className="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl py-4 text-lg transition-colors shadow-lg hover:shadow-xl transform hover:scale-105">
                  Login
                </button>
              </div>
            </form>

            {/* Footer */}
            <div className="mt-8 text-center text-sm text-gray-400">© 2025 PBL 221 - Yayasan Darussalam</div>
          </div>
        </div>
      </div>
    </div>
  );
}
