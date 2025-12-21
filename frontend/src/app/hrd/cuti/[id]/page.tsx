"use client";

import { useState, useEffect } from "react";
import { useParams, useRouter } from "next/navigation";
import axios from "axios";
import { ArrowLeft, Eye } from "lucide-react";
import AccessControl from "@/components/AccessControl";
import { apiClient } from "@/lib/api";

interface LeaveRequest {
  id: number;
  id_user?: number;
  tanggal_mulai: string;
  tanggal_selesai: string;
  tipe_cuti: string;
  status_pengajuan: string;
  alasan_pendukung: string;
  file_pendukung?: string;
  komentar?: string;
  created_at: string;
  user?: {
    id: number;
    email: string;
    profile_pribadi?: {
      nama_lengkap?: string;
      foto_profil?: string;
    };
  };
}

interface UserProfile {
  id: number;
  email: string;
  profile_pribadi?: {
    nama_lengkap?: string;
    foto_profil?: string;
  };
}

interface LeaveSummary {
  total_pengajuan: number;
  disetujui: number;
  ditolak: number;
  menunggu: number;
  total_hari_disetujui: number;
}

export default function HrdRekapCutiPage() {
  const params = useParams();
  const router = useRouter();
  const userId = params?.id as string;

  const [leaveData, setLeaveData] = useState<LeaveRequest[]>([]);
  const [userProfile, setUserProfile] = useState<UserProfile | null>(null);
  const [leaveSummary, setLeaveSummary] = useState<LeaveSummary>({
    total_pengajuan: 0,
    disetujui: 0,
    ditolak: 0,
    menunggu: 0,
    total_hari_disetujui: 0,
  });
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(10);
  const [startDate, setStartDate] = useState("");
  const [endDate, setEndDate] = useState("");
  const [showFilePreview, setShowFilePreview] = useState(false);
  const [previewFileUrl, setPreviewFileUrl] = useState<string | null>(null);
  const [previewFileType, setPreviewFileType] = useState<
    "image" | "pdf" | "other" | null
  >(null);
  const [imageLoadError, setImageLoadError] = useState(false);

  // Fetch user profile and leave data
  useEffect(() => {
    const fetchData = async () => {
      if (!userId) return;

      try {
        setIsLoading(true);
        const token = localStorage.getItem("auth_token");
        const headers = { Authorization: `Bearer ${token}` };
        const baseUrl =
          process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

        // Fetch user profile (sama seperti halaman rekap absensi)
        const userResponse = await axios.get(`${baseUrl}/api/users/${userId}`, {
          headers,
        });
        setUserProfile(userResponse.data.data);

        // Fetch semua pengajuan cuti lalu filter berdasarkan userId
        const leaveResponse = await apiClient.leave.getAll();
        if (leaveResponse.success) {
          const allLeaves = (leaveResponse.data as LeaveRequest[]) || [];
          const records = allLeaves.filter(
            (request) =>
              request.id_user === Number(userId) ||
              request.user?.id === Number(userId)
          );

          setLeaveData(records);

          // Hitung ringkasan cuti
          const summary: LeaveSummary = {
            total_pengajuan: records.length,
            disetujui: 0,
            ditolak: 0,
            menunggu: 0,
            total_hari_disetujui: 0,
          };

          const calculateDays = (startDate: string, endDate: string) => {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end.getTime() - start.getTime());
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            return isNaN(diffDays) ? 0 : diffDays;
          };

          records.forEach((record) => {
            const statusLower = (record.status_pengajuan || "").toLowerCase();
            if (
              statusLower.includes("disetujui") &&
              !statusLower.includes("menunggu")
            ) {
              summary.disetujui += 1;
              summary.total_hari_disetujui += calculateDays(
                record.tanggal_mulai,
                record.tanggal_selesai
              );
            } else if (statusLower.includes("ditolak")) {
              summary.ditolak += 1;
            } else {
              summary.menunggu += 1;
            }
          });

          setLeaveSummary(summary);
        } else {
          console.error("Gagal memuat data cuti:", leaveResponse.message);
        }
      } catch (error) {
        console.error("Error fetching leave recap:", error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();
  }, [userId]);

  // Filter data berdasarkan pencarian dan date range
  const filteredData = leaveData.filter((record) => {
    // Text search filter
    const tipe = (record.tipe_cuti || "").toLowerCase();
    const alasan = (record.alasan_pendukung || "").toLowerCase();
    const status = (record.status_pengajuan || "").toLowerCase();
    const tanggalMulai = (record.tanggal_mulai || "").toLowerCase();
    const tanggalSelesai = (record.tanggal_selesai || "").toLowerCase();
    const q = searchTerm.toLowerCase();
    const matchesSearch =
      tipe.includes(q) ||
      alasan.includes(q) ||
      status.includes(q) ||
      tanggalMulai.includes(q) ||
      tanggalSelesai.includes(q);

    // Date range filter - check if leave period overlaps with filter range
    let matchesDateRange = true;
    if (startDate || endDate) {
      const leaveStart = new Date(record.tanggal_mulai);
      const leaveEnd = new Date(record.tanggal_selesai);
      
      if (startDate && endDate) {
        const filterStart = new Date(startDate);
        const filterEnd = new Date(endDate);
        // Check for overlap: leave period overlaps with filter range
        matchesDateRange = leaveStart <= filterEnd && leaveEnd >= filterStart;
      } else if (startDate) {
        const filterStart = new Date(startDate);
        // Leave ends on or after filter start
        matchesDateRange = leaveEnd >= filterStart;
      } else if (endDate) {
        const filterEnd = new Date(endDate);
        // Leave starts on or before filter end
        matchesDateRange = leaveStart <= filterEnd;
      }
    }

    return matchesSearch && matchesDateRange;
  });

  // Pagination
  const totalPages = Math.ceil(filteredData.length / itemsPerPage) || 1;
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const currentData = filteredData.slice(startIndex, endIndex);

  const formatDate = (dateString: string) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  };

  const calculateDays = (startDate: string, endDate: string) => {
    if (!startDate || !endDate) return 0;
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end.getTime() - start.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    return isNaN(diffDays) ? 0 : diffDays;
  };

  const getStatusColor = (status: string) => {
    const statusLower = (status || "").toLowerCase();
    if (
      statusLower.includes("disetujui") &&
      !statusLower.includes("menunggu")
    ) {
      return "bg-green-100 text-green-800";
    }
    if (statusLower.includes("ditolak")) {
      return "bg-red-100 text-red-800";
    }
    if (statusLower.includes("menunggu") || statusLower.includes("ditinjau")) {
      return "bg-yellow-100 text-yellow-800";
    }
    return "bg-gray-100 text-gray-800";
  };

  const getStatusText = (status: string) => {
    const statusLower = (status || "").toLowerCase();
    const statusMap: { [key: string]: string } = {
      "ditinjau kepala sekolah": "Ditinjau Kepala Sekolah",
      "disetujui kepala sekolah": "Disetujui Kepala Sekolah",
      "disetujui kepala sekolah menunggu tinjauan dirpen":
        "Disetujui Kepala Sekolah (Menunggu Dirpen)",
      "ditolak kepala sekolah": "Ditolak Kepala Sekolah",
      "ditinjau hrd": "Ditinjau Staff HRD",
      "disetujui hrd": "Disetujui Staff HRD",
      "disetujui hrd menunggu tinjauan dirpen":
        "Disetujui Staff HRD (Menunggu Dirpen)",
      "ditolak hrd": "Ditolak Staff HRD",
      "ditinjau kepala hrd": "Ditinjau Kepala HRD",
      "disetujui kepala hrd": "Disetujui Kepala HRD",
      "disetujui kepala hrd menunggu tinjauan dirpen":
        "Disetujui Kepala HRD (Menunggu Dirpen)",
      "ditolak kepala hrd": "Ditolak Kepala HRD",
      "ditinjau dirpen": "Ditinjau Direktur Pendidikan",
      "disetujui dirpen": "Disetujui Direktur Pendidikan",
      "ditolak dirpen": "Ditolak Direktur Pendidikan",
    };

    if (statusMap[statusLower]) {
      return statusMap[statusLower];
    }
    return status;
  };

  const handleOpenFilePreview = (fileUrl: string) => {
    if (!fileUrl) return;
    const clean = fileUrl.split("?")[0].toLowerCase();
    const extension = clean.split(".").pop() || "";

    let fileType: "image" | "pdf" | "other" = "other";
    if (["jpg", "jpeg", "png", "gif", "webp", "bmp"].includes(extension)) {
      fileType = "image";
    } else if (extension === "pdf") {
      fileType = "pdf";
    }

    // Jika URL belum absolute, gabungkan dengan base URL
    let fullUrl = fileUrl;
    if (!fileUrl.startsWith("http")) {
      const baseUrl =
        process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";
      const path = fileUrl.startsWith("/") ? fileUrl : `/storage/${fileUrl}`;
      fullUrl = `${baseUrl}${path}`;
    }

    setPreviewFileType(fileType);
    setPreviewFileUrl(fullUrl);
    setImageLoadError(false);
    setShowFilePreview(true);
  };

  const handleCloseFilePreview = () => {
    setShowFilePreview(false);
    setPreviewFileUrl(null);
    setPreviewFileType(null);
    setImageLoadError(false);
  };

  const maxValue = Math.max(
    leaveSummary.total_pengajuan,
    leaveSummary.disetujui,
    leaveSummary.ditolak,
    leaveSummary.menunggu,
    1
  );

  return (
    <AccessControl allowedRoles={["kepala hrd", "staff hrd"]}>
      <div className="min-h-screen bg-gray-50 p-4 md:p-8 overflow-x-hidden">
        <div className="max-w-7xl mx-auto space-y-6">
          {/* Header */}
          <div className="flex items-center gap-4">
            <button
              onClick={() => router.back()}
              className="p-2 hover:bg-gray-200 rounded-full transition-colors"
            >
              <ArrowLeft className="w-6 h-6 text-gray-700" />
            </button>
            <h1 className="text-2xl font-bold text-gray-800">Rekap Cuti</h1>
          </div>

          {/* Main content grid */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Left section - Profile & Table */}
            <div className="lg:col-span-2 space-y-6">
              {/* Profile Card */}
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                {isLoading ? (
                  <div className="flex items-center gap-4">
                    <div className="w-24 h-24 bg-gray-200 rounded-full animate-pulse"></div>
                    <div className="space-y-2">
                      <div className="h-6 w-48 bg-gray-200 rounded animate-pulse"></div>
                      <div className="h-4 w-32 bg-gray-200 rounded animate-pulse"></div>
                    </div>
                  </div>
                ) : userProfile ? (
                  <div className="flex items-center gap-4">
                    <div className="relative">
                      {userProfile.profile_pribadi?.foto_profil ? (
                        <img
                          src={
                            userProfile.profile_pribadi.foto_profil.startsWith(
                              "http"
                            )
                              ? userProfile.profile_pribadi.foto_profil
                              : `${
                                  process.env.NEXT_PUBLIC_API_URL ||
                                  "http://localhost:8000"
                                }/${userProfile.profile_pribadi.foto_profil}`
                          }
                          alt="Profile"
                          className="w-24 h-24 rounded-full object-cover"
                        />
                      ) : (
                        <div className="w-24 h-24 rounded-full bg-sky-100 flex items-center justify-center">
                          <span className="text-3xl font-bold text-sky-800">
                            {userProfile.profile_pribadi?.nama_lengkap
                              ?.charAt(0)
                              .toUpperCase() ||
                              userProfile.email.charAt(0).toUpperCase()}
                          </span>
                        </div>
                      )}
                    </div>
                    <div className="min-w-0 overflow-hidden">
                      <h2 className="text-2xl font-bold text-gray-800">
                        {userProfile.profile_pribadi?.nama_lengkap ||
                          "Nama Belum Diisi"}
                      </h2>
                      <p className="text-gray-600 truncate">{userProfile.email}</p>
                    </div>
                  </div>
                ) : (
                  <p className="text-gray-500">User tidak ditemukan</p>
                )}
              </div>

              {/* Leave Table */}
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 className="text-lg font-bold text-gray-800 mb-4">
                  Riwayat Cuti
                </h2>

                {/* Search and Filter */}
                <div className="space-y-4 mb-4">
                  {/* Search Bar */}
                  <div className="flex flex-col md:flex-row gap-4">
                    <div className="flex-1 text-gray-700">
                      <input
                        type="text"
                        placeholder="Cari berdasarkan tipe cuti, status atau alasan..."
                        value={searchTerm}
                        onChange={(e) => {
                          setSearchTerm(e.target.value);
                          setCurrentPage(1);
                        }}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800"
                      />
                    </div>
                    <div className="flex items-center gap-2">
                      <label className="text-sm text-gray-600">Show:</label>
                      <select
                        value={itemsPerPage}
                        onChange={(e) => {
                          setItemsPerPage(Number(e.target.value));
                          setCurrentPage(1);
                        }}
                        className="px-3 py-2 text-gray-700 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800"
                      >
                        <option value={10}>10</option>
                        <option value={25}>25</option>
                        <option value={50}>50</option>
                        <option value={100}>100</option>
                      </select>
                    </div>
                  </div>

                  {/* Date Range Filter */}
                  <div className="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                    <div className="flex-1">
                      <label className="block text-sm font-medium text-gray-700 mb-1">Periode Cuti Mulai</label>
                      <input
                        type="date"
                        value={startDate}
                        onChange={(e) => setStartDate(e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800 text-gray-700"
                      />
                    </div>
                    <div className="flex-1">
                      <label className="block text-sm font-medium text-gray-700 mb-1">Periode Cuti Selesai</label>
                      <input
                        type="date"
                        value={endDate}
                        onChange={(e) => setEndDate(e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800 text-gray-700"
                      />
                    </div>
                    <div className="flex gap-2">
                      <button
                        onClick={() => setCurrentPage(1)}
                        className="px-4 py-2 bg-sky-800 text-white rounded-lg hover:bg-sky-900 transition-colors font-medium"
                      >
                        Filter
                      </button>
                      <button
                        onClick={() => {
                          setStartDate("");
                          setEndDate("");
                          setSearchTerm("");
                          setCurrentPage(1);
                        }}
                        className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                      >
                        Reset
                      </button>
                    </div>
                  </div>
                </div>

                {/* Data Table */}
                <div className="overflow-x-auto">
                  <table className="w-full text-sm">
                    <thead>
                      <tr className="bg-gray-50 border-b">
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">
                          Tgl Pengajuan
                        </th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">
                          Periode Cuti
                        </th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">
                          Durasi
                        </th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">
                          Tipe Cuti
                        </th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">
                          Status
                        </th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">
                          Alasan
                        </th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">
                          File
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      {isLoading ? (
                        <tr>
                          <td
                            colSpan={7}
                            className="text-center py-8 text-gray-500"
                          >
                            <div className="flex items-center justify-center">
                              <div className="w-6 h-6 border-2 border-sky-800 border-t-transparent rounded-full animate-spin mr-2"></div>
                              Loading...
                            </div>
                          </td>
                        </tr>
                      ) : currentData.length === 0 ? (
                        <tr>
                          <td
                            colSpan={7}
                            className="text-center py-8 text-gray-500"
                          >
                            Tidak ada data cuti
                          </td>
                        </tr>
                      ) : (
                        currentData.map((record) => (
                          <tr
                            key={record.id}
                            className="border-b hover:bg-gray-50"
                          >
                            <td className="py-3 px-4 text-gray-700">
                              {formatDate(record.created_at)}
                            </td>
                            <td className="py-3 px-4 text-gray-700">
                              {formatDate(record.tanggal_mulai)} -{" "}
                              {formatDate(record.tanggal_selesai)}
                            </td>
                            <td className="py-3 px-4 text-gray-700">
                              {calculateDays(
                                record.tanggal_mulai,
                                record.tanggal_selesai
                              )}{" "}
                              hari
                            </td>
                            <td className="py-3 px-4 text-gray-700 capitalize">
                              {record.tipe_cuti}
                            </td>
                            <td className="py-3 px-4">
                              <span
                                className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(
                                  record.status_pengajuan
                                )}`}
                              >
                                {getStatusText(record.status_pengajuan)}
                              </span>
                            </td>
                            <td className="py-3 px-4 text-gray-700">
                              {record.alasan_pendukung || "-"}
                            </td>
                            <td className="py-3 px-4">
                              {record.file_pendukung ? (
                                <button
                                  onClick={() =>
                                    handleOpenFilePreview(
                                      record.file_pendukung || ""
                                    )
                                  }
                                  className="text-sky-800 hover:text-sky-900 transition-colors"
                                >
                                  <Eye className="w-5 h-5" />
                                </button>
                              ) : (
                                "-"
                              )}
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>

                {/* Pagination */}
                {totalPages > 1 && (
                  <div className="flex items-center justify-between mt-4">
                    <div className="text-sm text-gray-600">
                      Showing {filteredData.length === 0 ? 0 : startIndex + 1}{" "}
                      to {Math.min(endIndex, filteredData.length)} of{" "}
                      {filteredData.length} entries
                    </div>
                    <div className="flex items-center gap-2">
                      <button
                        onClick={() =>
                          setCurrentPage((prev) => Math.max(prev - 1, 1))
                        }
                        disabled={currentPage === 1}
                        className="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                      >
                        Previous
                      </button>
                      <span className="px-3 py-1 bg-sky-800 text-white rounded text-sm">
                        {currentPage} of {totalPages}
                      </span>
                      <button
                        onClick={() =>
                          setCurrentPage((prev) =>
                            Math.min(prev + 1, totalPages)
                          )
                        }
                        disabled={currentPage === totalPages}
                        className="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                      >
                        Next
                      </button>
                    </div>
                  </div>
                )}
              </div>
            </div>

            {/* Right section - Leave Summary Chart */}
            <div className="lg:col-span-1">
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-8">
                <h3 className="text-lg font-bold text-gray-800 mb-6">
                  Rekap Cuti
                </h3>

                <div className="space-y-4">
                  {/* Total Pengajuan */}
                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-sm font-medium text-gray-700">
                        Total Pengajuan
                      </span>
                      <span className="text-sm font-bold text-gray-800">
                        {leaveSummary.total_pengajuan}
                      </span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-8">
                      <div
                        className="bg-sky-800 h-8 rounded-full transition-all duration-500"
                        style={{
                          width: `${
                            (leaveSummary.total_pengajuan / maxValue) * 100
                          }%`,
                        }}
                      ></div>
                    </div>
                  </div>

                  {/* Disetujui */}
                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-sm font-medium text-gray-700">
                        Disetujui
                      </span>
                      <span className="text-sm font-bold text-gray-800">
                        {leaveSummary.disetujui}
                      </span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-8">
                      <div
                        className="bg-sky-700 h-8 rounded-full transition-all duration-500"
                        style={{
                          width: `${
                            (leaveSummary.disetujui / maxValue) * 100
                          }%`,
                        }}
                      ></div>
                    </div>
                  </div>

                  {/* Ditolak */}
                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-sm font-medium text-gray-700">
                        Ditolak
                      </span>
                      <span className="text-sm font-bold text-gray-800">
                        {leaveSummary.ditolak}
                      </span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-8">
                      <div
                        className="bg-sky-600 h-8 rounded-full transition-all duration-500"
                        style={{
                          width: `${(leaveSummary.ditolak / maxValue) * 100}%`,
                        }}
                      ></div>
                    </div>
                  </div>

                  {/* Menunggu */}
                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-sm font-medium text-gray-700">
                        Menunggu Proses
                      </span>
                      <span className="text-sm font-bold text-gray-800">
                        {leaveSummary.menunggu}
                      </span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-8">
                      <div
                        className="bg-sky-400 h-8 rounded-full transition-all duration-500"
                        style={{
                          width: `${(leaveSummary.menunggu / maxValue) * 100}%`,
                        }}
                      ></div>
                    </div>
                  </div>

                  {/* Total Hari Disetujui */}
                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-sm font-medium text-gray-700">
                        Total Hari Cuti Disetujui
                      </span>
                      <span className="text-sm font-bold text-gray-800">
                        {leaveSummary.total_hari_disetujui} hari
                      </span>
                    </div>
                  </div>
                </div>

                {/* Legend */}
                <div className="mt-6 pt-4 border-t">
                  <p className="text-xs text-gray-500 italic">
                    * Data di atas merupakan rekap pengajuan cuti pegawai
                    berdasarkan status persetujuan.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* File Preview Modal */}
        {showFilePreview && previewFileUrl && (
          <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
              {/* Modal Header */}
              <div className="flex justify-between items-center px-6 py-4 border-b border-gray-200">
                <h3 className="text-lg font-semibold text-gray-800">
                  Preview File
                </h3>
                <button
                  onClick={handleCloseFilePreview}
                  className="text-gray-400 hover:text-gray-600 transition"
                >
                  <svg
                    className="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </button>
              </div>

              {/* Modal Body - File Preview */}
              <div className="flex-1 overflow-auto p-6 flex items-center justify-center">
                {previewFileType === "image" ? (
                  imageLoadError ? (
                    <div className="text-center">
                      <div className="mb-4">
                        <svg
                          className="w-16 h-16 text-gray-400 mx-auto"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                          />
                        </svg>
                      </div>
                      <p className="text-gray-600 mb-4">
                        Gagal memuat gambar. Silakan coba buka di tab baru.
                      </p>
                      <a
                        href={previewFileUrl || "#"}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-block bg-sky-800 text-white px-6 py-2 rounded-lg font-medium hover:bg-sky-900 transition-colors"
                      >
                        Buka di Tab Baru
                      </a>
                    </div>
                  ) : (
                    <img
                      src={previewFileUrl || ""}
                      alt="File Preview"
                      className="max-w-full max-h-full object-contain rounded-lg"
                      onError={() => setImageLoadError(true)}
                    />
                  )
                ) : previewFileType === "pdf" ? (
                  <iframe
                    src={previewFileUrl || ""}
                    className="w-full h-full min-h-[500px] border border-gray-300 rounded-lg"
                    title="PDF Preview"
                  />
                ) : (
                  <div className="text-center">
                    <div className="mb-4">
                      <svg
                        className="w-16 h-16 text-gray-400 mx-auto"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                        />
                      </svg>
                    </div>
                    <p className="text-gray-600 mb-4">
                      Preview tidak tersedia untuk tipe file ini
                    </p>
                    <a
                      href={previewFileUrl || "#"}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-block bg-sky-800 text-white px-6 py-2 rounded-lg font-medium hover:bg-sky-900 transition-colors"
                    >
                      Buka di Tab Baru
                    </a>
                  </div>
                )}
              </div>

              {/* Modal Footer */}
              <div className="px-6 py-4 border-t border-gray-200 flex justify-end">
                <a
                  href={previewFileUrl || "#"}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="bg-sky-800 text-white px-6 py-2 rounded-lg font-medium hover:bg-sky-900 transition-colors"
                >
                  Buka di Tab Baru
                </a>
              </div>
            </div>
          </div>
        )}
      </div>
    </AccessControl>
  );
}
