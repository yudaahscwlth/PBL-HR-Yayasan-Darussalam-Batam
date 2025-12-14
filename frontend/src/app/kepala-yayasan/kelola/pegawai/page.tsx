"use client";

import AccessControl from "@/components/AccessControl";
import { useState, useEffect, useRef } from "react";
import axios from "axios";
import {
  Filter,
  MoreHorizontal,
  ChevronLeft,
  ChevronRight,
  User as UserIcon,
  ClipboardList,
  ArrowLeft,
} from "lucide-react";
import Link from "next/link";
import { User } from "@/types/auth";
import { useRouter } from "next/navigation";
import { createPortal } from "react-dom";

/* ============================ */
/* Action Menu rendered via Portal */
/* ============================ */
function ActionMenuPortal({
  open,
  x,
  y,
  onClose,
  userId,
}: {
  open: boolean;
  x: number;
  y: number;
  onClose: () => void;
  userId: number;
}) {
  const menuRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    if (!open) return;
    const onDocClick = (e: MouseEvent) => {
      if (!menuRef.current) return;
      if (!menuRef.current.contains(e.target as Node)) onClose();
    };
    document.addEventListener("mousedown", onDocClick);
    return () => document.removeEventListener("mousedown", onDocClick);
  }, [open, onClose]);

  useEffect(() => {
    if (!open) return;
    const onScrollOrResize = () => onClose();
    window.addEventListener("scroll", onScrollOrResize, true);
    window.addEventListener("resize", onScrollOrResize);
    return () => {
      window.removeEventListener("scroll", onScrollOrResize, true);
      window.removeEventListener("resize", onScrollOrResize);
    };
  }, [open, onClose]);

  if (!open) return null;

  const menu = (
    <div
      ref={menuRef}
      className="fixed z-[1000] w-56 bg-white rounded-md shadow-lg border border-gray-100 py-1 text-left"
      style={{ top: y, left: x }}
    >
      <Link
        href={`/kepala-yayasan/profile/${userId}`}
        className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-sky-800 flex items-center gap-2"
      >
        <UserIcon className="w-4 h-4" />
        Detail Profil
      </Link>
      <Link
        href={`/kepala-yayasan/evaluasi/${userId}`}
        className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-sky-800 flex items-center gap-2"
      >
        <ClipboardList className="w-4 h-4" />
        Rekap Evaluasi
      </Link>
    </div>
  );

  return createPortal(menu, document.body);
}

export default function KepalaYayasanKelolaPegawaiPage() {
  const router = useRouter();
  const [users, setUsers] = useState<User[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState("");
  const [entriesPerPage, setEntriesPerPage] = useState(10);
  const [currentPage, setCurrentPage] = useState(1);

  const [showFilter, setShowFilter] = useState(false);
  const [selectedKecamatan, setSelectedKecamatan] = useState("");
  const [selectedGolonganDarah, setSelectedGolonganDarah] = useState("");
  const [selectedRentangUsia, setSelectedRentangUsia] = useState("");
  const [kecamatanList, setKecamatanList] = useState<
    Array<{ id: string; name: string }>
  >([]);

  const [menuState, setMenuState] = useState<{
    open: boolean;
    x: number;
    y: number;
    userId: number | null;
  }>({ open: false, x: 0, y: 0, userId: null });

  const openActionMenu = (e: React.MouseEvent, userId: number) => {
    const rect = (e.currentTarget as HTMLElement).getBoundingClientRect();
    const menuWidth = 224;
    const padding = 8;
    const approxMenuHeight = 200;

    let x = rect.right - menuWidth;
    let y = rect.bottom + padding;

    const vw = window.innerWidth;
    const vh = window.innerHeight;

    if (x < padding) x = padding;
    if (rect.right + padding + menuWidth <= vw) {
      x = rect.right - menuWidth;
    }
    if (y + approxMenuHeight > vh) {
      y = rect.top - padding - approxMenuHeight;
    }
    if (y < padding) y = padding;
    if (x + menuWidth > vw - padding) x = vw - padding - menuWidth;

    setMenuState({ open: true, x, y, userId });
  };

  const closeActionMenu = () => {
    setMenuState((prev) => ({ ...prev, open: false }));
  };

  useEffect(() => {
    const fetchUsers = async () => {
      try {
        setIsLoading(true);
        const token = localStorage.getItem("auth_token");
        const response = await axios.get(
          `${
            process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000"
          }/api/users`,
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        const fetchedData = response.data.data;
        const allUsers = Array.isArray(fetchedData)
          ? fetchedData
          : fetchedData?.data || [];

        const filteredUsers = allUsers.filter((user: any) => {
          const firstRole = user.roles?.[0];
          const roleName =
            typeof firstRole === "string" ? firstRole : firstRole?.name;
          const userRole = roleName?.toLowerCase()?.trim() || "";
          return userRole !== "superadmin" && userRole !== "kepala yayasan";
        });

        setUsers(filteredUsers);
      } catch (error) {
        console.error("Error fetching users:", error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchUsers();
  }, []);

  useEffect(() => {
    const loadKecamatan = async () => {
      try {
        const response = await fetch("/kecamatan-indonesia.json");
        const data = await response.json();
        setKecamatanList(data);
      } catch (error) {
        console.error("Error loading kecamatan data:", error);
      }
    };
    loadKecamatan();
  }, []);

  const calculateTenure = (startDate?: string) => {
    if (!startDate) return "-";
    const start = new Date(startDate);
    const now = new Date();

    let years = now.getFullYear() - start.getFullYear();
    let months = now.getMonth() - start.getMonth();

    if (months < 0) {
      years--;
      months += 12;
    }

    return `${years} tahun ${months} bulan`;
  };

  const calculateAge = (birthDate?: string) => {
    if (!birthDate) return null;
    const birth = new Date(birthDate);
    const now = new Date();
    let age = now.getFullYear() - birth.getFullYear();
    const monthDiff = now.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < birth.getDate())) {
      age--;
    }
    return age;
  };

  const filteredUsers = users.filter((user) => {
    const searchLower = searchQuery.toLowerCase();
    const name = user.profile_pribadi?.nama_lengkap?.toLowerCase() || "";
    const email = user.email.toLowerCase();
    const jabatan =
      user.profile_pekerjaan?.jabatan?.nama_jabatan?.toLowerCase() || "";
    const matchesSearch =
      name.includes(searchLower) ||
      email.includes(searchLower) ||
      jabatan.includes(searchLower);

    const kecamatan = user.profile_pribadi?.kecamatan || "";
    const matchesKecamatan =
      !selectedKecamatan || kecamatan === selectedKecamatan;

    const golonganDarah =
      user.profile_pribadi?.golongan_darah?.trim().toUpperCase() || "";
    const matchesGolonganDarah =
      !selectedGolonganDarah ||
      golonganDarah === selectedGolonganDarah.toUpperCase();

    let matchesAgeRange = true;
    if (selectedRentangUsia) {
      const age = calculateAge(user.profile_pribadi?.tanggal_lahir);
      if (age !== null) {
        switch (selectedRentangUsia) {
          case "18-25":
            matchesAgeRange = age >= 18 && age <= 25;
            break;
          case "26-35":
            matchesAgeRange = age >= 26 && age <= 35;
            break;
          case "36-45":
            matchesAgeRange = age >= 36 && age <= 45;
            break;
          case "46-55":
            matchesAgeRange = age >= 46 && age <= 55;
            break;
          case "56+":
            matchesAgeRange = age >= 56;
            break;
          default:
            matchesAgeRange = true;
        }
      } else {
        matchesAgeRange = false;
      }
    }

    return (
      matchesSearch &&
      matchesKecamatan &&
      matchesGolonganDarah &&
      matchesAgeRange
    );
  });

  const totalPages = Math.ceil(filteredUsers.length / entriesPerPage);
  const startIndex = (currentPage - 1) * entriesPerPage;
  const paginatedUsers = filteredUsers.slice(
    startIndex,
    startIndex + entriesPerPage
  );

  return (
    <AccessControl allowedRoles={["kepala yayasan"]}>
      <div className="min-h-screen bg-gray-50 p-4 md:p-8 font-sans text-gray-800">
        <div className="max-w-7xl mx-auto space-y-6">
          <div className="flex items-center gap-4">
            <button
              onClick={() => router.push("/kepala-yayasan/menu")}
              className="p-2 hover:bg-gray-200 rounded-full transition-colors"
            >
              <ArrowLeft className="w-6 h-6 text-gray-700" />
            </button>
            <h1 className="text-2xl font-bold text-gray-800">Kelola Pegawai</h1>
          </div>

          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 className="text-lg font-bold text-gray-800 mb-6">
              Kelola Pegawai
            </h2>

            <div className="flex flex-col md:flex-row justify-end gap-4 mb-6">
              <div className="flex gap-2">
                <button
                  onClick={() => setShowFilter(!showFilter)}
                  className={`px-4 py-2 rounded-md flex items-center gap-2 text-sm font-medium transition-colors ${
                    showFilter
                      ? "bg-sky-800 text-white hover:bg-sky-700"
                      : "bg-sky-800 hover:bg-sky-700 text-white"
                  }`}
                >
                  Filter
                  <Filter className="w-4 h-4" />
                </button>
              </div>
            </div>

            {showFilter && (
              <div className="bg-gray-50 rounded-lg border border-gray-200 p-4 mb-6">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Kecamatan
                    </label>
                    <select
                      value={selectedKecamatan}
                      onChange={(e) => {
                        setSelectedKecamatan(e.target.value);
                        setCurrentPage(1);
                      }}
                      className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800 text-sm"
                    >
                      <option value="">Semua Kecamatan</option>
                      {kecamatanList.map((kec) => (
                        <option key={kec.id} value={kec.name}>
                          {kec.name}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Golongan Darah
                    </label>
                    <select
                      value={selectedGolonganDarah}
                      onChange={(e) => {
                        setSelectedGolonganDarah(e.target.value);
                        setCurrentPage(1);
                      }}
                      className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800 text-sm"
                    >
                      <option value="">Semua Golongan Darah</option>
                      {["A", "B", "AB", "O"].map((gol) => (
                        <option key={gol} value={gol}>
                          {gol}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Rentang Usia
                    </label>
                    <select
                      value={selectedRentangUsia}
                      onChange={(e) => {
                        setSelectedRentangUsia(e.target.value);
                        setCurrentPage(1);
                      }}
                      className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800 text-sm"
                    >
                      <option value="">Semua Usia</option>
                      <option value="18-25">18-25 tahun</option>
                      <option value="26-35">26-35 tahun</option>
                      <option value="36-45">36-45 tahun</option>
                      <option value="46-55">46-55 tahun</option>
                      <option value="56+">56 tahun keatas</option>
                    </select>
                  </div>

                  <div className="flex items-end">
                    <button
                      onClick={() => {
                        setSelectedKecamatan("");
                        setSelectedGolonganDarah("");
                        setSelectedRentangUsia("");
                        setCurrentPage(1);
                      }}
                      className="w-full bg-sky-800 hover:bg-sky-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                      Reset Filter
                    </button>
                  </div>
                </div>
              </div>
            )}

            <div className="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
              <div className="flex items-center gap-2 text-sm text-gray-600">
                <select
                  value={entriesPerPage}
                  onChange={(e) => {
                    setEntriesPerPage(Number(e.target.value));
                    setCurrentPage(1);
                  }}
                  className="border border-gray-300 rounded px-2 py-1 focus:outline-none focus:border-sky-800"
                >
                  <option value={10}>10</option>
                  <option value={25}>25</option>
                  <option value={50}>50</option>
                </select>
                <span>entries per page</span>
              </div>

              <div className="flex items-center gap-2">
                <span className="text-sm text-gray-600">Search:</span>
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => {
                    setSearchQuery(e.target.value);
                    setCurrentPage(1);
                  }}
                  className="border border-gray-300 rounded px-3 py-1 focus:outline-none focus:border-sky-800 w-full md:w-64"
                />
              </div>
            </div>

            <div style={{ overflowX: "auto", overflowY: "visible" }}>
              <table className="w-full text-sm text-left">
                <thead className="bg-gray-50 text-gray-700 font-bold border-b border-gray-200">
                  <tr>
                    <th className="p-3">Nama</th>
                    <th className="p-3">Jabatan</th>
                    <th className="p-3">Tanggal Masuk</th>
                    <th className="p-3">Tahun Pengabdian</th>
                    <th className="p-3">Email</th>
                    <th className="p-3">Status</th>
                    <th className="p-3 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {isLoading ? (
                    <tr>
                      <td colSpan={7} className="p-8 text-center text-gray-500">
                        Loading data...
                      </td>
                    </tr>
                  ) : paginatedUsers.length === 0 ? (
                    <tr>
                      <td colSpan={7} className="p-8 text-center text-gray-500">
                        Tidak ada data pegawai.
                      </td>
                    </tr>
                  ) : (
                    paginatedUsers.map((user) => (
                      <tr
                        key={user.id}
                        className="hover:bg-gray-50 transition-colors"
                      >
                        <td className="p-3 font-medium text-gray-800">
                          {user.profile_pribadi?.nama_lengkap ||
                            "Nama Belum Diisi"}
                        </td>
                        <td className="p-3 text-gray-600">
                          {user.profile_pekerjaan?.jabatan?.nama_jabatan || "-"}
                        </td>
                        <td className="p-3 text-gray-600">
                          {user.profile_pekerjaan?.tanggal_masuk || "-"}
                        </td>
                        <td className="p-3 text-gray-600">
                          {calculateTenure(
                            user.profile_pekerjaan?.tanggal_masuk
                          )}
                        </td>
                        <td className="p-3 text-gray-600">{user.email}</td>
                        <td className="p-3">
                          <span
                            className={`px-2 py-1 rounded text-xs font-medium capitalize
                            ${
                              user.profile_pekerjaan?.status_pegawai === "aktif"
                                ? "bg-emerald-100 text-emerald-700"
                                : user.profile_pekerjaan?.status_pegawai ===
                                  "tetap"
                                ? "bg-green-100 text-green-700"
                                : user.profile_pekerjaan?.status_pegawai ===
                                  "kontrak"
                                ? "bg-blue-100 text-blue-700"
                                : user.profile_pekerjaan?.status_pegawai ===
                                  "honorer"
                                ? "bg-yellow-100 text-yellow-700"
                                : user.profile_pekerjaan?.status_pegawai ===
                                  "magang"
                                ? "bg-purple-100 text-purple-700"
                                : user.profile_pekerjaan?.status_pegawai ===
                                  "nonaktif"
                                ? "bg-red-100 text-red-700"
                                : user.profile_pekerjaan?.status_pegawai ===
                                  "pensiun"
                                ? "bg-gray-100 text-gray-700"
                                : user.profile_pekerjaan?.status_pegawai ===
                                  "cuti"
                                ? "bg-orange-100 text-orange-700"
                                : user.profile_pekerjaan?.status_pegawai ===
                                  "skorsing"
                                ? "bg-rose-100 text-rose-700"
                                : "bg-slate-100 text-slate-700"
                            }`}
                          >
                            {user.profile_pekerjaan?.status_pegawai ||
                              "Belum Set"}
                          </span>
                        </td>
                        <td className="p-3 text-center">
                          <button
                            onClick={(e) => openActionMenu(e, user.id)}
                            className="p-1 hover:bg-gray-200 rounded transition-colors"
                            aria-label="Buka menu aksi"
                          >
                            <MoreHorizontal className="w-5 h-5 text-gray-500" />
                          </button>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>

            <div className="flex flex-col md:flex-row justify-between items-center gap-4 mt-4 text-sm text-gray-600">
              <div>
                Showing {startIndex + 1} to{" "}
                {Math.min(startIndex + entriesPerPage, filteredUsers.length)} of{" "}
                {filteredUsers.length} entries
              </div>
              <div className="flex items-center gap-1">
                <button
                  onClick={() =>
                    setCurrentPage((prev) => Math.max(prev - 1, 1))
                  }
                  disabled={currentPage === 1}
                  className="p-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ChevronLeft className="w-4 h-4" />
                </button>
                {Array.from({ length: totalPages }, (_, i) => i + 1).map(
                  (page) => (
                    <button
                      key={page}
                      onClick={() => setCurrentPage(page)}
                      className={`px-3 py-1 border rounded ${
                        currentPage === page
                          ? "bg-sky-800 text-white border-sky-800"
                          : "border-gray-300 hover:bg-gray-50"
                      }`}
                    >
                      {page}
                    </button>
                  )
                )}
                <button
                  onClick={() =>
                    setCurrentPage((prev) => Math.min(prev + 1, totalPages))
                  }
                  disabled={currentPage === totalPages}
                  className="p-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ChevronRight className="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <ActionMenuPortal
          open={menuState.open && !!menuState.userId}
          x={menuState.x}
          y={menuState.y}
          userId={menuState.userId ?? 0}
          onClose={closeActionMenu}
        />
      </div>
    </AccessControl>
  );
}
