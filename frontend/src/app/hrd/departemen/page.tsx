'use client';

import React, { useState, useEffect } from 'react';
import { ArrowLeft, Plus, X, Loader2 } from 'lucide-react';

// Type definitions
interface ProfilePribadi {
  nama_lengkap: string;
}

interface User {
  id: number;
  name?: string;
  email: string;
  profilePribadi?: ProfilePribadi;
}

interface Departemen {
  id: number;
  nama_departemen: string;
  id_kepala_departemen?: number;
  kepala?: User;
}

interface Notification {
  message: string;
  type: 'success' | 'error';
}

interface FormData {
  id_kepala_departemen: string;
  nama_departemen: string;
}

export default function KelolaDepartemen() {
  const [showAddModal, setShowAddModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [selectedDepartemen, setSelectedDepartemen] = useState<Departemen | null>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [entriesPerPage, setEntriesPerPage] = useState(10);
  const [currentPage, setCurrentPage] = useState(1);
  
  // State untuk data
  const [dataDepartemen, setDataDepartemen] = useState<Departemen[]>([]);
  const [dataUser, setDataUser] = useState<User[]>([]);
  const [loading, setLoading] = useState(false);
  const [notification, setNotification] = useState<Notification | null>(null);

  const [formData, setFormData] = useState<FormData>({
    id_kepala_departemen: '',
    nama_departemen: ''
  });

  // Base URL API - sesuaikan dengan environment
  const API_URL = 'http://localhost:8000/api';

  // Get auth token from localStorage (dari Sanctum)
  const getAuthHeaders = () => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': token ? `Bearer ${token}` : '',
    };
  };

  // Fetch data departemen
  const fetchDepartemen = async () => {
    try {
      const response = await fetch(`${API_URL}/departemen`, {
        method: 'GET',
        headers: getAuthHeaders(),
      });

      if (!response.ok) {
        if (response.status === 401) {
          showNotification('Session expired. Please login again.', 'error');
          // Redirect to login if needed
          // window.location.href = '/login';
          return;
        }
        throw new Error('Failed to fetch data');
      }

      const result = await response.json();
      
      if (result.success) {
        setDataDepartemen(result.data.departemen || []);
        setDataUser(result.data.users || []);
      } else {
        showNotification(result.message || 'Gagal mengambil data', 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showNotification('Terjadi kesalahan saat mengambil data', 'error');
    } finally {
      setLoading(false);
    }
  };

  // Load data saat component mount
  useEffect(() => {
    fetchDepartemen();
  }, []);

  // Show notification
  const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    setNotification({ message, type });
    setTimeout(() => setNotification(null), 3000);
  };

  // Handle Add
  const handleSubmitAdd = async () => {
    if (!formData.nama_departemen) {
      showNotification('Nama departemen wajib diisi', 'error');
      return;
    }

    setLoading(true);
    try {
      const response = await fetch(`${API_URL}/departemen`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify(formData),
      });

      const result = await response.json();

      if (response.ok && result.success) {
        showNotification(result.message || 'Berhasil menambahkan data departemen', 'success');
        setShowAddModal(false);
        setFormData({ id_kepala_departemen: '', nama_departemen: '' });
        fetchDepartemen();
      } else {
        const errorMessage = result.message || result.errors 
          ? Object.values(result.errors).flat().join(', ')
          : 'Gagal menambahkan data';
        showNotification(errorMessage, 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showNotification('Terjadi kesalahan saat menambahkan data', 'error');
    } finally {
      setLoading(false);
    }
  };

  // Handle Edit
  const handleEdit = (data: Departemen) => {
    setSelectedDepartemen(data);
    setFormData({
      id_kepala_departemen: data.id_kepala_departemen?.toString() || '',
      nama_departemen: data.nama_departemen
    });
    setShowEditModal(true);
  };

  const handleSubmitEdit = async () => {
    if (!formData.nama_departemen) {
      showNotification('Nama departemen wajib diisi', 'error');
      return;
    }

    if (!selectedDepartemen) {
      showNotification('Tidak ada data yang dipilih', 'error');
      return;
    }

    setLoading(true);
    try {
      const response = await fetch(`${API_URL}/departemen/${selectedDepartemen.id}`, {
        method: 'PUT',
        headers: getAuthHeaders(),
        body: JSON.stringify(formData),
      });

      const result = await response.json();

      if (response.ok && result.success) {
        showNotification(result.message || 'Berhasil mengubah data departemen', 'success');
        setShowEditModal(false);
        fetchDepartemen();
      } else {
        const errorMessage = result.message || result.errors 
          ? Object.values(result.errors).flat().join(', ')
          : 'Gagal mengubah data';
        showNotification(errorMessage, 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showNotification('Terjadi kesalahan saat mengubah data', 'error');
    } finally {
      setLoading(false);
    }
  };

  // Handle Delete
  const handleDelete = (data: Departemen) => {
    setSelectedDepartemen(data);
    setShowDeleteModal(true);
  };

  const handleSubmitDelete = async () => {
    if (!selectedDepartemen) {
      showNotification('Tidak ada data yang dipilih', 'error');
      return;
    }

    setLoading(true);
    try {
      const response = await fetch(`${API_URL}/departemen/${selectedDepartemen.id}`, {
        method: 'DELETE',
        headers: getAuthHeaders(),
      });

      const result = await response.json();

      if (response.ok && result.success) {
        showNotification(result.message || 'Berhasil menghapus data departemen', 'success');
        setShowDeleteModal(false);
        fetchDepartemen();
      } else {
        showNotification(result.message || 'Gagal menghapus data', 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showNotification('Terjadi kesalahan saat menghapus data', 'error');
    } finally {
      setLoading(false);
    }
  };

  // Filter data
  const filteredData = dataDepartemen.filter(item =>
    item.nama_departemen.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // Pagination
  const totalPages = Math.ceil(filteredData.length / entriesPerPage);
  const startIndex = (currentPage - 1) * entriesPerPage;
  const endIndex = startIndex + entriesPerPage;
  const currentData = filteredData.slice(startIndex, endIndex);

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Notification */}
      {notification && (
        <div className={`fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg ${
          notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white animate-fade-in`}>
          {notification.message}
        </div>
      )}

      {/* Header */}
      <div className="bg-white shadow-sm sticky top-0 z-10">
        <div className="flex items-center px-4 py-3">
          <button className="mr-3" onClick={() => window.history.back()}>
            <ArrowLeft className="w-6 h-6 text-gray-700" />
          </button>
          <h1 className="text-lg font-semibold text-gray-700">Kelola Departemen</h1>
        </div>
      </div>

      {/* Main Content */}
      <div className="p-4">
        <div className="bg-white rounded-lg shadow-sm">
          <div className="p-4 border-b">
            <button 
              onClick={() => setShowAddModal(true)}
              className="bg-blue-600 text-white px-4 py-2 rounded-md flex items-center gap-2 text-sm font-medium hover:bg-blue-700 transition-colors"
            >
              <Plus className="w-4 h-4" />
              Tambah Baru
            </button>
          </div>

          {/* Controls */}
          <div className="p-4 border-b flex justify-between items-center flex-wrap gap-2">
            <div className="flex items-center gap-2">
              <span className="text-sm text-gray-700">Show</span>
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
              </select>
              <span className="text-sm text-gray-600">entries</span>
            </div>
            <div className="flex items-center gap-2 text-gray-700">
              <span className="text-sm">Search:</span>
              <input 
                type="text"
                value={searchTerm}
                onChange={(e) => {
                  setSearchTerm(e.target.value);
                  setCurrentPage(1);
                }}
                className="border border-gray-300 rounded px-2 py-1 text-sm w-32"
                placeholder="Cari..."
              />
            </div>
          </div>

          {/* Table Header */}
          <div className="border-b bg-gray-50">
            <div className="px-4 py-3">
              <h3 className="text-sm font-semibold text-gray-700">Nama departemen</h3>
            </div>
          </div>

          {/* Data List */}
          <div className="divide-y">
            {currentData.length > 0 ? (
              currentData.map((data) => (
                <div key={data.id} className="px-4 py-3 hover:bg-gray-50 transition-colors">
                  <div className="flex justify-between items-center">
                    <div>
                      <p className="text-sm text-gray-900 capitalize font-medium">{data.nama_departemen}</p>
                      <p className="text-xs text-gray-500 mt-1">
                        Kepala: {data.kepala?.profilePribadi?.nama_lengkap || 'Belum ada kepala departemen'}
                      </p>
                    </div>
                    <div className="flex gap-1">
                      <button
                        onClick={() => handleEdit(data)}
                        className="p-2 text-blue-600 hover:bg-blue-50 rounded-md"
                        title="Edit"
                      >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button
                        onClick={() => handleDelete(data)}
                        className="p-2 text-red-600 hover:bg-red-50 rounded-md"
                        title="Hapus"
                      >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              ))
            ) : (
              <div className="px-4 py-8 text-center text-gray-500">
                {searchTerm ? 'Tidak ada data yang sesuai dengan pencarian' : 'Tidak ada data departemen'}
              </div>
            )}
          </div>

          {/* Pagination */}
          {filteredData.length > 0 && (
            <div className="p-4 flex justify-between items-center text-sm border-t">
              <span className="text-gray-600">
                Showing {currentData.length > 0 ? startIndex + 1 : 0} to {Math.min(endIndex, filteredData.length)} of {filteredData.length} entries
              </span>
              <div className="flex gap-2">
                <button 
                  onClick={() => setCurrentPage(prev => Math.max(1, prev - 1))}
                  disabled={currentPage === 1}
                  className="px-3 py-1 border rounded text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Previous
                </button>
                <button className="px-3 py-1 bg-blue-600 text-white rounded">
                  {currentPage}
                </button>
                <button 
                  onClick={() => setCurrentPage(prev => Math.min(totalPages, prev + 1))}
                  disabled={currentPage === totalPages || totalPages === 0}
                  className="px-3 py-1 border rounded text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
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
        <div className="fixed inset-0 bg-black/50 bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between p-4 border-b sticky top-0 bg-white text-gray-600">
              <h2 className="text-lg font-semibold">Tambah Data Departemen</h2>
              <button onClick={() => setShowAddModal(false)} disabled={loading}>
                <X className="w-5 h-5" />
              </button>
            </div>
            
            <div className="p-4">
              <div className="space-y-4 text-gray-700">
                <div>
                  <label className="block text-sm font-medium mb-1">Nama Departemen <span className="text-red-500">*</span></label>
                  <input
                    type="text"
                    value={formData.nama_departemen}
                    onChange={(e) => setFormData({...formData, nama_departemen: e.target.value})}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Masukkan nama departemen"
                    disabled={loading}
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium mb-1">Kepala Departemen</label>
                  <select
                    value={formData.id_kepala_departemen}
                    onChange={(e) => setFormData({...formData, id_kepala_departemen: e.target.value})}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    disabled={loading}
                  >
                    <option value="">Pilih Kepala Departemen (Opsional)</option>
                    {dataUser.map((user) => (
                      <option key={user.id} value={user.id}>
                        {user.profilePribadi?.nama_lengkap || user.name || user.email}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
              
              <div className="flex gap-2 mt-6">
                <button
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  disabled={loading}
                  className="flex-1 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50 disabled:opacity-50 text-gray-700"
                >
                  Batal
                </button>
                <button
                  type="button"
                  onClick={handleSubmitAdd}
                  disabled={loading}
                  className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
                >
                  {loading ? 'Menyimpan...' : 'Simpan'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Edit Modal */}
      {showEditModal && (
        <div className="fixed inset-0 bg-black/50 bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between p-4 border-b sticky top-0 bg-white text-gray-600">
              <h2 className="text-lg font-semibold">Edit Data Departemen</h2>
              <button onClick={() => setShowEditModal(false)} disabled={loading}>
                <X className="w-5 h-5" />
              </button>
            </div>
            
            <div className="p-4">
              <div className="space-y-4 text-gray-700">
                <div>
                  <label className="block text-sm font-medium mb-1">Nama Departemen <span className="text-red-500">*</span></label>
                  <input
                    type="text"
                    value={formData.nama_departemen}
                    onChange={(e) => setFormData({...formData, nama_departemen: e.target.value})}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    disabled={loading}
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium mb-1">Kepala Departemen</label>
                  <select
                    value={formData.id_kepala_departemen}
                    onChange={(e) => setFormData({...formData, id_kepala_departemen: e.target.value})}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    disabled={loading}
                  >
                    <option value="">Pilih Kepala Departemen (Opsional)</option>
                    {dataUser.map((user) => (
                      <option key={user.id} value={user.id}>
                        {user.profilePribadi?.nama_lengkap || user.name || user.email}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
              
              <div className="flex gap-2 mt-6">
                <button
                  type="button"
                  onClick={() => setShowEditModal(false)}
                  disabled={loading}
                  className="flex-1 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50 disabled:opacity-50 text-gray-600"
                >
                  Batal
                </button>
                <button
                  type="button"
                  onClick={handleSubmitEdit}
                  disabled={loading}
                  className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
                >
                  {loading ? 'Menyimpan...' : 'Simpan Perubahan'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Delete Modal */}
      {showDeleteModal && selectedDepartemen && (
        <div className="fixed inset-0 bg-black/50 bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg w-full max-w-md">
            <div className="flex items-center justify-between p-4 border-b text-gray-600">
              <h2 className="text-lg font-semibold text-red-600">Hapus Data Departemen</h2>
              <button onClick={() => setShowDeleteModal(false)} disabled={loading}>
                <X className="w-5 h-5" />
              </button>
            </div>
            
            <div className="p-4">
              <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p className="text-sm text-gray-700">
                  Apakah Anda yakin ingin menghapus departemen:
                </p>
                <p className="text-base font-semibold text-gray-900 mt-2 capitalize">
                  {selectedDepartemen.nama_departemen}
                </p>
              </div>
              <p className="text-sm text-gray-600">
                Tindakan ini tidak dapat dibatalkan.
              </p>
            </div>
            
            <div className="flex gap-2 p-4 border-t">
              <button
                type="button"
                onClick={() => setShowDeleteModal(false)}
                disabled={loading}
                className="flex-1 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50 disabled:opacity-50 text-gray-700"
              >
                Batal
              </button>
              <button
                type="button"
                onClick={handleSubmitDelete}
                disabled={loading}
                className="flex-1 px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 disabled:opacity-50"
              >
                {loading ? 'Menghapus...' : 'Ya, Hapus'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
