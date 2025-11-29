"use client";

import React, { FormEvent, useCallback, useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import AccessControl from "@/components/AccessControl";
import { apiClient } from "@/lib/api";

interface KategoriEvaluasi {
  id: number;
  nama: string;
  created_at?: string | null;
  updated_at?: string | null;
}

type AlertState =
  | {
      type: "success" | "error";
      message: string;
    }
  | null;

function Modal({ title, children, open, onClose }: { title: string; children: React.ReactNode; open: boolean; onClose: () => void }) {
  if (!open) return null;
  
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 safe-area-padding">
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />
      <div className="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl ring-1 ring-black/5 max-h-[85vh] overflow-y-auto">
        <div className="flex items-center justify-between mb-4">
          <h3 className="text-lg font-semibold text-zinc-900">{title}</h3>
          <button 
            onClick={onClose} 
            className="text-zinc-500 hover:text-zinc-700 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-sky-300 touch-manipulation"
            aria-label="Tutup modal"
          >
            <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M18 6L6 18M6 6l12 12" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </button>
        </div>
        <div>{children}</div>
      </div>
    </div>
  );
}

export default function HRDKategoriEvaluasiPage() {
  const router = useRouter();
  const [categories, setCategories] = useState<KategoriEvaluasi[]>([]);
  const [filtered, setFiltered] = useState<KategoriEvaluasi[]>([]);
  const [query, setQuery] = useState("");
  const [pageSize, setPageSize] = useState(10);
  const [page, setPage] = useState(1);
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [alert, setAlert] = useState<AlertState>(null);

  // modal state
  const [openModal, setOpenModal] = useState(false);
  const [editing, setEditing] = useState<KategoriEvaluasi | null>(null);
  const [formNama, setFormNama] = useState("");

  const loadCategories = useCallback(async () => {
    setIsLoading(true);
    setAlert(null);
    try {
      const res = await apiClient.kategoriEvaluasi.getAll();
      console.log("API Response:", res);
      console.log("Response success:", res?.success);
      console.log("Response data:", res?.data);
      if (res?.success) {
        const data = (res.data as KategoriEvaluasi[]) || [];
        console.log("Categories to set:", data);
        setCategories(data);
      } else {
        setAlert({ type: "error", message: res?.message || "Gagal memuat kategori." });
      }
    } catch (err) {
      console.error("Error loading categories:", err);
      setAlert({ type: "error", message: "Terjadi kesalahan saat memuat data." });
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    loadCategories();
  }, [loadCategories]);

  useEffect(() => {
    const q = query.trim().toLowerCase();
    const list = categories.filter((c) => c.nama.toLowerCase().includes(q));
    setFiltered(list);
    setPage(1);
  }, [query, categories]);

  const source = filtered.length ? filtered : categories;
  const totalPages = Math.max(1, Math.ceil(source.length / pageSize));
  const pageItems = source.slice((page - 1) * pageSize, page * pageSize);

  const openAdd = () => {
    setEditing(null);
    setFormNama("");
    setOpenModal(true);
  };

  const openEdit = (k: KategoriEvaluasi) => {
    setEditing(k);
    setFormNama(k.nama);
    setOpenModal(true);
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    if (!formNama.trim()) {
      setAlert({ type: "error", message: "Nama kategori harus diisi." });
      return;
    }
    setIsSubmitting(true);
    setAlert(null);
    try {
      let res;
      if (editing) res = await apiClient.kategoriEvaluasi.update(editing.id, { nama: formNama.trim() });
      else res = await apiClient.kategoriEvaluasi.create({ nama: formNama.trim() });

      if (res?.success) {
        setAlert({ type: "success", message: editing ? "Kategori diperbarui." : "Kategori ditambahkan." });
        setOpenModal(false);
        await loadCategories();
      } else {
        setAlert({ type: "error", message: res?.message || "Operasi gagal." });
      }
    } catch (err) {
      console.error(err);
      setAlert({ type: "error", message: "Terjadi kesalahan saat menyimpan." });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleDelete = async (k: KategoriEvaluasi) => {
    if (!confirm(`Hapus kategori "${k.nama}"?`)) return;
    setIsSubmitting(true);
    try {
      const res = await apiClient.kategoriEvaluasi.delete(k.id);
      if (res?.success) {
        setAlert({ type: "success", message: "Kategori dihapus." });
        await loadCategories();
      } else setAlert({ type: "error", message: res?.message || "Gagal menghapus." });
    } catch (err) {
      console.error(err);
      setAlert({ type: "error", message: "Terjadi kesalahan." });
    } finally {
      setIsSubmitting(false);
    }
  };

  // Mobile Card Component
  const MobileCard = ({ kategori }: { kategori: KategoriEvaluasi }) => (
    <div className="bg-white rounded-xl border border-zinc-200 p-4 shadow-sm">
      <div className="flex items-start justify-between">
        <div className="flex-1 min-w-0 mr-3">
          <h3 
            className="text-zinc-800 font-medium text-sm leading-5 break-words"
            dangerouslySetInnerHTML={{ __html: kategori.nama }}
          />
        </div>
        <div className="flex flex-col gap-2 shrink-0">
          <button
            onClick={() => openEdit(kategori)}
            className="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium border border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100 active:bg-sky-200 transition-colors touch-manipulation min-h-[36px]"
          >
            <svg className="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
              <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
            </svg>
            <span>Edit</span>
          </button>
          <button
            onClick={() => handleDelete(kategori)}
            className="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium border border-red-200 bg-red-500 text-white hover:bg-red-600 active:bg-red-700 transition-colors touch-manipulation min-h-[36px]"
          >
            <svg className="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
            </svg>
            <span>Hapus</span>
          </button>
        </div>
      </div>
    </div>
  );

  // Mobile Pagination
  const MobilePagination = () => (
    <div className="flex flex-col gap-3 mt-6 md:hidden">
      <div className="flex items-center justify-between text-sm text-zinc-600">
        <span className="text-xs">
          Hal. {page} dari {totalPages}
        </span>
        <span className="text-xs">
          {Math.min((page - 1) * pageSize + 1, source.length)}-{Math.min(page * pageSize, source.length)} dari {source.length}
        </span>
      </div>
      
      <div className="flex items-center gap-2">
        <button 
          onClick={() => setPage((p) => Math.max(1, p - 1))} 
          disabled={page === 1}
          className="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-zinc-300 bg-white text-zinc-700 disabled:opacity-40 disabled:cursor-not-allowed active:bg-zinc-50 transition-colors touch-manipulation text-sm font-medium"
        >
          <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M15 18l-6-6 6-6" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
          </svg>
          Sebelumnya
        </button>
        
        <button 
          onClick={() => setPage((p) => Math.min(totalPages, p + 1))} 
          disabled={page === totalPages}
          className="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-zinc-300 bg-white text-zinc-700 disabled:opacity-40 disabled:cursor-not-allowed active:bg-zinc-50 transition-colors touch-manipulation text-sm font-medium"
        >
          Selanjutnya
          <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M9 18l6-6-6-6" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </button>
      </div>
    </div>
  );

  // Desktop Pagination
  const DesktopPagination = () => (
    <div className="hidden md:flex items-center justify-between mt-6 text-sm text-zinc-600">
      <div className="text-sm">
        Menampilkan <span className="font-medium">{Math.min((page - 1) * pageSize + 1, source.length)}</span> -{" "}
        <span className="font-medium">{Math.min(page * pageSize, source.length)}</span> dari{" "}
        <span className="font-medium">{source.length}</span> entri
      </div>

      <div className="flex items-center gap-2">
        <button 
          onClick={() => setPage((p) => Math.max(1, p - 1))} 
          disabled={page === 1}
          className="px-4 py-2 rounded-lg border border-zinc-300 bg-white text-zinc-700 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-zinc-50 transition-colors"
        >
          Sebelumnya
        </button>

        <div className="flex items-center gap-1">
          {Array.from({ length: Math.min(5, totalPages) }).map((_, i) => {
            let pageNum;
            if (totalPages <= 5) {
              pageNum = i + 1;
            } else if (page <= 3) {
              pageNum = i + 1;
            } else if (page >= totalPages - 2) {
              pageNum = totalPages - 4 + i;
            } else {
              pageNum = page - 2 + i;
            }

            return (
              <button 
                key={pageNum}
                onClick={() => setPage(pageNum)}
                className={`w-10 h-10 rounded-lg flex items-center justify-center text-sm font-medium ${
                  page === pageNum 
                    ? "bg-sky-600 text-white shadow-sm" 
                    : "border border-zinc-300 bg-white hover:bg-zinc-50 text-zinc-700"
                } transition-colors`}
              >
                {pageNum}
              </button>
            );
          })}
        </div>

        <button 
          onClick={() => setPage((p) => Math.min(totalPages, p + 1))} 
          disabled={page === totalPages}
          className="px-4 py-2 rounded-lg border border-zinc-300 bg-white text-zinc-700 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-zinc-50 transition-colors"
        >
          Selanjutnya
        </button>
      </div>
    </div>
  );

  return (
    <AccessControl allowedRoles={["kepala hrd", "staff hrd"]}>
      <div className="min-h-screen bg-gradient-to-b from-white to-zinc-50 safe-area-padding">
        <div className="container mx-auto p-4 max-w-6xl">
          {/* Header */}
          <header className="mb-6">
            {/* Back Button and Title */}
            <div className="flex items-center gap-3 mb-4">
              <button
                onClick={() => router.back()}
                className="flex items-center justify-center w-10 h-10 rounded-xl border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50 active:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-sky-300 shadow-sm touch-manipulation transition-colors"
                aria-label="Kembali"
              >
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M15 18l-6-6 6-6" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </button>

              <div className="flex-1">
                <h1 className="text-xl font-bold text-sky-800 leading-tight">Kelola Kategori Evaluasi</h1>
                <p className="text-xs text-zinc-500 mt-1 leading-relaxed">
                  Atur kategori untuk evaluasi karyawan
                </p>
              </div>
            </div>

            {/* Search and Action Bar */}
            <div className="flex flex-col gap-3">
              {/* Search Input */}
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg className="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M21 21l-4.35-4.35" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
                    <circle cx="11" cy="11" r="6" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                </div>
                <input
                  value={query}
                  onChange={(e) => setQuery(e.target.value)}
                  placeholder="Cari kategori..."
                  className="w-full pl-10 pr-4 py-3 bg-white border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-300 focus:border-sky-300 text-zinc-700 placeholder-zinc-400 text-sm shadow-sm"
                />
              </div>

              {/* Action Buttons */}
              <div className="flex items-center gap-2">
                <button 
                  onClick={loadCategories}
                  className="flex items-center gap-2 px-4 py-3 bg-white border border-zinc-200 rounded-xl text-zinc-700 hover:bg-zinc-50 active:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-sky-300 shadow-sm touch-manipulation transition-colors flex-1 text-sm font-medium"
                >
                  <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                  <span>Segarkan</span>
                </button>

                <button 
                  onClick={openAdd} 
                  className="flex items-center gap-2 px-4 py-3 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white rounded-xl font-semibold shadow-sm shadow-sky-200 focus:outline-none focus:ring-2 focus:ring-sky-300 touch-manipulation transition-colors flex-1 text-sm"
                >
                  <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 5v14M5 12h14" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                  <span>Tambah</span>
                </button>
              </div>
            </div>
          </header>

          {/* Main Content */}
          <main className="bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden">
            {/* Table Controls */}
            <div className="p-4 border-b border-zinc-100">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <label className="text-sm text-zinc-600 whitespace-nowrap">Tampilkan</label>
                  <select 
                    value={pageSize} 
                    onChange={(e) => setPageSize(Number(e.target.value))}
                    className="border border-zinc-200 rounded-lg px-3 py-2 text-sm text-zinc-700 focus:outline-none focus:ring-2 focus:ring-sky-300 bg-white"
                  >
                    {[5, 10, 25, 50].map((n) => (
                      <option key={n} value={n}>
                        {n}
                      </option>
                    ))}
                  </select>
                  <span className="text-sm text-zinc-500 whitespace-nowrap">entri</span>
                </div>
              </div>
            </div>

            {/* Alert */}
            {alert && (
              <div className={`mx-4 mt-4 rounded-lg p-3 text-sm ${
                alert.type === "success" 
                  ? "bg-emerald-50 border border-emerald-200 text-emerald-700" 
                  : "bg-red-50 border border-red-200 text-red-700"
              }`}>
                {alert.message}
              </div>
            )}

            <div className="p-4">
              {/* Desktop Table */}
              <div className="hidden md:block">
                <table className="w-full text-sm">
                  <thead className="bg-zinc-50">
                    <tr>
                      <th className="px-4 py-3 text-left text-zinc-600 font-semibold text-sm rounded-l-xl">Nama Kategori</th>
                      <th className="px-4 py-3 text-zinc-600 font-semibold text-sm w-48 text-center rounded-r-xl">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    {isLoading ? (
                      <tr>
                        <td colSpan={2} className="px-4 py-8 text-center text-zinc-400">
                          <div className="flex items-center justify-center gap-2">
                            <div className="w-4 h-4 border-2 border-sky-600 border-t-transparent rounded-full animate-spin"></div>
                            Memuat kategori...
                          </div>
                        </td>
                      </tr>
                    ) : pageItems.length === 0 ? (
                      <tr>
                        <td colSpan={2} className="px-4 py-8 text-center text-zinc-400">
                          <div className="flex flex-col items-center gap-2 py-4">
                            <svg className="w-12 h-12 text-zinc-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                              <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                            <span className="text-sm">Tidak ada kategori yang ditemukan</span>
                          </div>
                        </td>
                      </tr>
                    ) : (
                      pageItems.map((k, index) => (
                        <tr key={k.id} className={`border-t border-zinc-100 ${index % 2 === 0 ? 'bg-white' : 'bg-zinc-50'}`}>
                          <td className="px-4 py-4">
                            <div className="text-zinc-800 font-medium text-sm" dangerouslySetInnerHTML={{ __html: k.nama }}></div>
                          </td>
                          <td className="px-4 py-4">
                            <div className="flex items-center justify-center gap-2">
                              <button
                                onClick={() => openEdit(k)}
                                className="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium border border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-300 transition-colors"
                              >
                                <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                  <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
                                  <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
                                </svg>
                                Edit
                              </button>
                              <button
                                onClick={() => handleDelete(k)}
                                className="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium border border-red-200 bg-red-500 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 transition-colors"
                              >
                                <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                  <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
                                </svg>
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

              {/* Mobile Cards */}
              <div className="md:hidden">
                {isLoading ? (
                  <div className="text-center py-8 text-zinc-400">
                    <div className="flex items-center justify-center gap-2">
                      <div className="w-4 h-4 border-2 border-sky-600 border-t-transparent rounded-full animate-spin"></div>
                      <span className="text-sm">Memuat kategori...</span>
                    </div>
                  </div>
                ) : pageItems.length === 0 ? (
                  <div className="text-center py-8 text-zinc-400">
                    <div className="flex flex-col items-center gap-2 py-4">
                      <svg className="w-16 h-16 text-zinc-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
                      </svg>
                      <span className="text-sm text-zinc-500">Tidak ada kategori yang ditemukan</span>
                    </div>
                  </div>
                ) : (
                  <div className="space-y-3">
                    {pageItems.map((k) => (
                      <MobileCard key={k.id} kategori={k} />
                    ))}
                  </div>
                )}
              </div>

              {/* Pagination */}
              <MobilePagination />
              <DesktopPagination />
            </div>
          </main>
        </div>

        {/* Modal */}
        <Modal title={editing ? "Edit Kategori" : "Tambah Kategori"} open={openModal} onClose={() => setOpenModal(false)}>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="text-sm font-medium text-zinc-700 block mb-2">Nama Kategori</label>
              <input
                value={formNama}
                onChange={(e) => setFormNama(e.target.value)}
                className="w-full border border-zinc-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:border-sky-300 text-zinc-700 text-sm"
                placeholder="Masukkan nama kategori"
                autoFocus
              />
            </div>

            <div className="flex items-center gap-3 pt-2">
              <button 
                type="button" 
                onClick={() => setOpenModal(false)}
                className="flex-1 px-4 py-3 rounded-xl border border-zinc-300 text-zinc-700 hover:bg-zinc-50 active:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-sky-300 transition-colors touch-manipulation text-sm font-medium"
              >
                Batal
              </button>
              <button 
                type="submit" 
                disabled={isSubmitting}
                className="flex-1 px-4 py-3 rounded-xl bg-sky-600 text-white font-semibold hover:bg-sky-700 active:bg-sky-800 disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-sky-300 transition-colors touch-manipulation text-sm"
              >
                {isSubmitting ? (
                  <div className="flex items-center justify-center gap-2">
                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    Menyimpan...
                  </div>
                ) : editing ? "Perbarui" : "Simpan"}
              </button>
            </div>
          </form>
        </Modal>
      </div>
    </AccessControl>
  );
}