"use client";

import { useState, useEffect } from "react";
import { useRouter, useParams } from "next/navigation";
import { ArrowLeft, X } from "lucide-react";
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
  user?: {
    id: number;
    email: string;
    profile_pribadi?: {
      nama_lengkap: string;
      nomor_induk_kependudukan: string;
      tempat_lahir: string;
      tanggal_lahir: string;
    };
    profile_pekerjaan?: {
      departemen?: {
        nama: string;
      };
      jabatan?: {
        nama: string;
      };
    };
  };
}

export default function HRDSlipGajiHistory() {
  const router = useRouter();
  const params = useParams();
  const userId = parseInt(params.userId as string);
  const [slipGajiData, setSlipGajiData] = useState<SlipGaji[]>([]);
  const [employeeData, setEmployeeData] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isMounted, setIsMounted] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [formData, setFormData] = useState({
    tanggal: "",
    total_gaji: "",
    keterangan: "",
  });

  // Prevent hydration mismatch
  useEffect(() => {
    setIsMounted(true);
  }, []);

  useEffect(() => {
    if (userId) {
      loadData();
    }
  }, [userId]);

  const loadData = async () => {
    try {
      setIsLoading(true);
      const [historyResponse, employeeResponse] = await Promise.all([
        apiClient.slipGaji.getUserHistory(userId),
        apiClient.slipGaji.getEmployeeData(userId),
      ]);

      if (historyResponse.success && historyResponse.data) {
        const data = Array.isArray(historyResponse.data)
          ? historyResponse.data
          : (historyResponse.data as any).data || [];

        // Ensure total_gaji is a number
        const processedData = data.map((item: any) => ({
          ...item,
          total_gaji:
            typeof item.total_gaji === "number"
              ? item.total_gaji
              : parseFloat(item.total_gaji) || 0,
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

      // Format manual untuk menghindari hydration mismatch
      const day = String(date.getDate()).padStart(2, "0");
      const month = String(date.getMonth() + 1).padStart(2, "0");
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
    const numAmount = typeof amount === "number" ? amount : parseFloat(amount);

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

  const handleEdit = async (item: SlipGaji) => {
    setEditingId(item.id);

    // Set form data
    setFormData({
      tanggal: item.tanggal.split("T")[0],
      total_gaji: item.total_gaji.toString(),
      keterangan: item.keterangan || "",
    });

    setShowEditModal(true);
  };

  const handleCloseModal = () => {
    setShowEditModal(false);
    setEditingId(null);
    setFormData({
      tanggal: "",
      total_gaji: "",
      keterangan: "",
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      if (!editingId) return;

      const data = {
        id_user: userId,
        tanggal: formData.tanggal,
        total_gaji: parseFloat(formData.total_gaji),
        keterangan: formData.keterangan || undefined,
      };

      await apiClient.slipGaji.update(editingId, data);
      await loadData();
      handleCloseModal();
      toast.success("Slip gaji berhasil diupdate");
    } catch (error: any) {
      console.error("Error updating slip gaji:", error);
      toast.error(
        error.response?.data?.message || "Gagal mengupdate slip gaji"
      );
    }
  };

  return (
    <AccessControl allowedRoles={["kepala hrd", "staff hrd"]}>
      <div className="min-h-screen bg-gray-100">
        <div className="px-5 py-6">
          <div className="flex items-center gap-4 mb-4">
            <button
              onClick={() => router.back()}
              className="p-2 hover:bg-gray-200 rounded-full transition-colors"
              aria-label="Kembali"
            >
              <ArrowLeft className="w-6 h-6 text-gray-700" />
            </button>
            <h1 className="text-2xl font-bold text-gray-800">
              Riwayat Slip Gaji
            </h1>
          </div>

          {/* Employee Info */}
          {employeeData && (
            <div className="bg-white rounded-xl shadow-sm p-6 mb-4 border border-gray-200">
              {/* Judul */}
              <h2 className="text-lg font-bold text-gray-800 mb-6 border-b border-gray-200 pb-3">
                Data Karyawan
              </h2>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="space-y-1">
                  <p className="text-sm font-semibold text-gray-600">Nama</p>
                  <p className="text-base font-medium text-black">
                    {employeeData.nama}
                  </p>
                </div>

                <div className="space-y-1">
                  <p className="text-sm font-semibold text-gray-600">NIK</p>
                  <p className="text-base font-medium text-black">
                    {employeeData.nik}
                  </p>
                </div>

                <div className="space-y-1">
                  <p className="text-sm font-semibold text-gray-600">
                    Tempat, Tanggal Lahir
                  </p>
                  <p className="text-base font-medium text-black">
                    {employeeData.tempat_lahir},{" "}
                    {isMounted && employeeData.tanggal_lahir
                      ? formatDate(employeeData.tanggal_lahir)
                      : "-"}
                  </p>
                </div>

                <div className="space-y-1">
                  <p className="text-sm font-semibold text-gray-600">
                    Departemen
                  </p>
                  <p className="text-base font-medium text-black">
                    {employeeData.departemen}
                  </p>
                </div>

                <div className="space-y-1">
                  <p className="text-sm font-semibold text-gray-600">Jabatan</p>
                  <p className="text-base font-medium text-black">
                    {employeeData.jabatan}
                  </p>
                </div>

                <div className="space-y-1">
                  <p className="text-sm font-semibold text-gray-600">
                    Nomor Rekening
                  </p>
                  <p className="text-base font-medium text-black">
                    {employeeData.nomor_rekening || "-"}
                  </p>
                </div>
              </div>
            </div>
          )}

          {/* Slip Gaji Table */}
          <div className="bg-white rounded-xl shadow-sm overflow-hidden">
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
                        <td className="py-3 px-4 text-gray-700">{index + 1}</td>
                        <td className="py-3 px-4 text-gray-700">
                          {isMounted ? formatDate(item.tanggal) : ""}
                        </td>
                        <td className="py-3 px-4 text-gray-700 font-semibold">
                          {isMounted ? formatCurrency(item.total_gaji) : ""}
                        </td>
                        <td className="py-3 px-4 text-gray-700">
                          {item.keterangan || "-"}
                        </td>
                        <td className="py-3 px-4">
                          <button
                            onClick={() => handleEdit(item)}
                            className="bg-yellow-500 text-white px-3 py-1 rounded text-xs hover:bg-yellow-600 transition-colors"
                          >
                            Edit
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
                        const gaji =
                          typeof item.total_gaji === "number"
                            ? item.total_gaji
                            : parseFloat(item.total_gaji) || 0;
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

      {/* Edit Modal */}
      {showEditModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-transparent">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
            <div className="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
              <h2 className="text-xl font-bold text-gray-800">
                Edit Slip Gaji
              </h2>
              <button
                onClick={handleCloseModal}
                className="p-1 hover:bg-gray-200 rounded-full transition-colors"
                aria-label="Tutup"
              >
                <X className="w-5 h-5 text-gray-600" />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="p-6">
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="date"
                    value={formData.tanggal}
                    onChange={(e) =>
                      setFormData({ ...formData, tanggal: e.target.value })
                    }
                    required
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Total Gaji <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="number"
                    value={formData.total_gaji}
                    onChange={(e) =>
                      setFormData({ ...formData, total_gaji: e.target.value })
                    }
                    required
                    min="0"
                    step="0.01"
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Masukkan total gaji"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Keterangan
                  </label>
                  <textarea
                    value={formData.keterangan}
                    onChange={(e) =>
                      setFormData({ ...formData, keterangan: e.target.value })
                    }
                    rows={3}
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Masukkan keterangan (opsional)"
                  />
                </div>
              </div>

              <div className="flex gap-3 mt-6">
                <button
                  type="button"
                  onClick={handleCloseModal}
                  className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </AccessControl>
  );
}
