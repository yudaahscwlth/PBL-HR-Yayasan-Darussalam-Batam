"use client";

import { useState, useEffect } from "react";
import { useParams, useRouter } from "next/navigation";
import axios from "axios";
import { ArrowLeft, Save, Loader2 } from "lucide-react";
import AccessControl from "@/components/AccessControl";
import { apiClient } from "@/lib/api";
import { useAuthStore } from "@/store/authStore";
import toast from "react-hot-toast";

interface EvaluationRecord {
  id: number;
  id_user: number;
  id_penilai: number;
  id_tahun_ajaran: number;
  id_kategori: number;
  nilai: number;
  catatan: string | null;
  created_at: string;
  updated_at: string;
  kategori_evaluasi?: {
    id: number;
    nama: string;
  };
  tahun_ajaran?: {
    id: number;
    nama: string;
    semester: string;
    is_aktif?: boolean;
  };
  penilai?: {
    id: number;
    email: string;
    profile_pribadi?: {
      nama_lengkap?: string;
    };
  };
}

interface TahunAjaran {
  id: number;
  nama: string;
  semester: string;
  is_aktif: boolean;
}

interface UserProfile {
  id: number;
  email: string;
  profile_pribadi?: {
    nama_lengkap?: string;
    foto_profil?: string;
  };
}

export default function HrdRekapEvaluasiPage() {
  const params = useParams();
  const router = useRouter();
  const userId = params?.id as string;
  const { user: currentUser } = useAuthStore();

  const [evaluationData, setEvaluationData] = useState<EvaluationRecord[]>([]);
  const [allEvaluations, setAllEvaluations] = useState<EvaluationRecord[]>([]);
  const [userProfile, setUserProfile] = useState<UserProfile | null>(null);
  const [tahunAjaranList, setTahunAjaranList] = useState<TahunAjaran[]>([]);
  const [selectedTahunAjaran, setSelectedTahunAjaran] = useState<string>("");
  const [kategoriEvaluasiList, setKategoriEvaluasiList] = useState<
    Array<{ id: number; nama: string }>
  >([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isEditMode, setIsEditMode] = useState(false);
  const [editValues, setEditValues] = useState<Record<number, number>>({});
  const [editCatatan, setEditCatatan] = useState<string>("");
  const [hasEditPermission, setHasEditPermission] = useState(false);

  // Check edit permission
  useEffect(() => {
    const checkPermission = async () => {
      try {
        // Check if user has permission to edit (only staff hrd can edit)
        // Kepala hrd can only read/view
        const userRoles = currentUser?.roles || [];
        const roleNames = userRoles.map((r: any) =>
          typeof r === "string" ? r.toLowerCase() : r?.name?.toLowerCase()
        );
        const canEdit = roleNames.some((role: string) => role === "staff hrd");
        setHasEditPermission(canEdit);
      } catch (error) {
        console.error("Error checking permission:", error);
        setHasEditPermission(false);
      }
    };
    checkPermission();
  }, [currentUser]);

  // Fetch user profile and evaluation data
  useEffect(() => {
    const fetchData = async () => {
      if (!userId) return;

      try {
        setIsLoading(true);
        const token = localStorage.getItem("auth_token");
        const headers = { Authorization: `Bearer ${token}` };
        const baseUrl =
          process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

        // Fetch user profile, kategori evaluasi, and evaluations
        const [userResponse, kategoriResponse, evaluationResponse] =
          await Promise.all([
            axios.get(`${baseUrl}/api/users/${userId}`, { headers }),
            apiClient.kategoriEvaluasi.getAll(),
            apiClient.evaluation.getAll(),
          ]);

        setUserProfile(userResponse.data.data);

        // Set kategori evaluasi list for sorting
        if (kategoriResponse.success) {
          const kategoriList =
            (kategoriResponse.data as Array<{ id: number; nama: string }>) ||
            [];
          setKategoriEvaluasiList(kategoriList);
        }

        // Fetch all evaluations and filter by userId
        if (evaluationResponse.success) {
          const allEvaluationsData =
            (evaluationResponse.data as EvaluationRecord[]) || [];
          const records = allEvaluationsData.filter(
            (evaluation) => evaluation.id_user === Number(userId)
          );

          setAllEvaluations(records);

          // Fetch tahun ajaran list separately if not in evaluation data
          let tahunList: TahunAjaran[] = [];

          // Try to get tahun ajaran from evaluation records
          const tahunMap = new Map<number, TahunAjaran>();
          const tahunAjaranIds = new Set<number>();

          records.forEach((record) => {
            if (record.id_tahun_ajaran) {
              tahunAjaranIds.add(record.id_tahun_ajaran);
            }
            if (record.tahun_ajaran && record.tahun_ajaran.id) {
              const tahunAjaran: TahunAjaran = {
                id: record.tahun_ajaran.id,
                nama: record.tahun_ajaran.nama,
                semester: record.tahun_ajaran.semester,
                is_aktif: record.tahun_ajaran.is_aktif ?? false,
              };
              if (!tahunMap.has(record.tahun_ajaran.id)) {
                tahunMap.set(record.tahun_ajaran.id, tahunAjaran);
              }
            }
          });

          // If tahun_ajaran not loaded in evaluation, fetch separately
          if (tahunMap.size === 0 && tahunAjaranIds.size > 0) {
            try {
              const tahunAjaranResponse = await axios.get(
                `${baseUrl}/api/tahun-ajaran`,
                { headers }
              );
              if (tahunAjaranResponse.data?.data) {
                const allTahunAjaran = Array.isArray(
                  tahunAjaranResponse.data.data
                )
                  ? tahunAjaranResponse.data.data
                  : tahunAjaranResponse.data.data?.data || [];

                allTahunAjaran.forEach((tahun: TahunAjaran) => {
                  if (tahunAjaranIds.has(tahun.id)) {
                    tahunMap.set(tahun.id, tahun);
                  }
                });
              }
            } catch (error) {
              console.error("Error fetching tahun ajaran:", error);
            }
          }

          tahunList = Array.from(tahunMap.values()).sort((a, b) =>
            b.nama.localeCompare(a.nama)
          );

          setTahunAjaranList(tahunList);

          // Set default selected tahun ajaran (most recent)
          if (tahunList.length > 0 && !selectedTahunAjaran) {
            setSelectedTahunAjaran(tahunList[0].id.toString());
          }
        } else {
          console.error(
            "Gagal memuat data evaluasi:",
            evaluationResponse.message
          );
        }
      } catch (error) {
        console.error("Error fetching evaluation recap:", error);
        toast.error("Gagal memuat data evaluasi");
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();
  }, [userId, selectedTahunAjaran]);

  // Filter evaluations by selected tahun ajaran
  useEffect(() => {
    if (selectedTahunAjaran) {
      const filtered = allEvaluations.filter(
        (evaluation) =>
          evaluation.id_tahun_ajaran === Number(selectedTahunAjaran)
      );

      // Sort by kategori evaluasi order (same as /hrd/evaluasi page)
      // Create a map of kategori id to index for sorting
      const kategoriOrderMap = new Map<number, number>();
      kategoriEvaluasiList.forEach((kategori, index) => {
        kategoriOrderMap.set(kategori.id, index);
      });

      // Sort evaluations based on kategori order
      const sorted = filtered.sort((a, b) => {
        const orderA = kategoriOrderMap.get(a.id_kategori) ?? 999;
        const orderB = kategoriOrderMap.get(b.id_kategori) ?? 999;
        return orderA - orderB;
      });

      setEvaluationData(sorted);

      // Initialize edit values
      const values: Record<number, number> = {};
      const catatan =
        sorted.find((e) => e.catatan)?.catatan || sorted[0]?.catatan || "";
      sorted.forEach((evaluation) => {
        values[evaluation.id_kategori] = evaluation.nilai;
      });
      setEditValues(values);
      setEditCatatan(catatan);
    } else if (allEvaluations.length > 0 && tahunAjaranList.length === 0) {
      // If we have evaluations but no tahun ajaran list, show all evaluations
      // This handles the case where tahun ajaran data is not loaded yet
      // Sort by kategori evaluasi order
      const kategoriOrderMap = new Map<number, number>();
      kategoriEvaluasiList.forEach((kategori, index) => {
        kategoriOrderMap.set(kategori.id, index);
      });

      const sorted = allEvaluations.sort((a, b) => {
        const orderA = kategoriOrderMap.get(a.id_kategori) ?? 999;
        const orderB = kategoriOrderMap.get(b.id_kategori) ?? 999;
        return orderA - orderB;
      });
      setEvaluationData(sorted);

      // Initialize edit values
      const values: Record<number, number> = {};
      const catatan =
        sorted.find((e) => e.catatan)?.catatan || sorted[0]?.catatan || "";
      sorted.forEach((evaluation) => {
        values[evaluation.id_kategori] = evaluation.nilai;
      });
      setEditValues(values);
      setEditCatatan(catatan);
    }
  }, [
    selectedTahunAjaran,
    allEvaluations,
    tahunAjaranList.length,
    kategoriEvaluasiList,
  ]);

  const handleNilaiChange = (idKategori: number, nilai: number) => {
    setEditValues((prev) => ({
      ...prev,
      [idKategori]: nilai,
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    e.stopPropagation();

    // Hanya submit jika dalam mode edit
    if (!isEditMode) {
      return;
    }

    if (!selectedTahunAjaran || !hasEditPermission) return;

    // Validate all values are filled
    const allCategories = currentEvaluations.map((e) => e.id_kategori);
    const missingCategories = allCategories.filter(
      (id) => !editValues[id] || editValues[id] === 0
    );
    if (missingCategories.length > 0) {
      toast.error("Mohon isi nilai untuk semua indikator penilaian");
      return;
    }

    try {
      setIsSubmitting(true);
      const token = localStorage.getItem("auth_token");
      const baseUrl =
        process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

      // Prepare data sesuai dengan struktur API endpoint
      // Menggunakan API endpoint store yang akan update jika evaluasi sudah ada
      const evaluationData = Object.keys(editValues).map((key) => {
        const idKategori = Number(key);
        return {
          id_kategori: idKategori,
          nilai: editValues[idKategori],
          catatan: null as string | null,
        };
      });

      // Set catatan pada evaluasi pertama
      if (evaluationData.length > 0 && editCatatan) {
        evaluationData[0].catatan = editCatatan;
      }

      console.log("Sending update request:", {
        url: `${baseUrl}/api/evaluation`,
        id_user: Number(userId),
        id_tahun_ajaran: Number(selectedTahunAjaran),
        evaluations: evaluationData,
      });

      // Call API endpoint untuk update evaluasi
      // API endpoint store akan update jika evaluasi sudah ada
      const response = await axios.post(
        `${baseUrl}/api/evaluation`,
        {
          id_user: Number(userId),
          id_tahun_ajaran: Number(selectedTahunAjaran),
          evaluations: evaluationData,
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        }
      );

      console.log("Update response:", response.data);

      // Check response
      if (!response.data?.success) {
        throw new Error(response.data?.message || "Gagal memperbarui evaluasi");
      }

      toast.success("Evaluasi berhasil diperbarui");

      // Refresh data before exiting edit mode
      const evaluationResponse = await apiClient.evaluation.getAll();
      if (evaluationResponse.success) {
        const allEvaluationsData =
          (evaluationResponse.data as EvaluationRecord[]) || [];
        const records = allEvaluationsData.filter(
          (evaluation) => evaluation.id_user === Number(userId)
        );
        setAllEvaluations(records);
      }

      setIsEditMode(false);
    } catch (error: any) {
      console.error("Error updating evaluation:", error);
      console.error("Error response:", error.response?.data);
      console.error("Error status:", error.response?.status);

      // Handle different error formats
      let errorMessage = "Gagal memperbarui evaluasi";

      if (error.response) {
        // Check for validation errors
        if (error.response.data?.errors) {
          const errors = error.response.data.errors;
          const firstError = Object.values(errors)[0];
          errorMessage = Array.isArray(firstError)
            ? firstError[0]
            : String(firstError);
        } else if (error.response.data?.notifikasi) {
          errorMessage = error.response.data.notifikasi;
        } else if (error.response.data?.message) {
          errorMessage = error.response.data.message;
        } else if (error.response.status === 401) {
          errorMessage = "Sesi Anda telah berakhir. Silakan login kembali.";
        } else if (error.response.status === 403) {
          errorMessage = "Anda tidak memiliki izin untuk melakukan aksi ini.";
        } else if (error.response.status === 404) {
          errorMessage = "Endpoint tidak ditemukan. Pastikan URL benar.";
        } else if (error.response.status === 422) {
          errorMessage =
            "Data yang dikirim tidak valid. Pastikan semua field terisi dengan benar.";
        } else if (error.response.status === 419) {
          errorMessage = "CSRF token tidak valid. Silakan refresh halaman.";
        }
      } else if (error.message) {
        errorMessage = error.message;
      }

      toast.error(errorMessage);
    } finally {
      setIsSubmitting(false);
    }
  };

  const formatDate = (dateString: string) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  };

  const currentEvaluations = evaluationData || [];
  const totalSkor = currentEvaluations.reduce((sum, e) => sum + e.nilai, 0);
  // Get catatan from any evaluation that has it, or from first evaluation
  // Catatan biasanya sama untuk semua evaluasi dalam satu tahun ajaran
  const catatanEvaluasi =
    currentEvaluations.find((e) => e.catatan && e.catatan.trim() !== "")
      ?.catatan ||
    currentEvaluations[0]?.catatan ||
    "";
  const tanggalEvaluasi = currentEvaluations[0]?.created_at || "";
  const penilaiEvaluasi = currentEvaluations[0]?.penilai;

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
            <h1 className="text-2xl font-bold text-gray-800">Rekap Evaluasi</h1>
          </div>

          {/* Main content */}
          <div className="space-y-6">
            {/* Profile & Table */}
            <div className="space-y-6">
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
                            userProfile.profile_pribadi.foto_profil.startsWith(
                              "http"
                            )
                              ? userProfile.profile_pribadi.foto_profil
                              : `${
                                  process.env.NEXT_PUBLIC_API_URL ||
                                  "http://localhost:8000"
                                }/${userProfile.profile_pribadi.foto_profil}`
                          }
                          alt="Profile"
                          className="w-24 h-24 rounded-full object-cover"
                        />
                      ) : (
                        <div className="w-24 h-24 rounded-full bg-sky-100 flex items-center justify-center">
                          <span className="text-3xl font-bold text-sky-800">
                            {userProfile.profile_pribadi?.nama_lengkap
                              ?.charAt(0)
                              .toUpperCase() ||
                              userProfile.email.charAt(0).toUpperCase()}
                          </span>
                        </div>
                      )}
                    </div>
                    <div>
                      <h2 className="text-2xl font-bold text-gray-800">
                        {userProfile.profile_pribadi?.nama_lengkap ||
                          "Nama Belum Diisi"}
                      </h2>
                      <p className="text-gray-600">{userProfile.email}</p>
                    </div>
                  </div>
                ) : (
                  <p className="text-gray-500">User tidak ditemukan</p>
                )}
              </div>

              {/* Evaluation Form */}
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 className="text-lg font-bold text-gray-800 mb-4">
                  Riwayat Evaluasi
                </h2>

                {isLoading ? (
                  <div className="text-center py-8 text-gray-500">
                    <div className="flex items-center justify-center">
                      <div className="w-6 h-6 border-2 border-sky-800 border-t-transparent rounded-full animate-spin mr-2"></div>
                      Loading...
                    </div>
                  </div>
                ) : allEvaluations.length === 0 ? (
                  <div className="text-center py-8 text-gray-500">
                    <p>Belum ada data evaluasi yang ditambahkan</p>
                    <p className="text-sm mt-2">
                      Silakan evaluasi pegawai terlebih dahulu
                    </p>
                  </div>
                ) : tahunAjaranList.length === 0 &&
                  allEvaluations.length > 0 ? (
                  <div className="text-center py-8 text-gray-500">
                    <p>
                      Data evaluasi ditemukan tetapi tahun ajaran tidak dapat
                      dimuat
                    </p>
                    <p className="text-sm mt-2">
                      Silakan refresh halaman atau hubungi administrator
                    </p>
                  </div>
                ) : (
                  <form onSubmit={handleSubmit}>
                    {/* Tahun Ajaran Selector */}
                    <div className="mb-4 space-y-4">
                      <div className="flex items-center gap-4">
                        <label className="text-sm font-medium text-gray-700">
                          Tahun Ajaran:
                        </label>
                        <select
                          value={selectedTahunAjaran}
                          onChange={(e) => {
                            setSelectedTahunAjaran(e.target.value);
                            setIsEditMode(false);
                          }}
                          className="px-3 py-2 border border-gray-200 text-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800"
                          disabled={isEditMode || isSubmitting}
                        >
                          <option value="">Pilih Tahun Ajaran</option>
                          {tahunAjaranList.map((tahun) => (
                            <option key={tahun.id} value={tahun.id}>
                              {tahun.nama} - {tahun.semester}
                            </option>
                          ))}
                        </select>
                      </div>

                      {/* Status and Penilai */}
                      {currentEvaluations.length > 0 && (
                        <div className="flex flex-wrap items-center gap-4 text-sm">
                          <div>
                            <span className="font-medium text-gray-700">
                              Status:{" "}
                            </span>
                            <span className="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                              Sudah Terisi
                            </span>
                          </div>
                          {penilaiEvaluasi && (
                            <div>
                              <span className="font-medium text-gray-700">
                                Nama Penilai:{" "}
                              </span>
                              <span className="text-gray-600">
                                {penilaiEvaluasi.profile_pribadi
                                  ?.nama_lengkap ||
                                  penilaiEvaluasi.email ||
                                  "-"}
                              </span>
                            </div>
                          )}
                        </div>
                      )}
                    </div>

                    {/* Evaluation Table */}
                    {selectedTahunAjaran && currentEvaluations.length > 0 && (
                      <>
                        {isSubmitting && (
                          <div className="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-3 text-blue-700">
                            <Loader2 className="w-5 h-5 animate-spin" />
                            <span className="font-medium">
                              Sedang menyimpan perubahan...
                            </span>
                          </div>
                        )}
                        <div className="overflow-x-auto mb-4">
                          <table className="w-full text-sm border border-gray-200">
                            <thead>
                              <tr className="bg-gray-50 border-b text-center">
                                <th className="p-3 font-semibold text-gray-700">
                                  No
                                </th>
                                <th className="p-3 font-semibold text-gray-700 text-left">
                                  Indikator Penilaian
                                </th>
                                <th className="p-3 font-semibold text-gray-700">
                                  <div>Sangat Baik</div>
                                  <div className="text-xs font-normal">(5)</div>
                                </th>
                                <th className="p-3 font-semibold text-gray-700">
                                  <div>Baik</div>
                                  <div className="text-xs font-normal">(4)</div>
                                </th>
                                <th className="p-3 font-semibold text-gray-700">
                                  <div>Sedang</div>
                                  <div className="text-xs font-normal">(3)</div>
                                </th>
                                <th className="p-3 font-semibold text-gray-700">
                                  <div>Kurang</div>
                                  <div className="text-xs font-normal">(2)</div>
                                </th>
                                <th className="p-3 font-semibold text-gray-700">
                                  <div>Sangat Kurang</div>
                                  <div className="text-xs font-normal">(1)</div>
                                </th>
                              </tr>
                            </thead>
                            <tbody>
                              {currentEvaluations.map((evaluation, index) => {
                                const currentNilai = isEditMode
                                  ? editValues[evaluation.id_kategori] ||
                                    evaluation.nilai
                                  : evaluation.nilai;

                                return (
                                  <tr
                                    key={evaluation.id}
                                    className={
                                      index % 2 === 0
                                        ? "bg-white"
                                        : "bg-gray-50"
                                    }
                                  >
                                    <td className="p-3 text-center text-gray-700">
                                      {index + 1}
                                    </td>
                                    <td className="p-3 text-gray-700 font-medium">
                                      {evaluation.kategori_evaluasi?.nama ||
                                        "-"}
                                    </td>
                                    {[5, 4, 3, 2, 1].map((nilai) => (
                                      <td
                                        key={nilai}
                                        className="p-3 text-center"
                                      >
                                        <input
                                          type="radio"
                                          name={`nilai-${evaluation.id_kategori}`}
                                          value={nilai}
                                          checked={currentNilai === nilai}
                                          onChange={() =>
                                            handleNilaiChange(
                                              evaluation.id_kategori,
                                              nilai
                                            )
                                          }
                                          disabled={!isEditMode || isSubmitting}
                                          className={`w-5 h-5 text-sky-800 focus:ring-sky-800 ${
                                            !isEditMode || isSubmitting
                                              ? "cursor-not-allowed opacity-60"
                                              : "cursor-pointer"
                                          }`}
                                        />
                                      </td>
                                    ))}
                                  </tr>
                                );
                              })}
                              <tr className="bg-gray-100 font-bold">
                                <td
                                  colSpan={2}
                                  className="p-3 text-center text-gray-800"
                                >
                                  Total Skor
                                </td>
                                <td
                                  colSpan={5}
                                  className="p-3 text-center text-gray-800"
                                >
                                  {isEditMode
                                    ? Object.values(editValues).reduce(
                                        (sum, val) => sum + val,
                                        0
                                      )
                                    : totalSkor}
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>

                        {/* Catatan */}
                        <div className="mb-4">
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Catatan
                          </label>
                          <textarea
                            value={isEditMode ? editCatatan : catatanEvaluasi}
                            onChange={(e) => setEditCatatan(e.target.value)}
                            disabled={!isEditMode || isSubmitting}
                            rows={5}
                            className={`w-full px-4 py-3 border border-gray-300 text-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800 resize-none ${
                              !isEditMode || isSubmitting
                                ? "bg-gray-50 cursor-not-allowed"
                                : ""
                            }`}
                            placeholder="Catatan jika ada"
                          />
                        </div>

                        {/* Tanggal Evaluasi */}
                        {tanggalEvaluasi && (
                          <div className="mb-4 text-sm text-gray-600">
                            <span className="font-medium">
                              Tanggal Evaluasi:{" "}
                            </span>
                            {formatDate(tanggalEvaluasi)}
                          </div>
                        )}

                        {/* Edit Button */}
                        {hasEditPermission && (
                          <div className="flex gap-2">
                            {!isEditMode ? (
                              <button
                                type="button"
                                onClick={(e) => {
                                  e.preventDefault();
                                  e.stopPropagation();
                                  setIsEditMode(true);
                                }}
                                className="px-4 py-2 bg-sky-800 hover:bg-sky-900 text-white rounded-lg font-medium transition-colors"
                              >
                                Edit Evaluasi
                              </button>
                            ) : (
                              <>
                                <button
                                  type="submit"
                                  disabled={isSubmitting}
                                  className="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                >
                                  {isSubmitting ? (
                                    <>
                                      <Loader2 className="w-4 h-4 animate-spin" />
                                      Menyimpan...
                                    </>
                                  ) : (
                                    <>
                                      <Save className="w-4 h-4" />
                                      Simpan
                                    </>
                                  )}
                                </button>
                                <button
                                  type="button"
                                  onClick={() => {
                                    setIsEditMode(false);
                                    // Reset edit values
                                    const values: Record<number, number> = {};
                                    currentEvaluations.forEach((evaluation) => {
                                      values[evaluation.id_kategori] =
                                        evaluation.nilai;
                                    });
                                    setEditValues(values);
                                    setEditCatatan(catatanEvaluasi);
                                  }}
                                  disabled={isSubmitting}
                                  className="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                  Batal
                                </button>
                              </>
                            )}
                          </div>
                        )}
                      </>
                    )}
                  </form>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </AccessControl>
  );
}
