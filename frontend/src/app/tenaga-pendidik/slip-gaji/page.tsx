"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { ArrowLeft, Download } from "lucide-react";
import { useAuthStore } from "@/store/authStore";
import { apiClient } from "@/lib/api";
import AccessControl from "@/components/AccessControl";
import toast from "react-hot-toast";
import BottomBack from "@/components/BottomBack";

interface SlipGaji {
  id: number;
  id_user: number;
  tanggal: string;
  total_gaji: number;
  keterangan: string | null;
  created_at: string;
  updated_at: string;
}

interface EmployeeData {
  id: number;
  nama: string;
  nik: string;
  tempat_lahir: string;
  tanggal_lahir: string;
  departemen: string;
  jabatan: string;
  nomor_rekening: string | null; // Dari profile_pribadi
}

export default function TPSlipGaji() {
  const router = useRouter();
  const { user } = useAuthStore();
  const [slipGajiData, setSlipGajiData] = useState<SlipGaji[]>([]);
  const [employeeData, setEmployeeData] = useState<EmployeeData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isMounted, setIsMounted] = useState(false);

  // Prevent hydration mismatch
  useEffect(() => {
    setIsMounted(true);
  }, []);

  useEffect(() => {
    if (user?.id && isMounted) {
      loadData();
    }
  }, [user, isMounted]);

  const loadData = async () => {
    try {
      setIsLoading(true);
      const [historyResponse, employeeResponse] = await Promise.all([
        apiClient.slipGaji.getUserHistory(user!.id).catch(err => {
          console.error("Error loading history:", err);
          return { success: false, data: [] };
        }),
        apiClient.slipGaji.getEmployeeData(user!.id).catch(err => {
          console.error("Error loading employee data:", err);
          return { success: false, data: null as EmployeeData | null };
        }),
      ]);

      if (historyResponse.success && historyResponse.data) {
        const data = Array.isArray(historyResponse.data) 
          ? historyResponse.data 
          : (historyResponse.data as any).data || [];
        
        // Ensure total_gaji is a number
        const processedData = data.map((item: any) => ({
          ...item,
          total_gaji: typeof item.total_gaji === 'number' 
            ? item.total_gaji 
            : parseFloat(item.total_gaji) || 0
        }));
        
        setSlipGajiData(processedData as SlipGaji[]);
      }

      if (employeeResponse.success && employeeResponse.data) {
        setEmployeeData(employeeResponse.data as EmployeeData);
      } else if (employeeResponse && !employeeResponse.success) {
        console.warn("Failed to load employee data:", employeeResponse);
        // Try to load from profile instead if getEmployeeData fails
        // This is a fallback - employee data is optional for viewing slip gaji
      }
    } catch (error: any) {
      console.error("Error loading data:", error);
      if (error.response?.status === 403) {
        console.error("Access denied. Make sure you have permission to view this data.");
      }
    } finally {
      setIsLoading(false);
    }
  };

  const formatDate = (dateString: string) => {
    if (!dateString) return "-";
    try {
      const date = new Date(dateString);
      if (isNaN(date.getTime())) return dateString;
      
      // Format manual untuk menghindari hydration mismatch
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    } catch (error) {
      return dateString;
    }
  };

  const formatCurrency = (amount: number) => {
    // Handle NaN and invalid numbers
    if (isNaN(amount) || amount === null || amount === undefined) {
      return "Rp 0";
    }
    
    // Ensure amount is a number
    const numAmount = typeof amount === 'number' ? amount : parseFloat(amount);
    
    if (isNaN(numAmount)) {
      return "Rp 0";
    }
    
    try {
      // Format manual untuk menghindari hydration mismatch
      const formatted = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
      }).format(numAmount);
      return formatted;
    } catch (error) {
      return `Rp ${numAmount.toLocaleString("id-ID")}`;
    }
  };

  const handleDownloadPDF = async (slipGajiId: number) => {
    try {
      toast.loading("Mengunduh PDF...", { id: "download-pdf" });
      await apiClient.slipGaji.downloadPDF(slipGajiId);
      toast.success("PDF berhasil diunduh", { id: "download-pdf" });
    } catch (error: any) {
      console.error("Error downloading PDF:", error);
      toast.error(error?.message || "Gagal mengunduh PDF", { id: "download-pdf" });
    }
  };

  return (
    <AccessControl allowedRoles={["tenaga pendidik"]}>
      <div className="min-h-screen bg-gray-100 pb-28">
        {/* Header Section */}
        <div className="px-5 pt-3 pb-4">
          <div className="flex items-center gap-4">
            <BottomBack variant="inline" />
            <h1 className="text-xl font-bold text-gray-800">Slip Gaji</h1>
          </div>
        </div>

        {/* Main Content */}
        <div className="px-5">

          {/* Employee Info */}
          {employeeData && (
            <div className="bg-white rounded-xl shadow-sm p-6 mb-4">
              <h2 className="text-lg font-semibold text-gray-800 mb-4">
                Data Karyawan
              </h2>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <span className="text-gray-600">Nama:</span>{" "}
                  <span className="font-medium text-gray-700">{employeeData.nama}</span>
                </div>
                <div>
                  <span className="text-gray-600">NIK:</span>{" "}
                  <span className="font-medium text-gray-700">{employeeData.nik}</span>
                </div>
                <div>
                  <span className="text-gray-600">Tempat, Tgl Lahir:</span>{" "}
                    <span className="font-medium text-gray-700">
                    {employeeData.tempat_lahir},{" "}
                    {isMounted && employeeData.tanggal_lahir
                      ? formatDate(employeeData.tanggal_lahir)
                      : "-"}
                  </span>
                </div>
                <div>
                  <span className="text-gray-600">Departemen:</span>{" "}
                  <span className="font-medium text-gray-700">{employeeData.departemen}</span>
                </div>
                <div>
                  <span className="text-gray-600">Jabatan:</span>{" "}
                  <span className="font-medium text-gray-700">{employeeData.jabatan}</span>
                </div>
                <div>
                  <span className="text-gray-600">Nomor Rekening:</span>{" "}
                  <span className="font-medium text-gray-700">
                    {employeeData.nomor_rekening || "-"}
                  </span>
                </div>
              </div>
            </div>
          )}

          {/* Slip Gaji Table */}
          <div className="bg-white rounded-xl shadow-sm overflow-hidden">
            <div className="p-4 border-b">
              <h2 className="text-lg font-semibold text-gray-800">
                Riwayat Slip Gaji
              </h2>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="bg-gray-50 border-b">
                    <th className="text-left py-3 px-4 font-semibold text-gray-700">
                      No
                    </th>
                    <th className="text-left py-3 px-4 font-semibold text-gray-700">
                      Tanggal
                    </th>
                    <th className="text-left py-3 px-4 font-semibold text-gray-700">
                      Gaji Per Bulan
                    </th>
                    <th className="text-left py-3 px-4 font-semibold text-gray-700">
                      Keterangan
                    </th>
                    <th className="text-left py-3 px-4 font-semibold text-gray-700">
                      Aksi
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {isLoading ? (
                    <tr>
                      <td
                        colSpan={5}
                        className="text-center py-8 text-gray-500"
                      >
                        <div className="flex items-center justify-center">
                          <div className="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mr-2"></div>
                          Loading...
                        </div>
                      </td>
                    </tr>
                  ) : slipGajiData.length === 0 ? (
                    <tr>
                      <td
                        colSpan={5}
                        className="text-center py-8 text-gray-500"
                      >
                        Tidak ada data slip gaji
                      </td>
                    </tr>
                  ) : (
                    slipGajiData.map((item, index) => (
                      <tr key={item.id} className="border-b hover:bg-gray-50">
                        <td className="py-3 px-4 text-gray-700">
                          {index + 1}
                        </td>
                        <td className="py-3 px-4 text-gray-700">
                          {isMounted ? formatDate(item.tanggal) : ''}
                        </td>
                        <td className="py-3 px-4 text-gray-700 font-semibold">
                          {isMounted ? formatCurrency(item.total_gaji) : ''}
                        </td>
                        <td className="py-3 px-4 text-gray-700">
                          {item.keterangan || "-"}
                        </td>
                        <td className="py-3 px-4">
                          <button
                            onClick={() => handleDownloadPDF(item.id)}
                            className="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600 transition-colors flex items-center gap-1"
                            title="Download PDF"
                          >
                            <Download className="w-3 h-3" />
                            PDF
                          </button>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
            
            {/* Total Gaji */}
            {!isLoading && slipGajiData.length > 0 && (
              <div className="border-t bg-gray-50 px-4 py-4">
                <div className="flex justify-between items-center">
                  <span className="text-lg font-semibold text-gray-700">
                    Total Gaji:
                  </span>
                  <span className="text-lg font-bold text-blue-600">
                    {formatCurrency(
                      slipGajiData.reduce((sum, item) => {
                        const gaji = typeof item.total_gaji === 'number' ? item.total_gaji : parseFloat(item.total_gaji) || 0;
                        return sum + gaji;
                      }, 0)
                    )}
                  </span>
                </div>
                <div className="mt-2 text-sm text-gray-600">
                  Jumlah slip gaji: {slipGajiData.length} bulan
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </AccessControl>
  );
}
