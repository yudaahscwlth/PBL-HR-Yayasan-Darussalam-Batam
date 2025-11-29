"use client";

import AccessControl from "@/components/AccessControl";
import { useRouter } from "next/navigation";
import { useState, useEffect } from "react";
import { apiClient } from "@/lib/api";
import { ArrowLeft, Loader2 } from "lucide-react";
import { useAuthStore } from "@/store/authStore";

interface Evaluasi {
  id: number;
  id_user: number;
  id_penilai: number;
  id_kategori: number;
  id_tahun_ajaran: number;
  nilai: number;
  catatan: string | null;
  kategori_evaluasi?: {
    id: number;
    nama: string;
  };
  penilai?: {
    id: number;
    profile_pribadi?: {
      nama_lengkap: string;
    };
  };
  tahun_ajaran?: {
    id: number;
    nama: string;
    semester: string;
  };
}

interface TahunAjaran {
  id: number;
  nama: string;
  semester: string;
  is_aktif: boolean;
}

export default function KepalaSekolahEvaluasiPribadiPage() {
  const router = useRouter();
  const { user } = useAuthStore();
  
  const [evaluations, setEvaluations] = useState<Evaluasi[]>([]);
  const [tahunAjaranList, setTahunAjaranList] = useState<TahunAjaran[]>([]);
  const [selectedTahunAjaran, setSelectedTahunAjaran] = useState<string>("");
  const [filteredEvaluations, setFilteredEvaluations] = useState<Evaluasi[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setIsLoading(true);
        
        const evalRes = await apiClient.evaluation.getPersonal();
        if (evalRes?.success && evalRes.data) {
          const evalData = Array.isArray(evalRes.data) ? evalRes.data : [];
          setEvaluations(evalData);
          
          const tahunAjarans = evalData
            .map((e: Evaluasi) => e.tahun_ajaran)
            .filter((t): t is TahunAjaran => t !== undefined)
            .reduce((acc: TahunAjaran[], tahun: TahunAjaran) => {
              if (!acc.find(t => t.id === tahun.id)) {
                acc.push(tahun);
              }
              return acc;
            }, [])
            .sort((a, b) => b.nama.localeCompare(a.nama));
          
          setTahunAjaranList(tahunAjarans);
          
          const activeTahun = tahunAjarans.find(t => t.is_aktif);
          if (activeTahun) {
            setSelectedTahunAjaran(activeTahun.id.toString());
          } else if (tahunAjarans.length > 0) {
            setSelectedTahunAjaran(tahunAjarans[0].id.toString());
          }
        }
      } catch (error) {
        console.error("Error fetching evaluation data:", error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();
  }, []);

  useEffect(() => {
    if (selectedTahunAjaran && evaluations.length > 0) {
      const filtered = evaluations.filter(
        (e) => e.id_tahun_ajaran.toString() === selectedTahunAjaran
      );
      setFilteredEvaluations(filtered);
    } else {
      setFilteredEvaluations([]);
    }
  }, [selectedTahunAjaran, evaluations]);

  const groupedEvaluations = filteredEvaluations.reduce((acc, evaluation) => {
    const kategoriId = evaluation.id_kategori;
    if (!acc[kategoriId]) {
      acc[kategoriId] = {
        kategori: evaluation.kategori_evaluasi || { id: kategoriId, nama: "Unknown" },
        nilai: evaluation.nilai,
        catatan: evaluation.catatan,
      };
    }
    return acc;
  }, {} as Record<number, { kategori: { id: number; nama: string }; nilai: number; catatan: string | null }>);

  const kategoriList = Object.values(groupedEvaluations);
  const totalScore = kategoriList.reduce((sum, item) => sum + item.nilai, 0);
  const averageScore = kategoriList.length > 0 ? Math.round(totalScore / kategoriList.length) : 0;
  const catatan = filteredEvaluations.find(e => e.catatan)?.catatan || "";
  const penilai = filteredEvaluations[0]?.penilai;

  return (
    <AccessControl allowedRoles={["kepala sekolah"]}>
      <div className="min-h-screen bg-gray-50 px-4 py-8 md:px-8 font-sans">
        <div className="max-w-full mx-auto">
          <div className="flex items-center gap-4 mb-8">
            <button
              onClick={() => router.back()}
              className="p-2 hover:bg-gray-200 rounded-full transition-colors"
            >
              <ArrowLeft className="w-6 h-6 text-gray-700" />
            </button>
            <h1 className="text-2xl font-bold text-gray-800">Evaluasi Pribadi</h1>
          </div>

          {isLoading ? (
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-8 flex items-center justify-center">
              <Loader2 className="w-8 h-8 animate-spin text-sky-800" />
            </div>
          ) : (
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              <div className="p-6 md:p-8 space-y-6">
                <div className="space-y-4">
                  <h2 className="text-xl font-semibold text-gray-800 border-b pb-4">
                    Evaluasi Pribadi {user?.profile_pribadi?.nama_lengkap || user?.email}
                  </h2>
                  
                  <div className="grid gap-4 max-w-lg">
                    <div className="flex items-center gap-2">
                      <span className="text-sm font-medium text-gray-700">Nama Penilai :</span>
                      <span className="text-sm text-gray-600">
                        {penilai?.profile_pribadi?.nama_lengkap || "-"}
                      </span>
                    </div>

                    <div className="space-y-2">
                      <label className="block text-sm font-medium text-gray-700">
                        Tahun Ajaran
                      </label>
                      <select
                        value={selectedTahunAjaran}
                        onChange={(e) => setSelectedTahunAjaran(e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-800 focus:border-sky-800 outline-none transition-all text-gray-900"
                      >
                        <option value="" className="text-gray-900">Pilih Tahun Ajaran</option>
                        {tahunAjaranList.map((t) => (
                          <option key={t.id} value={t.id} className="text-gray-900">
                            {t.nama} - {t.semester} {t.is_aktif ? "(Aktif)" : ""}
                          </option>
                        ))}
                      </select>
                    </div>

                    <div className="flex items-center gap-2">
                      <span className="text-sm font-medium text-gray-700">Status :</span>
                      <span className={`px-3 py-1 text-white text-xs font-medium rounded-md ${
                        kategoriList.length > 0 ? 'bg-green-600' : 'bg-gray-400'
                      }`}>
                        {kategoriList.length > 0 ? 'Sudah Terisi' : 'Belum Terisi'}
                      </span>
                    </div>
                  </div>
                </div>

                {selectedTahunAjaran && kategoriList.length > 0 ? (
                  <>
                    <div className="overflow-x-auto">
                      <table className="w-full border-collapse">
                        <thead>
                          <tr className="border-b-2 border-gray-300">
                            <th className="text-left p-3 font-semibold text-gray-700">No</th>
                            <th className="text-left p-3 font-semibold text-gray-700">Indikator Penilaian</th>
                            <th className="text-center p-3 font-semibold text-gray-700">
                              <div>Sangat Baik</div>
                              <div className="text-xs font-normal">(5)</div>
                            </th>
                            <th className="text-center p-3 font-semibold text-gray-700">
                              <div>Baik</div>
                              <div className="text-xs font-normal">(4)</div>
                            </th>
                            <th className="text-center p-3 font-semibold text-gray-700">
                              <div>Sedang</div>
                              <div className="text-xs font-normal">(3)</div>
                            </th>
                            <th className="text-center p-3 font-semibold text-gray-700">
                              <div>Kurang</div>
                              <div className="text-xs font-normal">(2)</div>
                            </th>
                            <th className="text-center p-3 font-semibold text-gray-700">
                              <div>Sangat Kurang</div>
                              <div className="text-xs font-normal">(1)</div>
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          {kategoriList.map((item, index) => (
                            <tr 
                              key={item.kategori.id}
                              className={index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}
                            >
                              <td className="p-3 border-b border-gray-200 text-gray-700">{index + 1}</td>
                              <td className="p-3 border-b border-gray-200 text-gray-700 font-medium">
                                {item.kategori.nama}
                              </td>
                              {[5, 4, 3, 2, 1].map((score) => (
                                <td key={score} className="p-3 border-b border-gray-200 text-center">
                                  <input
                                    type="radio"
                                    checked={item.nilai === score}
                                    disabled
                                    className="w-5 h-5 text-sky-800 cursor-not-allowed opacity-60"
                                  />
                                </td>
                              ))}
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>

                    <div className="bg-gray-100 p-4 rounded-lg flex justify-between items-center font-bold text-gray-800">
                      <span>Total Skor (Rata-rata)</span>
                      <span className="text-lg">{averageScore}</span>
                    </div>

                    {catatan && (
                      <div className="space-y-2">
                        <label className="block text-sm font-medium text-gray-700 uppercase">
                          Catatan Tambahan
                        </label>
                        <textarea
                          value={catatan}
                          disabled
                          rows={4}
                          className="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed resize-none text-gray-900"
                        />
                      </div>
                    )}
                  </>
                ) : selectedTahunAjaran ? (
                  <div className="text-center py-8 text-gray-500">
                    <p>Belum ada data evaluasi untuk tahun ajaran yang dipilih.</p>
                  </div>
                ) : (
                  <div className="text-center py-8 text-gray-500">
                    <p>Pilih tahun ajaran untuk melihat evaluasi.</p>
                  </div>
                )}
              </div>
            </div>
          )}
        </div>
      </div>
    </AccessControl>
  );
}

