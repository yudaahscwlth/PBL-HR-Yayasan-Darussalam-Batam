"use client";

import { useState, useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import { apiClient } from "@/lib/api";
import BottomNavbar from "@/components/BottomNavbar";

interface AttendanceRecord {
  id: number;
  tanggal: string;
  check_in: string | null;
  check_out: string | null;
  status: string;
  keterangan: string | null;
  file_pendukung: string | null;
  latitude_in: number | null;
  longitude_in: number | null;
  latitude_out: number | null;
  longitude_out: number | null;
  created_at: string;
  updated_at: string;
}

export default function TenagaPendidikAbsensiPribadi() {
  const { user } = useAuthStore();
  const [attendanceData, setAttendanceData] = useState<AttendanceRecord[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(10);
  const [totalRecords, setTotalRecords] = useState(0);

  // Fetch attendance data
  useEffect(() => {
    const loadAttendanceData = async () => {
      try {
        setIsLoading(true);
        const response = await apiClient.attendance.getHistory();

        if (response.success && response.data) {
          const data = response.data as AttendanceRecord[];
          setAttendanceData(data);
          setTotalRecords(data.length);
        }
      } catch (error) {
        console.error("Error loading attendance data:", error);
      } finally {
        setIsLoading(false);
      }
    };

    loadAttendanceData();
  }, []);

  // Filter data based on search term
  const filteredData = attendanceData.filter(
    (record) => record.tanggal.toLowerCase().includes(searchTerm.toLowerCase()) || record.status.toLowerCase().includes(searchTerm.toLowerCase()) || (record.keterangan && record.keterangan.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  // Pagination
  const totalPages = Math.ceil(filteredData.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const currentData = filteredData.slice(startIndex, endIndex);

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  };

  const formatTime = (timeString: string | null) => {
    if (!timeString) return "-";
    const time = new Date(timeString);
    return time.toLocaleTimeString("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case "hadir":
        return "bg-green-100 text-green-800";
      case "terlambat":
        return "bg-blue-100 text-blue-800";
      case "tidak hadir":
        return "bg-red-100 text-red-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  return (
    <div className="min-h-screen bg-gray-100 pb-28">
      {/* Header Section */}
      <div className="px-5 pt-3 pb-4">
        <div className="flex items-center gap-4">
          <button onClick={() => window.history.back()} className="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors flex items-center gap-2">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
          </button>
          <h1 className="text-xl font-bold text-gray-800">Absensi Pribadi</h1>
        </div>
      </div>

      {/* Content */}
      <div className="px-5 mt-4">
        <div className="bg-white rounded-2xl shadow-md p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-bold text-gray-800">Absensi Pribadi</h2>
            <button className="bg-[#1e4d8b] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">Add New</button>
          </div>

          {/* Search and Filter */}
          <div className="flex flex-col sm:flex-row gap-4 mb-4">
            <div className="flex-1">
              <input
                type="text"
                placeholder="Cari berdasarkan tanggal, status, atau keterangan..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div className="flex items-center gap-2">
              <label className="text-sm text-gray-600">Show:</label>
              <select value={itemsPerPage} onChange={(e) => setItemsPerPage(Number(e.target.value))} className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value={10}>10</option>
                <option value={25}>25</option>
                <option value={50}>50</option>
                <option value={100}>100</option>
              </select>
            </div>
          </div>

          {/* Data Table */}
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-gray-50 border-b">
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">Tanggal</th>
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">Check In</th>
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">Check Out</th>
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">Presensi</th>
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">Keterangan</th>
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">File Pendukung</th>
                  <th className="text-left py-3 px-4 font-semibold text-gray-700">Aksi</th>
                </tr>
              </thead>
              <tbody>
                {isLoading ? (
                  <tr>
                    <td colSpan={7} className="text-center py-8 text-gray-500">
                      <div className="flex items-center justify-center">
                        <div className="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mr-2"></div>
                        Loading...
                      </div>
                    </td>
                  </tr>
                ) : currentData.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="text-center py-8 text-gray-500">
                      Tidak ada data absensi
                    </td>
                  </tr>
                ) : (
                  currentData.map((record) => (
                    <tr key={record.id} className="border-b hover:bg-gray-50">
                      <td className="py-3 px-4 text-gray-700">{formatDate(record.tanggal)}</td>
                      <td className="py-3 px-4 text-gray-700">{formatTime(record.check_in)}</td>
                      <td className="py-3 px-4 text-gray-700">{formatTime(record.check_out)}</td>
                      <td className="py-3 px-4">
                        <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(record.status)}`}>{record.status}</span>
                      </td>
                      <td className="py-3 px-4 text-gray-700">{record.keterangan || "-"}</td>
                      <td className="py-3 px-4">
                        {record.file_pendukung ? (
                          <div className="w-8 h-8 bg-gray-200 rounded flex items-center justify-center">
                            <svg className="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                            </svg>
                          </div>
                        ) : (
                          "-"
                        )}
                      </td>
                      <td className="py-3 px-4">
                        <button className="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600 transition-colors">Log Absensi</button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {/* Pagination */}
          {totalPages > 1 && (
            <div className="flex items-center justify-between mt-4">
              <div className="text-sm text-gray-600">
                Showing {startIndex + 1} to {Math.min(endIndex, filteredData.length)} of {filteredData.length} entries
              </div>
              <div className="flex items-center gap-2">
                <button
                  onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
                  disabled={currentPage === 1}
                  className="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Previous
                </button>
                <span className="px-3 py-1 bg-blue-500 text-white rounded text-sm">
                  {currentPage} of {totalPages}
                </span>
                <button
                  onClick={() => setCurrentPage((prev) => Math.min(prev + 1, totalPages))}
                  disabled={currentPage === totalPages}
                  className="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Next
                </button>
              </div>
            </div>
          )}

          {/* Legend */}
          <div className="mt-6 p-4 bg-gray-50 rounded-lg">
            <h3 className="text-sm font-semibold text-gray-700 mb-2">Legend:</h3>
            <div className="flex flex-wrap gap-4 text-xs">
              <div className="flex items-center gap-2">
                <span className="w-3 h-3 bg-green-100 rounded-full"></span>
                <span className="text-green-800">Hadir</span>
              </div>
              <div className="flex items-center gap-2">
                <span className="w-3 h-3 bg-blue-100 rounded-full"></span>
                <span className="text-blue-800">Terlambat</span>
              </div>
              <div className="flex items-center gap-2">
                <span className="w-3 h-3 bg-red-100 rounded-full"></span>
                <span className="text-red-800">Tidak Hadir</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Bottom Navigation */}
      <BottomNavbar />
    </div>
  );
}
