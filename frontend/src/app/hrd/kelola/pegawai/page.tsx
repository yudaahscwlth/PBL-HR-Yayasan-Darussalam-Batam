"use client";

import AccessControl from "@/components/AccessControl";
import { useState, useEffect, useRef } from "react";
import axios from "axios";
import {
  Search,
  Filter,
  Trash2,
  Plus,
  MoreHorizontal,
  ChevronLeft,
  ChevronRight,
  User as UserIcon,
  FileText,
  Calendar,
  ClipboardList,
  ArrowLeft,
  X
} from "lucide-react";
import Link from "next/link";
import { User, Jabatan, Departemen, TempatKerja } from "@/types/auth";
import { useRouter } from "next/navigation";
import toast from "react-hot-toast";
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

  // Tutup saat klik di luar
  useEffect(() => {
    if (!open) return;
    const onDocClick = (e: MouseEvent) => {
      if (!menuRef.current) return;
      if (!menuRef.current.contains(e.target as Node)) onClose();
    };
    document.addEventListener("mousedown", onDocClick);
    return () => document.removeEventListener("mousedown", onDocClick);
  }, [open, onClose]);

  // Tutup saat scroll/resize (opsional: bisa dihitung ulang posisinya)
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
        href={`/hrd/profile/${userId}`}
        className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-sky-800 flex items-center gap-2"
      >
        <UserIcon className="w-4 h-4" />
        Detail Profil
      </Link>
      <Link
        href={`/hrd/absensi/${userId}`}
        className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-sky-800 flex items-center gap-2"
      >
        <Calendar className="w-4 h-4" />
        Rekap Absensi
      </Link>
      <Link
        href={`/hrd/cuti/${userId}`}
        className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-sky-800 flex items-center gap-2"
      >
        <FileText className="w-4 h-4" />
        Rekap Cuti
      </Link>
      <Link
        href={`/hrd/evaluasi/${userId}`}
        className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-sky-800 flex items-center gap-2"
      >
        <ClipboardList className="w-4 h-4" />
        Rekap Evaluasi
      </Link>
    </div>
  );

  return createPortal(menu, document.body);
}

export default function HrdKelolaPegawaiPage() {
  const router = useRouter();
  const [users, setUsers] = useState<User[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState("");
  const [entriesPerPage, setEntriesPerPage] = useState(10);
  const [currentPage, setCurrentPage] = useState(1);
  const [selectedUsers, setSelectedUsers] = useState<number[]>([]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  
  // Filter states
  const [showFilter, setShowFilter] = useState(false);
  const [selectedKecamatan, setSelectedKecamatan] = useState("");
  const [selectedGolonganDarah, setSelectedGolonganDarah] = useState("");
  const [selectedRentangUsia, setSelectedRentangUsia] = useState("");
  const [kecamatanList, setKecamatanList] = useState<Array<{id: string; name: string}>>([]);

  const [formData, setFormData] = useState({
    email: '',
    password: '',
    nama_lengkap: '',
    nik: '',
    nik_karyawan: '',
    tanggal_masuk: '',
    id_jabatan: '',
    id_departemen: '',
    id_tempat_kerja: '',
    status: '',
    role: ''
  });
  const [jabatanList, setJabatanList] = useState<Jabatan[]>([]);
  const [departemenList, setDepartemenList] = useState<Departemen[]>([]);
  const [tempatKerjaList, setTempatKerjaList] = useState<TempatKerja[]>([]);
  const [rolesList] = useState(['superadmin', 'kepala yayasan', 'direktur pendidikan', 'kepala hrd', 'staff hrd', 'kepala departemen', 'kepala sekolah', 'tenaga pendidik']);

  // State untuk portal dropdown
  const [menuState, setMenuState] = useState<{
    open: boolean;
    x: number;
    y: number;
    userId: number | null;
  }>({ open: false, x: 0, y: 0, userId: null });

  const openActionMenu = (e: React.MouseEvent, userId: number) => {
    const rect = (e.currentTarget as HTMLElement).getBoundingClientRect();
    const menuWidth = 224; // w-56
    const padding = 8;
    const approxMenuHeight = 200;

    // Default: rata kanan tombol, muncul di bawah
    let x = rect.right - menuWidth;
    let y = rect.bottom + padding;

    const vw = window.innerWidth;
    const vh = window.innerHeight;

    if (x < padding) x = padding;
    if (rect.right + padding + menuWidth <= vw) {
      x = rect.right - menuWidth;
    }
    if (y + approxMenuHeight > vh) {
      y = rect.top - padding - approxMenuHeight; // tampil di atas jika kebawah tidak cukup
    }
    // Pastikan tidak keluar layar kiri/atas
    if (y < padding) y = padding;
    if (x + menuWidth > vw - padding) x = vw - padding - menuWidth;

    setMenuState({ open: true, x, y, userId });
  };

  const closeActionMenu = () => {
    setMenuState(prev => ({ ...prev, open: false }));
  };

  useEffect(() => {
    const fetchData = async () => {
      try {
        const token = localStorage.getItem("auth_token");
        const headers = { Authorization: `Bearer ${token}` };

        const [jabatanRes, departemenRes, tempatKerjaRes] = await Promise.all([
          axios.get(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/api/jabatan`, { headers }),
          axios.get(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/api/departemen`, { headers }),
          axios.get(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/api/tempat-kerja`, { headers })
        ]);

        setJabatanList(jabatanRes.data.data || []);
        // Handle DepartemenController response structure which returns { departemen: [...], users: [...] }
        const departemenData = departemenRes.data.data;
        setDepartemenList(Array.isArray(departemenData) ? departemenData : departemenData?.departemen || []);
        setTempatKerjaList(tempatKerjaRes.data.data || []);
      } catch (error) {
        console.error("Error fetching dropdown data:", error);
      }
    };

    if (isModalOpen) {
      fetchData();
    }
  }, [isModalOpen]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const token = localStorage.getItem("auth_token");
      await axios.post(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/api/users`, formData, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setIsModalOpen(false);
      // Refresh users
      const response = await axios.get(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/api/users`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      const fetchedData = response.data.data;
      setUsers(Array.isArray(fetchedData) ? fetchedData : fetchedData?.data || []);

      // Reset form
      setFormData({
        email: '',
        password: '',
        nama_lengkap: '',
        nik: '',
        nik_karyawan: '',
        tanggal_masuk: '',
        id_jabatan: '',
        id_departemen: '',
        id_tempat_kerja: '',
        status: '',
        role: ''
      });
      toast.success("Pegawai berhasil ditambahkan");
    } catch (error) {
      console.error("Error creating user:", error);
      toast.error("Gagal menambahkan pegawai");
    }
  };

  useEffect(() => {
    const fetchUsers = async () => {
      try {
        setIsLoading(true);
        const token = localStorage.getItem("auth_token");
        const response = await axios.get(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/api/users`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        const fetchedData = response.data.data;
        const allUsers = Array.isArray(fetchedData) ? fetchedData : fetchedData?.data || [];

        // Filter out superadmin dan kepala yayasan
        const filteredUsers = allUsers.filter((user: any) => {
          const firstRole = user.roles?.[0];
          const roleName = typeof firstRole === 'string' ? firstRole : firstRole?.name;
          const userRole = roleName?.toLowerCase()?.trim() || '';
          return userRole !== 'superadmin' && userRole !== 'kepala yayasan';
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

  // Load kecamatan data from JSON
  useEffect(() => {
    const loadKecamatan = async () => {
      try {
        const response = await fetch('/kecamatan-indonesia.json');
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

  const filteredUsers = users.filter(user => {
    // Search filter
    const searchLower = searchQuery.toLowerCase();
    const name = user.profile_pribadi?.nama_lengkap?.toLowerCase() || "";
    const email = user.email.toLowerCase();
    const jabatan = user.profile_pekerjaan?.jabatan?.nama_jabatan?.toLowerCase() || "";
    const matchesSearch = name.includes(searchLower) || email.includes(searchLower) || jabatan.includes(searchLower);

    // Kecamatan filter
    const kecamatan = user.profile_pribadi?.kecamatan || "";
    const matchesKecamatan = !selectedKecamatan || kecamatan === selectedKecamatan;

    // Golongan darah filter
    const golonganDarah = user.profile_pribadi?.golongan_darah?.trim().toUpperCase() || "";
    const matchesGolonganDarah = !selectedGolonganDarah || golonganDarah === selectedGolonganDarah.toUpperCase();

    // Age range filter
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
        // If no birth date, don't filter them out - they just won't match age filters
        matchesAgeRange = false;
      }
    }

    return matchesSearch && matchesKecamatan && matchesGolonganDarah && matchesAgeRange;
  });

  const totalPages = Math.ceil(filteredUsers.length / entriesPerPage);
  const startIndex = (currentPage - 1) * entriesPerPage;
  const paginatedUsers = filteredUsers.slice(startIndex, startIndex + entriesPerPage);

  const handleSelectAll = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.checked) {
      setSelectedUsers(paginatedUsers.map(u => u.id));
    } else {
      setSelectedUsers([]);
    }
  };

  const handleSelectUser = (id: number) => {
    setSelectedUsers(prev =>
      prev.includes(id) ? prev.filter(userId => userId !== id) : [...prev, id]
    );
  };

  const [deleteTarget, setDeleteTarget] = useState<{ type: 'single' | 'bulk'; id?: number } | null>(null);

  const confirmDelete = async () => {
    if (!deleteTarget) return;

    try {
      const token = localStorage.getItem("auth_token");

      if (deleteTarget.type === 'single' && deleteTarget.id) {
        await axios.delete(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/api/users/${deleteTarget.id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        toast.success("Pegawai berhasil dihapus");
      } else if (deleteTarget.type === 'bulk') {
        await Promise.all(selectedUsers.map(id =>
          axios.delete(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/api/users/${id}`, {
            headers: { Authorization: `Bearer ${token}` }
          })
        ));
        setSelectedUsers([]);
        toast.success("Pegawai terpilih berhasil dihapus");
      }

      // Refresh users
      const response = await axios.get(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/api/users`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      const fetchedData = response.data.data;
      setUsers(Array.isArray(fetchedData) ? fetchedData : fetchedData?.data || []);

    } catch (error) {
      console.error("Error deleting user(s):", error);
      toast.error("Gagal menghapus pegawai");
    } finally {
      setDeleteTarget(null);
    }
  };

  const handleDeleteUserClick = (id: number) => {
    setDeleteTarget({ type: 'single', id });
  };

  const handleDeleteSelectedClick = () => {
    setDeleteTarget({ type: 'bulk' });
  };

  return (
    <AccessControl allowedRoles={["kepala hrd", "staff hrd"]}>
      <div className="min-h-screen bg-gray-50 p-4 md:p-8 font-sans text-gray-800">
        <div className="max-w-7xl mx-auto space-y-6">

          {/* Header */}
          <div className="flex items-center gap-4">
            <button
              onClick={() => router.back()}
              className="p-2 hover:bg-gray-200 rounded-full transition-colors"
            >
              <ArrowLeft className="w-6 h-6 text-gray-700" />
            </button>
            <h1 className="text-2xl font-bold text-gray-800">Kelola Pegawai</h1>
          </div>

          {/* Main Content Card */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 className="text-lg font-bold text-gray-800 mb-6">Kelola Pegawai</h2>

            {/* Actions Bar */}
            <div className="flex flex-col md:flex-row justify-between gap-4 mb-6">
              <div className="flex gap-2">
                <button
                  onClick={() => setIsModalOpen(true)}
                  className="bg-sky-800 hover:bg-sky-700 text-white px-4 py-2 rounded-md flex items-center gap-2 text-sm font-medium transition-colors"
                >
                  <Plus className="w-4 h-4" />
                  Tambah Baru
                </button>
                {selectedUsers.length > 0 && (
                  <button
                    onClick={handleDeleteSelectedClick}
                    className="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md flex items-center gap-2 text-sm font-medium transition-colors"
                  >
                    <Trash2 className="w-4 h-4" />
                    Hapus Pilihan
                  </button>
                )}
              </div>

              <div className="flex gap-2">
                <button 
                  onClick={() => setShowFilter(!showFilter)}
                  className={`px-4 py-2 rounded-md flex items-center gap-2 text-sm font-medium transition-colors ${
                    showFilter 
                      ? 'bg-sky-800 text-white hover:bg-sky-700' 
                      : 'bg-sky-800 hover:bg-sky-700 text-white'
                  }`}
                >
                  Filter
                  <Filter className="w-4 h-4" />
                </button>
              </div>
            </div>

            {/* Filter Panel */}
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
                      {kecamatanList.map(kec => (
                        <option key={kec.id} value={kec.name}>{kec.name}</option>
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
                      {['A', 'B', 'AB', 'O'].map(gol => (
                        <option key={gol} value={gol}>{gol}</option>
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

            {/* Search and Pagination Controls */}
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

            {/* Table */}
            <div style={{ overflowX: 'auto', overflowY: 'visible' }}>
              <table className="w-full text-sm text-left">
                <thead className="bg-gray-50 text-gray-700 font-bold border-b border-gray-200">
                  <tr>
                    <th className="p-3 w-10">
                      <input
                        type="checkbox"
                        onChange={handleSelectAll}
                        checked={paginatedUsers.length > 0 && selectedUsers.length === paginatedUsers.length}
                        className="rounded border-gray-300 text-sky-800 focus:ring-sky-800"
                      />
                    </th>
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
                      <td colSpan={8} className="p-8 text-center text-gray-500">
                        Loading data...
                      </td>
                    </tr>
                  ) : paginatedUsers.length === 0 ? (
                    <tr>
                      <td colSpan={8} className="p-8 text-center text-gray-500">
                        Tidak ada data pegawai.
                      </td>
                    </tr>
                  ) : (
                    paginatedUsers.map((user) => (
                      <tr key={user.id} className="hover:bg-gray-50 transition-colors">
                        <td className="p-3">
                          <input
                            type="checkbox"
                            checked={selectedUsers.includes(user.id)}
                            onChange={() => handleSelectUser(user.id)}
                            className="rounded border-gray-300 text-sky-800 focus:ring-sky-800"
                          />
                        </td>
                        <td className="p-3 font-medium text-gray-800">
                          {user.profile_pribadi?.nama_lengkap || "Nama Belum Diisi"}
                        </td>
                        <td className="p-3 text-gray-600">
                          {user.profile_pekerjaan?.jabatan?.nama_jabatan || "-"}
                        </td>
                        <td className="p-3 text-gray-600">
                          {user.profile_pekerjaan?.tanggal_masuk || "-"}
                        </td>
                        <td className="p-3 text-gray-600">
                          {calculateTenure(user.profile_pekerjaan?.tanggal_masuk)}
                        </td>
                        <td className="p-3 text-gray-600">
                          {user.email}
                        </td>
                        <td className="p-3">
                          <span className={`px-2 py-1 rounded text-xs font-medium capitalize
                            ${user.profile_pekerjaan?.status_pegawai === 'aktif' ? 'bg-emerald-100 text-emerald-700' :
                              user.profile_pekerjaan?.status_pegawai === 'tetap' ? 'bg-green-100 text-green-700' :
                              user.profile_pekerjaan?.status_pegawai === 'kontrak' ? 'bg-blue-100 text-blue-700' :
                              user.profile_pekerjaan?.status_pegawai === 'honorer' ? 'bg-yellow-100 text-yellow-700' :
                              user.profile_pekerjaan?.status_pegawai === 'magang' ? 'bg-purple-100 text-purple-700' :
                              user.profile_pekerjaan?.status_pegawai === 'nonaktif' ? 'bg-red-100 text-red-700' :
                              user.profile_pekerjaan?.status_pegawai === 'pensiun' ? 'bg-gray-100 text-gray-700' :
                              user.profile_pekerjaan?.status_pegawai === 'cuti' ? 'bg-orange-100 text-orange-700' :
                              user.profile_pekerjaan?.status_pegawai === 'skorsing' ? 'bg-rose-100 text-rose-700' :
                              'bg-slate-100 text-slate-700'}`}>
                            {user.profile_pekerjaan?.status_pegawai || "Belum Set"}
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

            {/* Pagination Footer */}
            <div className="flex flex-col md:flex-row justify-between items-center gap-4 mt-4 text-sm text-gray-600">
              <div>
                Showing {startIndex + 1} to {Math.min(startIndex + entriesPerPage, filteredUsers.length)} of {filteredUsers.length} entries
              </div>
              <div className="flex items-center gap-1">
                <button
                  onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                  disabled={currentPage === 1}
                  className="p-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ChevronLeft className="w-4 h-4" />
                </button>
                {Array.from({ length: totalPages }, (_, i) => i + 1).map(page => (
                  <button
                    key={page}
                    onClick={() => setCurrentPage(page)}
                    className={`px-3 py-1 border rounded ${currentPage === page
                      ? 'bg-sky-800 text-white border-sky-800'
                      : 'border-gray-300 hover:bg-gray-50'
                      }`}
                  >
                    {page}
                  </button>
                ))}
                <button
                  onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                  disabled={currentPage === totalPages}
                  className="p-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ChevronRight className="w-4 h-4" />
                </button>
              </div>
            </div>

          </div>
        </div>

        {/* Add Employee Modal */}
        {isModalOpen && (
          <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
              <div className="flex justify-between items-center p-4 border-b sticky top-0 bg-white">
                <h3 className="text-lg font-semibold text-gray-800">Tambah Pegawai</h3>
                <button onClick={() => setIsModalOpen(false)} className="text-gray-500 hover:text-gray-700">
                  <X className="w-5 h-5" />
                </button>
              </div>

              <form onSubmit={handleSubmit} className="p-4 space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                  <input
                    type="email"
                    required
                    placeholder="Masukkan email"
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Password</label>
                  <input
                    type="password"
                    required
                    placeholder="Masukkan password"
                    value={formData.password}
                    onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                    className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                  <input
                    type="text"
                    required
                    placeholder="Masukkan nama lengkap"
                    value={formData.nama_lengkap}
                    onChange={(e) => setFormData({ ...formData, nama_lengkap: e.target.value })}
                    className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Nomor Induk Kependudukan</label>
                  <input
                    type="text"
                    required
                    placeholder="Masukkan NIK"
                    value={formData.nik}
                    onChange={(e) => setFormData({ ...formData, nik: e.target.value })}
                    className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Nomor Induk Karyawan</label>
                  <input
                    type="text"
                    required
                    placeholder="Masukkan NIK Karyawan"
                    value={formData.nik_karyawan}
                    onChange={(e) => setFormData({ ...formData, nik_karyawan: e.target.value })}
                    className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Tanggal Bergabung</label>
                  <input
                    type="date"
                    required
                    value={formData.tanggal_masuk}
                    onChange={(e) => setFormData({ ...formData, tanggal_masuk: e.target.value })}
                    className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                  <select
                    required
                    value={formData.id_jabatan}
                    onChange={(e) => setFormData({ ...formData, id_jabatan: e.target.value })}
                    className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800"
                  >
                    <option value="">Pilih Jabatan</option>
                    {jabatanList.map(j => (
                      <option key={j.id} value={j.id}>{j.nama_jabatan}</option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
                  <select
                    required
                    value={formData.id_departemen}
                    onChange={(e) => setFormData({ ...formData, id_departemen: e.target.value })}
                    className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800"
                  >
                    <option value="">Pilih Departemen</option>
                    {departemenList.map(d => (
                      <option key={d.id} value={d.id}>{d.nama_departemen}</option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Tempat Bekerja</label>
                  <select
                    required
                    value={formData.id_tempat_kerja}
                    onChange={(e) => setFormData({ ...formData, id_tempat_kerja: e.target.value })}
                    className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800"
                  >
                    <option value="">Pilih Tempat Kerja</option>
                    {tempatKerjaList.map(t => (
                      <option key={t.id} value={t.id}>{t.nama_tempat}</option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select
                    required
                    value={formData.status}
                    onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                    className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800"
                  >
                    <option value="">Pilih Status Karyawan</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                    <option value="tetap">Tetap</option>
                    <option value="kontrak">Kontrak</option>
                    <option value="magang">Magang</option>
                    <option value="honorer">Honorer</option>
                    <option value="pensiun">Pensiun</option>
                    <option value="cuti">Cuti</option>
                    <option value="skorsing">Skorsing</option>
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Role</label>
                  <select
                    required
                    value={formData.role}
                    onChange={(e) => setFormData({ ...formData, role: e.target.value })}
                    className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-sky-800"
                  >
                    <option value="">Pilih Role</option>
                    {rolesList.map(role => (
                      <option key={role} value={role}>{role}</option>
                    ))}
                  </select>
                </div>

                <div className="flex justify-end gap-2 pt-4 border-t mt-6">
                  <button
                    type="button"
                    onClick={() => setIsModalOpen(false)}
                    className="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors"
                  >
                    Close
                  </button>
                  <button
                    type="submit"
                    className="px-4 py-2 bg-sky-800 text-white rounded hover:bg-sky-700 transition-colors"
                  >
                    Simpan
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* Delete Confirmation Modal */}
        {deleteTarget && (
          <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg shadow-xl w-full max-w-sm p-6">
              <div className="flex flex-col items-center text-center">
                <div className="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-4">
                  <Trash2 className="w-6 h-6 text-red-600" />
                </div>
                <h3 className="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Hapus</h3>
                <p className="text-gray-500 mb-6">
                  {deleteTarget.type === 'bulk'
                    ? `Apakah Anda yakin ingin menghapus ${selectedUsers.length} pegawai terpilih?`
                    : "Apakah Anda yakin ingin menghapus pegawai ini?"}
                  <br />
                  Tindakan ini tidak dapat dibatalkan.
                </p>
                <div className="flex gap-3 w-full">
                  <button
                    onClick={() => setDeleteTarget(null)}
                    className="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                  >
                    Batal
                  </button>
                  <button
                    onClick={confirmDelete}
                    className="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                  >
                    Hapus
                  </button>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Render menu aksi via portal di luar layout tabel */}
      <ActionMenuPortal
        open={menuState.open && !!menuState.userId}
        x={menuState.x}
        y={menuState.y}
        userId={menuState.userId ?? 0}
        onClose={closeActionMenu}
      />
    </AccessControl>
  );
}
