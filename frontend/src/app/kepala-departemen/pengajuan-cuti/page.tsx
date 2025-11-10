"use client";

import { useState, useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/api";

interface LeaveRequest {
  id: number;
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

export default function KepalaDepartemenPengajuanCuti() {
  const { user, isAuthenticated } = useAuthStore();
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showModal, setShowModal] = useState(false);
  const [leaveRequests, setLeaveRequests] = useState<LeaveRequest[]>([]);
  
  // Pagination and search state for Ajukan Cuti
  const [searchAjukan, setSearchAjukan] = useState("");
  const [entriesAjukan, setEntriesAjukan] = useState(10);
  const [currentPageAjukan, setCurrentPageAjukan] = useState(1);
  
  // Pagination and search state for Riwayat
  const [searchRiwayat, setSearchRiwayat] = useState("");
  const [entriesRiwayat, setEntriesRiwayat] = useState(10);
  const [currentPageRiwayat, setCurrentPageRiwayat] = useState(1);
  
  const [toastMessage, setToastMessage] = useState<{
    type: "success" | "error" | "warning" | "info";
    message: string;
    show: boolean;
  }>({ type: "info", message: "", show: false });

  // Form state
  const [formData, setFormData] = useState({
    tanggal_mulai: "",
    tanggal_selesai: "",
    jenis_cuti: "cuti tahunan",
    alasan: "",
  });

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
      const isKepalaDepartemen = user.roles?.includes("kepala departemen");

      if (!isKepalaDepartemen) {
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
        setLeaveRequests(response.data as LeaveRequest[]);
      }
    } catch (error) {
      console.error("Error loading leave requests:", error);
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

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    try {
      // Validate dates
      if (new Date(formData.tanggal_mulai) > new Date(formData.tanggal_selesai)) {
        showToast("error", "Tanggal selesai harus lebih besar dari tanggal mulai");
        setIsSubmitting(false);
        return;
      }

      const response = await apiClient.leave.create(formData);

      if (response.success) {
        showToast("success", "✅ Pengajuan cuti berhasil dikirim!");
        
        // Reset form
        setFormData({
          tanggal_mulai: "",
          tanggal_selesai: "",
          jenis_cuti: "cuti tahunan",
          alasan: "",
        });

        // Close modal and reload leave requests
        setShowModal(false);
        await loadLeaveRequests();
      } else {
        showToast("error", response.message || "❌ Gagal mengirim pengajuan cuti");
      }
    } catch (error: any) {
      console.error("Error submitting leave request:", error);
      showToast(
        "error",
        error.response?.data?.message || "❌ Gagal mengirim pengajuan cuti"
      );
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleInputChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const getStatusColor = (status: string) => {
    const statusLower = status.toLowerCase();
    if (statusLower.includes("disetujui") || statusLower.includes("approved")) {
      return "bg-green-100 text-green-800";
    } else if (statusLower.includes("ditolak") || statusLower.includes("rejected")) {
      return "bg-red-100 text-red-800";
    } else {
      return "bg-yellow-100 text-yellow-800";
    }
  };

  const getStatusText = (status: string) => {
    // Simplify complex status text
    const statusLower = status.toLowerCase();
    if (statusLower.includes("disetujui")) {
      return "Disetujui";
    } else if (statusLower.includes("ditolak")) {
      return "Ditolak";
    } else if (statusLower.includes("ditinjau")) {
      return "Sedang Ditinjau";
    } else {
      return status;
    }
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const months = [
      "Jan", "Feb", "Mar", "Apr", "Mei", "Jun",
      "Jul", "Agu", "Sep", "Okt", "Nov", "Des"
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

  // Filter data for "Ajukan Cuti" section (pending/in review)
  const pendingRequests = leaveRequests.filter(req => 
    req.status_pengajuan.toLowerCase().includes("ditinjau") ||
    req.status_pengajuan.toLowerCase().includes("menunggu")
  );

  // Filter data for "Riwayat" section (approved/rejected)
  const historyRequests = leaveRequests.filter(req => 
    req.status_pengajuan.toLowerCase().includes("disetujui") ||
    req.status_pengajuan.toLowerCase().includes("ditolak")
  );

  // Search and filter for Ajukan Cuti
  const filteredAjukan = pendingRequests.filter(req =>
    req.tipe_cuti.toLowerCase().includes(searchAjukan.toLowerCase()) ||
    req.alasan_pendukung.toLowerCase().includes(searchAjukan.toLowerCase())
  );

  // Search and filter for Riwayat
  const filteredRiwayat = historyRequests.filter(req =>
    req.tipe_cuti.toLowerCase().includes(searchRiwayat.toLowerCase()) ||
    req.alasan_pendukung.toLowerCase().includes(searchRiwayat.toLowerCase())
  );

  // Pagination for Ajukan Cuti
  const totalPagesAjukan = Math.ceil(filteredAjukan.length / entriesAjukan);
  const paginatedAjukan = filteredAjukan.slice(
    (currentPageAjukan - 1) * entriesAjukan,
    currentPageAjukan * entriesAjukan
  );

  // Pagination for Riwayat
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
    <div className="w-full max-w-md mx-auto min-h-screen bg-zinc-100 relative overflow-auto pb-6">
      {/* Header */}
      <div className="px-6 pt-6 pb-4 flex items-center gap-4">
          <button
            onClick={() => router.back()}
          className="w-6 h-6 flex items-center justify-center hover:bg-gray-200 rounded transition text-black"
        >
          <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fillRule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clipRule="evenodd" />
            </svg>
          </button>
        <h1 className="text-xl font-medium font-['Poppins'] text-black">Pengajuan Cuti</h1>
      </div>

      {/* Ajukan Cuti Section */}
      <div className="mx-4 mb-6 bg-white rounded-[10px] shadow-md border border-black/20 p-4">
        <h2 className="text-base font-normal font-['Poppins'] mb-4 text-black">Ajukan Cuti</h2>
        
        {/* Tombol Tambah Baru */}
        <button
          onClick={() => setShowModal(true)}
          className="bg-sky-800 rounded-[5px] px-4 py-2 flex items-center gap-2 hover:bg-sky-900 transition mb-4"
        >
          <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
          </svg>
          <span className="text-white text-xs font-['Poppins']">Tambah Baru</span>
        </button>

        {/* Search and Show Entries */}
        <div className="flex justify-between items-center mb-3 text-[10px] text-black">
          <div className="flex items-center gap-1.5">
            <span className="font-['Poppins']">Show</span>
            <select
              value={entriesAjukan}
              onChange={(e) => setEntriesAjukan(Number(e.target.value))}
              className="w-8 h-3.5 px-1 bg-white rounded-sm border border-black/30 text-[10px] font-['Poppins'] text-black"
            >
              <option value={5}>5</option>
              <option value={10}>10</option>
              <option value={25}>25</option>
            </select>
            <span className="font-['Poppins']">entries</span>
          </div>
          <div className="flex items-center gap-0.5">
            <span className="font-['Poppins']">Search:</span>
            <input
              type="text"
              value={searchAjukan}
              onChange={(e) => setSearchAjukan(e.target.value)}
              className="w-20 h-4 px-2 bg-white rounded-sm border border-black/20 text-[10px] text-black"
            />
          </div>
        </div>

        {/* Table */}
        <div className="overflow-x-auto">
          <table className="w-full text-[9px] border-collapse">
            <thead>
              <tr className="bg-gray-100 border-b">
                <th className="p-2 text-left font-bold font-['Inter'] text-black whitespace-nowrap">Tanggal Pengajuan</th>
                <th className="p-2 text-left font-bold font-['Inter'] text-black">Nama</th>
                <th className="p-2 text-left font-bold font-['Inter'] text-black whitespace-nowrap">Tipe Pengajuan</th>
                <th className="p-2 text-left font-bold font-['Inter'] text-black">Durasi</th>
                <th className="p-2 text-left font-bold font-['Inter'] text-black whitespace-nowrap">Alasan Pendukung</th>
                <th className="p-2 text-left font-bold font-['Inter'] text-black whitespace-nowrap">File Pendukung</th>
                <th className="p-2 text-left font-bold font-['Inter'] text-black">Status</th>
              </tr>
            </thead>
             <tbody>
               {paginatedAjukan.length === 0 ? (
                 <tr>
                   <td colSpan={7} className="p-4 text-center text-black">
                     Tidak ada pengajuan cuti yang sedang diproses
                   </td>
                 </tr>
               ) : (
                 paginatedAjukan.map((request) => (
                   <tr key={request.id} className="border-b hover:bg-gray-50">
                     <td className="p-2 font-['Inter'] text-black whitespace-nowrap">{formatDate(request.created_at)}</td>
                     <td className="p-2 font-['Inter'] text-black">
                       {request.user?.profile_pribadi?.nama_lengkap || request.user?.email || '-'}
                     </td>
                     <td className="p-2 font-['Inter'] capitalize text-black">{request.tipe_cuti}</td>
                     <td className="p-2 font-['Inter'] text-black whitespace-nowrap">
                       {calculateDays(request.tanggal_mulai, request.tanggal_selesai)} hari
                     </td>
                     <td className="p-2 font-['Inter'] text-black max-w-[150px] truncate" title={request.alasan_pendukung}>
                       {request.alasan_pendukung || '-'}
                     </td>
                     <td className="p-2 font-['Inter'] text-black text-center">
                       {request.file_pendukung ? (
                         <a 
                           href={`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/storage/${request.file_pendukung}`}
                           target="_blank"
                           rel="noopener noreferrer"
                           className="text-sky-800 hover:text-sky-900 underline"
                         >
                           Lihat
                         </a>
                       ) : (
                         '-'
                       )}
                     </td>
                     <td className="p-2">
                       <span className={`px-2 py-0.5 rounded text-[8px] whitespace-nowrap ${getStatusColor(request.status_pengajuan)}`}>
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
        <div className="flex justify-between items-center mt-3 text-[10px] text-black">
          <span className="font-['Poppins']">
            Showing {filteredAjukan.length === 0 ? 0 : (currentPageAjukan - 1) * entriesAjukan + 1} to{' '}
            {Math.min(currentPageAjukan * entriesAjukan, filteredAjukan.length)} of {filteredAjukan.length} entries
          </span>
          <div className="flex items-center gap-px bg-zinc-800/10 rounded-sm border border-black/5">
            <button
              onClick={() => setCurrentPageAjukan(Math.max(1, currentPageAjukan - 1))}
              disabled={currentPageAjukan === 1}
              className="px-2 py-1 text-sky-800 text-[9px] hover:bg-gray-200 disabled:opacity-50"
            >
              Previous
            </button>
            <span className="px-2 py-1 bg-sky-800 text-white text-[10px]">{currentPageAjukan}</span>
            <button
              onClick={() => setCurrentPageAjukan(Math.min(totalPagesAjukan, currentPageAjukan + 1))}
              disabled={currentPageAjukan === totalPagesAjukan || totalPagesAjukan === 0}
              className="px-2 py-1 text-sky-800 text-[9px] hover:bg-gray-200 disabled:opacity-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>

      {/* Riwayat Ajuan Section */}
      <div className="mx-4 mb-6 bg-white rounded-[10px] shadow-md border border-black/20 p-4">
        <h2 className="text-base font-normal font-['Poppins'] mb-4 text-black">Riwayat Ajuan</h2>

        {/* Search and Show Entries */}
        <div className="flex justify-between items-center mb-3 text-[10px] text-black">
          <div className="flex items-center gap-1.5">
            <span className="font-['Poppins']">Show</span>
            <select
              value={entriesRiwayat}
              onChange={(e) => setEntriesRiwayat(Number(e.target.value))}
              className="w-8 h-3.5 px-1 bg-white rounded-sm border border-black/30 text-[10px] font-['Poppins'] text-black"
            >
              <option value={5}>5</option>
              <option value={10}>10</option>
              <option value={25}>25</option>
            </select>
            <span className="font-['Poppins']">entries</span>
          </div>
          <div className="flex items-center gap-0.5">
            <span className="font-['Poppins']">Search:</span>
            <input
              type="text"
              value={searchRiwayat}
              onChange={(e) => setSearchRiwayat(e.target.value)}
              className="w-20 h-4 px-2 bg-white rounded-sm border border-black/20 text-[10px] text-black"
            />
        </div>
      </div>

        {/* Table */}
        <div className="overflow-x-auto">
          <table className="w-full text-[9px] border-collapse">
            <thead>
              <tr className="bg-gray-100 border-b">
                <th className="p-2 text-left font-bold font-['Inter'] text-black whitespace-nowrap">Tanggal Pengajuan</th>
                <th className="p-2 text-left font-bold font-['Inter'] text-black">Nama</th>
                <th className="p-2 text-left font-bold font-['Inter'] text-black whitespace-nowrap">Tipe Pengajuan</th>
                <th className="p-2 text-left font-bold font-['Inter'] text-black">Durasi</th>
                <th className="p-2 text-left font-bold font-['Inter'] text-black whitespace-nowrap">Alasan Pendukung</th>
                <th className="p-2 text-left font-bold font-['Inter'] text-black whitespace-nowrap">File Pendukung</th>
                <th className="p-2 text-left font-bold font-['Inter'] text-black">Status</th>
              </tr>
            </thead>
             <tbody>
               {paginatedRiwayat.length === 0 ? (
                 <tr>
                   <td colSpan={7} className="p-4 text-center text-black">
                     Belum ada riwayat pengajuan cuti
                   </td>
                 </tr>
               ) : (
                 paginatedRiwayat.map((request) => (
                   <tr key={request.id} className="border-b hover:bg-gray-50">
                     <td className="p-2 font-['Inter'] text-black whitespace-nowrap">{formatDate(request.created_at)}</td>
                     <td className="p-2 font-['Inter'] text-black">
                       {request.user?.profile_pribadi?.nama_lengkap || request.user?.email || '-'}
                     </td>
                     <td className="p-2 font-['Inter'] capitalize text-black">{request.tipe_cuti}</td>
                     <td className="p-2 font-['Inter'] text-black whitespace-nowrap">
                       {calculateDays(request.tanggal_mulai, request.tanggal_selesai)} hari
                     </td>
                     <td className="p-2 font-['Inter'] text-black max-w-[150px] truncate" title={request.alasan_pendukung}>
                       {request.alasan_pendukung || '-'}
                     </td>
                     <td className="p-2 font-['Inter'] text-black text-center">
                       {request.file_pendukung ? (
                         <a 
                           href={`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/storage/${request.file_pendukung}`}
                           target="_blank"
                           rel="noopener noreferrer"
                           className="text-sky-800 hover:text-sky-900 underline"
                         >
                           Lihat
                         </a>
                       ) : (
                         '-'
                       )}
                     </td>
                     <td className="p-2">
                       <span className={`px-2 py-0.5 rounded text-[8px] whitespace-nowrap ${getStatusColor(request.status_pengajuan)}`}>
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
        <div className="flex justify-between items-center mt-3 text-[10px] text-black">
          <span className="font-['Poppins']">
            Showing {filteredRiwayat.length === 0 ? 0 : (currentPageRiwayat - 1) * entriesRiwayat + 1} to{' '}
            {Math.min(currentPageRiwayat * entriesRiwayat, filteredRiwayat.length)} of {filteredRiwayat.length} entries
          </span>
          <div className="flex items-center gap-px bg-zinc-800/10 rounded-sm border border-black/5">
          <button
              onClick={() => setCurrentPageRiwayat(Math.max(1, currentPageRiwayat - 1))}
              disabled={currentPageRiwayat === 1}
              className="px-2 py-1 text-sky-800 text-[9px] hover:bg-gray-200 disabled:opacity-50"
            >
              Previous
          </button>
            <span className="px-2 py-1 bg-sky-800 text-white text-[10px]">{currentPageRiwayat}</span>
          <button
              onClick={() => setCurrentPageRiwayat(Math.min(totalPagesRiwayat, currentPageRiwayat + 1))}
              disabled={currentPageRiwayat === totalPagesRiwayat || totalPagesRiwayat === 0}
              className="px-2 py-1 text-sky-800 text-[9px] hover:bg-gray-200 disabled:opacity-50"
            >
              Next
          </button>
        </div>
        </div>
      </div>

      {/* Modal Form Tambah Cuti */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div className="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center rounded-t-2xl">
              <h3 className="text-lg font-semibold font-['Poppins'] text-black">Form Pengajuan Cuti</h3>
              <button
                onClick={() => setShowModal(false)}
                className="text-gray-400 hover:text-gray-600 transition"
              >
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
      </div>

            <form onSubmit={handleSubmit} className="p-6 space-y-4">
            {/* Jenis Cuti */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2 font-['Poppins']">
                Jenis Cuti
              </label>
              <select
                name="jenis_cuti"
                value={formData.jenis_cuti}
                onChange={handleInputChange}
                  className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-800 capitalize text-black"
                required
              >
                <option value="cuti tahunan">Cuti Tahunan</option>
                <option value="cuti melahirkan">Cuti Melahirkan</option>
                <option value="cuti nikah">Cuti Nikah</option>
                <option value="cuti kematian">Cuti Kematian</option>
                <option value="cuti bersama">Cuti Bersama</option>
                <option value="cuti pemotongan gaji">Cuti Pemotongan Gaji</option>
                <option value="cuti lainnya">Cuti Lainnya</option>
              </select>
            </div>

            {/* Tanggal Mulai */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2 font-['Poppins'] ">
                Tanggal Mulai
              </label>
              <input
                type="date"
                name="tanggal_mulai"
                value={formData.tanggal_mulai}
                onChange={handleInputChange}
                min={new Date().toISOString().split("T")[0]}
                  className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-800 text-black"
                required
              />
            </div>

            {/* Tanggal Selesai */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2 font-['Poppins']">
                Tanggal Selesai
              </label>
              <input
                type="date"
                name="tanggal_selesai"
                value={formData.tanggal_selesai}
                onChange={handleInputChange}
                min={formData.tanggal_mulai || new Date().toISOString().split("T")[0]}
                  className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-800 text-black"
                required
              />
            </div>

            {/* Durasi Cuti */}
            {formData.tanggal_mulai && formData.tanggal_selesai && (
                <div className="bg-blue-50 rounded-xl p-4 border border-blue-200">
                <div className="flex items-center justify-between">
                    <span className="text-sm font-semibold text-gray-700 font-['Poppins']">
                    Durasi Cuti
                  </span>
                    <span className="text-2xl font-bold text-sky-800">
                    {calculateDays(formData.tanggal_mulai, formData.tanggal_selesai)} hari
                  </span>
                </div>
              </div>
            )}

            {/* Alasan */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2 font-['Poppins']">
                Alasan Cuti
              </label>
              <textarea
                name="alasan"
                value={formData.alasan}
                onChange={handleInputChange}
                rows={4}
                placeholder="Jelaskan alasan pengajuan cuti Anda..."
                  className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-800 resize-none text-black"
                required
              />
            </div>

            {/* Submit Button */}
              <div className="flex gap-3 pt-4">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-300 transition"
                >
                  Batal
                </button>
            <button
              type="submit"
              disabled={isSubmitting}
                  className="flex-1 bg-sky-800 text-white py-3 rounded-xl font-semibold hover:bg-sky-900 transition disabled:opacity-50 disabled:cursor-not-allowed"
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
                  Mengirim...
                </div>
              ) : (
                "Ajukan Cuti"
              )}
            </button>
              </div>
          </form>
        </div>
        </div>
      )}

      {/* Toast Notification */}
      {toastMessage.show && (
        <div className="fixed top-10 left-1/2 transform -translate-x-1/2 z-50 max-w-sm w-full px-4">
          <div
            className={`p-4 rounded-lg shadow-2xl border-l-4 ${
              toastMessage.type === "success"
                ? "bg-green-50 border-green-500 text-green-800"
                : toastMessage.type === "error"
                ? "bg-red-50 border-red-500 text-red-800"
                : toastMessage.type === "warning"
                ? "bg-yellow-50 border-yellow-500 text-yellow-800"
                : "bg-blue-50 border-blue-500 text-blue-800"
            }`}
          >
            <div className="flex items-start">
              <div className="flex-shrink-0">
                {toastMessage.type === "success" && (
                  <span className="text-green-500 text-xl">✅</span>
                )}
                {toastMessage.type === "error" && (
                  <span className="text-red-500 text-xl">❌</span>
                )}
                {toastMessage.type === "warning" && (
                  <span className="text-yellow-500 text-xl">⚠️</span>
                )}
                {toastMessage.type === "info" && (
                  <span className="text-blue-500 text-xl">ℹ️</span>
                )}
              </div>
              <div className="ml-3 flex-1">
                <p className="text-sm font-medium whitespace-pre-line">
                  {toastMessage.message}
                </p>
              </div>
              <div className="ml-4 flex-shrink-0">
                <button
                  onClick={() =>
                    setToastMessage((prev) => ({ ...prev, show: false }))
                  }
                  className={`inline-flex rounded-md p-1.5 ${
                    toastMessage.type === "success"
                      ? "text-green-500 hover:bg-green-100"
                      : toastMessage.type === "error"
                      ? "text-red-500 hover:bg-red-100"
                      : toastMessage.type === "warning"
                      ? "text-yellow-500 hover:bg-yellow-100"
                      : "text-blue-500 hover:bg-blue-100"
                  }`}
                >
                  <span className="sr-only">Close</span>
                  <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      fillRule="evenodd"
                      d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                      clipRule="evenodd"
                    />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

