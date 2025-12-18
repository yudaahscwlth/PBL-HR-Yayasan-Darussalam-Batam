"use client";

import { useState, useEffect } from "react";
import { useParams, useRouter } from "next/navigation";
import { useAuthStore } from "@/store/authStore";
import { apiClient } from "@/lib/api";
import BottomBack from "@/components/BottomBack";

interface LogEntry {
  id: number;
  aksi: string;
  data_lama: any;
  data_baru: any;
  user: {
    id: number;
    nama_lengkap: string;
  } | null;
  created_at: string;
}

interface AttendanceLogData {
  attendance: {
    id: number;
    tanggal: string;
    status: string;
    check_in: string | null;
    check_out: string | null;
    keterangan: string | null;
    file_pendukung: string | null;
  };
  user: {
    id: number;
    nama_lengkap: string;
    foto: string | null;
  };
  logs: LogEntry[];
}

export default function LogAbsensiPage() {
  const params = useParams();
  const router = useRouter();
  const { user } = useAuthStore();
  const [logData, setLogData] = useState<AttendanceLogData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [expandedLogs, setExpandedLogs] = useState<Set<number>>(new Set());

  const attendanceId = params?.id ? parseInt(params.id as string) : null;

  useEffect(() => {
    if (!attendanceId) {
      setIsLoading(false);
      return;
    }

    const loadLogData = async () => {
      try {
        setIsLoading(true);
        const response = await apiClient.attendance.getLog(attendanceId);

        if (response.success && response.data) {
          setLogData(response.data as AttendanceLogData);
        } else {
          alert(response.message || "Gagal memuat log absensi");
          router.back();
        }
      } catch (error: any) {
        console.error("Error loading log data:", error);
        alert(error.response?.data?.message || "Gagal memuat log absensi");
        router.back();
      } finally {
        setIsLoading(false);
      }
    };

    loadLogData();
  }, [attendanceId, router]);

  const toggleLogExpansion = (logId: number) => {
    const newExpanded = new Set(expandedLogs);
    if (newExpanded.has(logId)) {
      newExpanded.delete(logId);
    } else {
      newExpanded.add(logId);
    }
    setExpandedLogs(newExpanded);
  };

  const getActionColor = (aksi: string) => {
    switch (aksi.toLowerCase()) {
      case "created":
        return "bg-green-100 text-green-800 border-green-200";
      case "updated":
        return "bg-yellow-100 text-yellow-800 border-yellow-200";
      case "deleted":
        return "bg-red-100 text-red-800 border-red-200";
      case "restored":
        return "bg-blue-100 text-blue-800 border-blue-200";
      case "manual_create":
        return "bg-blue-100 text-blue-800 border-blue-200";
      default:
        return "bg-gray-100 text-gray-800 border-gray-200";
    }
  };

  const getActionLabel = (aksi: string) => {
    switch (aksi.toLowerCase()) {
      case "created":
        return "Dibuat";
      case "updated":
        return "Diperbarui";
      case "deleted":
        return "Dihapus";
      case "restored":
        return "Dipulihkan";
      case "manual_create":
        return "Dibuat Manual";
      default:
        return aksi;
    }
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString("id-ID", {
      day: "2-digit",
      month: "short",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  const formatDateShort = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center">
        <div className="text-center">
          <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <p className="text-gray-600">Memuat log absensi...</p>
        </div>
      </div>
    );
  }

  if (!logData) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center">
        <div className="text-center">
          <p className="text-gray-600 mb-4">Data log absensi tidak ditemukan</p>
          <button onClick={() => router.back()} className="bg-[#1e4d8b] text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            Kembali
          </button>
        </div>
      </div>
    );
  }

  const apiBaseUrl = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";
  const userPhotoUrl = logData.user.foto ? `${apiBaseUrl}/storage/${logData.user.foto}` : null;

  return (
    <div className="min-h-screen bg-gray-100 pb-28">
      {/* Header Section */}
      <div className="px-5 pt-3 pb-4">
        <div className="flex items-center gap-4">
          <BottomBack variant="inline" />
          <h1 className="text-xl font-bold text-gray-800">Log Absensi</h1>
        </div>
      </div>

      {/* Content */}
      <div className="px-5 mt-4 space-y-4">
        {/* User Info Card */}
        <div className="bg-white rounded-2xl shadow-md p-6">
          <div className="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
              <h2 className="text-lg font-bold text-gray-800">
                Log Absensi {logData.user.nama_lengkap} / {formatDateShort(logData.attendance.tanggal)}
              </h2>
              <div className="mt-2 text-sm text-gray-600">
                <p>
                  Status: <span className="font-semibold capitalize">{logData.attendance.status}</span>
                </p>
                {logData.attendance.check_in && (
                  <p>
                    Check In: <span className="font-semibold">{formatDate(logData.attendance.check_in)}</span>
                  </p>
                )}
                {logData.attendance.check_out && (
                  <p>
                    Check Out: <span className="font-semibold">{formatDate(logData.attendance.check_out)}</span>
                  </p>
                )}
              </div>
            </div>
            {userPhotoUrl && (
              <div className="flex-shrink-0">
                <img src={userPhotoUrl} alt={logData.user.nama_lengkap} className="w-24 h-24 rounded-full object-cover border-4 border-gray-200" />
              </div>
            )}
          </div>
        </div>

        {/* Logs Card */}
        <div className="bg-white rounded-2xl shadow-md p-6">
          <h3 className="text-lg font-bold text-gray-800 mb-4">Riwayat Aktivitas</h3>

          {logData.logs.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              <svg className="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <p>Tidak ada log aktivitas</p>
            </div>
          ) : (
            <div className="space-y-3">
              {logData.logs.map((log) => (
                <div key={log.id} className={`border rounded-lg ${getActionColor(log.aksi)}`}>
                  <div className="p-4">
                    <div className="flex items-start justify-between">
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-2">
                          <span className="font-semibold text-sm">{getActionLabel(log.aksi)}</span>
                          <span className="text-xs opacity-75">oleh</span>
                          <span className="font-semibold text-sm">{log.user ? log.user.nama_lengkap : "Sistem (Auto-generated)"}</span>
                        </div>
                        <p className="text-xs opacity-75">{formatDate(log.created_at)}</p>
                      </div>
                      {(log.data_lama || log.data_baru) && (
                        <button onClick={() => toggleLogExpansion(log.id)} className="ml-4 text-sm font-medium hover:opacity-75 transition-opacity flex items-center gap-1">
                          {expandedLogs.has(log.id) ? "Sembunyikan" : "Lihat Detail"}
                          <svg className={`w-4 h-4 transition-transform ${expandedLogs.has(log.id) ? "rotate-180" : ""}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                          </svg>
                        </button>
                      )}
                    </div>

                    {expandedLogs.has(log.id) && (log.data_lama || log.data_baru) && (
                      <div className="mt-4 pt-4 border-t border-current border-opacity-20 space-y-3">
                        {log.data_lama && (
                          <div>
                            <h4 className="font-semibold text-sm mb-2">Data Lama:</h4>
                            <pre className="bg-white bg-opacity-50 p-3 rounded text-xs overflow-x-auto">{JSON.stringify(log.data_lama, null, 2)}</pre>
                          </div>
                        )}
                        {log.data_baru && (
                          <div>
                            <h4 className="font-semibold text-sm mb-2">Data Baru:</h4>
                            <pre className="bg-white bg-opacity-50 p-3 rounded text-xs overflow-x-auto">{JSON.stringify(log.data_baru, null, 2)}</pre>
                          </div>
                        )}
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
