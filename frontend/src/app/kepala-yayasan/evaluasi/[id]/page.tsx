"use client";

import { useState, useEffect } from "react";
import { useParams, useRouter } from "next/navigation";
import axios from "axios";
import { ArrowLeft } from "lucide-react";
import AccessControl from "@/components/AccessControl";
import { apiClient } from "@/lib/api";

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

export default function KepalaYayasanRekapEvaluasiPage() {
  const params = useParams();
  const router = useRouter();
  const userId = params?.id as string;

  const [evaluationData, setEvaluationData] = useState<EvaluationRecord[]>([]);
  const [allEvaluations, setAllEvaluations] = useState<EvaluationRecord[]>([]);
  const [userProfile, setUserProfile] = useState<UserProfile | null>(null);
  const [tahunAjaranList, setTahunAjaranList] = useState<TahunAjaran[]>([]);
  const [selectedTahunAjaran, setSelectedTahunAjaran] = useState<string>("");
  const [kategoriEvaluasiList, setKategoriEvaluasiList] = useState<
    Array<{ id: number; nama: string }>
  >([]);
  const [isLoading, setIsLoading] = useState(true);

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
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();
  }, [userId]);

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
    }
  }, [
    selectedTahunAjaran,
    allEvaluations,
    tahunAjaranList.length,
    kategoriEvaluasiList,
  ]);

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
    <AccessControl allowedRoles={["kepala yayasan"]}>
      <div className="min-h-screen bg-gray-50 p-4 md:p-8 overflow-x-hidden">
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
                    <div className="min-w-0 overflow-hidden">
                      <h2 className="text-2xl font-bold text-gray-800">
                        {userProfile.profile_pribadi?.nama_lengkap ||
                          "Nama Belum Diisi"}
                      </h2>
                      <p className="text-gray-600 truncate">{userProfile.email}</p>
                    </div>
                  </div>
                ) : (
                  <p className="text-gray-500">User tidak ditemukan</p>
                )}
              </div>

              {/* Evaluation Display */}
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
                  <div>
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
                          }}
                          className="px-3 py-2 border border-gray-200 text-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800"
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
                                          checked={evaluation.nilai === nilai}
                                          disabled
                                          className="w-5 h-5 text-sky-800 focus:ring-sky-800 cursor-not-allowed opacity-60"
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
                                  {totalSkor}
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
                            value={catatanEvaluasi}
                            disabled
                            rows={5}
                            className="w-full px-4 py-3 border border-gray-300 text-gray-700 rounded-lg bg-gray-50 cursor-not-allowed resize-none"
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
                      </>
                    )}
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </AccessControl>
  );
}
