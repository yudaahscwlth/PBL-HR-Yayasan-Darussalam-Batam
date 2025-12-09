"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { ArrowLeft, Download } from "lucide-react";
import { useAuthStore } from "@/store/authStore";
import { apiClient } from "@/lib/api";
import AccessControl from "@/components/AccessControl";
import toast from "react-hot-toast";

interface SlipGaji {
  id: number;
  id_user: number;
  tanggal: string;
  total_gaji: number;
  keterangan: string | null;
  created_at: string;
  updated_at: string;
}

export default function HRDSlipGajiRiwayat() {
  const router = useRouter();
  const { user } = useAuthStore();
  const [slipGajiData, setSlipGajiData] = useState<SlipGaji[]>([]);
  const [employeeData, setEmployeeData] = useState<any>(null);
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
    if (!user?.id) return;
    
    try {
      setIsLoading(true);
      const [historyResponse, employeeResponse] = await Promise.all([
        apiClient.slipGaji.getUserHistory(user.id),
        apiClient.slipGaji.getEmployeeData(user.id),
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
        setEmployeeData(employeeResponse.data);
      }
    } catch (error) {
      console.error("Error loading data:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const formatDate = (dateString: string) => {
    if (!dateString) return "-";
    try {
      const date = new Date(dateString);
      if (isNaN(date.getTime())) return dateString;
      
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    } catch (error) {
      return dateString;
    }
  };

  const formatCurrency = (amount: number) => {
    if (isNaN(amount) || amount === null || amount === undefined) {
      return "Rp 0";
    }
    
    const numAmount = typeof amount === 'number' ? amount : parseFloat(amount);
    
    if (isNaN(numAmount)) {
      return "Rp 0";
    }
    
    try {
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

  // Prevent hydration mismatch by not rendering until mounted
  if (!isMounted) {
    return (
      <AccessControl allowedRoles={["kepala hrd", "staff hrd"]}>
        <div className="min-h-screen bg-gray-100 pb-28 flex items-center justify-center">
          <div className="text-center">
            <div className="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
            <p className="text-gray-600">Memuat...</p>
          </div>
        </div>
      </AccessControl>
    );
  }

  return (
    <AccessControl allowedRoles={["kepala hrd", "staff hrd"]}>
      <div className="min-h-screen bg-gray-100 pb-28">
        <div className="px-5 py-6">
          {/* Header */}
          <div className="flex items-center gap-4 mb-6">
            <button
              onClick={() => router.back()}
              className="p-2 hover:bg-gray-200 rounded-full transition-colors"
              aria-label="Kembali"
            >
              <ArrowLeft className="w-6 h-6 text-gray-700" />
            </button>
            <h1 className="text-2xl font-bold text-gray-800">Riwayat Slip Gaji</h1>
          </div>

          {/* Employee Data Section */}
          {employeeData && (
            <div className="bg-white rounded-xl shadow-sm p-6 mb-6">
              <h2 className="text-lg font-semibold text-gray-800 mb-4">Data Karyawan</h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Nama</p>
                  <p className="text-base font-medium text-gray-800">
                    {employeeData.nama || "-"}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">NIK</p>
                  <p className="text-base font-medium text-gray-800">
                    {employeeData.nik || "-"}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Tempat, Tgl Lahir</p>
                  <p className="text-base font-medium text-gray-800">
                    {employeeData.tempat_lahir && employeeData.tanggal_lahir
                      ? `${employeeData.tempat_lahir}, ${formatDate(employeeData.tanggal_lahir)}`
                      : "-"}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Departemen</p>
                  <p className="text-base font-medium text-gray-800">
                    {employeeData.departemen || "-"}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Jabatan</p>
                  <p className="text-base font-medium text-gray-800">
                    {employeeData.jabatan || "-"}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Nomor Rekening</p>
                  <p className="text-base font-medium text-gray-800">
                    {employeeData.nomor_rekening || "-"}
                  </p>
                </div>
              </div>
            </div>
          )}

          {/* Slip Gaji History Table */}
          <div className="bg-white rounded-xl shadow-sm overflow-hidden">
            <div className="p-4 border-b">
              <h2 className="text-lg font-semibold text-gray-800">Riwayat Slip Gaji</h2>
            </div>
            <div className="overflow-x-auto">
              {isLoading ? (
                <div className="p-8 text-center">
                  <div className="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                  <p className="text-gray-600">Memuat data...</p>
                </div>
              ) : slipGajiData.length === 0 ? (
                <div className="p-8 text-center text-gray-500">
                  <p>Tidak ada data slip gaji</p>
                </div>
              ) : (
                <>
                  <table className="w-full text-sm">
                    <thead>
                      <tr className="bg-gray-50 border-b">
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">No</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Tanggal</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Gaji Per Bulan</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Keterangan</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      {slipGajiData.map((item, index) => (
                        <tr key={item.id} className="border-b hover:bg-gray-50">
                          <td className="py-3 px-4 text-gray-700">{index + 1}</td>
                          <td className="py-3 px-4 text-gray-700">
                            {formatDate(item.tanggal)}
                          </td>
                          <td className="py-3 px-4 text-gray-700 font-semibold">
                            {formatCurrency(item.total_gaji)}
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
                      ))}
                    </tbody>
                  </table>
                  
                  {/* Total Gaji */}
                  {slipGajiData.length > 0 && (
                    <div className="border-t bg-gray-50 px-4 py-4">
                      <div className="flex justify-between items-center">
                        <span className="text-sm font-semibold text-gray-700">
                          Total Gaji:
                        </span>
                        <span className="text-sm font-bold text-blue-600">
                          {formatCurrency(
                            slipGajiData.reduce((sum, item) => {
                              const gaji = typeof item.total_gaji === 'number' ? item.total_gaji : parseFloat(item.total_gaji) || 0;
                              return sum + gaji;
                            }, 0)
                          )}
                        </span>
                      </div>
                      <div className="mt-1 text-xs text-gray-600">
                        Jumlah slip gaji: {slipGajiData.length} bulan
                      </div>
                    </div>
                  )}
                </>
              )}
            </div>
          </div>
        </div>
      </div>
    </AccessControl>
  );
}
