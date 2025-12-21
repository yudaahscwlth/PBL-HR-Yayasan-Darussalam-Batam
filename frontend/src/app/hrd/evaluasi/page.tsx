"use client";

import AccessControl from "@/components/AccessControl";
import { useRouter } from "next/navigation";
import { useState, useEffect } from "react";
import axios from "axios";
import { ArrowLeft, Loader2 } from "lucide-react";
import { useAuthStore } from "@/store/authStore";
import toast from "react-hot-toast";

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  profile_pribadi?: {
    nama_lengkap: string;
  };
}

interface KategoriEvaluasi {
  id: number;
  nama: string;
}

interface TahunAjaran {
  id: number;
  nama: string;
  semester: string;
  is_aktif: boolean;
}

export default function HRDEvaluasiPage() {
  const router = useRouter();
  const { user: currentUser } = useAuthStore();

  const [users, setUsers] = useState<User[]>([]);
  const [kategoriEvaluasi, setKategoriEvaluasi] = useState<KategoriEvaluasi[]>(
    []
  );
  const [tahunAjaran, setTahunAjaran] = useState<TahunAjaran[]>([]);

  const [selectedUser, setSelectedUser] = useState<string>("");
  const [selectedTahun, setSelectedTahun] = useState<string>("");
  const [evaluations, setEvaluations] = useState<Record<number, number>>({});
  const [catatan, setCatatan] = useState<string>("");

  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [evaluationExists, setEvaluationExists] = useState(false);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setIsLoading(true);
        const token = localStorage.getItem("auth_token");

        if (!token) {
          console.error("No token found in localStorage");
          toast.error("Sesi anda telah berakhir. Silakan login kembali.");
          setIsLoading(false);
          return;
        }

        const headers = { Authorization: `Bearer ${token}` };

        console.log("Fetching evaluation data...");
        const [usersRes, kategoriRes, tahunRes] = await Promise.all([
          axios.get(
            `${
              process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000"
            }/api/users`,
            { headers }
          ),
          axios.get(
            `${
              process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000"
            }/api/kategori-evaluasi`,
            { headers }
          ),
          axios.get(
            `${
              process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000"
            }/api/tahun-ajaran`,
            { headers }
          ),
        ]);

        console.log("Data fetched successfully");
        const filteredUsers = usersRes.data.data.filter(
          (u: User) => u.id !== currentUser?.id
        );
        setUsers(filteredUsers);

        setKategoriEvaluasi(kategoriRes.data.data);
        setTahunAjaran(tahunRes.data.data);

        const activeTahun = tahunRes.data.data.find(
          (t: TahunAjaran) => t.is_aktif
        );
        if (activeTahun) {
          setSelectedTahun(activeTahun.id.toString());
        }
      } catch (error) {
        console.error("Error fetching data:", error);
        toast.error("Gagal memuat data. Silakan coba lagi.");
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();
  }, [currentUser]);

  // Check if evaluation already exists when user and tahun ajaran are selected
  useEffect(() => {
    const checkEvaluationExists = async () => {
      if (!selectedUser || !selectedTahun) {
        setEvaluationExists(false);
        return;
      }

      try {
        const token = localStorage.getItem("auth_token");
        const response = await axios.post(
          `${
            process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000"
          }/api/evaluation/check-exists`,
          {
            id_user: parseInt(selectedUser),
            id_tahun_ajaran: parseInt(selectedTahun),
          },
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );

        setEvaluationExists(response.data.exists);

        // Load existing evaluation data if exists
        if (response.data.exists) {
          if (response.data.evaluations) {
            setEvaluations(response.data.evaluations);
          }
          if (response.data.catatan) {
            setCatatan(response.data.catatan);
          }
        } else {
          setEvaluations({});
          setCatatan("");
        }
      } catch (error) {
        console.error("Error checking evaluation existence:", error);
        setEvaluationExists(false);
      }
    };

    checkEvaluationExists();
  }, [selectedUser, selectedTahun]);

  const handleScoreChange = (kategoriId: number, score: number) => {
    setEvaluations((prev) => ({
      ...prev,
      [kategoriId]: score,
    }));
  };

  const calculateTotalScore = () => {
    const scores = Object.values(evaluations);
    if (scores.length === 0) return 0;
    const sum = scores.reduce((a, b) => a + b, 0);
    return Math.round(sum / kategoriEvaluasi.length);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedUser || !selectedTahun) {
      toast.error("Mohon pilih pegawai dan tahun ajaran.");
      return;
    }

    // Check if all categories are filled
    if (Object.keys(evaluations).length < kategoriEvaluasi.length) {
      toast.error("Mohon isi nilai untuk semua indikator penilaian.");
      return;
    }

    try {
      setIsSubmitting(true);
      const token = localStorage.getItem("auth_token");

      const evaluationData = kategoriEvaluasi.map((kategori) => ({
        id_kategori: kategori.id,
        nilai: evaluations[kategori.id] || 0,
        catatan: null as string | null,
      }));

      if (evaluationData.length > 0) {
        evaluationData[0].catatan = catatan;
      }

      await axios.post(
        `${
          process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000"
        }/api/evaluation`,
        {
          id_user: parseInt(selectedUser),
          id_tahun_ajaran: parseInt(selectedTahun),
          evaluations: evaluationData,
        },
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );

      toast.success("Evaluasi berhasil disimpan.");

      // Reset form
      setEvaluations({});
      setCatatan("");
      setSelectedUser("");
      setEvaluationExists(false);
      window.scrollTo(0, 0);
    } catch (error: any) {
      console.error("Error submitting evaluation:", error);
      toast.error(error.response?.data?.message || "Gagal menyimpan evaluasi.");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <AccessControl allowedRoles={["staff hrd"]}>
      <div className="min-h-screen bg-gray-50 px-4 py-8 md:px-8 font-sans">
        <div className="max-w-full mx-auto">
          {/* Header */}
          <div className="flex items-center gap-4 mb-8">
            <button
              onClick={() => router.back()}
              className="p-2 hover:bg-gray-200 rounded-full transition-colors"
            >
              <ArrowLeft className="w-6 h-6 text-gray-700" />
            </button>
            <h1 className="text-2xl font-bold text-gray-800">Evaluasi</h1>
          </div>

          <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <form onSubmit={handleSubmit} className="p-6 md:p-8 space-y-8">
              {/* Form Header */}
              <div className="space-y-6">
                <h2 className="text-xl font-semibold text-gray-800 border-b pb-4">
                  Evaluasi
                </h2>

                <div className="grid gap-6 max-w-lg">
                  <div className="space-y-2">
                    <label className="block text-sm font-medium text-gray-700">
                      Nama Pegawai (Yang Dinilai)
                    </label>
                    <select
                      value={selectedUser}
                      onChange={(e) => setSelectedUser(e.target.value)}
                      className="w-full px-3 text-gray-700 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-800 focus:border-sky-800 outline-none transition-all"
                      required
                    >
                      <option value="">Pilih Pegawai</option>
                      {users.map((u) => (
                        <option key={u.id} value={u.id}>
                          {u.profile_pribadi?.nama_lengkap || u.name} ({u.email}
                          )
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="space-y-2">
                    <label className="block text-sm font-medium text-gray-700">
                      Tahun Ajaran
                    </label>
                    <select
                      value={selectedTahun}
                      onChange={(e) => setSelectedTahun(e.target.value)}
                      className="w-full text-gray-700 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-800 focus:border-sky-800 outline-none transition-all"
                      required
                    >
                      <option value="">Pilih Tahun Ajaran</option>
                      {tahunAjaran.map((t) => (
                        <option key={t.id} value={t.id}>
                          {t.nama} - {t.semester} {t.is_aktif ? "(Aktif)" : ""}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="flex items-center gap-2">
                    <span className="text-sm font-medium text-gray-700">
                      Status :
                    </span>
                    <span
                      className={`px-3 py-1 text-white text-xs font-medium rounded-md ${
                        evaluationExists ? "bg-green-600" : "bg-gray-400"
                      }`}
                    >
                      {evaluationExists ? "Sudah Terisi" : "Belum Lengkap"}
                    </span>
                  </div>
                </div>
              </div>

              {/* Indicators */}
              <div className="space-y-6">
                <h3 className="text-center font-bold text-gray-800 text-sm uppercase tracking-wider">
                  Indikator Penilaian
                </h3>

                {/* Table Header */}
                <div className="overflow-x-auto">
                  <table className="w-full border-collapse">
                    <thead>
                      <tr className="border-b-2 border-gray-300">
                        <th className="text-left p-3 font-semibold text-gray-700">
                          No
                        </th>
                        <th className="text-left p-3 font-semibold text-gray-700">
                          Indikator Penilaian
                        </th>
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
                      {kategoriEvaluasi.map((kategori, index) => (
                        <tr
                          key={kategori.id}
                          className={
                            index % 2 === 0 ? "bg-gray-50" : "bg-white"
                          }
                        >
                          <td className="p-3 border-b border-gray-200 text-gray-700">
                            {index + 1}
                          </td>
                          <td className="p-3 border-b border-gray-200 text-gray-700 font-medium">
                            {kategori.nama}
                          </td>
                          {[5, 4, 3, 2, 1].map((score) => (
                            <td
                              key={score}
                              className="p-3 border-b border-gray-200 text-center"
                            >
                              <input
                                type="radio"
                                name={`evaluation-${kategori.id}`}
                                value={score}
                                checked={evaluations[kategori.id] === score}
                                onChange={() =>
                                  handleScoreChange(kategori.id, score)
                                }
                                disabled={evaluationExists}
                                className={`w-5 h-5 text-sky-800 focus:ring-sky-800 ${
                                  evaluationExists
                                    ? "cursor-not-allowed opacity-60"
                                    : "cursor-pointer"
                                }`}
                              />
                            </td>
                          ))}
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>

                {/* Additional Info Section */}
                <div className="bg-gray-100 p-4 rounded-lg space-y-3 text-sm text-gray-700">
                  <p className="font-medium">Kegiatan Pengembangan Karakter</p>
                  <ul className="list-disc list-inside space-y-1 ml-2 text-gray-600">
                    <li>Team Building and Upgrading</li>
                    <li>Workshop</li>
                    <li>Pengajian bulanan</li>
                    <li>Upacara 17 Agustus</li>
                  </ul>
                  <p className="text-xs italic text-gray-500 mt-2">
                    (Ketidakhadiran dalam kegiatan tersebut mengurangi nilai)
                  </p>
                </div>

                {/* Total Score */}
                <div className="bg-gray-100 p-4 rounded-lg flex justify-between items-center font-bold text-gray-800">
                  <span>Total Skor (Rata-rata)</span>
                  <span className="text-lg">{calculateTotalScore()}</span>
                </div>

                {/* Notes */}
                <div className="space-y-2">
                  <label className="block text-sm font-medium text-gray-700 uppercase">
                    Catatan Tambahan
                  </label>
                  <textarea
                    value={catatan}
                    onChange={(e) => setCatatan(e.target.value)}
                    rows={4}
                    disabled={evaluationExists}
                    className={`w-full px-4 py-3 border border-gray-300 text-gray-700 rounded-lg focus:ring-2 focus:ring-sky-800 focus:border-sky-800 outline-none resize-none ${
                      evaluationExists ? "bg-gray-100 cursor-not-allowed" : ""
                    }`}
                    placeholder="Tulis catatan evaluasi di sini..."
                  />
                </div>
              </div>

              {/* Submit Button */}
              {!evaluationExists && (
                <div className="pt-4">
                  <button
                    type="submit"
                    disabled={isSubmitting}
                    className="w-full bg-sky-800 hover:bg-sky-900 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2"
                  >
                    {isSubmitting ? (
                      <>
                        <Loader2 className="w-5 h-5 animate-spin" />
                        Menyimpan...
                      </>
                    ) : (
                      "Simpan Evaluasi"
                    )}
                  </button>
                </div>
              )}
            </form>
          </div>
        </div>
      </div>
    </AccessControl>
  );
}
