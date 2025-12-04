"use client";

import { useEffect, useMemo, useState } from "react";
import { useRouter, useParams } from "next/navigation";
import axios from "axios";
import { useAuthStore } from "@/store/authStore";
import { ChevronLeft, Plus, Trash2, Edit2, Check, Calendar, X } from "lucide-react";

interface JamKerja {
  id: number;
  id_jabatan: number;
  hari: string;
  jam_masuk: string | null;
  jam_pulang: string | null;
  is_libur: boolean;
  keterangan: string | null;
}

type HariKey = "senin" | "selasa" | "rabu" | "kamis" | "jumat" | "sabtu" | "minggu";

const HARI_LABELS: Record<HariKey, string> = {
  senin: "Senin",
  selasa: "Selasa",
  rabu: "Rabu",
  kamis: "Kamis",
  jumat: "Jumat",
  sabtu: "Sabtu",
  minggu: "Minggu",
};

interface FormState {
  jam_masuk: string;
  jam_pulang: string;
  is_libur: "0" | "1";
  keterangan: string;
  hari: HariKey[];
}

export default function JamKerjaPage() {
  const router = useRouter();
  const params = useParams();
  const jabatanId = Array.isArray(params?.id) ? params.id[0] : params?.id;

  const { user, isAuthenticated } = useAuthStore();
  const [isLoading, setIsLoading] = useState(true);
  const [jamKerjaList, setJamKerjaList] = useState<JamKerja[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [notification, setNotification] = useState<{ type: "success" | "error"; message: string } | null>(null);
  const [showAddModal, setShowAddModal] = useState(false);

  // NEW: store nama jabatan
  const [jabatanName, setJabatanName] = useState<string>("");

  const [form, setForm] = useState<FormState>({
    jam_masuk: "07:30",
    jam_pulang: "16:00",
    is_libur: "0",
    keterangan: "",
    hari: [],
  });

  const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

  // Group jam kerja by hari for table
  const jamKerjaByHari = useMemo(() => {
    const map: Record<HariKey, JamKerja | null> = {
      senin: null,
      selasa: null,
      rabu: null,
      kamis: null,
      jumat: null,
      sabtu: null,
      minggu: null,
    };

    jamKerjaList.forEach((jk) => {
      const key = jk.hari as HariKey;
      map[key] = jk;
    });

    return map;
  }, [jamKerjaList]);

  useEffect(() => {
    if (!jabatanId) return;

    if (!isAuthenticated) {
      router.push("/");
      return;
    }

    if (user) {
      const isHRD = user.roles?.includes("kepala hrd") || user.roles?.includes("staff hrd");
      if (!isHRD) {
        router.push("/unauthorized");
        return;
      }
    }

    // fetch both nama jabatan dan jam kerja
    fetchJabatanName();
    fetchJamKerja();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [jabatanId, isAuthenticated, user]);

  const showNotification = (message: string, type: "success" | "error") => {
    setNotification({ message, type });
    setTimeout(() => setNotification(null), 3500);
  };

  const fetchJamKerja = async () => {
    try {
      setIsLoading(true);
      const token = localStorage.getItem("auth_token");
      const res = await axios.get(`${API_BASE_URL}/api/jabatan/${jabatanId}/jam-kerja`, {
        headers: { Authorization: `Bearer ${token}` },
      });

      if (res.data?.success) {
        setJamKerjaList(res.data.data || []);
      } else {
        showNotification(res.data?.message || "Gagal memuat jam kerja", "error");
      }
    } catch (err) {
      console.error(err);
      showNotification("Terjadi kesalahan saat memuat jam kerja", "error");
    } finally {
      setIsLoading(false);
    }
  };

  // NEW: fetch nama jabatan
  const fetchJabatanName = async () => {
    try {
      const token = localStorage.getItem("auth_token");
      const res = await axios.get(`${API_BASE_URL}/api/jabatan/${jabatanId}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
  
      if (res.data?.success && res.data.data) {
        // utamakan nama_jabatan, fallback ke nama kalau ada
        const nama =
          res.data.data.nama_jabatan ||
          res.data.data.nama ||
          "Tidak diketahui";
        setJabatanName(nama);
      } else {
        setJabatanName("Tidak diketahui");
      }
    } catch (err) {
      console.error("Gagal memuat nama jabatan:", err);
      setJabatanName("Tidak diketahui");
    }
  };

  const handleCheckboxChange = (hari: HariKey) => {
    setForm((prev) => {
      const exists = prev.hari.includes(hari);
      return {
        ...prev,
        hari: exists ? prev.hari.filter((h) => h !== hari) : [...prev.hari, hari],
      };
    });
  };

  const normalizeTimeValue = (value: string): string | null => {
    return value && value.trim() !== "" ? value : null;
  };

  const handleStatusChange = (value: "0" | "1") => {
    setForm((prev) => {
      let nextKeterangan = prev.keterangan;

      if (value === "1" && nextKeterangan.trim() === "") {
        nextKeterangan = "Libur";
      } else if (value === "0" && nextKeterangan === "Libur") {
        nextKeterangan = "";
      }

      return {
        ...prev,
        is_libur: value,
        keterangan: nextKeterangan,
      };
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!jabatanId) return;

    // When creating new, require at least one day
    if (!editingId && form.hari.length === 0) {
      showNotification("Pilih minimal satu hari", "error");
      return;
    }

    try {
      setIsSubmitting(true);
      const token = localStorage.getItem("auth_token");
      const jamMasukValue = form.is_libur === "1" ? null : normalizeTimeValue(form.jam_masuk);
      const jamPulangValue = form.is_libur === "1" ? null : normalizeTimeValue(form.jam_pulang);

      if (editingId) {
        const payload = {
          jam_masuk: jamMasukValue,
          jam_pulang: jamPulangValue,
          is_libur: form.is_libur,
          keterangan: form.keterangan || null,
        };

        const res = await axios.put(
          `${API_BASE_URL}/api/jabatan/${jabatanId}/jam-kerja/${editingId}`,
          payload,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        if (res.data?.success) {
          showNotification(res.data.message || "Jam kerja berhasil diperbarui", "success");
          setEditingId(null);
          setForm((prev) => ({ ...prev, hari: [] }));
          setShowAddModal(false);
          fetchJamKerja();
        } else {
          showNotification(res.data?.message || "Gagal memperbarui jam kerja", "error");
        }
      } else {
        const payload = {
          jam_masuk: jamMasukValue,
          jam_pulang: jamPulangValue,
          is_libur: form.is_libur,
          keterangan: form.keterangan || null,
          hari: form.hari,
        };

        const res = await axios.post(
          `${API_BASE_URL}/api/jabatan/${jabatanId}/jam-kerja`,
          payload,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        if (res.data?.success) {
          showNotification(res.data.message || "Jam kerja berhasil disimpan", "success");
          setForm((prev) => ({ ...prev, hari: [] }));
          setShowAddModal(false);
          fetchJamKerja();
        } else {
          showNotification(res.data?.message || "Gagal menyimpan jam kerja", "error");
        }
      }
    } catch (err: any) {
      console.error(err);
      const msg = err?.response?.data?.message || "Gagal menyimpan jam kerja";
      showNotification(msg, "error");
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleDelete = async (jamKerjaId: number) => {
    if (!jabatanId) return;
    if (!confirm("Hapus jam kerja untuk hari ini?")) return;

    try {
      const token = localStorage.getItem("auth_token");
      const res = await axios.delete(
        `${API_BASE_URL}/api/jabatan/${jabatanId}/jam-kerja/${jamKerjaId}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );

      if (res.data?.success) {
        showNotification("Jam kerja berhasil dihapus", "success");
        fetchJamKerja();
      } else {
        showNotification(res.data?.message || "Gagal menghapus jam kerja", "error");
      }
    } catch (err) {
      console.error(err);
      showNotification("Terjadi kesalahan saat menghapus jam kerja", "error");
    }
  };

  const isSundaySelected = form.hari.includes("minggu");
  const isSundayEdit = Boolean(editingId && form.hari[0] === "minggu");

  const openCreateModal = () => {
    setEditingId(null);
    setForm({
      jam_masuk: "07:30",
      jam_pulang: "16:00",
      is_libur: "0",
      keterangan: "",
      hari: [],
    });
    setShowAddModal(true);
  };

  if (!jabatanId) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <p className="text-gray-600">ID jabatan tidak valid.</p>
      </div>
    );
  }

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4" />
          <p className="text-gray-600">Memuat jam kerja...</p>
        </div>
      </div>
    );
  }

  return (
    // Layout changed to a column flex so the main content can grow to fill the viewport.
    <div className="min-h-screen flex flex-col bg-gray-50">
      {/* Header */}
      <div className="bg-white px-4 sm:px-6 py-4 border-b border-gray-200 sticky top-0 z-20 shadow-sm">
        <div className="w-full mx-auto flex items-center justify-center relative">
          {/* Tombol kembali di kiri */}
          <button 
            onClick={() => router.back()} 
            className="absolute left-0 inline-flex items-center gap-2 text-gray-600 hover:text-blue-600 transition-colors"
          >
            <ChevronLeft className="w-5 h-5" />
            <span className="hidden sm:inline">Kembali</span>
          </button>
          
          {/* Judul di tengah */}
          <div className="text-center">
            <h1 className="text-lg sm:text-xl font-semibold text-gray-800">
              Kelola Jam Kerja
            </h1>
            {jabatanName && (
              <p className="text-sm text-gray-500 mt-0.5">
                {jabatanName}
              </p>
            )}
          </div>
           {/* Spacer untuk balance */}
           <div className="absolute right-0 w-20 sm:w-24"></div>

        </div>
      </div>

      {/* main set to flex-1 so it expands to fill available height; full width allowed */}
      <main className="flex-1 w-full px-4 mt-6">
        {/* Use max-w-none to allow the inner card to grow the full width of the viewport; you can adjust
            inner padding (lg:px-12) to control horizontal breathing room on very large screens. */}
        <div className="w-full max-w-none mx-auto lg:px-12">
          {/* Notification */}
          {notification && (
            <div className={`mb-4 p-3 rounded-lg shadow ${notification.type === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'}`}>
              {notification.message}
            </div>
          )}

          <div className="grid grid-cols-1 gap-6">
            {/* Card: full width and can expand vertically */}
            <div className="bg-white rounded-2xl shadow p-6 w-full min-h-[50vh]">
              <div className="mb-4">
                <h3 className="text-lg font-semibold text-gray-800 mb-3">Daftar Jam Kerja Per Hari</h3>
                <div className="flex items-center justify-between">
                  <button
                    onClick={openCreateModal}
                    className="inline-flex items-center gap-2 rounded-lg border px-3 py-2 bg-blue-600 text-white text-sm font-medium hover:shadow"
                  >
                    <Plus className="w-4 h-4" />
                    <span>Tambah Baru</span>
                  </button>
                  <div className="text-sm text-gray-500">{jamKerjaList.length} entri</div>
                </div>
              </div>

              <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-100 text-base">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-4 py-3 text-left font-medium text-gray-600">Hari</th>
                      <th className="px-4 py-3 text-left font-medium text-gray-600">Jam Masuk</th>
                      <th className="px-4 py-3 text-left font-medium text-gray-600">Jam Pulang</th>
                      <th className="px-4 py-3 text-center font-medium text-gray-600">Libur</th>
                      <th className="px-4 py-3 text-left font-medium text-gray-600">Keterangan</th>
                      <th className="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-100">
                    {(Object.keys(HARI_LABELS) as HariKey[]).map((hari) => {
                      const data = jamKerjaByHari[hari];
                      const isLibur = !!data?.is_libur;

                      return (
                        <tr key={hari} className="hover:bg-gray-50">
                          <td className="px-4 py-3 font-medium text-gray-800">{HARI_LABELS[hari]}</td>
                          <td className="px-4 py-3 text-gray-700">{data && !isLibur && data.jam_masuk ? data.jam_masuk.slice(0, 5) : '-'}</td>
                          <td className="px-4 py-3 text-gray-700">{data && !isLibur && data.jam_pulang ? data.jam_pulang.slice(0, 5) : '-'}</td>
                          <td className="px-4 py-3 text-center">
                            {data ? (
                              <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold ${isLibur ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`}>
                                {isLibur ? 'Libur' : 'Kerja'}
                              </span>
                            ) : (
                              <span className="text-xs text-gray-400">-</span>
                            )}
                          </td>
                          <td className="px-4 py-3 text-gray-700">{data?.keterangan || (data ? '-' : 'Belum diatur')}</td>
                          <td className="px-4 py-3 text-center">
                            {data ? (
                              <div className="inline-flex items-center gap-2">
                                <button
  onClick={() => {
    const hariKey = data.hari as HariKey;
    setEditingId(data.id);
    setForm({
      jam_masuk: data.jam_masuk?.slice(0, 5) || "",
      jam_pulang: data.jam_pulang?.slice(0, 5) || "",
      is_libur: data.is_libur ? "1" : "0",
      keterangan: data.keterangan || (data.is_libur ? "Libur" : ""),
      hari: [hariKey],
    });
    setShowAddModal(true);
    window.scrollTo({ top: 0, behavior: "smooth" });
  }}
  className="inline-flex items-center gap-2 px-2 py-1 rounded-md bg-yellow-200 text-yellow-800 text-xs hover:bg-yellow-300"
>
  <Edit2 className="w-3 h-3" />
  Edit
</button>

<button
  onClick={() => handleDelete(data.id)}
  className="inline-flex items-center gap-2 px-2 py-1 rounded-md bg-red-200 text-red-800 text-xs hover:bg-red-300"
>
  <Trash2 className="w-3 h-3" />
  Hapus
</button>


                              </div>
                            ) : (
                              <span className="text-xs text-gray-400">-</span>
                            )}
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>

                {/* Empty state */}
                {jamKerjaList.length === 0 && (
                  <div className="py-10 text-center text-gray-500">
                    <p className="mb-2">Belum ada data jam kerja untuk jabatan ini.</p>
                    <p className="text-sm">Gunakan tombol "Tambah Baru" untuk menambahkan jam kerja.</p>
                  </div>
                )}
              </div>

              {/* Quick summary / legend */}
              <div className="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div className="bg-white rounded-lg p-4 shadow-sm">
                  <div className="text-sm text-gray-600">Total hari terkonfigurasi</div>
                  <div className="text-2xl font-semibold text-gray-800">{jamKerjaList.length}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      {/* Add/Edit Modal */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center px-4">
          <div className="absolute inset-0 bg-black/40" onClick={() => { if (!isSubmitting) { setShowAddModal(false); setEditingId(null); } }} />

          <div className="relative w-full max-w-3xl bg-white rounded-2xl shadow-lg overflow-hidden">
            <div className="flex items-center justify-between px-6 py-4 border-b">
              <div className="flex items-center gap-3">
                <Calendar className="w-5 h-5 text-blue-600" />
                <h3 className="text-lg font-semibold text-gray-800">{editingId ? 'Edit Jam Kerja' : 'Tambah Jam Kerja'}</h3>
              </div>
              <button onClick={() => { if (!isSubmitting) { setShowAddModal(false); setEditingId(null); } }} className="p-2 rounded-full hover:bg-gray-100">
                <X className="w-4 h-4" />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Hari <span className="text-red-500">*</span></label>
                <div className="flex flex-wrap gap-2">
                  {(Object.keys(HARI_LABELS) as HariKey[]).map((hari) => {
                    const active = form.hari.includes(hari as HariKey);
                    return (
                      <button
                        key={hari}
                        type="button"
                        onClick={() => handleCheckboxChange(hari as HariKey)}
                        className={`px-3 py-1.5 rounded-full text-sm font-medium border transition-all focus:outline-none ${active ? 'bg-blue-600 text-white border-blue-600 shadow' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'}`}
                      >
                        {HARI_LABELS[hari as HariKey]}
                      </button>
                    );
                  })}
                </div>
                {!editingId && isSundaySelected && (
                  <div className="mt-2 rounded-lg border border-amber-100 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                    Hari Minggu otomatis dianggap <span className="font-semibold">libur</span>, jadi jam masuk/pulang boleh dikosongkan dan keterangan akan diisi "Libur".
                  </div>
                )}
                {isSundayEdit && (
                  <div className="mt-2 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-xs text-blue-700">
                    Konfigurasi hari Minggu selalu disimpan sebagai hari libur.
                  </div>
                )}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Status Hari</label>
                <select
                  value={form.is_libur}
                  disabled={isSundayEdit}
                  onChange={(e) => handleStatusChange(e.target.value as "0" | "1")}
                  className="w-full px-3 py-2 border border-gray-200 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 disabled:bg-gray-50 disabled:text-gray-500"
                >
                  <option value="0">Hari kerja</option>
                  <option value="1">Libur</option>
                </select>
                {isSundayEdit && (
                  <p className="mt-1 text-xs text-gray-500">Status hari tidak dapat diubah untuk hari Minggu.</p>
                )}
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Jam Masuk</label>
                  <input
                    type="time"
                    value={form.jam_masuk}
                    disabled={form.is_libur === "1" || isSundayEdit}
                    onChange={(e) => setForm((prev) => ({ ...prev, jam_masuk: e.target.value }))}
                    className="w-full px-3 py-2 border border-gray-200 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 disabled:bg-gray-50"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Jam Pulang</label>
                  <input
                    type="time"
                    value={form.jam_pulang}
                    disabled={form.is_libur === "1" || isSundayEdit}
                    onChange={(e) => setForm((prev) => ({ ...prev, jam_pulang: e.target.value }))}
                    className="w-full px-3 py-2 border border-gray-200 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 disabled:bg-gray-50"
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <input
                  type="text"
                  value={form.keterangan}
                  onChange={(e) => setForm((prev) => ({ ...prev, keterangan: e.target.value }))}
                  className="w-full px-3 py-2 border border-gray-200 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                  placeholder="Contoh: Jam kerja normal, Hari libur, Shift sore"
                />
              </div>

              <div className="flex items-center justify-end gap-3">
                <button type="button" onClick={() => { if (!isSubmitting) { setShowAddModal(false); setEditingId(null); } }} className="px-4 py-2 bg-gray-100 rounded-lg text-sm text-gray-700 hover:bg-gray-200">Batal</button>

                <button type="submit" disabled={isSubmitting} className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-60">
                  <Check className="w-4 h-4" />
                  <span>{isSubmitting ? 'Menyimpan...' : editingId ? 'Perbarui' : 'Simpan'}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
