"use client";

import AccessControl from "@/components/AccessControl";
import { useState, useEffect } from "react";
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
  ArrowLeft
} from "lucide-react";
import Link from "next/link";
import { User } from "@/types/auth";
import { useRouter } from "next/navigation";

export default function HrdKelolaPegawaiPage() {
  const router = useRouter();
  const [users, setUsers] = useState<User[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState("");
  const [entriesPerPage, setEntriesPerPage] = useState(10);
  const [currentPage, setCurrentPage] = useState(1);
  const [selectedUsers, setSelectedUsers] = useState<number[]>([]);
  const [activeDropdown, setActiveDropdown] = useState<number | null>(null);

  useEffect(() => {
    const fetchUsers = async () => {
      try {
        setIsLoading(true);
        const token = localStorage.getItem("auth_token");
        const response = await axios.get(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/api/users`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        console.log("API Response:", response.data);
        setUsers(response.data.data);
      } catch (error) {
        console.error("Error fetching users:", error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchUsers();
  }, []);

  // Close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (activeDropdown !== null && !(event.target as Element).closest('.action-dropdown')) {
        setActiveDropdown(null);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [activeDropdown]);

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

  const filteredUsers = users.filter(user => {
    const searchLower = searchQuery.toLowerCase();
    const name = user.profile_pribadi?.nama_lengkap?.toLowerCase() || "";
    const email = user.email.toLowerCase();
    const jabatan = user.profile_pekerjaan?.jabatan?.nama_jabatan?.toLowerCase() || "";
    
    return name.includes(searchLower) || email.includes(searchLower) || jabatan.includes(searchLower);
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

  const toggleDropdown = (id: number, e: React.MouseEvent) => {
    e.stopPropagation();
    setActiveDropdown(activeDropdown === id ? null : id);
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
                <button className="bg-sky-800 hover:bg-sky-900 text-white px-4 py-2 rounded-md flex items-center gap-2 text-sm font-medium transition-colors">
                  <Plus className="w-4 h-4" />
                  Tambah Baru
                </button>
                {selectedUsers.length > 0 && (
                  <button className="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md flex items-center gap-2 text-sm font-medium transition-colors">
                    <Trash2 className="w-4 h-4" />
                    Hapus Pilihan
                  </button>
                )}
              </div>
              
              <div className="flex gap-2">
                <button className="bg-sky-800 hover:bg-sky-900 text-white px-4 py-2 rounded-md flex items-center gap-2 text-sm font-medium transition-colors">
                  Filter
                  <Filter className="w-4 h-4" />
                </button>
              </div>
            </div>

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
            <div className="overflow-x-auto">
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
                            ${user.profile_pekerjaan?.status_pegawai === 'tetap' ? 'bg-green-100 text-green-700' : 
                              user.profile_pekerjaan?.status_pegawai === 'kontrak' ? 'bg-blue-100 text-blue-700' : 
                              user.profile_pekerjaan?.status_pegawai === 'honorer' ? 'bg-yellow-100 text-yellow-700' : 
                              'bg-gray-100 text-gray-700'}`}>
                            {user.profile_pekerjaan?.status_pegawai || "Belum Set"}
                          </span>
                        </td>
                        <td className="p-3 text-center relative action-dropdown">
                          <button 
                            onClick={(e) => toggleDropdown(user.id, e)}
                            className="p-1 hover:bg-gray-200 rounded transition-colors"
                          >
                            <MoreHorizontal className="w-5 h-5 text-gray-500" />
                          </button>
                          
                          {activeDropdown === user.id && (
                            <div className="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-100 py-1 text-left">
                              <Link 
                                href={`/hrd/profile/${user.id}`}
                                className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-sky-800 flex items-center gap-2"
                              >
                                <UserIcon className="w-4 h-4" />
                                Detail Profil
                              </Link>
                              <Link 
                                href={`/hrd/absensi/${user.id}`}
                                className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-sky-800 flex items-center gap-2"
                              >
                                <Calendar className="w-4 h-4" />
                                Rekap Absensi
                              </Link>
                              <Link 
                                href={`/hrd/cuti/${user.id}`}
                                className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-sky-800 flex items-center gap-2"
                              >
                                <FileText className="w-4 h-4" />
                                Rekap Cuti
                              </Link>
                              <Link 
                                href={`/hrd/evaluasi/${user.id}`}
                                className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-sky-800 flex items-center gap-2"
                              >
                                <ClipboardList className="w-4 h-4" />
                                Rekap Evaluasi
                              </Link>
                            </div>
                          )}
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
                    className={`px-3 py-1 border rounded ${
                      currentPage === page 
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
      </div>
    </AccessControl>
  );
}
