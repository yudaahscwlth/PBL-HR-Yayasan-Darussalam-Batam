'use client';

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuthStore } from "@/store/authStore";
import axios from "axios";

interface Jabatan {
  id: number;
  nama_jabatan: string;
  created_at?: string;
  updated_at?: string;
}

interface FormData {
  nama_jabatan: string;
}

interface FormErrors {
  [key: string]: string[];
}

interface ApiError {
  response?: {
    data?: {
      errors?: FormErrors;
      message?: string;
    };
    status?: number;
  };
  message?: string;
}

export default function KelolaJabatan() {
  const { user, isAuthenticated } = useAuthStore();
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(true);
  const [showAddModal, setShowAddModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [selectedItem, setSelectedItem] = useState<Jabatan | null>(null);
  const [jabatanData, setJabatanData] = useState<Jabatan[]>([]);
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState<FormData>({ nama_jabatan: '' });
  const [errors, setErrors] = useState<FormErrors>({});
  const [notification, setNotification] = useState<{
    message: string;
    type: "success" | "error";
  } | null>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [entriesPerPage, setEntriesPerPage] = useState(10);
  const [currentPage, setCurrentPage] = useState(1);

  const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

  useEffect(() => {
    if (!isAuthenticated) {
      router.push("/");
      return;
    }

    if (user) {
      const isHRD =
        user.roles?.includes("kepala hrd") || user.roles?.includes("staff hrd");

      if (!isHRD) {
        router.push("/unauthorized");
        return;
      }

      setIsLoading(false);
      fetchData();
    }
  }, [isAuthenticated, user, router]);

  const fetchData = async () => {
    try {
      const token = localStorage.getItem("auth_token");
      const response = await axios.get(`${API_BASE_URL}/api/jabatan`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.data.success) {
        setJabatanData(response.data.data);
      }
    } catch (error: unknown) {
      console.error("Error fetching data:", error);
      showNotification("Gagal memuat data", "error");
    }
  };

  const showNotification = (message: string, type: "success" | "error") => {
    setNotification({ message, type });
    setTimeout(() => setNotification(null), 3000);
  };

  const handleAdd = () => {
    setFormData({ nama_jabatan: "" });
    setErrors({});
    setShowAddModal(true);
  };

  const handleEdit = (item: Jabatan) => {
    setSelectedItem(item);
    setFormData({
      nama_jabatan: item.nama_jabatan,
    });
    setErrors({});
    setShowEditModal(true);
  };

  const handleDelete = (item: Jabatan) => {
    setSelectedItem(item);
    setShowDeleteModal(true);
  };

  const handleSubmitAdd = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrors({});

    try {
      const token = localStorage.getItem("auth_token");
      const response = await axios.post(
        `${API_BASE_URL}/api/jabatan`,
        formData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.data.success) {
        showNotification(response.data.message, "success");
        setShowAddModal(false);
        fetchData();
      }
    } catch (error: unknown) {
      const apiError = error as ApiError;
      if (apiError.response?.data?.errors) {
        setErrors(apiError.response.data.errors);
      } else {
        showNotification("Gagal menambahkan data", "error");
      }
    }
  };

  const handleSubmitEdit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrors({});

    if (!selectedItem) return;

    try {
      const token = localStorage.getItem("auth_token");
      const response = await axios.put(
        `${API_BASE_URL}/api/jabatan/${selectedItem.id}`,
        formData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.data.success) {
        showNotification(response.data.message, "success");
        setShowEditModal(false);
        fetchData();
      }
    } catch (error: unknown) {
      const apiError = error as ApiError;
      if (apiError.response?.data?.errors) {
        setErrors(apiError.response.data.errors);
      } else {
        showNotification("Gagal mengubah data", "error");
      }
    }
  };

  const handleSubmitDelete = async () => {
    if (!selectedItem) return;

    try {
      const token = localStorage.getItem("auth_token");
      const response = await axios.delete(
        `${API_BASE_URL}/api/jabatan/${selectedItem.id}`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.data.success) {
        showNotification(response.data.message, "success");
        setShowDeleteModal(false);
        fetchData();
      }
    } catch (error: unknown) {
      showNotification("Gagal menghapus data", "error");
    }
  };

  const filteredData = jabatanData.filter(item =>
    item.nama_jabatan.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const totalPages = Math.ceil(filteredData.length / entriesPerPage);
  const startIndex = (currentPage - 1) * entriesPerPage;
  const paginatedData = filteredData.slice(startIndex, startIndex + entriesPerPage);

  if (isLoading) {
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
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white px-4 py-4 border-b border-gray-200 sticky top-0 z-10">
        <div className="flex items-center justify-between">
          <button
            onClick={() => router.back()}
            className="flex items-center text-blue-600"
          >
            <svg
              className="w-6 h-6 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M15 19l-7-7 7-7"
              />
            </svg>
            Kembali
          </button>
          <h1 className="text-lg font-semibold text-gray-800">Kelola Jabatan</h1>
          <div className="w-16"></div>
        </div>
      </div>

      {/* Notification */}
      {notification && (
        <div
          className={`mx-4 mt-4 p-4 rounded-lg ${
            notification.type === "success"
              ? "bg-green-100 text-green-800"
              : "bg-red-100 text-red-800"
          }`}
        >
          {notification.message}
        </div>
      )}

      {/* Content */}
      <div className="p-4">
        <div className="bg-white rounded-lg shadow">
          {/* Header Card */}
          <div className="p-4 border-b border-gray-200">
            <button
              onClick={handleAdd}
              className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center justify-center w-full"
            >
              <svg
                className="w-5 h-5 mr-2"
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
              Tambah Baru
            </button>
          </div>

          {/* Filters */}
          <div className="p-4 border-b border-gray-200">
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center">
                <span className="text-sm text-gray-700 mr-2">Show</span>
                <select
                  value={entriesPerPage}
                  onChange={(e) => {
                    setEntriesPerPage(Number(e.target.value));
                    setCurrentPage(1);
                  }}
                  className="border border-gray-300 text-gray-700 rounded px-2 py-1 text-sm"
                >
                  <option value={5}>5</option>
                  <option value={10}>10</option>
                  <option value={25}>25</option>
                  <option value={50}>50</option>
                </select>
                <span className="text-sm text-gray-700 ml-2">entries</span>
              </div>

              <div className="flex items-center">
                <span className="text-sm text-gray-700 mr-2">Search:</span>
                <input
                  type="text"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="border border-gray-300 text-gray-700 rounded px-2 py-1 text-sm w-24"
                />
              </div>
            </div>
          </div>

          {/* Table Header */}
          <div className="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <span className="text-sm font-semibold text-gray-700">Nama</span>
          </div>

          {/* Card List */}
          <div className="p-4">
            {paginatedData.length === 0 ? (
              <div className="text-center py-12 text-gray-500">
                <svg
                  className="w-16 h-16 mx-auto mb-4 text-gray-300"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                  />
                </svg>
                <p className="text-lg font-medium">Tidak ada data</p>
                <p className="text-sm">
                  Klik `Tambah Baru` untuk menambahkan jabatan
                </p>
              </div>
            ) : (
              <div className="space-y-4">
                {paginatedData.map((item, index) => (
                  <div
                    key={item.id}
                    className="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow"
                  >
                    {/* Header dengan nomor dan nama */}
                    <div className="flex items-start justify-between mb-3">
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-1">
                          <span className="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">
                            {startIndex + index + 1}
                          </span>
                          <h3 className="text-lg font-semibold text-gray-900 capitalize">
                            {item.nama_jabatan}
                          </h3>
                        </div>
                      </div>
                    </div>

                    {/* Tombol Aksi */}
                    <div className="flex gap-2 pt-3 border-t border-gray-100">
                      <button
                        onClick={() => handleEdit(item)}
                        className="flex-1 flex items-center justify-center gap-2 bg-yellow-50 text-yellow-600 px-4 py-2 rounded-lg hover:bg-yellow-100 transition-colors"
                      >
                        <svg
                          className="w-4 h-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                          />
                        </svg>
                        <span className="text-sm font-medium">Edit</span>
                      </button>
                      <button
                        onClick={() => handleDelete(item)}
                        className="flex-1 flex items-center justify-center gap-2 bg-red-50 text-red-600 px-4 py-2 rounded-lg hover:bg-red-100 transition-colors"
                      >
                        <svg
                          className="w-4 h-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                          />
                        </svg>
                        <span className="text-sm font-medium">Hapus</span>
                      </button>
                      <button
                        onClick={() => router.push(`/hrd/jabatan/${item.id}/jam-kerja`)}
                        className="flex-1 flex items-center justify-center gap-2 bg-gray-50 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors"
                      >
                        <svg
                          className="w-4 h-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                          />
                        </svg>
                        <span className="text-sm font-medium">Jam kerja</span>
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Pagination */}
          {paginatedData.length > 0 && (
            <div className="p-4 border-t border-gray-200">
              <div className="text-sm text-gray-700 mb-3">
                Showing {startIndex + 1} to {Math.min(startIndex + entriesPerPage, filteredData.length)} of {filteredData.length} entries
              </div>
              <div className="flex justify-center gap-2">
                <button
                  onClick={() => setCurrentPage(prev => Math.max(1, prev - 1))}
                  disabled={currentPage === 1}
                  className="px-3 py-1.5 border border-gray-300 text-gray-700 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Previous
                </button>
                <button className="px-3 py-1.5 bg-blue-600 text-white rounded text-sm font-medium">
                  {currentPage}
                </button>
                <button
                  onClick={() => setCurrentPage(prev => Math.min(totalPages, prev + 1))}
                  disabled={currentPage === totalPages}
                  className="px-3 py-1.5 border border-gray-300 text-gray-700 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Next
                </button>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Add Modal */}
      {showAddModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div className="p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-4">
                Tambah Data
              </h2>
              <form onSubmit={handleSubmitAdd}>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Nama Jabatan <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="text"
                      value={formData.nama_jabatan}
                      onChange={(e) =>
                        setFormData({
                          ...formData,
                          nama_jabatan: e.target.value,
                        })
                      }
                      className={`w-full px-3 py-2 border rounded-lg text-gray-900 placeholder:text-gray-400 ${
                        errors.nama_jabatan
                          ? "border-red-500"
                          : "border-gray-300"
                      }`}
                      placeholder="Contoh: Manager, Staff, dll"
                      required
                    />
                    {errors.nama_jabatan && (
                      <p className="text-red-500 text-xs mt-1">
                        {errors.nama_jabatan[0]}
                      </p>
                    )}
                  </div>
                </div>
                <div className="flex gap-2 mt-6">
                  <button
                    type="button"
                    onClick={() => setShowAddModal(false)}
                    className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                  >
                    Batal
                  </button>
                  <button
                    type="submit"
                    className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                  >
                    Simpan
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}

      {/* Edit Modal */}
      {showEditModal && selectedItem && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div className="p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-4">
                Edit Data
              </h2>
              <form onSubmit={handleSubmitEdit}>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Nama Jabatan <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="text"
                      value={formData.nama_jabatan}
                      onChange={(e) =>
                        setFormData({
                          ...formData,
                          nama_jabatan: e.target.value,
                        })
                      }
                      className={`w-full px-3 py-2 border rounded-lg text-gray-900 placeholder:text-gray-400 ${
                        errors.nama_jabatan
                          ? "border-red-500"
                          : "border-gray-300"
                      }`}
                      placeholder="Contoh: Manager, Staff, dll"
                      required
                    />
                    {errors.nama_jabatan && (
                      <p className="text-red-500 text-xs mt-1">
                        {errors.nama_jabatan[0]}
                      </p>
                    )}
                  </div>
                </div>
                <div className="flex gap-2 mt-6">
                  <button
                    type="button"
                    onClick={() => setShowEditModal(false)}
                    className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                  >
                    Batal
                  </button>
                  <button
                    type="submit"
                    className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                  >
                    Simpan
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}

      {/* Delete Modal */}
      {showDeleteModal && selectedItem && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg max-w-md w-full">
            <div className="p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-4">
                Hapus Data
              </h2>
              <p className="text-gray-700 mb-6">
                Apakah anda yakin ingin{" "}
                <span className="text-red-600 font-semibold">
                  menghapus data
                </span>{" "}
                <span className="capitalize font-semibold text-gray-900">
                  {selectedItem.nama_jabatan}
                </span>
                ?
              </p>
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => setShowDeleteModal(false)}
                  className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                >
                  Batal
                </button>
                <button
                  onClick={handleSubmitDelete}
                  className="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                >
                  Hapus
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}