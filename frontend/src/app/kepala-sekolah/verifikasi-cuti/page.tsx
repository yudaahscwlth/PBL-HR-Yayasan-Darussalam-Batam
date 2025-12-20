"use client";

import { useState, useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/api";
import AccessControl from "@/components/AccessControl";
import toast from "react-hot-toast";
import BottomBack from "@/components/BottomBack";

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
      nama_lengkap: string;
    };
  };
}

export default function KepalaSekolahVerifikasiCutiPage() {
  const { user, isAuthenticated } = useAuthStore();
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [leaveRequests, setLeaveRequests] = useState<LeaveRequest[]>([]);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [selectedRequest, setSelectedRequest] = useState<LeaveRequest | null>(
    null
  );
  const [actionType, setActionType] = useState<"approve" | "reject" | null>(
    null
  );
  const [komentar, setKomentar] = useState("");
  const [showImageModal, setShowImageModal] = useState(false);
  const [previewImageUrl, setPreviewImageUrl] = useState<string | null>(null);

  // Pagination and search state for Verifikasi Cuti
  const [searchVerifikasi, setSearchVerifikasi] = useState("");
  const [entriesVerifikasi, setEntriesVerifikasi] = useState(10);
  const [currentPageVerifikasi, setCurrentPageVerifikasi] = useState(1);

  // Pagination and search state for Riwayat
  const [searchRiwayat, setSearchRiwayat] = useState("");
  const [entriesRiwayat, setEntriesRiwayat] = useState(10);
  const [currentPageRiwayat, setCurrentPageRiwayat] = useState(1);



  // Redirect if not authenticated
  useEffect(() => {
    if (!isAuthenticated && user === null) {
      return;
    }

    if (!isAuthenticated) {
      router.push("/");
      return;
    }

    if (user) {
      const isKepalaSekolah = user.roles?.includes("kepala sekolah");

      if (!isKepalaSekolah) {
        router.push("/unauthorized");
        return;
      }

      setIsLoading(false);
    }
  }, [isAuthenticated, user, router]);

  // Load leave requests
  useEffect(() => {
    if (isAuthenticated && user) {
      loadLeaveRequests();
    }
  }, [isAuthenticated, user]);

  const loadLeaveRequests = async () => {
    try {
      const response = await apiClient.leave.getAll();
      if (response.success) {
        const allRequests = response.data as LeaveRequest[];
        // Filter berdasarkan tempat kerja kepala sekolah (dari departemen yang sama)
        // Untuk sekarang, kita akan filter berdasarkan status saja
        // Jika perlu filter berdasarkan tempat kerja, bisa ditambahkan logika di sini
        setLeaveRequests(allRequests);
      } else {
        console.error("Failed to load leave requests:", response.message);
      }
    } catch (error) {
      console.error("Error loading leave requests:", error);
      toast.error("Gagal memuat data pengajuan cuti");
    }
  };

  const showToast = (
    type: "success" | "error" | "warning" | "info",
    message: string
  ) => {
    setToastMessage({ type, message, show: true });
    setTimeout(() => {
      setToastMessage((prev) => ({ ...prev, show: false }));
    }, 5000);
  };

  const handleConfirmAction = async () => {
    if (!selectedRequest || !actionType) return;

    // Validate komentar for reject
    if (actionType === "reject" && !komentar.trim()) {
      toast.error("Alasan penolakan wajib diisi");
      return;
    }

    setIsSubmitting(true);

    try {
      let response;

      if (actionType === "approve") {
        // Gunakan endpoint approve khusus kepala sekolah
        response = await apiClient.leave.approveKepsek(
          selectedRequest.id,
          komentar.trim() || undefined
        );
      } else {
        // Reject: gunakan endpoint reject khusus kepala sekolah
        response = await apiClient.leave.rejectKepsek(
          selectedRequest.id,
          komentar.trim()
        );
      }

      if (response.success) {
        showToast(
          "success",
          `✅ Cuti berhasil ${actionType === "approve" ? "disetujui" : "ditolak"
          }!`
        );
        setShowConfirmModal(false);
        setSelectedRequest(null);
        setActionType(null);
        setKomentar("");
        // Reload leave requests to show updated status
        await loadLeaveRequests();
      } else {
        showToast(
          "error",
          response.message ||
          `❌ Gagal ${actionType === "approve" ? "menyetujui" : "menolak"
          } cuti`
        );
      }
    } catch (error: any) {
      console.error("Error confirming leave request:", error);
      const errorMessage =
        error.response?.data?.message ||
        error.response?.data?.error ||
        error.message ||
        `❌ Gagal ${actionType === "approve" ? "menyetujui" : "menolak"} cuti`;
      toast.error(errorMessage);
    } finally {
      setIsSubmitting(false);
    }
  };

  const openConfirmModal = (
    request: LeaveRequest,
    type: "approve" | "reject"
  ) => {
    setSelectedRequest(request);
    setActionType(type);
    setKomentar("");
    setShowConfirmModal(true);
  };

  const handleViewImage = (url: string) => {
    setPreviewImageUrl(url);
    setShowImageModal(true);
  };

  const getStatusColor = (status: string) => {
    const statusLower = status.toLowerCase();
    if (
      statusLower.includes("disetujui") &&
      !statusLower.includes("menunggu")
    ) {
      return "bg-green-100 text-green-800";
    } else if (
      statusLower.includes("disetujui") &&
      statusLower.includes("menunggu")
    ) {
      return "bg-blue-100 text-blue-800";
    } else if (statusLower.includes("ditolak")) {
      return "bg-red-100 text-red-800";
    } else {
      return "bg-yellow-100 text-yellow-800";
    }
  };

  const getStatusText = (status: string) => {
    const statusLower = status.toLowerCase();

    // Mapping status enum ke teks yang lebih jelas
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

    // Cek apakah status ada di mapping
    if (statusMap[statusLower]) {
      return statusMap[statusLower];
    }

    // Fallback untuk status yang tidak terdaftar
    return status;
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const months = [
      "Jan",
      "Feb",
      "Mar",
      "Apr",
      "Mei",
      "Jun",
      "Jul",
      "Agu",
      "Sep",
      "Okt",
      "Nov",
      "Des",
    ];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
  };

  const calculateDays = (startDate: string, endDate: string) => {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end.getTime() - start.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    return diffDays;
  };

  // Filter data - hanya show requests yang perlu verifikasi kepala sekolah
  const pendingRequests = leaveRequests.filter((req) => {
    const status = req.status_pengajuan.toLowerCase();
    return status === "ditinjau kepala sekolah";
  });

  const historyRequests = leaveRequests.filter((req) => {
    const status = req.status_pengajuan.toLowerCase();
    return (
      status.includes("disetujui kepala sekolah") ||
      status.includes("ditolak kepala sekolah") ||
      status === "disetujui dirpen" ||
      status === "ditolak dirpen"
    );
  });

  const filteredVerifikasi = pendingRequests.filter(
    (req) =>
      req.tipe_cuti.toLowerCase().includes(searchVerifikasi.toLowerCase()) ||
      req.alasan_pendukung
        .toLowerCase()
        .includes(searchVerifikasi.toLowerCase()) ||
      req.user?.profile_pribadi?.nama_lengkap
        ?.toLowerCase()
        .includes(searchVerifikasi.toLowerCase()) ||
      req.user?.email?.toLowerCase().includes(searchVerifikasi.toLowerCase())
  );
  const filteredRiwayat = historyRequests.filter(
    (req) =>
      req.tipe_cuti.toLowerCase().includes(searchRiwayat.toLowerCase()) ||
      req.alasan_pendukung
        .toLowerCase()
        .includes(searchRiwayat.toLowerCase()) ||
      req.user?.profile_pribadi?.nama_lengkap
        ?.toLowerCase()
        .includes(searchRiwayat.toLowerCase()) ||
      req.user?.email?.toLowerCase().includes(searchRiwayat.toLowerCase())
  );

  // Pagination
  const totalPagesVerifikasi = Math.ceil(
    filteredVerifikasi.length / entriesVerifikasi
  );
  const paginatedVerifikasi = filteredVerifikasi.slice(
    (currentPageVerifikasi - 1) * entriesVerifikasi,
    currentPageVerifikasi * entriesVerifikasi
  );
  const totalPagesRiwayat = Math.ceil(filteredRiwayat.length / entriesRiwayat);
  const paginatedRiwayat = filteredRiwayat.slice(
    (currentPageRiwayat - 1) * entriesRiwayat,
    currentPageRiwayat * entriesRiwayat
  );

  if (isLoading || !isAuthenticated || !user) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Loading...</p>
        </div>
      </div>
    );
  }

  return (
    <AccessControl allowedRoles={["kepala sekolah"]}>
      <div className="min-h-screen bg-gray-100 pb-28">
        {/* Header Section */}
        <div className="px-5 pt-3 pb-4">
          <div className="flex items-center gap-4">
            <BottomBack variant="inline" />
            <h1 className="text-xl font-bold text-gray-800">Verifikasi Cuti</h1>
          </div>
        </div>

        {/* Main Content */}
        <div className="px-5 space-y-6">
          {/* Verifikasi Cuti */}
          <section className="bg-white rounded-[10px] shadow-md border border-black/20 p-4 w-full">
            <h2 className="text-base font-normal font-['Poppins'] text-black mb-4 md:text-lg">
              Verifikasi Cuti
            </h2>

            {/* Controls */}
            <div className="grid grid-cols-1 gap-2 md:grid-cols-2 md:items-center md:gap-3 mb-3 text-[10px] md:text-sm text-black">
              {/* Kiri: Show entries */}
              <div className="flex flex-wrap items-center gap-2">
                <span className="font-['Poppins'] shrink-0">Show</span>
                <select
                  value={entriesVerifikasi}
                  onChange={(e) =>
                    setEntriesVerifikasi(Number(e.target.value))
                  }
                  className="h-7 md:h-8 w-16 md:w-24 px-2 bg-white rounded border border-black/30 font-['Poppins'] text-black"
                >
                  <option value={5}>5</option>
                  <option value={10}>10</option>
                  <option value={25}>25</option>
                </select>
                <span className="font-['Poppins'] shrink-0">entries</span>
              </div>

              {/* Kanan: Search */}
              <div className="flex items-center gap-2 md:justify-end">
                <label className="font-['Poppins'] shrink-0">Search:</label>
                <input
                  type="text"
                  value={searchVerifikasi}
                  onChange={(e) => setSearchVerifikasi(e.target.value)}
                  placeholder="Cari nama/tipe/alasan"
                  className="w-full md:w-72 h-8 md:h-9 px-3 bg-white rounded border border-black/20 text-black"
                />
              </div>
            </div>

            {/* Table */}
            <div className="overflow-x-auto rounded-md border border-zinc-200">
              <table className="w-full text-[9px] md:text-sm border-collapse">
                <thead className="bg-gray-100 border-b sticky top-0 z-10">
                  <tr>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black whitespace-nowrap">
                      Tanggal Pengajuan
                    </th>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black">
                      Nama
                    </th>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black whitespace-nowrap">
                      Tipe Pengajuan
                    </th>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black">
                      Durasi
                    </th>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black whitespace-nowrap">
                      Alasan Pendukung
                    </th>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black whitespace-nowrap">
                      File Pendukung
                    </th>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black">
                      Status
                    </th>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black">
                      Aksi
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {paginatedVerifikasi.length === 0 ? (
                    <tr>
                      <td
                        colSpan={8}
                        className="p-4 text-center text-black"
                      >
                        Tidak ada pengajuan cuti yang perlu diverifikasi
                      </td>
                    </tr>
                  ) : (
                    paginatedVerifikasi.map((request) => (
                      <tr
                        key={request.id}
                        className="border-b hover:bg-gray-50"
                      >
                        <td className="p-2 md:p-3 font-sans text-black whitespace-nowrap">
                          {formatDate(request.created_at)}
                        </td>
                        <td className="p-2 md:p-3 font-sans text-black">
                          {request.user?.profile_pribadi?.nama_lengkap ||
                            request.user?.email ||
                            "-"}
                        </td>
                        <td className="p-2 md:p-3 font-sans capitalize text-black">
                          {request.tipe_cuti}
                        </td>
                        <td className="p-2 md:p-3 font-sans text-black whitespace-nowrap">
                          {calculateDays(
                            request.tanggal_mulai,
                            request.tanggal_selesai
                          )}{" "}
                          hari
                        </td>
                        <td
                          className="p-2 md:p-3 font-sans text-black max-w-[150px] md:max-w-none truncate"
                          title={request.alasan_pendukung}
                        >
                          {request.alasan_pendukung || "-"}
                        </td>
                        <td className="p-2 md:p-3 font-sans text-black text-center">
                          {request.file_pendukung ? (
                            <button
                              onClick={() =>
                                handleViewImage(
                                  `${process.env.NEXT_PUBLIC_API_URL ||
                                  "http://localhost:8000"
                                  }/storage/${request.file_pendukung}`
                                )
                              }
                              className="text-sky-800 hover:text-sky-900 transition-colors"
                              title="Lihat Bukti"
                            >
                              <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                strokeWidth={1.5}
                                stroke="currentColor"
                                className="w-5 h-5 mx-auto"
                              >
                                <path
                                  strokeLinecap="round"
                                  strokeLinejoin="round"
                                  d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
                                />
                                <path
                                  strokeLinecap="round"
                                  strokeLinejoin="round"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                              </svg>
                            </button>
                          ) : (
                            "-"
                          )}
                        </td>
                        <td className="p-2 md:p-3">
                          <span
                            className={`px-2 py-0.5 rounded text-[8px] md:text-xs whitespace-nowrap ${getStatusColor(
                              request.status_pengajuan
                            )}`}
                          >
                            {getStatusText(request.status_pengajuan)}
                          </span>
                        </td>
                        <td className="p-2 md:p-3">
                          <div className="flex gap-1 md:gap-2">
                            <button
                              onClick={() =>
                                openConfirmModal(request, "approve")
                              }
                              className="px-2 py-1 md:px-3 md:py-1.5 bg-sky-800 text-white text-[8px] md:text-xs rounded hover:bg-sky-600 transition whitespace-nowrap"
                            >
                              Setujui
                            </button>
                            <button
                              onClick={() =>
                                openConfirmModal(request, "reject")
                              }
                              className="px-2 py-1 md:px-3 md:py-1.5 bg-red-600 text-white text-[8px] md:text-xs rounded hover:bg-red-700 transition whitespace-nowrap"
                            >
                              Tolak
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>

            {/* Pagination */}
            <div className="flex flex-col gap-2 md:flex-row md:justify-between md:items-center mt-3 text-[10px] md:text-sm text-black">
              <span className="font-['Poppins']">
                Showing{" "}
                {filteredVerifikasi.length === 0
                  ? 0
                  : (currentPageVerifikasi - 1) * entriesVerifikasi +
                  1}{" "}
                to{" "}
                {Math.min(
                  currentPageVerifikasi * entriesVerifikasi,
                  filteredVerifikasi.length
                )}{" "}
                of {filteredVerifikasi.length} entries
              </span>
              <div className="flex items-center gap-px bg-zinc-800/10 rounded-sm border border-black/5">
                <button
                  onClick={() =>
                    setCurrentPageVerifikasi(
                      Math.max(1, currentPageVerifikasi - 1)
                    )
                  }
                  disabled={currentPageVerifikasi === 1}
                  className="px-2 py-1 md:px-3 md:py-1.5 text-sky-800 text-[9px] md:text-sm hover:bg-gray-200 disabled:opacity-50"
                >
                  Previous
                </button>
                <span className="px-2 py-1 md:px-3 md:py-1.5 bg-sky-800 text-white text-[10px] md:text-sm">
                  {currentPageVerifikasi}
                </span>
                <button
                  onClick={() =>
                    setCurrentPageVerifikasi(
                      Math.min(
                        totalPagesVerifikasi,
                        currentPageVerifikasi + 1
                      )
                    )
                  }
                  disabled={
                    currentPageVerifikasi === totalPagesVerifikasi ||
                    totalPagesVerifikasi === 0
                  }
                  className="px-2 py-1 md:px-3 md:py-1.5 text-sky-800 text-[9px] md:text-sm hover:bg-gray-200 disabled:opacity-50"
                >
                  Next
                </button>
              </div>
            </div>
          </section>

          {/* Riwayat */}
          <section className="bg-white rounded-[10px] shadow-md border border-black/20 p-4 w-full">
            <h2 className="text-base font-normal font-['Poppins'] mb-4 text-black md:text-lg">
              Riwayat Verifikasi
            </h2>

            {/* Controls */}
            <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-3 text-[10px] md:text-sm text-black">
              <div className="flex flex-wrap items-center gap-2">
                <span className="font-['Poppins'] shrink-0">Show</span>
                <select
                  value={entriesRiwayat}
                  onChange={(e) =>
                    setEntriesRiwayat(Number(e.target.value))
                  }
                  className="h-7 md:h-8 w-16 md:w-24 px-2 bg-white rounded border border-black/30 font-['Poppins'] text-black"
                >
                  <option value={5}>5</option>
                  <option value={10}>10</option>
                  <option value={25}>25</option>
                </select>
                <span className="font-['Poppins'] shrink-0">entries</span>
              </div>
              <div className="flex items-center gap-1 md:gap-2">
                <span className="font-['Poppins']">Search:</span>
                <input
                  type="text"
                  value={searchRiwayat}
                  onChange={(e) => setSearchRiwayat(e.target.value)}
                  placeholder="Cari nama/tipe/alasan"
                  className="w-24 md:w-64 h-4 md:h-9 px-2 bg-white rounded-sm md:rounded-md border border-black/20 text-[10px] md:text-sm text-black"
                />
              </div>
            </div>

            {/* Table */}
            <div className="overflow-x-auto rounded-md border border-zinc-200">
              <table className="w-full text-[9px] md:text-sm border-collapse">
                <thead className="bg-gray-100 border-b sticky top-0 z-10">
                  <tr>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black whitespace-nowrap">
                      Tanggal Pengajuan
                    </th>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black">
                      Nama
                    </th>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black whitespace-nowrap">
                      Tipe Pengajuan
                    </th>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black">
                      Durasi
                    </th>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black whitespace-nowrap">
                      Alasan Pendukung
                    </th>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black whitespace-nowrap">
                      File Pendukung
                    </th>
                    <th className="p-2 md:p-3 text-left font-semibold font-sans text-black">
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {paginatedRiwayat.length === 0 ? (
                    <tr>
                      <td
                        colSpan={7}
                        className="p-4 text-center text-black"
                      >
                        Belum ada riwayat verifikasi cuti
                      </td>
                    </tr>
                  ) : (
                    paginatedRiwayat.map((request) => (
                      <tr
                        key={request.id}
                        className="border-b hover:bg-gray-50"
                      >
                        <td className="p-2 md:p-3 font-sans text-black whitespace-nowrap">
                          {formatDate(request.created_at)}
                        </td>
                        <td className="p-2 md:p-3 font-sans text-black">
                          {request.user?.profile_pribadi?.nama_lengkap ||
                            request.user?.email ||
                            "-"}
                        </td>
                        <td className="p-2 md:p-3 font-sans capitalize text-black">
                          {request.tipe_cuti}
                        </td>
                        <td className="p-2 md:p-3 font-sans text-black whitespace-nowrap">
                          {calculateDays(
                            request.tanggal_mulai,
                            request.tanggal_selesai
                          )}{" "}
                          hari
                        </td>
                        <td
                          className="p-2 md:p-3 font-sans text-black max-w-[150px] md:max-w-none truncate"
                          title={request.alasan_pendukung}
                        >
                          {request.alasan_pendukung || "-"}
                        </td>
                        <td className="p-2 md:p-3 font-sans text-black text-center">
                          {request.file_pendukung ? (
                            <button
                              onClick={() =>
                                handleViewImage(
                                  `${process.env.NEXT_PUBLIC_API_URL ||
                                  "http://localhost:8000"
                                  }/storage/${request.file_pendukung}`
                                )
                              }
                              className="text-sky-800 hover:text-sky-900 transition-colors"
                              title="Lihat Bukti"
                            >
                              <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                strokeWidth={1.5}
                                stroke="currentColor"
                                className="w-5 h-5 mx-auto"
                              >
                                <path
                                  strokeLinecap="round"
                                  strokeLinejoin="round"
                                  d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
                                />
                                <path
                                  strokeLinecap="round"
                                  strokeLinejoin="round"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                              </svg>
                            </button>
                          ) : (
                            "-"
                          )}
                        </td>
                        <td className="p-2 md:p-3">
                          <span
                            className={`px-2 py-0.5 rounded text-[8px] md:text-xs whitespace-nowrap ${getStatusColor(
                              request.status_pengajuan
                            )}`}
                          >
                            {getStatusText(request.status_pengajuan)}
                          </span>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>

            {/* Pagination */}
            <div className="flex flex-col gap-2 md:flex-row md:justify-between md:items-center mt-3 text-[10px] md:text-sm text-black">
              <span className="font-['Poppins']">
                Showing{" "}
                {filteredRiwayat.length === 0
                  ? 0
                  : (currentPageRiwayat - 1) * entriesRiwayat + 1}{" "}
                to{" "}
                {Math.min(
                  currentPageRiwayat * entriesRiwayat,
                  filteredRiwayat.length
                )}{" "}
                of {filteredRiwayat.length} entries
              </span>
              <div className="flex items-center gap-px bg-zinc-800/10 rounded-sm border border-black/5">
                <button
                  onClick={() =>
                    setCurrentPageRiwayat(
                      Math.max(1, currentPageRiwayat - 1)
                    )
                  }
                  disabled={currentPageRiwayat === 1}
                  className="px-2 py-1 md:px-3 md:py-1.5 text-sky-800 text-[9px] md:text-sm hover:bg-gray-200 disabled:opacity-50"
                >
                  Previous
                </button>
                <span className="px-2 py-1 md:px-3 md:py-1.5 bg-sky-800 text-white text-[10px] md:text-sm">
                  {currentPageRiwayat}
                </span>
                <button
                  onClick={() =>
                    setCurrentPageRiwayat(
                      Math.min(totalPagesRiwayat, currentPageRiwayat + 1)
                    )
                  }
                  disabled={
                    currentPageRiwayat === totalPagesRiwayat ||
                    totalPagesRiwayat === 0
                  }
                  className="px-2 py-1 md:px-3 md:py-1.5 text-sky-800 text-[9px] md:text-sm hover:bg-gray-200 disabled:opacity-50"
                >
                  Next
                </button>
              </div>
            </div>
          </section>
        </div>

        {/* Confirmation Modal */}
        {showConfirmModal && selectedRequest && (
          <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto md:max-w-xl">
              <div className="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center rounded-t-2xl">
                <h3 className="text-lg font-semibold font-['Poppins'] text-black">
                  {actionType === "approve"
                    ? "Setujui Pengajuan Cuti"
                    : "Tolak Pengajuan Cuti"}
                </h3>
                <button
                  onClick={() => {
                    setShowConfirmModal(false);
                    setSelectedRequest(null);
                    setActionType(null);
                    setKomentar("");
                  }}
                  className="text-gray-400 hover:text-gray-600 transition"
                  aria-label="Tutup"
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

              <div className="p-6 space-y-4">
                {/* Request Details */}
                <div className="bg-gray-50 rounded-xl p-4 space-y-2">
                  <div className="flex justify-between">
                    <span className="text-sm font-semibold text-gray-700 font-['Poppins']">
                      Nama:
                    </span>
                    <span className="text-sm text-black">
                      {selectedRequest.user?.profile_pribadi?.nama_lengkap ||
                        selectedRequest.user?.email ||
                        "-"}
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm font-semibold text-gray-700 font-['Poppins']">
                      Tipe Cuti:
                    </span>
                    <span className="text-sm text-black capitalize">
                      {selectedRequest.tipe_cuti}
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm font-semibold text-gray-700 font-['Poppins']">
                      Durasi:
                    </span>
                    <span className="text-sm text-black">
                      {calculateDays(
                        selectedRequest.tanggal_mulai,
                        selectedRequest.tanggal_selesai
                      )}{" "}
                      hari
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm font-semibold text-gray-700 font-['Poppins']">
                      Tanggal Mulai:
                    </span>
                    <span className="text-sm text-black">
                      {formatDate(selectedRequest.tanggal_mulai)}
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm font-semibold text-gray-700 font-['Poppins']">
                      Tanggal Selesai:
                    </span>
                    <span className="text-sm text-black">
                      {formatDate(selectedRequest.tanggal_selesai)}
                    </span>
                  </div>
                </div>

                {/* Komentar */}
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2 font-['Poppins']">
                    {actionType === "approve"
                      ? "Komentar (Opsional)"
                      : "Alasan Penolakan"}
                  </label>
                  <textarea
                    value={komentar}
                    onChange={(e) => setKomentar(e.target.value)}
                    rows={4}
                    placeholder={
                      actionType === "approve"
                        ? "Tambahkan komentar jika diperlukan..."
                        : "Masukkan alasan penolakan..."
                    }
                    className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-800 resize-none text-black"
                    required={actionType === "reject"}
                  />
                </div>

                {/* Submit Button */}
                <div className="flex gap-3 pt-4">
                  <button
                    type="button"
                    onClick={() => {
                      setShowConfirmModal(false);
                      setSelectedRequest(null);
                      setActionType(null);
                      setKomentar("");
                    }}
                    className="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-300 transition"
                  >
                    Batal
                  </button>
                  <button
                    type="button"
                    onClick={handleConfirmAction}
                    disabled={
                      isSubmitting ||
                      (actionType === "reject" && !komentar.trim())
                    }
                    className={`flex-1 py-3 rounded-xl font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed ${actionType === "approve"
                      ? "bg-sky-800 text-white hover:bg-sky-600"
                      : "bg-red-600 text-white hover:bg-red-700"
                      }`}
                  >
                    {isSubmitting ? (
                      <div className="flex items-center justify-center">
                        <svg
                          className="animate-spin h-5 w-5 mr-2"
                          xmlns="http://www.w3.org/2000/svg"
                          fill="none"
                          viewBox="0 0 24 24"
                        >
                          <circle
                            className="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            strokeWidth="4"
                          ></circle>
                          <path
                            className="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                          ></path>
                        </svg>
                        Memproses...
                      </div>
                    ) : actionType === "approve" ? (
                      "Setujui"
                    ) : (
                      "Tolak"
                    )}
                  </button>
                </div>
              </div>
            </div>
          </div>
        )}
        {/* Modal Preview Image */}
        {showImageModal && previewImageUrl && (
          <div className="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60" onClick={() => setShowImageModal(false)}>
            <div
              className="relative bg-white p-2 rounded-2xl shadow-2xl max-w-4xl w-auto max-h-[90vh] flex flex-col"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="flex justify-between items-center mb-4 px-2 pt-2">
                <h3 className="font-['Poppins'] font-semibold text-lg text-gray-800">Preview File</h3>
                <button
                  onClick={() => setShowImageModal(false)}
                  className="text-gray-500 hover:text-gray-700 transition-colors p-1 rounded-full hover:bg-gray-100"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor" className="w-6 h-6">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <div className="flex-1 overflow-auto flex items-center justify-center bg-gray-50 rounded-xl border border-gray-100 p-4">
                <img
                  src={previewImageUrl}
                  alt="Bukti Pendukung"
                  className="max-w-full max-h-[calc(80vh-3rem)] object-contain rounded-lg shadow-sm"
                />
              </div>
            </div>
          </div>
        )}
      </div>
    </AccessControl>
  );
}
