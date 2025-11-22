"use client";

import { useState, useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import axios from "axios";
import AccessControl from "@/components/AccessControl";

interface TahunAjaran {
  id: number;
  nama: string;
  semester: string;
  is_aktif: number;
  created_at?: string;
  updated_at?: string;
}

interface FormData {
  nama: string;
  semester: string;
  status: string;
}

export default function HRDTahunAjaran() {
  const { user, isAuthenticated } = useAuthStore();
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(true);
  const [dataTahunAjaran, setDataTahunAjaran] = useState<TahunAjaran[]>([]);
  const [showAddModal, setShowAddModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [selectedItem, setSelectedItem] = useState<TahunAjaran | null>(null);
  const [formData, setFormData] = useState<FormData>({
    nama: "",
    semester: "ganjil",
    status: "0",
  });
  const [errors, setErrors] = useState<any>({});
  const [notification, setNotification] = useState<{
    message: string;
    type: "success" | "error";
  } | null>(null);

  const API_BASE_URL =
    process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

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
      const response = await axios.get(`${API_BASE_URL}/api/tahun-ajaran`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.data.success) {
        setDataTahunAjaran(response.data.data);
      }
    } catch (error: any) {
      console.error("Error fetching data:", error);
      showNotification("Gagal memuat data", "error");
    }
  };

  const showNotification = (message: string, type: "success" | "error") => {
    setNotification({ message, type });
    setTimeout(() => setNotification(null), 3000);
  };

  const handleAdd = () => {
    setFormData({ nama: "", semester: "ganjil", status: "0" });
    setErrors({});
    setShowAddModal(true);
  };

  const handleEdit = (item: TahunAjaran) => {
    setSelectedItem(item);
    setFormData({
      nama: item.nama,
      semester: item.semester,
      status: item.is_aktif.toString(),
    });
    setErrors({});
    setShowEditModal(true);
  };

  const handleDelete = (item: TahunAjaran) => {
    setSelectedItem(item);
    setShowDeleteModal(true);
  };

  const handleSubmitAdd = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrors({});

    try {
      const token = localStorage.getItem("auth_token");
      const response = await axios.post(
        `${API_BASE_URL}/api/tahun-ajaran`,
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
    } catch (error: any) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        showNotification(
          error.response?.data?.message || "Gagal menambahkan data",
          "error"
        );
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
        `${API_BASE_URL}/api/tahun-ajaran/${selectedItem.id}`,
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
    } catch (error: any) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        showNotification(
          error.response?.data?.message || "Gagal mengubah data",
          "error"
        );
      }
    }
  };

  const handleSubmitDelete = async () => {
    if (!selectedItem) return;

    try {
      const token = localStorage.getItem("auth_token");
      const response = await axios.delete(
        `${API_BASE_URL}/api/tahun-ajaran/${selectedItem.id}`,
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
    } catch (error: any) {
      showNotification(
        error.response?.data?.message || "Gagal menghapus data",
        "error"
      );
    }
  };

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
    <AccessControl allowedRoles={["kepala hrd", "staff hrd"]}>
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
            <h1 className="text-lg font-semibold text-gray-800">
              Kelola Tahun Ajaran
            </h1>
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
                className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center justify-center"
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

            {/* Card List */}
            <div className="p-4">
              {dataTahunAjaran.length === 0 ? (
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
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                  <p className="text-lg font-medium">Tidak ada data</p>
                  <p className="text-sm">
                    Klik "Tambah Baru" untuk menambahkan tahun ajaran
                  </p>
                </div>
              ) : (
                <div className="space-y-4">
                  {dataTahunAjaran.map((item, index) => (
                    <div
                      key={item.id}
                      className="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow"
                    >
                      {/* Header dengan nomor dan nama */}
                      <div className="flex items-start justify-between mb-3">
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-1">
                            <span className="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">
                              {index + 1}
                            </span>
                            <h3 className="text-lg font-semibold text-gray-900 capitalize">
                              {item.nama}
                            </h3>
                          </div>
                        </div>
                      </div>

                      {/* Info Detail */}
                      <div className="space-y-2 mb-3">
                        <div className="flex items-center text-sm">
                          <svg
                            className="w-4 h-4 mr-2 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              strokeWidth={2}
                              d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                            />
                          </svg>
                          <span className="text-gray-600 font-medium mr-2">
                            Semester:
                          </span>
                          <span className="text-gray-900 capitalize">
                            {item.semester}
                          </span>
                        </div>
                        <div className="flex items-center text-sm">
                          <svg
                            className="w-4 h-4 mr-2 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              strokeWidth={2}
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                          </svg>
                          <span className="text-gray-600 font-medium mr-2">
                            Status:
                          </span>
                          <span
                            className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                              item.is_aktif !== 0
                                ? "bg-green-100 text-green-800"
                                : "bg-red-100 text-red-800"
                            }`}
                          >
                            {item.is_aktif !== 0 ? "Aktif" : "Tidak Aktif"}
                          </span>
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
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
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
                        Tahun Ajaran <span className="text-red-500">*</span>
                      </label>
                      <input
                        type="text"
                        value={formData.nama}
                        onChange={(e) =>
                          setFormData({ ...formData, nama: e.target.value })
                        }
                        className={`w-full px-3 py-2 border rounded-lg text-gray-900 placeholder:text-gray-400 ${
                          errors.nama ? "border-red-500" : "border-gray-300"
                        }`}
                        placeholder="2025/2026"
                        required
                      />
                      {errors.nama && (
                        <p className="text-red-500 text-xs mt-1">
                          {Array.isArray(errors.nama)
                            ? errors.nama[0]
                            : errors.nama}
                        </p>
                      )}
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Semester <span className="text-red-500">*</span>
                      </label>
                      <select
                        value={formData.semester}
                        onChange={(e) =>
                          setFormData({ ...formData, semester: e.target.value })
                        }
                        className={`w-full px-3 py-2 border rounded-lg text-gray-900 ${
                          errors.semester
                            ? "border-red-500"
                            : "border-gray-300"
                        }`}
                        required
                      >
                        <option value="ganjil">Ganjil</option>
                        <option value="genap">Genap</option>
                      </select>
                      {errors.semester && (
                        <p className="text-red-500 text-xs mt-1">
                          {Array.isArray(errors.semester)
                            ? errors.semester[0]
                            : errors.semester}
                        </p>
                      )}
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Status <span className="text-red-500">*</span>
                      </label>
                      <select
                        value={formData.status}
                        onChange={(e) =>
                          setFormData({ ...formData, status: e.target.value })
                        }
                        className={`w-full px-3 py-2 border rounded-lg text-gray-900 ${
                          errors.status ? "border-red-500" : "border-gray-300"
                        }`}
                        required
                      >
                        <option value="0">Tidak Aktif</option>
                        <option value="1">Aktif</option>
                      </select>
                      {errors.status && (
                        <p className="text-red-500 text-xs mt-1">
                          {Array.isArray(errors.status)
                            ? errors.status[0]
                            : errors.status}
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
                        Tahun Ajaran <span className="text-red-500">*</span>
                      </label>
                      <input
                        type="text"
                        value={formData.nama}
                        onChange={(e) =>
                          setFormData({ ...formData, nama: e.target.value })
                        }
                        className={`w-full px-3 py-2 border rounded-lg text-gray-900 placeholder:text-gray-400 ${
                          errors.nama ? "border-red-500" : "border-gray-300"
                        }`}
                        placeholder="2025/2026"
                        required
                      />
                      {errors.nama && (
                        <p className="text-red-500 text-xs mt-1">
                          {Array.isArray(errors.nama)
                            ? errors.nama[0]
                            : errors.nama}
                        </p>
                      )}
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Semester <span className="text-red-500">*</span>
                      </label>
                      <select
                        value={formData.semester}
                        onChange={(e) =>
                          setFormData({ ...formData, semester: e.target.value })
                        }
                        className={`w-full px-3 py-2 border rounded-lg text-gray-900 ${
                          errors.semester
                            ? "border-red-500"
                            : "border-gray-300"
                        }`}
                        required
                      >
                        <option value="ganjil">Ganjil</option>
                        <option value="genap">Genap</option>
                      </select>
                      {errors.semester && (
                        <p className="text-red-500 text-xs mt-1">
                          {Array.isArray(errors.semester)
                            ? errors.semester[0]
                            : errors.semester}
                        </p>
                      )}
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Status <span className="text-red-500">*</span>
                      </label>
                      <select
                        value={formData.status}
                        onChange={(e) =>
                          setFormData({ ...formData, status: e.target.value })
                        }
                        className={`w-full px-3 py-2 border rounded-lg text-gray-900 ${
                          errors.status ? "border-red-500" : "border-gray-300"
                        }`}
                        required
                      >
                        <option value="0">Tidak Aktif</option>
                        <option value="1">Aktif</option>
                      </select>
                      {errors.status && (
                        <p className="text-red-500 text-xs mt-1">
                          {Array.isArray(errors.status)
                            ? errors.status[0]
                            : errors.status}
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
                    {selectedItem.nama} - {selectedItem.semester}
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
    </AccessControl>
  );
}

