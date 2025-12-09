"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { ArrowLeft, Upload, Download, FileSpreadsheet, X } from "lucide-react";
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

interface Employee {
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
      id: number;
      nama_departemen: string;
    };
    jabatan?: {
      id: number;
      nama_jabatan: string;
    };
  };
}

export default function HRDSlipGaji() {
  const router = useRouter();
  const { user } = useAuthStore();
  const [slipGajiData, setSlipGajiData] = useState<SlipGaji[]>([]);
  const [employees, setEmployees] = useState<Employee[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isMounted, setIsMounted] = useState(false);
  
  // Prevent hydration mismatch
  useEffect(() => {
    setIsMounted(true);
  }, []);
  
  // Debug: Log employees state changes
  useEffect(() => {
    if (isMounted) {
      console.log("Employees state changed:", employees.length, "employees");
      if (employees.length > 0) {
        console.log("First employee sample:", employees[0]);
      }
    }
  }, [employees, isMounted]);
  
  const [isLoadingEmployees, setIsLoadingEmployees] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage] = useState(10);
  const [selectedMonth, setSelectedMonth] = useState<number>(new Date().getMonth() + 1);
  const [selectedYear, setSelectedYear] = useState<number>(new Date().getFullYear());
  const [unpaidEmployees, setUnpaidEmployees] = useState<any[]>([]);
  const [paidEmployees, setPaidEmployees] = useState<any[]>([]);
  const [isLoadingPaymentStatus, setIsLoadingPaymentStatus] = useState(false);
  const [unpaidPageSize, setUnpaidPageSize] = useState<number>(5);
  const [paidPageSize, setPaidPageSize] = useState<number>(5);
  const [unpaidCurrentPage, setUnpaidCurrentPage] = useState<number>(1);
  const [paidCurrentPage, setPaidCurrentPage] = useState<number>(1);
  const [showModal, setShowModal] = useState(false);
  const [selectedUserId, setSelectedUserId] = useState<number | null>(null);
  const [employeeData, setEmployeeData] = useState<any>(null);
  const [showUploadModal, setShowUploadModal] = useState(false);
  const [uploadFile, setUploadFile] = useState<File | null>(null);
  const [isUploading, setIsUploading] = useState(false);
  const [uploadResult, setUploadResult] = useState<any>(null);
  const [mySlipGajiData, setMySlipGajiData] = useState<SlipGaji[]>([]);
  const [isLoadingMySlipGaji, setIsLoadingMySlipGaji] = useState(false);
  const [nomorRekeningManual, setNomorRekeningManual] = useState("");

  // Form state
  const [formData, setFormData] = useState({
    id_user: "",
    tanggal: "",
    total_gaji: "",
    keterangan: "",
  });

  // Fetch slip gaji data
  useEffect(() => {
    if (!isMounted) return;
    
    console.log("Component mounted, loading data...");
    const initializeData = async () => {
      await Promise.all([
        loadSlipGajiData(),
        loadEmployees(),
        loadEmployeesByPaymentStatus(),
        loadMySlipGajiData()
      ]);
    };
    initializeData();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isMounted]);

  // Load my slip gaji data when user changes
  useEffect(() => {
    if (user?.id && isMounted) {
      loadMySlipGajiData();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [user, isMounted]);

  // Reload payment status when month/year changes
  useEffect(() => {
    if (isMounted) {
      loadEmployeesByPaymentStatus();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedMonth, selectedYear]);

  const loadSlipGajiData = async () => {
    try {
      setIsLoading(true);
      const response = await apiClient.slipGaji.getAll();
      if (response.success && response.data) {
        const data = Array.isArray(response.data) 
          ? response.data 
          : (response.data as any).data || [];
        setSlipGajiData(data as SlipGaji[]);
      }
    } catch (error: any) {
      console.error("Error loading slip gaji data:", error);
      if (error.response?.data?.message) {
        console.error("Error message:", error.response.data.message);
      }
    } finally {
      setIsLoading(false);
    }
  };

  const loadMySlipGajiData = async () => {
    if (!user?.id) return;
    try {
      setIsLoadingMySlipGaji(true);
      const response = await apiClient.slipGaji.getUserHistory(user.id);
      
      if (response.success && response.data) {
        const data = Array.isArray(response.data) 
          ? response.data 
          : (response.data as any).data || [];
        
        // Ensure total_gaji is a number
        const processedData = data.map((item: any) => ({
          ...item,
          total_gaji: typeof item.total_gaji === 'number' 
            ? item.total_gaji 
            : parseFloat(item.total_gaji) || 0
        }));
        
        setMySlipGajiData(processedData as SlipGaji[]);
      }
    } catch (error: any) {
      console.error("Error loading my slip gaji data:", error);
    } finally {
      setIsLoadingMySlipGaji(false);
    }
  };

  const loadEmployees = async () => {
    try {
      setIsLoadingEmployees(true);
      console.log("Loading employees...");
      const response = await apiClient.users.getAll();
      console.log("Users API Response:", response);
      console.log("Response success:", response.success);
      console.log("Response data:", response.data);
      
      // Handle response - bisa jadi response langsung adalah data array
      // atau response memiliki struktur {success: true, data: [...]}
      // atau response memiliki struktur {data: [...]} tanpa success
      let employeesData: any[] = [];
      
      console.log("Checking response structure...");
      console.log("Is array?", Array.isArray(response));
      console.log("Has data property?", response && (response as any).data);
      console.log("Has success property?", response && (response as any).success);
      
      // Priority 1: Cek jika response memiliki data langsung (tanpa success) - KASUS INI!
      if (response && (response as any).data && Array.isArray((response as any).data)) {
        employeesData = (response as any).data;
        console.log("✓ Found data array directly:", employeesData.length);
      }
      // Priority 2: Cek jika response langsung adalah array
      else if (Array.isArray(response)) {
        employeesData = response;
        console.log("✓ Response is direct array:", employeesData.length);
      }
      // Priority 3: Cek jika response memiliki success dan data
      else if (response && (response as any).success && (response as any).data) {
        if (Array.isArray((response as any).data)) {
          employeesData = (response as any).data;
        } else if (typeof (response as any).data === 'object') {
          // Jika data adalah object, coba ambil array dari dalamnya
          if (Array.isArray((response as any).data.data)) {
            employeesData = (response as any).data.data;
          } else if (Array.isArray((response as any).data.users)) {
            employeesData = (response as any).data.users;
          } else {
            employeesData = Object.values((response as any).data);
          }
        }
        console.log("✓ Response has success property:", employeesData.length);
      }
      // Priority 4: Fallback: jika response adalah object dengan array di dalamnya
      else if (response && typeof response === 'object') {
        // Coba cari array di dalam response
        const keys = Object.keys(response);
        for (const key of keys) {
          if (Array.isArray((response as any)[key])) {
            employeesData = (response as any)[key];
            console.log(`✓ Found array in key "${key}":`, employeesData.length);
            break;
          }
        }
      }
      
      console.log("Final parsed employees data:", employeesData);
      console.log("Number of employees:", employeesData.length);
      
      if (employeesData.length > 0) {
        setEmployees(employeesData as Employee[]);
        console.log("✓ Employees state updated:", employeesData.length, "employees");
      } else {
        console.warn("⚠ No employees found after parsing");
        setEmployees([]);
      }
    } catch (error: any) {
      console.error("Error loading employees:", error);
      console.error("Error details:", {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status,
        stack: error.stack
      });
      setEmployees([]);
      
      // Show error to user
      if (error.response?.data?.message) {
        alert(`Error: ${error.response.data.message}`);
      } else if (error.message?.includes('Network Error')) {
        alert("Tidak dapat terhubung ke server. Pastikan backend server berjalan di http://localhost:8000");
      } else {
        alert("Gagal memuat data karyawan. Periksa console untuk detail error.");
      }
    } finally {
      setIsLoadingEmployees(false);
    }
  };

  const loadEmployeeData = async (userId: number) => {
    try {
      const response = await apiClient.slipGaji.getEmployeeData(userId);
      if (response.success && response.data) {
        setEmployeeData(response.data);
      }
    } catch (error) {
      console.error("Error loading employee data:", error);
    }
  };

  // Filter data
  const filteredData = slipGajiData.filter(
    (item) =>
      item.user?.profile_pribadi?.nama_lengkap
        ?.toLowerCase()
        .includes(searchTerm.toLowerCase()) ||
      item.user?.email?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      item.tanggal.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // Pagination
  const totalPages = Math.ceil(filteredData.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const currentData = filteredData.slice(startIndex, endIndex);

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
    if (!amount && amount !== 0) return "Rp 0";
    try {
      // Format manual untuk menghindari hydration mismatch
      const formatted = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
      }).format(amount);
      return formatted;
    } catch (error) {
      return `Rp ${amount.toLocaleString("id-ID")}`;
    }
  };

  const handleOpenModal = () => {
    setFormData({
      id_user: "",
      tanggal: "",
      total_gaji: "",
      keterangan: "",
    });
    setSelectedUserId(null);
    setEmployeeData(null);
    setShowModal(true);
  };

  const handleCloseModal = () => {
    setShowModal(false);
    setFormData({
      id_user: "",
      tanggal: "",
      total_gaji: "",
      keterangan: "",
    });
    setSelectedUserId(null);
    setEmployeeData(null);
    setNomorRekeningManual("");
  };

  const handleUserSelect = async (userId: number) => {
    setSelectedUserId(userId);
    setFormData((prev) => ({ ...prev, id_user: userId.toString() }));
    setNomorRekeningManual("");
    await loadEmployeeData(userId);
    // Nomor rekening akan diisi otomatis di loadEmployeeData
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      // Prepare slip gaji data
      const data = {
        id_user: parseInt(formData.id_user),
        tanggal: formData.tanggal,
        total_gaji: parseFloat(formData.total_gaji),
        keterangan: formData.keterangan || undefined,
        nomor_rekening: nomorRekeningManual || employeeData?.nomor_rekening || undefined,
      };

      // Create slip gaji
      await apiClient.slipGaji.create(data);

      // If nomor rekening was entered manually and employee doesn't have one in profile,
      // update the employee's profile with the new nomor rekening
      if (nomorRekeningManual && !employeeData?.nomor_rekening) {
        try {
          await apiClient.users.update(parseInt(formData.id_user), {
            nomor_rekening: nomorRekeningManual,
          });
          console.log("Nomor rekening berhasil disimpan ke profile karyawan");
        } catch (profileError: any) {
          console.error("Error updating profile with nomor rekening:", profileError);
          // Don't fail the entire operation if profile update fails
        }
      }

      await loadSlipGajiData();
      await loadEmployeesByPaymentStatus(); // Reload payment status
      handleCloseModal();
      toast.success("Slip gaji berhasil disimpan");
    } catch (error: any) {
      console.error("Error saving slip gaji:", error);
      toast.error(error.response?.data?.message || "Gagal menyimpan slip gaji");
    }
  };


  const handleDelete = async (id: number) => {
    if (!confirm("Apakah Anda yakin ingin menghapus slip gaji ini?")) {
      return;
    }

    try {
      await apiClient.slipGaji.delete(id);
      await loadSlipGajiData();
      await loadEmployeesByPaymentStatus(); // Reload payment status
      toast.success("Slip gaji berhasil dihapus");
    } catch (error: any) {
      console.error("Error deleting slip gaji:", error);
      toast.error(error.response?.data?.message || "Gagal menghapus slip gaji");
    }
  };

  const handleDownloadTemplate = async () => {
    try {
      setIsUploading(true);
      const blob = await apiClient.slipGaji.downloadTemplate();
      
      // Verify blob is valid
      if (!blob || blob.size === 0) {
        throw new Error("File yang didownload kosong");
      }
      
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = `template_import_slip_gaji_${new Date().toISOString().split("T")[0]}.xlsx`;
      document.body.appendChild(a);
      a.click();
      
      // Cleanup
      setTimeout(() => {
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
      }, 100);
      
      toast.success("Template berhasil didownload");
    } catch (error: any) {
      console.error("Error downloading template:", error);
      console.error("Error response:", error?.response);
      console.error("Error response data:", error?.response?.data);
      
      // Extract error message from various possible locations
      let errorMessage = "Gagal download template";
      
      if (error?.message) {
        errorMessage = error.message;
      } else if (error?.response?.data) {
        if (typeof error.response.data === 'string') {
          try {
            const json = JSON.parse(error.response.data);
            errorMessage = json.message || json.error?.message || errorMessage;
          } catch {
            errorMessage = error.response.data;
          }
        } else if (error.response.data.message) {
          errorMessage = error.response.data.message;
        } else if (error.response.data.error) {
          if (typeof error.response.data.error === 'string') {
            errorMessage = error.response.data.error;
          } else if (error.response.data.error.message) {
            errorMessage = error.response.data.error.message;
          }
        }
      }
      
      toast.error(errorMessage);
    } finally {
      setIsUploading(false);
    }
  };

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const validTypes = [
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/vnd.ms-excel",
      ];
      if (!validTypes.includes(file.type)) {
        toast.error("File harus berformat Excel (.xlsx atau .xls)");
        return;
      }
      if (file.size > 10 * 1024 * 1024) {
        toast.error("Ukuran file maksimal 10MB");
        return;
      }
      setUploadFile(file);
      setUploadResult(null);
    }
  };

  const handleUploadExcel = async () => {
    if (!uploadFile) {
      toast.error("Pilih file Excel terlebih dahulu");
      return;
    }

    setIsUploading(true);
    setUploadResult(null);

    try {
      const response = await apiClient.slipGaji.uploadExcel(uploadFile);
      
      if (response.success) {
        setUploadResult(response.data);
        toast.success(response.message);
        await loadSlipGajiData();
        await loadEmployeesByPaymentStatus(); // Reload payment status
        
        // Reset setelah 3 detik jika tidak ada error
        if (response.data.error_count === 0) {
          setTimeout(() => {
            setShowUploadModal(false);
            setUploadFile(null);
            setUploadResult(null);
          }, 2000);
        }
      } else {
        toast.error(response.message || "Gagal mengupload file");
        setUploadResult(response.data || null);
      }
    } catch (error: any) {
      console.error("Error uploading Excel:", error);
      console.error("Error response:", error?.response);
      console.error("Error response status:", error?.response?.status);
      console.error("Error response data:", error?.response?.data);
      console.error("Error response headers:", error?.response?.headers);
      
      // Extract error message
      let errorMessage = "Gagal mengupload file Excel";
      
      // Try to get error message from various sources
      if (error?.message) {
        errorMessage = error.message;
      }
      
      // Check response data
      if (error?.response?.data) {
        const errorData = error.response.data;
        
        // Handle different response formats
        if (typeof errorData === 'string') {
          try {
            const parsed = JSON.parse(errorData);
            if (parsed.message) {
              errorMessage = parsed.message;
            }
            if (parsed.data) {
              setUploadResult(parsed.data);
            }
          } catch {
            errorMessage = errorData;
          }
        } else if (typeof errorData === 'object') {
          if (errorData.message) {
            errorMessage = errorData.message;
          }
          
          // Handle Laravel validation errors
          if (errorData.errors) {
            const errorMessages = Object.values(errorData.errors).flat();
            errorMessage = Array.isArray(errorMessages) 
              ? errorMessages.join(", ") 
              : String(errorMessages);
          }
          
          // Set upload result if available
          if (errorData.data) {
            setUploadResult(errorData.data);
          } else if (errorData.errors && Array.isArray(errorData.errors)) {
            setUploadResult({
              error_count: errorData.errors.length,
              errors: errorData.errors.map((err: any, idx: number) => ({
                line: err.line || idx + 1,
                error: typeof err === 'string' ? err : (err.error || JSON.stringify(err)),
              })),
            });
          }
        }
      }
      
      // If error response is empty, check status code
      if (errorMessage === "Gagal mengupload file Excel" && error?.response?.status) {
        if (error.response.status === 400) {
          errorMessage = "Bad Request. Periksa format file Excel dan pastikan data sesuai template.";
        } else if (error.response.status === 422) {
          errorMessage = "Validasi gagal. Periksa format file Excel.";
        } else if (error.response.status === 403) {
          errorMessage = "Anda tidak memiliki izin untuk mengupload file Excel.";
        } else if (error.response.status === 500) {
          errorMessage = "Server error. Periksa log backend untuk detail error.";
        }
      }
      
      toast.error(errorMessage);
    } finally {
      setIsUploading(false);
    }
  };

  const handleViewHistory = (userId: number) => {
    router.push(`/hrd/slip-gaji/${userId}`);
  };

  const loadEmployeesByPaymentStatus = async () => {
    try {
      setIsLoadingPaymentStatus(true);
      const response = await apiClient.slipGaji.getEmployeesByPaymentStatus(selectedMonth, selectedYear);
      
      if (response.success && response.data) {
        setUnpaidEmployees(response.data.unpaid_employees || []);
        setPaidEmployees(response.data.paid_employees || []);
      }
    } catch (error: any) {
      console.error("Error loading employees by payment status:", error);
      toast.error("Gagal memuat data karyawan");
    } finally {
      setIsLoadingPaymentStatus(false);
    }
  };

  const handleCreateSlipGaji = async (employeeId: number) => {
    // Set form data untuk karyawan yang dipilih
    setSelectedUserId(employeeId);
    setFormData((prev) => ({
      ...prev,
      id_user: employeeId.toString(),
      tanggal: `${selectedYear}-${String(selectedMonth).padStart(2, '0')}-01`,
    }));
    
    // Load employee data
    await loadEmployeeData(employeeId);
    
    // Open modal
    setShowModal(true);
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
      <div className="min-h-screen bg-gray-100">
        <div className="px-5 py-6">
          <div className="flex items-center justify-between mb-4">
            <div className="flex items-center gap-4">
              <button
                onClick={() => router.back()}
                className="p-2 hover:bg-gray-200 rounded-full transition-colors"
                aria-label="Kembali"
              >
                <ArrowLeft className="w-6 h-6 text-gray-700" />
              </button>
              <h1 className="text-2xl font-bold text-gray-800">Slip Gaji</h1>
            </div>
            
            {/* Filter Bulan dan Tahun */}
            <div className="flex items-center gap-3">
              <div className="flex items-center gap-2">
                <label className="text-sm font-medium text-gray-700">Bulan:</label>
                <select
                  value={selectedMonth}
                  onChange={(e) => setSelectedMonth(Number(e.target.value))}
                  className="px-3 py-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                >
                  {Array.from({ length: 12 }, (_, i) => i + 1).map((month) => (
                    <option key={month} value={month}>
                      {new Date(selectedYear, month - 1).toLocaleDateString('id-ID', { month: 'long' })}
                    </option>
                  ))}
                </select>
              </div>
              <div className="flex items-center gap-2">
                <label className="text-sm font-medium text-gray-700">Tahun:</label>
                <select
                  value={selectedYear}
                  onChange={(e) => setSelectedYear(Number(e.target.value))}
                  className="px-3 py-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                >
                  {Array.from({ length: 10 }, (_, i) => new Date().getFullYear() - 5 + i).map((year) => (
                    <option key={year} value={year}>
                      {year}
                    </option>
                  ))}
                </select>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <button
                onClick={() => setShowUploadModal(true)}
                className="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2"
              >
                <Upload className="w-5 h-5" />
                Upload Excel
              </button>
              <button
                onClick={handleDownloadTemplate}
                disabled={isUploading}
                className="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors flex items-center gap-2 disabled:bg-gray-400 disabled:cursor-not-allowed"
              >
                {isUploading ? (
                  <>
                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    Downloading...
                  </>
                ) : (
                  <>
                    <Download className="w-5 h-5" />
                    Download Template
                  </>
                )}
              </button>
            </div>
          </div>

          {/* Tabel Karyawan Belum Digaji */}
          <div className="mb-6 bg-white rounded-xl shadow-sm overflow-hidden">
            <div className="p-4 border-b bg-yellow-50 flex items-center justify-between">
              <h2 className="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span className="w-3 h-3 bg-yellow-500 rounded-full"></span>
                Karyawan Belum Digaji ({unpaidEmployees.length})
              </h2>
              <div className="flex items-center gap-2">
                <label className="text-sm text-gray-700">Tampilkan:</label>
                <select
                  value={unpaidPageSize}
                  onChange={(e) => {
                    setUnpaidPageSize(Number(e.target.value));
                    setUnpaidCurrentPage(1);
                  }}
                  className="px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  {[1, 2, 3, 4, 5].map((size) => (
                    <option key={size} value={size}>
                      {size}
                    </option>
                  ))}
                </select>
                <span className="text-sm text-gray-700">per halaman</span>
              </div>
            </div>
            <div className="overflow-x-auto">
              {isLoadingPaymentStatus ? (
                <div className="p-8 text-center">
                  <div className="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                  <p className="text-gray-600">Memuat data...</p>
                </div>
              ) : unpaidEmployees.length === 0 ? (
                <div className="p-8 text-center text-gray-500">
                  <p>Semua karyawan sudah digaji untuk bulan ini</p>
                </div>
              ) : (
                <>
                  <table className="w-full text-sm">
                    <thead>
                      <tr className="bg-gray-50 border-b">
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">No</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Nama</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">NIK</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Departemen</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Jabatan</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      {unpaidEmployees
                        .slice((unpaidCurrentPage - 1) * unpaidPageSize, unpaidCurrentPage * unpaidPageSize)
                        .map((employee, index) => (
                          <tr key={employee.id} className="border-b hover:bg-gray-50">
                            <td className="py-3 px-4 text-gray-700">{(unpaidCurrentPage - 1) * unpaidPageSize + index + 1}</td>
                            <td className="py-3 px-4 text-gray-700">{employee.nama}</td>
                            <td className="py-3 px-4 text-gray-700">{employee.nik}</td>
                            <td className="py-3 px-4 text-gray-700">{employee.departemen}</td>
                            <td className="py-3 px-4 text-gray-700">{employee.jabatan}</td>
                            <td className="py-3 px-4">
                              <button
                                onClick={() => handleCreateSlipGaji(employee.id)}
                                className="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600 transition-colors"
                              >
                                Buat Slip Gaji
                              </button>
                            </td>
                          </tr>
                        ))}
                    </tbody>
                  </table>
                  
                  {/* Pagination untuk Karyawan Belum Digaji */}
                  {Math.ceil(unpaidEmployees.length / unpaidPageSize) > 1 && (
                    <div className="flex items-center justify-between p-4 border-t">
                      <div className="text-sm text-gray-600">
                        Menampilkan {(unpaidCurrentPage - 1) * unpaidPageSize + 1} sampai{" "}
                        {Math.min(unpaidCurrentPage * unpaidPageSize, unpaidEmployees.length)} dari{" "}
                        {unpaidEmployees.length} entri
                      </div>
                      <div className="flex items-center gap-2">
                        <button
                          onClick={() => setUnpaidCurrentPage((prev) => Math.max(prev - 1, 1))}
                          disabled={unpaidCurrentPage === 1}
                          className="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                        >
                          Sebelumnya
                        </button>
                        <span className="px-3 py-1 bg-blue-500 text-white rounded text-sm">
                          {unpaidCurrentPage} dari {Math.ceil(unpaidEmployees.length / unpaidPageSize)}
                        </span>
                        <button
                          onClick={() => setUnpaidCurrentPage((prev) => Math.min(prev + 1, Math.ceil(unpaidEmployees.length / unpaidPageSize)))}
                          disabled={unpaidCurrentPage >= Math.ceil(unpaidEmployees.length / unpaidPageSize)}
                          className="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                        >
                          Selanjutnya
                        </button>
                      </div>
                    </div>
                  )}
                </>
              )}
            </div>
          </div>

          {/* Tabel Karyawan Sudah Digaji */}
          <div className="mb-6 bg-white rounded-xl shadow-sm overflow-hidden">
            <div className="p-4 border-b bg-green-50 flex items-center justify-between">
              <h2 className="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span className="w-3 h-3 bg-green-500 rounded-full"></span>
                Karyawan Sudah Digaji ({paidEmployees.length})
              </h2>
              <div className="flex items-center gap-2">
                <label className="text-sm text-gray-700">Tampilkan:</label>
                <select
                  value={paidPageSize}
                  onChange={(e) => {
                    setPaidPageSize(Number(e.target.value));
                    setPaidCurrentPage(1);
                  }}
                  className="px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  {[1, 2, 3, 4, 5].map((size) => (
                    <option key={size} value={size}>
                      {size}
                    </option>
                  ))}
                </select>
                <span className="text-sm text-gray-700">per halaman</span>
              </div>
            </div>
            <div className="overflow-x-auto">
              {isLoadingPaymentStatus ? (
                <div className="p-8 text-center">
                  <div className="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                  <p className="text-gray-600">Memuat data...</p>
                </div>
              ) : paidEmployees.length === 0 ? (
                <div className="p-8 text-center text-gray-500">
                  <p>Belum ada karyawan yang digaji untuk bulan ini</p>
                </div>
              ) : (
                <>
                  <table className="w-full text-sm">
                    <thead>
                      <tr className="bg-gray-50 border-b">
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">No</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Nama</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">NIK</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Departemen</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Jabatan</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Tanggal</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Gaji Bulan Terakhir</th>
                        <th className="text-left py-3 px-4 font-semibold text-gray-700">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      {paidEmployees
                        .slice((paidCurrentPage - 1) * paidPageSize, paidCurrentPage * paidPageSize)
                        .map((employee, index) => (
                          <tr key={employee.id} className="border-b hover:bg-gray-50">
                            <td className="py-3 px-4 text-gray-700">{(paidCurrentPage - 1) * paidPageSize + index + 1}</td>
                            <td className="py-3 px-4 text-gray-700">{employee.nama}</td>
                            <td className="py-3 px-4 text-gray-700">{employee.nik}</td>
                            <td className="py-3 px-4 text-gray-700">{employee.departemen}</td>
                            <td className="py-3 px-4 text-gray-700">{employee.jabatan}</td>
                            <td className="py-3 px-4 text-gray-700">
                              {isMounted ? formatDate(employee.slip_gaji?.tanggal || '') : ''}
                            </td>
                            <td className="py-3 px-4 text-gray-700 font-semibold">
                              {isMounted ? formatCurrency(employee.slip_gaji?.total_gaji || 0) : ''}
                            </td>
                            <td className="py-3 px-4">
                              <button
                                onClick={() => handleViewHistory(employee.id)}
                                className="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600 transition-colors"
                              >
                                Riwayat
                              </button>
                            </td>
                          </tr>
                        ))}
                    </tbody>
                  </table>
                  
                  {/* Pagination untuk Karyawan Sudah Digaji */}
                  {Math.ceil(paidEmployees.length / paidPageSize) > 1 && (
                    <div className="flex items-center justify-between p-4 border-t">
                      <div className="text-sm text-gray-600">
                        Menampilkan {(paidCurrentPage - 1) * paidPageSize + 1} sampai{" "}
                        {Math.min(paidCurrentPage * paidPageSize, paidEmployees.length)} dari{" "}
                        {paidEmployees.length} entri
                      </div>
                      <div className="flex items-center gap-2">
                        <button
                          onClick={() => setPaidCurrentPage((prev) => Math.max(prev - 1, 1))}
                          disabled={paidCurrentPage === 1}
                          className="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                        >
                          Sebelumnya
                        </button>
                        <span className="px-3 py-1 bg-blue-500 text-white rounded text-sm">
                          {paidCurrentPage} dari {Math.ceil(paidEmployees.length / paidPageSize)}
                        </span>
                        <button
                          onClick={() => setPaidCurrentPage((prev) => Math.min(prev + 1, Math.ceil(paidEmployees.length / paidPageSize)))}
                          disabled={paidCurrentPage >= Math.ceil(paidEmployees.length / paidPageSize)}
                          className="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                        >
                          Selanjutnya
                        </button>
                      </div>
                    </div>
                  )}
                </>
              )}
            </div>
          </div>

          {/* Search Bar */}
          <div className="mb-4">
            <input
              type="text"
              placeholder="Cari nama, email, atau tanggal..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          {/* Riwayat Slip Gaji Saya */}
          <div className="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div className="p-4 border-b bg-blue-50">
              <h2 className="text-lg font-semibold text-gray-800">Riwayat Slip Gaji Saya</h2>
            </div>
            <div className="overflow-x-auto">
              {isLoadingMySlipGaji ? (
                <div className="p-8 text-center">
                  <div className="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                  <p className="text-gray-600">Memuat data...</p>
                </div>
              ) : mySlipGajiData.length === 0 ? (
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
                      </tr>
                    </thead>
                    <tbody>
                      {mySlipGajiData.map((item, index) => (
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
                        </tr>
                      ))}
                    </tbody>
                  </table>
                  
                  {/* Total Gaji */}
                  {mySlipGajiData.length > 0 && (
                    <div className="border-t bg-gray-50 px-4 py-4">
                      <div className="flex justify-between items-center">
                        <span className="text-sm font-semibold text-gray-700">
                          Total Gaji:
                        </span>
                        <span className="text-sm font-bold text-blue-600">
                          {formatCurrency(
                            mySlipGajiData.reduce((sum, item) => {
                              const gaji = typeof item.total_gaji === 'number' ? item.total_gaji : parseFloat(item.total_gaji) || 0;
                              return sum + gaji;
                            }, 0)
                          )}
                        </span>
                      </div>
                      <div className="mt-1 text-xs text-gray-600">
                        Jumlah slip gaji: {mySlipGajiData.length} bulan
                      </div>
                    </div>
                  )}
                </>
              )}
            </div>
          </div>

          {/* Data Table - Riwayat Semua Slip Gaji */}
          <div className="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div className="p-4 border-b">
              <h2 className="text-lg font-semibold text-gray-800">Riwayat Semua Slip Gaji</h2>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="bg-gray-50 border-b">
                    <th className="text-left py-3 px-4 font-semibold text-gray-700">
                      No
                    </th>
                    <th className="text-left py-3 px-4 font-semibold text-gray-700">
                      Nama
                    </th>
                    <th className="text-left py-3 px-4 font-semibold text-gray-700">
                      Tanggal
                    </th>
                    <th className="text-left py-3 px-4 font-semibold text-gray-700">
                      Gaji Bulan Terakhir
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
                  ) : currentData.length === 0 ? (
                    <tr>
                      <td
                        colSpan={5}
                        className="text-center py-8 text-gray-500"
                      >
                        Tidak ada data slip gaji
                      </td>
                    </tr>
                  ) : (
                    currentData.map((item, index) => (
                      <tr key={item.id} className="border-b hover:bg-gray-50">
                        <td className="py-3 px-4 text-gray-700">
                          {startIndex + index + 1}
                        </td>
                        <td className="py-3 px-4 text-gray-700">
                          {item.user?.profile_pribadi?.nama_lengkap ||
                            item.user?.email ||
                            "-"}
                        </td>
                        <td className="py-3 px-4 text-gray-700">
                          {formatDate(item.tanggal)}
                        </td>
                        <td className="py-3 px-4 text-gray-700 font-semibold">
                          {formatCurrency(item.total_gaji)}
                        </td>
                        <td className="py-3 px-4">
                          <div className="flex gap-2">
                            <button
                              onClick={() => handleViewHistory(item.id_user)}
                              className="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600 transition-colors"
                            >
                              Riwayat
                            </button>
                            <button
                              onClick={() => handleDelete(item.id)}
                              className="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600 transition-colors"
                            >
                              Hapus
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
            {totalPages > 1 && (
              <div className="flex items-center justify-between p-4 border-t">
                <div className="text-sm text-gray-600">
                  Menampilkan {startIndex + 1} sampai{" "}
                  {Math.min(endIndex, filteredData.length)} dari{" "}
                  {filteredData.length} entri
                </div>
                <div className="flex items-center gap-2">
                  <button
                    onClick={() =>
                      setCurrentPage((prev) => Math.max(prev - 1, 1))
                    }
                    disabled={currentPage === 1}
                    className="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                  >
                    Sebelumnya
                  </button>
                  <span className="px-3 py-1 bg-blue-500 text-white rounded text-sm">
                    {currentPage} dari {totalPages}
                  </span>
                  <button
                    onClick={() =>
                      setCurrentPage((prev) => Math.min(prev + 1, totalPages))
                    }
                    disabled={currentPage === totalPages}
                    className="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                  >
                    Selanjutnya
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Add Modal */}
        {showModal && (
          <div 
            className="fixed inset-0 bg-transparent flex items-center justify-center z-50 p-4 animate-fadeIn"
            onClick={(e) => {
              if (e.target === e.currentTarget) {
                handleCloseModal();
                setShowEditModal(false);
                setEditingId(null);
              }
            }}
          >
            <div className="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto animate-slideUp">
              {/* Modal Header */}
              <div className="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h2 className="text-xl font-bold text-gray-800">
                  Tambah Slip Gaji
                </h2>
                <button
                  onClick={handleCloseModal}
                  className="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100"
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

              <div className="p-6">

                {employeeData && (
                  <div className="mb-4 p-4 bg-gray-50 rounded-lg">
                    <h3 className="font-semibold text-gray-700 mb-2">
                      Data Karyawan
                    </h3>
                    <div className="grid grid-cols-2 gap-2 text-sm">
                      <div>
                        <span className="text-gray-600">Nama:</span>{" "}
                        <span className="font-medium">
                          {employeeData.nama}
                        </span>
                      </div>
                      <div>
                        <span className="text-gray-600">NIK:</span>{" "}
                        <span className="font-medium">{employeeData.nik}</span>
                      </div>
                      <div>
                        <span className="text-gray-600">Tempat, Tgl Lahir:</span>{" "}
                        <span className="font-medium">
                          {employeeData.tempat_lahir},{" "}
                          {employeeData.tanggal_lahir
                            ? formatDate(employeeData.tanggal_lahir)
                            : "-"}
                        </span>
                      </div>
                      <div>
                        <span className="text-gray-600">Departemen:</span>{" "}
                        <span className="font-medium">
                          {employeeData.departemen}
                        </span>
                      </div>
                      <div>
                        <span className="text-gray-600">Jabatan:</span>{" "}
                        <span className="font-medium">
                          {employeeData.jabatan}
                        </span>
                      </div>
                    </div>
                  </div>
                )}

                <form onSubmit={handleSubmit}>
                  <div className="space-y-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Pilih Karyawan *
                      </label>
                      <select
                        value={formData.id_user}
                        onChange={(e) => {
                          const userId = parseInt(e.target.value);
                          if (userId) {
                            handleUserSelect(userId);
                          }
                        }}
                        required
                        disabled={isLoadingEmployees}
                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                      >
                        <option value="">
                          {isLoadingEmployees 
                            ? "Memuat data karyawan..." 
                            : "-- Pilih Karyawan --"}
                        </option>
                        {!isLoadingEmployees && employees.length === 0 && (
                          <option value="" disabled>
                            Tidak ada data karyawan (Cek console untuk detail)
                          </option>
                        )}
                        {employees.map((emp) => (
                          <option key={emp.id} value={emp.id}>
                            {emp.profile_pribadi?.nama_lengkap || emp.email || `User #${emp.id}`}
                          </option>
                        ))}
                      </select>
                      {isLoadingEmployees && (
                        <p className="text-xs text-blue-500 mt-1 flex items-center gap-2">
                          <span className="animate-spin">⏳</span>
                          Memuat data karyawan...
                        </p>
                      )}
                      {!isLoadingEmployees && employees.length > 0 && (
                        <p className="text-xs text-green-600 mt-1">
                          ✓ {employees.length} karyawan tersedia
                        </p>
                      )}
                      {!isLoadingEmployees && employees.length === 0 && (
                        <p className="text-xs text-red-500 mt-1">
                          ⚠ Tidak ada data karyawan. Periksa console browser (F12) untuk detail.
                        </p>
                      )}
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal *
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
                        Total Gaji *
                      </label>
                      <input
                        type="number"
                        step="0.01"
                        min="0"
                        value={formData.total_gaji}
                        onChange={(e) =>
                          setFormData({ ...formData, total_gaji: e.target.value })
                        }
                        required
                        placeholder="0"
                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    </div>

                    {/* Nomor Rekening - Info dari profile */}
                    {employeeData?.nomor_rekening && (
                      <div className="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p className="text-sm text-blue-800">
                          <span className="font-medium">Nomor Rekening:</span> {employeeData.nomor_rekening} 
                          <span className="text-xs text-blue-600 ml-2">(Diambil dari profile karyawan)</span>
                        </p>
                      </div>
                    )}
                    {!employeeData?.nomor_rekening && selectedUserId && (
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                          Nomor Rekening *
                        </label>
                        <input
                          type="text"
                          value={nomorRekeningManual}
                          onChange={(e) => setNomorRekeningManual(e.target.value)}
                          required
                          placeholder="Masukkan nomor rekening"
                          className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p className="text-xs text-yellow-600 mt-1">
                          ⚠ Nomor rekening belum tersedia di profile karyawan. Silakan isi manual.
                        </p>
                      </div>
                    )}

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
                        placeholder="Keterangan (opsional)"
                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    </div>
                  </div>

                  <div className="flex gap-3 mt-6 pt-4 border-t border-gray-200">
                    <button
                      type="submit"
                      className="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-sm hover:shadow-md"
                    >
                      Simpan
                    </button>
                    <button
                      type="button"
                      onClick={() => {
                        handleCloseModal();
                        setShowEditModal(false);
                        setEditingId(null);
                      }}
                      className="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium"
                    >
                      Batal
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        )}

        {/* Upload Excel Modal */}
        {showUploadModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-transparent">
            <div className="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto animate-slideUp">
              <div className="p-6 border-b flex items-center justify-between">
                <h2 className="text-xl font-semibold text-gray-800 flex items-center gap-2">
                  <FileSpreadsheet className="w-6 h-6 text-green-600" />
                  Upload Excel - Import Slip Gaji
                </h2>
                <button
                  onClick={() => {
                    setShowUploadModal(false);
                    setUploadFile(null);
                    setUploadResult(null);
                  }}
                  className="text-gray-500 hover:text-gray-700 transition-colors"
                >
                  <X className="w-6 h-6" />
                </button>
              </div>

              <div className="p-6 space-y-4">
                {/* File Upload Area */}
                {!uploadFile && (
                  <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 transition-colors">
                    <input
                      type="file"
                      id="excel-upload"
                      accept=".xlsx,.xls"
                      onChange={handleFileSelect}
                      className="hidden"
                    />
                    <label
                      htmlFor="excel-upload"
                      className="cursor-pointer flex flex-col items-center"
                    >
                      <Upload className="w-12 h-12 text-gray-400 mb-4" />
                      <p className="text-gray-600 mb-2">
                        Klik untuk memilih file atau drag & drop
                      </p>
                      <p className="text-sm text-gray-500">
                        Format: .xlsx atau .xls (Maks. 10MB)
                      </p>
                    </label>
                  </div>
                )}

                {/* Selected File */}
                {uploadFile && (
                  <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <FileSpreadsheet className="w-8 h-8 text-blue-600" />
                      <div>
                        <p className="font-medium text-gray-800">
                          {uploadFile.name}
                        </p>
                        <p className="text-sm text-gray-600">
                          {(uploadFile.size / 1024 / 1024).toFixed(2)} MB
                        </p>
                      </div>
                    </div>
                    <button
                      onClick={() => {
                        setUploadFile(null);
                        setUploadResult(null);
                      }}
                      className="text-red-500 hover:text-red-700"
                    >
                      <X className="w-5 h-5" />
                    </button>
                  </div>
                )}

                {/* Upload Result */}
                {uploadResult && (
                  <div
                    className={`rounded-lg p-4 ${
                      uploadResult.error_count > 0
                        ? "bg-yellow-50 border border-yellow-200"
                        : "bg-green-50 border border-green-200"
                    }`}
                  >
                    <div className="flex items-center gap-2 mb-2">
                      <p
                        className={`font-semibold ${
                          uploadResult.error_count > 0
                            ? "text-yellow-800"
                            : "text-green-800"
                        }`}
                      >
                        {uploadResult.error_count > 0
                          ? "Import dengan beberapa error"
                          : "Import berhasil"}
                      </p>
                    </div>
                    <p className="text-sm text-gray-700 mb-2">
                      Berhasil: {uploadResult.success_count} | Gagal:{" "}
                      {uploadResult.error_count}
                    </p>
                    {uploadResult.errors &&
                      uploadResult.errors.length > 0 && (
                        <div className="mt-3 max-h-40 overflow-y-auto">
                          <p className="text-sm font-medium text-gray-700 mb-2">
                            Detail Error:
                          </p>
                          <ul className="text-xs space-y-1">
                            {uploadResult.errors
                              .slice(0, 10)
                              .map((err: any, idx: number) => (
                                <li
                                  key={idx}
                                  className="text-red-600"
                                >{`Baris ${err.line}: ${err.error}`}</li>
                              ))}
                            {uploadResult.errors.length > 10 && (
                              <li className="text-gray-500">
                                ... dan {uploadResult.errors.length - 10}{" "}
                                error lainnya
                              </li>
                            )}
                          </ul>
                        </div>
                      )}
                  </div>
                )}

                {/* Action Buttons */}
                <div className="flex gap-3 pt-4">
                  <button
                    onClick={() => {
                      setShowUploadModal(false);
                      setUploadFile(null);
                      setUploadResult(null);
                    }}
                    className="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    disabled={isUploading}
                  >
                    Batal
                  </button>
                  <button
                    onClick={handleUploadExcel}
                    disabled={!uploadFile || isUploading}
                    className="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                  >
                    {isUploading ? (
                      <>
                        <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        Mengupload...
                      </>
                    ) : (
                      <>
                        <Upload className="w-5 h-5" />
                        Upload & Import
                      </>
                    )}
                  </button>
                </div>

                {/* Info */}
                <div className="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                  <p className="font-medium mb-2">Cara menggunakan:</p>
                  <ol className="list-decimal list-inside space-y-1">
                    <li>Download template Excel terlebih dahulu</li>
                    <li>Isi data sesuai format (NIK, Tanggal, Total Gaji, Keterangan)</li>
                    <li>Upload file Excel yang sudah diisi</li>
                    <li>Sistem akan memvalidasi dan mengimport data</li>
                  </ol>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </AccessControl>
  );
}
