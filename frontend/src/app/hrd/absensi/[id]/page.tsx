"use client";

import { useState, useEffect } from "react";
import { useParams, useRouter } from "next/navigation";
import axios from "axios";
import { ArrowLeft, Eye } from "lucide-react";
import AccessControl from "@/components/AccessControl";

interface AttendanceRecord {
  id: number;
  tanggal: string;
  check_in: string | null;
  check_out: string | null;
  status: string;
  keterangan: string | null;
  file_pendukung: string | null;
  latitude_in: number | null;
  longitude_in: number | null;
  latitude_out: number | null;
  longitude_out: number | null;
  created_at: string;
  updated_at: string;
}

interface UserProfile {
  id: number;
  email: string;
  profile_pribadi?: {
    nama_lengkap?: string;
    foto_profil?: string;
  };
}

interface AttendanceSummary {
  hadir: number;
  terlambat: number;
  sakit: number;
  cuti: number;
  alpha: number;
}

export default function HrdRekapAbsensiPage() {
  const params = useParams();
  const router = useRouter();
  const userId = params?.id as string;

  const [attendanceData, setAttendanceData] = useState<AttendanceRecord[]>([]);
  const [userProfile, setUserProfile] = useState<UserProfile | null>(null);
  const [attendanceSummary, setAttendanceSummary] = useState<AttendanceSummary>({
    hadir: 0,
    terlambat: 0,
    sakit: 0,
    cuti: 0,
    alpha: 0,
  });
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(10);
  const [showFilePreview, setShowFilePreview] = useState(false);
  const [previewFileUrl, setPreviewFileUrl] = useState<string | null>(null);
  const [previewFileType, setPreviewFileType] = useState<"image" | "pdf" | "other" | null>(null);
  const [imageLoadError, setImageLoadError] = useState(false);

  // Fetch user profile and attendance data
  useEffect(() => {
    const fetchData = async () => {
      if (!userId) return;
      
      try {
        setIsLoading(true);
        const token = localStorage.getItem("auth_token");
        const headers = { Authorization: `Bearer ${token}` };
        const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';

        // Fetch user profile
        const userResponse = await axios.get(`${baseUrl}/api/users/${userId}`, { headers });
        setUserProfile(userResponse.data.data);

        // Fetch attendance data using the web route from AbsensiController
        // The backend has a showRekapPegawaiPage method that returns blade view,
        // but we can directly query the Absensi model via API
        const attendanceResponse = await axios.get(
          `${baseUrl}/api/attendance/user/${userId}`, 
          { headers }
        );
        
        const records = attendanceResponse.data.data || [];
        setAttendanceData(records);

        // Calculate attendance summary
        const summary: AttendanceSummary = {
          hadir: 0,
          terlambat: 0,
          sakit: 0,
          cuti: 0,
          alpha: 0,
        };

        records.forEach((record: AttendanceRecord) => {
          const status = (record.status || "").toLowerCase();
          if (status === "hadir") summary.hadir++;
          else if (status === "terlambat") summary.terlambat++;
          else if (status === "sakit") summary.sakit++;
          else if (status === "cuti") summary.cuti++;
          else if (status === "tidak hadir" || status === "alpha" || status === "alpa") summary.alpha++;
        });

        setAttendanceSummary(summary);
      } catch (error: any) {
        console.error("Error fetching data:", error);
        
        // If the API endpoint doesn't exist, try fetching all attendance and filter client-side
        if (error.response?.status === 404) {
          try {
            const token = localStorage.getItem("auth_token");
            const headers = { Authorization: `Bearer ${token}` };
            const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';
            
            // Fallback: Get all attendance records and filter by user_id
            const allAttendanceResponse = await axios.get(`${baseUrl}/api/attendance/all`, { headers });
            const allRecords = allAttendanceResponse.data.data || [];
            const records = allRecords.filter((record: any) => record.id_user === parseInt(userId));
            
            setAttendanceData(records);

            // Calculate summary
            const summary: AttendanceSummary = {
              hadir: 0,
              terlambat: 0,
              sakit: 0,
              cuti: 0,
              alpha: 0,
            };

            records.forEach((record: AttendanceRecord) => {
              const status = (record.status || "").toLowerCase();
              if (status === "hadir") summary.hadir++;
              else if (status === "terlambat") summary.terlambat++;
              else if (status === "sakit") summary.sakit++;
              else if (status === "cuti") summary.cuti++;
              else if (status === "tidak hadir" || status === "alpha" || status === "alpa") summary.alpha++;
            });

            setAttendanceSummary(summary);
          } catch (fallbackError) {
            console.error("Fallback also failed:", fallbackError);
          }
        }
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();
  }, [userId]);

  // Filter data based on search term
  const filteredData = attendanceData.filter((record) => {
    const tanggal = (record.tanggal || "").toLowerCase();
    const status = (record.status || "").toLowerCase();
    const ket = (record.keterangan || "").toLowerCase();
    const q = searchTerm.toLowerCase();
    return tanggal.includes(q) || status.includes(q) || ket.includes(q);
  });

  // Pagination
  const totalPages = Math.ceil(filteredData.length / itemsPerPage) || 1;
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const currentData = filteredData.slice(startIndex, endIndex);

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  };

  const formatTime = (timeString: string | null) => {
    if (!timeString) return "-";
    const time = new Date(timeString);
    if (isNaN(time.getTime())) return "-";
    return time.toLocaleTimeString("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  const getStatusColor = (status: string) => {
    switch ((status || "").toLowerCase()) {
      case "hadir":
        return "bg-green-100 text-green-800";
      case "terlambat":
        return "bg-blue-100 text-blue-800";
      case "tidak hadir":
      case "alpha":
        return "bg-red-100 text-red-800";
      case "sakit":
        return "bg-yellow-100 text-yellow-800";
      case "cuti":
        return "bg-purple-100 text-purple-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
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

    // Construct full URL if it's not already absolute
    let fullUrl = fileUrl;
    if (!fileUrl.startsWith("http")) {
      const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';
      const path = fileUrl.startsWith("/") ? fileUrl : `/${fileUrl}`;
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

  // Calculate max value for chart scaling
  const maxValue = Math.max(
    attendanceSummary.hadir,
    attendanceSummary.terlambat,
    attendanceSummary.sakit,
    attendanceSummary.cuti,
    attendanceSummary.alpha,
    1 // Minimum 1 to avoid division by zero
  );

  return (
    <AccessControl allowedRoles={["kepala hrd", "staff hrd"]}>
      <div className="min-h-screen bg-gray-50 p-4 md:p-8">
        <div className="max-w-7xl mx-auto space-y-6">
          {/* Header */}
          <div className="flex items-center gap-4">
            <button
              onClick={() => router.back()}
              className="p-2 hover:bg-gray-200 rounded-full transition-colors"
            >
              <ArrowLeft className="w-6 h-6 text-gray-700" />
            </button>
            <h1 className="text-2xl font-bold text-gray-800">Rekap Absensi</h1>
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
                            userProfile.profile_pribadi.foto_profil.startsWith("http")
                              ? userProfile.profile_pribadi.foto_profil
                              : `${process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000"}/${userProfile.profile_pribadi.foto_profil}`
                          }
                          alt="Profile"
                          className="w-24 h-24 rounded-full object-cover"
                        />
                      ) : (
                        <div className="w-24 h-24 rounded-full bg-sky-100 flex items-center justify-center">
                          <span className="text-3xl font-bold text-sky-800">
                            {userProfile.profile_pribadi?.nama_lengkap?.charAt(0).toUpperCase() || userProfile.email.charAt(0).toUpperCase()}
                          </span>
                        </div>
                      )}
                    </div>
                    <div>
                      <h2 className="text-2xl font-bold text-gray-800">
                        {userProfile.profile_pribadi?.nama_lengkap || "Nama Belum Diisi"}
                      </h2>
                      <p className="text-gray-600">{userProfile.email}</p>
                    </div>
                  </div>
                ) : (
                  <p className="text-gray-500">User tidak ditemukan</p>
                )}
              </div>

              {/* Attendance Table */}
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 className="text-lg font-bold text-gray-800 mb-4">Riwayat Absensi</h2>

                {/* Search and Filter */}
                <div className="flex flex-col md:flex-row gap-4 mb-4">
                  <div className="flex-1 text-gray-700">
                    <input
                      type="text"
                      placeholder="Cari berdasarkan tanggal, status, atau keterangan..."
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

                {/* Data Table */}
                <div className="overflow-x-auto">
                  <table className="w-full text-sm">
                    <thead>
                      <tr className="bg-gray-50 border-b">
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Tanggal</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Check In</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Check Out</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Presensi</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Keterangan</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">File</th>
                      </tr>
                    </thead>
                    <tbody>
                      {isLoading ? (
                        <tr>
                          <td colSpan={6} className="text-center py-8 text-gray-500">
                            <div className="flex items-center justify-center">
                              <div className="w-6 h-6 border-2 border-sky-800 border-t-transparent rounded-full animate-spin mr-2"></div>
                              Loading...
                            </div>
                          </td>
                        </tr>
                      ) : currentData.length === 0 ? (
                        <tr>
                          <td colSpan={6} className="text-center py-8 text-gray-500">
                            Tidak ada data absensi
                          </td>
                        </tr>
                      ) : (
                        currentData.map((record) => (
                          <tr key={record.id} className="border-b hover:bg-gray-50">
                            <td className="py-3 px-4 text-gray-700">{formatDate(record.tanggal)}</td>
                            <td className="py-3 px-4 text-gray-700">{formatTime(record.check_in)}</td>
                            <td className="py-3 px-4 text-gray-700">{formatTime(record.check_out)}</td>
                            <td className="py-3 px-4">
                              <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(record.status)}`}>
                                {record.status}
                              </span>
                            </td>
                            <td className="py-3 px-4 text-gray-700">{record.keterangan || "-"}</td>
                            <td className="py-3 px-4">
                              {record.file_pendukung ? (
                                <button
                                  onClick={() => handleOpenFilePreview(record.file_pendukung!)}
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
                      Showing {startIndex + 1} to {Math.min(endIndex, filteredData.length)} of {filteredData.length} entries
                    </div>
                    <div className="flex items-center gap-2">
                      <button
                        onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
                        disabled={currentPage === 1}
                        className="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                      >
                        Previous
                      </button>
                      <span className="px-3 py-1 bg-sky-800 text-white rounded text-sm">
                        {currentPage} of {totalPages}
                      </span>
                      <button
                        onClick={() => setCurrentPage((prev) => Math.min(prev + 1, totalPages))}
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

            {/* Right section - Attendance Summary Chart */}
            <div className="lg:col-span-1">
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-8">
                <h3 className="text-lg font-bold text-gray-800 mb-6">Kehadiran</h3>
                
                <div className="space-y-4">
                  {/* Hadir */}
                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-sm font-medium text-gray-700">Hadir</span>
                      <span className="text-sm font-bold text-gray-800">{attendanceSummary.hadir}</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-8">
                      <div
                        className="bg-sky-800 h-8 rounded-full transition-all duration-500"
                        style={{ width: `${(attendanceSummary.hadir / maxValue) * 100}%` }}
                      ></div>
                    </div>
                  </div>

                  {/* Terlambat */}
                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-sm font-medium text-gray-700">Terlambat</span>
                      <span className="text-sm font-bold text-gray-800">{attendanceSummary.terlambat}</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-8">
                      <div
                        className="bg-sky-700 h-8 rounded-full transition-all duration-500"
                        style={{ width: `${(attendanceSummary.terlambat / maxValue) * 100}%` }}
                      ></div>
                    </div>
                  </div>

                  {/* Sakit */}
                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-sm font-medium text-gray-700">Sakit</span>
                      <span className="text-sm font-bold text-gray-800">{attendanceSummary.sakit}</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-8">
                      <div
                        className="bg-sky-600 h-8 rounded-full transition-all duration-500"
                        style={{ width: `${(attendanceSummary.sakit / maxValue) * 100}%` }}
                      ></div>
                    </div>
                  </div>

                  {/* Cuti */}
                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-sm font-medium text-gray-700">Cuti</span>
                      <span className="text-sm font-bold text-gray-800">{attendanceSummary.cuti}</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-8">
                      <div
                        className="bg-sky-400 h-8 rounded-full transition-all duration-500"
                        style={{ width: `${(attendanceSummary.cuti / maxValue) * 100}%` }}
                      ></div>
                    </div>
                  </div>

                  {/* Alpha */}
                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-sm font-medium text-gray-700">Alpha</span>
                      <span className="text-sm font-bold text-gray-800">{attendanceSummary.alpha}</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-8">
                      <div
                        className="bg-sky-300 h-8 rounded-full transition-all duration-500"
                        style={{ width: `${(attendanceSummary.alpha / maxValue) * 100}%` }}
                      ></div>
                    </div>
                  </div>
                </div>

                {/* Legend */}
                <div className="mt-6 pt-4 border-t">
                  <p className="text-xs text-gray-500 italic">
                    * Data kehadiran hanya menampilkan rekap total per status
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
                <h3 className="text-lg font-semibold text-gray-800">Preview File</h3>
                <button onClick={handleCloseFilePreview} className="text-gray-400 hover:text-gray-600 transition">
                  <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              {/* Modal Body - File Preview */}
              <div className="flex-1 overflow-auto p-6 flex items-center justify-center">
                {previewFileType === "image" ? (
                  imageLoadError ? (
                    <div className="text-center">
                      <div className="mb-4">
                        <svg className="w-16 h-16 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                      </div>
                      <p className="text-gray-600 mb-4">Gagal memuat gambar. Silakan coba buka di tab baru.</p>
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
                      <svg className="w-16 h-16 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <p className="text-gray-600 mb-4">Preview tidak tersedia untuk tipe file ini</p>
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
