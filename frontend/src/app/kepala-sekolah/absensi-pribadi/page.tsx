"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuthStore } from "@/store/authStore";
import { apiClient } from "@/lib/api";
import BottomBack from "@/components/BottomBack";

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

export default function KepalaSekolahAbsensiPribadi() {
  const router = useRouter();
  const { user } = useAuthStore();
  const [attendanceData, setAttendanceData] = useState<AttendanceRecord[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(10);
  const [totalRecords, setTotalRecords] = useState(0);

  // Modal state for manual attendance (samakan dengan HRD)
  const [showModal, setShowModal] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formData, setFormData] = useState({
    tanggal_mulai: new Date().toISOString().split("T")[0],
    durasi_hari: "",
    status_absensi: "Sakit",
    file_pendukung: null as File | null,
    keterangan_pendukung: "",
  });
  const [selectedFileName, setSelectedFileName] = useState(
    "Tidak ada file yang dipilih"
  );

  // File preview modal state
  const [showFilePreview, setShowFilePreview] = useState(false);
  const [previewFileUrl, setPreviewFileUrl] = useState<string | null>(null);
  const [previewFileType, setPreviewFileType] = useState<string | null>(null);
  const [imageLoadError, setImageLoadError] = useState(false);
  const [lastToastTime, setLastToastTime] = useState<number>(0);

  // Fetch attendance data
  useEffect(() => {
    const loadAttendanceData = async () => {
      try {
        setIsLoading(true);
        const response = await apiClient.attendance.getHistory();

        if (response.success && response.data) {
          const data = response.data as AttendanceRecord[];
          setAttendanceData(data);
          setTotalRecords(data.length);
        }
      } catch (error) {
        console.error("Error loading attendance data:", error);
      } finally {
        setIsLoading(false);
      }
    };

    loadAttendanceData();
  }, []);

  // Filter data based on search term
  const filteredData = attendanceData.filter(
    (record) =>
      record.tanggal.toLowerCase().includes(searchTerm.toLowerCase()) ||
      record.status.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (record.keterangan &&
        record.keterangan.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  // Pagination
  const totalPages = Math.ceil(filteredData.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const currentData = filteredData.slice(startIndex, endIndex);

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  };

  const formatTime = (timeString: string | null) => {
    if (!timeString) return "-";
    const time = new Date(timeString);
    return time.toLocaleTimeString("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case "hadir":
        return "bg-green-100 text-green-800";
      case "terlambat":
        return "bg-blue-100 text-blue-800";
      case "tidak hadir":
        return "bg-red-100 text-red-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  // Toast sederhana (mengikuti pola di HRD)
  const showToast = (
    message: string,
    type: "success" | "error" | "warning" | "info" = "info"
  ) => {
    const now = Date.now();
    if (now - lastToastTime < 2000) return;

    setLastToastTime(now);

    const toast = document.createElement("div");
    toast.className = `fixed top-4 right-4 z-[60] px-6 py-3 rounded-lg shadow-lg text-white font-medium max-w-sm transform transition-all duration-300 ease-in-out ${type === "success"
      ? "bg-green-500"
      : type === "error"
        ? "bg-red-500"
        : type === "warning"
          ? "bg-yellow-500"
          : "bg-blue-500"
      }`;

    const icon =
      type === "success"
        ? "✅"
        : type === "error"
          ? "❌"
          : type === "warning"
            ? "⚠️"
            : "ℹ️";

    const toastContent = document.createElement("div");
    toastContent.className = "flex items-center gap-2";
    const iconSpan = document.createElement("span");
    iconSpan.textContent = icon;
    const messageSpan = document.createElement("span");
    messageSpan.textContent = message;
    toastContent.appendChild(iconSpan);
    toastContent.appendChild(messageSpan);
    toast.appendChild(toastContent);

    toast.style.opacity = "0";
    toast.style.transform = "translateX(100%)";
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = "1";
      toast.style.transform = "translateX(0)";
    }, 10);

    setTimeout(() => {
      toast.style.opacity = "0";
      toast.style.transform = "translateX(100%)";
      setTimeout(() => {
        if (toast.parentNode) {
          toast.remove();
        }
      }, 300);
    }, 5000);
  };

  const handleOpenModal = () => {
    setFormData({
      tanggal_mulai: new Date().toISOString().split("T")[0],
      durasi_hari: "",
      status_absensi: "Sakit",
      file_pendukung: null,
      keterangan_pendukung: "",
    });
    setSelectedFileName("Tidak ada file yang dipilih");
    setShowModal(true);
  };

  const handleCloseModal = () => {
    setShowModal(false);
    setIsSubmitting(false);
  };

  const handleInputChange = (
    e: React.ChangeEvent<
      HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement
    >
  ) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] || null;
    setFormData((prev) => ({ ...prev, file_pendukung: file }));
    setSelectedFileName(file ? file.name : "Tidak ada file yang dipilih");
  };

  const handleOpenFilePreview = (filePath: string) => {
    const apiBaseUrl = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";
    const fileUrl = `${apiBaseUrl}/storage/${filePath}`;

    // Determine file type from extension
    const extension = filePath.split(".").pop()?.toLowerCase();
    let fileType = "other";

    if (["jpg", "jpeg", "png", "gif", "webp"].includes(extension || "")) {
      fileType = "image";
    } else if (extension === "pdf") {
      fileType = "pdf";
    }

    setPreviewFileType(fileType);
    setPreviewFileUrl(fileUrl);
    setImageLoadError(false);
    setShowFilePreview(true);
  };

  const handleCloseFilePreview = () => {
    setShowFilePreview(false);
    setPreviewFileUrl(null);
    setPreviewFileType(null);
    setImageLoadError(false);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    try {
      const durasiHari = parseInt(formData.durasi_hari) || 0;
      if (durasiHari < 0) {
        showToast("Durasi hari tidak boleh negatif", "error");
        setIsSubmitting(false);
        return;
      }

      const response = await apiClient.attendance.createManual({
        tanggal_mulai: formData.tanggal_mulai,
        durasi_hari: durasiHari,
        status_absensi: formData.status_absensi,
        keterangan_pendukung:
          formData.keterangan_pendukung || undefined,
        file_pendukung: formData.file_pendukung || undefined,
      });

      if (response.success) {
        const historyResponse = await apiClient.attendance.getHistory();
        if (historyResponse.success && historyResponse.data) {
          const data = historyResponse.data as AttendanceRecord[];
          setAttendanceData(data);
          setTotalRecords(data.length);
        }

        handleCloseModal();
        showToast(
          response.message || "Absensi berhasil ditambahkan!",
          "success"
        );
      } else {
        showToast(
          response.message ||
          "Gagal menambahkan absensi. Silakan coba lagi.",
          "error"
        );
      }
    } catch (error: any) {
      console.error("Error submitting attendance:", error);
      const errorMessage =
        error.response?.data?.message ||
        error.message ||
        "Gagal menambahkan absensi. Silakan coba lagi.";
      showToast(errorMessage, "error");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-100 pb-28">
      {/* Header Section */}
      <div className="px-5 pt-3 pb-4">
        <div className="flex items-center gap-4">
          <BottomBack variant="inline" />
          <h1 className="text-xl font-bold text-gray-800">Absensi Pribadi</h1>
        </div>
      </div>

      {/* Content */}
      <div className="px-5 mt-4">
        <div className="bg-white rounded-2xl shadow-md p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-bold text-gray-800">Absensi Pribadi</h2>
            <button
              onClick={handleOpenModal}
              className="hidden md:flex bg-sky-800 rounded-[8px] px-4 py-2 items-center gap-2 hover:bg-sky-900 transition"
            >
              <svg
                className="w-4 h-4 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M12 4v16m8-8H4"
                />
              </svg>
              <span className="text-white text-sm font-['Poppins']">
                Tambah Baru
              </span>
            </button>
          </div>

          {/* Search and Filter */}
          <div className="flex flex-col sm:flex-row gap-4 mb-4">
            <div className="flex-1">
              <input
                type="text"
                placeholder="Cari berdasarkan tanggal, status, atau keterangan..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
              />
            </div>
            <div className="flex items-center gap-2">
              <label className="text-sm text-gray-600">Show:</label>
              <select
                value={itemsPerPage}
                onChange={(e) => setItemsPerPage(Number(e.target.value))}
                className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
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
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">
                    Tanggal
                  </th>
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">
                    Check In
                  </th>
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">
                    Check Out
                  </th>
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">
                    Presensi
                  </th>
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">
                    Keterangan
                  </th>
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">
                    File Pendukung
                  </th>
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody>
                {isLoading ? (
                  <tr>
                    <td colSpan={7} className="text-center py-8 text-gray-500">
                      <div className="flex items-center justify-center">
                        <div className="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mr-2"></div>
                        Loading...
                      </div>
                    </td>
                  </tr>
                ) : currentData.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="text-center py-8 text-gray-500">
                      Tidak ada data absensi
                    </td>
                  </tr>
                ) : (
                  currentData.map((record) => (
                    <tr key={record.id} className="border-b hover:bg-gray-50">
                      <td className="py-3 px-4 text-gray-700">
                        {formatDate(record.tanggal)}
                      </td>
                      <td className="py-3 px-4 text-gray-700">
                        {formatTime(record.check_in)}
                      </td>
                      <td className="py-3 px-4 text-gray-700">
                        {formatTime(record.check_out)}
                      </td>
                      <td className="py-3 px-4">
                        <span
                          className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(
                            record.status
                          )}`}
                        >
                          {record.status}
                        </span>
                      </td>
                      <td className="py-3 px-4 text-gray-700">
                        {record.keterangan || "-"}
                      </td>
                      <td className="py-3 px-4">
                        {record.file_pendukung ? (
                          <button
                            onClick={() =>
                              handleOpenFilePreview(record.file_pendukung!)
                            }
                            className="text-sky-800 hover:text-sky-900 transition-colors p-1 rounded hover:bg-sky-50"
                          >
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
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                              />
                              <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                              />
                            </svg>
                          </button>
                        ) : (
                          "-"
                        )}
                      </td>
                      <td className="py-3 px-4">
                        <button
                          onClick={() =>
                            router.push(
                              `/kepala-sekolah/absensi-pribadi/log/${record.id}`
                            )
                          }
                          className="bg-sky-800 text-white px-3 py-1 rounded text-xs hover:bg-sky-900 transition-colors"
                        >
                          Log Absensi
                        </button>
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
                Showing {startIndex + 1} to{" "}
                {Math.min(endIndex, filteredData.length)} of{" "}
                {filteredData.length} entries
              </div>
              <div className="flex items-center gap-2">
                <button
                  onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
                  disabled={currentPage === 1}
                  className="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 text-sky-800"
                >
                  Previous
                </button>
                <span className="px-3 py-1 bg-sky-800 text-white rounded text-sm">
                  {currentPage} of {totalPages}
                </span>
                <button
                  onClick={() =>
                    setCurrentPage((prev) => Math.min(prev + 1, totalPages))
                  }
                  disabled={currentPage === totalPages}
                  className="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 text-sky-800"
                >
                  Next
                </button>
              </div>
            </div>
          )}

          {/* Legend */}
          <div className="mt-6 p-4 bg-gray-50 rounded-lg">
            <h3 className="text-sm font-semibold text-gray-700 mb-2">
              Legend:
            </h3>
            <div className="flex flex-wrap gap-4 text-xs">
              <div className="flex items-center gap-2">
                <span className="w-3 h-3 bg-green-100 rounded-full"></span>
                <span className="text-green-800">Hadir</span>
              </div>
              <div className="flex items-center gap-2">
                <span className="w-3 h-3 bg-blue-100 rounded-full"></span>
                <span className="text-blue-800">Terlambat</span>
              </div>
              <div className="flex items-center gap-2">
                <span className="w-3 h-3 bg-red-100 rounded-full"></span>
                <span className="text-red-800">Tidak Hadir</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Modal Tambah Absensi */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            {/* Header */}
            <div className="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center rounded-t-2xl">
              <h3 className="text-lg font-semibold text-gray-800">
                Tambah Absensi
              </h3>
              <button
                onClick={handleCloseModal}
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

            {/* Body */}
            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              {/* Tanggal Mulai */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">
                  Tanggal Mulai
                </label>
                <input
                  type="date"
                  name="tanggal_mulai"
                  value={formData.tanggal_mulai}
                  onChange={handleInputChange}
                  className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800"
                  required
                />
              </div>

              {/* Durasi Hari */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">
                  Durasi Hari
                </label>
                <input
                  type="number"
                  name="durasi_hari"
                  value={formData.durasi_hari}
                  onChange={handleInputChange}
                  placeholder="Jumlah hari absen (0 = hari ini saja)"
                  min="0"
                  className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800"
                  required
                />
              </div>

              {/* Status Absensi */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">
                  Status Absensi
                </label>
                <select
                  name="status_absensi"
                  value={formData.status_absensi}
                  onChange={handleInputChange}
                  className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-800"
                  required
                >
                  <option value="Sakit">Sakit</option>
                  <option value="Cuti">Cuti</option>
                </select>
              </div>

              {/* File Pendukung */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">
                  File Pendukung (opsional)
                </label>
                <div className="flex items-center gap-3">
                  <label className="bg-sky-800 text-white px-4 py-2 rounded-lg cursor-pointer hover:bg-sky-900 transition-colors text-sm font-medium">
                    Pilih File
                    <input
                      type="file"
                      onChange={handleFileChange}
                      className="hidden"
                      accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                    />
                  </label>
                  <span className="text-sm text-gray-600 flex-1">
                    {selectedFileName}
                  </span>
                </div>
              </div>

              {/* Keterangan Pendukung */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">
                  Keterangan Pendukung
                </label>
                <textarea
                  name="keterangan_pendukung"
                  value={formData.keterangan_pendukung}
                  onChange={handleInputChange}
                  placeholder="Dapat dikosongkan jika tidak ada"
                  rows={4}
                  className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none text-gray-800"
                />
              </div>

              {/* Actions */}
              <div className="flex justify-end gap-3 pt-4">
                <button
                  type="button"
                  onClick={handleCloseModal}
                  disabled={isSubmitting}
                  className="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors disabled:opacity-50"
                >
                  Close
                </button>
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="px-6 py-2 bg-sky-800 text-white rounded-lg font-medium hover:bg-sky-900 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {isSubmitting ? "Menyimpan..." : "Simpan"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* File Preview Modal */}
      {showFilePreview && previewFileUrl && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
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
                      className="inline-block bg-[#1e4d8b] text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors"
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
                    className="inline-block bg-[#1e4d8b] text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors"
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
                className="bg-[#1e4d8b] text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors"
              >
                Buka di Tab Baru
              </a>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
