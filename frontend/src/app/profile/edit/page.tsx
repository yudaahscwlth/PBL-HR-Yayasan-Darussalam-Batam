"use client";

import { useState, useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import axios from "axios";
import { User, UserSosialMedia } from "@/types/auth";

interface ProfileData {
  email: string;
  profilePribadi: {
    id?: number;
    nomor_induk_kependudukan: string;
    nama_lengkap: string;
    tempat_lahir: string;
    tanggal_lahir: string;
    jenis_kelamin: string;
    golongan_darah: string;
    status_pernikahan: string;
    npwp: string;
    kecamatan: string;
    alamat_lengkap: string;
    no_hp: string;
    foto?: string;
  };
  profilePekerjaan: {
    id?: number;
    departemen?: string;
    tempat_kerja?: string;
    jabatan?: string;
    nomor_induk_karyawan?: string;
    tanggal_masuk?: string;
    status?: string;
  };
  orangTua: {
    id?: number;
    nama_ayah: string;
    pekerjaan_ayah: string;
    nama_ibu: string;
    pekerjaan_ibu: string;
    alamat_orang_tua: string;
  };
  keluarga: Array<{
    id?: number;
    nama: string;
    hubungan: string;
    tanggal_lahir: string;
    pekerjaan: string;
  }>;
  userSosialMedia: Array<{
    id?: number;
    id_platform: number;
    username: string;
    link: string;
    nama_platform?: string;
  }>;
}

interface SosialMediaPlatform {
  id: number;
  nama_platform: string;
}

interface KotaData {
  provinsi: string;
  kota: string[];
}

interface KecamatanData {
  name: string;
}

export default function ProfileEditPage() {
  const { user, isAuthenticated } = useAuthStore();
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [activeTab, setActiveTab] = useState("personal");
  // Initialize with user data if available
  const getInitialProfileData = (): ProfileData => {
    // Default empty data structure - will be populated from API
    return {
      email: "",
      profilePribadi: {
        nomor_induk_kependudukan: "",
        nama_lengkap: "",
        tempat_lahir: "",
        tanggal_lahir: "",
        jenis_kelamin: "",
        golongan_darah: "",
        status_pernikahan: "",
        npwp: "",
        kecamatan: "",
        alamat_lengkap: "",
        no_hp: "",
      },
      profilePekerjaan: {
        departemen: "",
        tempat_kerja: "",
        jabatan: "",
        nomor_induk_karyawan: "",
        tanggal_masuk: "",
        status: "",
      },
      orangTua: {
        nama_ayah: "",
        pekerjaan_ayah: "",
        nama_ibu: "",
        pekerjaan_ibu: "",
        alamat_orang_tua: "",
      },
      keluarga: [],
      userSosialMedia: [],
    };
  };

  const [profileData, setProfileData] = useState<ProfileData>(getInitialProfileData());
  const [sosialMediaPlatforms, setSosialMediaPlatforms] = useState<SosialMediaPlatform[]>([]);
  const [kotaData, setKotaData] = useState<KotaData[]>([]);
  const [kecamatanData, setKecamatanData] = useState<KecamatanData[]>([]);
  const [isEditMode, setIsEditMode] = useState(false);

  useEffect(() => {
    if (!isAuthenticated || !user) {
      router.push("/");
      return;
    }

    const loadData = async () => {
      try {
        setIsLoading(true);
        const token = localStorage.getItem("auth_token");
        
        // Fetch complete user profile data
        const response = await axios.get(`${process.env.NEXT_PUBLIC_API_URL}/api/profile/complete`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        // Data dari API berada di response.data.data.user
        const apiData = response.data.data;
        const userData = apiData.user;
        console.log("Full API Response:", response.data); // Debug log
        console.log("API Data wrapper:", apiData); // Debug log
        console.log("User data from API:", userData); // Debug log
        console.log("Profile Pribadi:", userData?.profilePribadi); // Debug log
        console.log("Profile Pekerjaan:", userData?.profilePekerjaan); // Debug log
        console.log("Orang Tua:", userData?.orangTua); // Debug log
        console.log("Keluarga:", userData?.keluarga); // Debug log
        console.log("User Sosial Media:", userData?.userSosialMedia); // Debug log
        console.log("Current user from store:", user); // Debug log

        // Struktur data yang benar sesuai backend
        const mergedData = {
          email: userData?.email || user?.email || "",
          profilePribadi: {
            id: userData?.profilePribadi?.id || undefined,
            nomor_induk_kependudukan: userData?.profilePribadi?.nomor_induk_kependudukan || "",
            nama_lengkap: userData?.profilePribadi?.nama_lengkap || "",
            tempat_lahir: userData?.profilePribadi?.tempat_lahir || "",
            tanggal_lahir: userData?.profilePribadi?.tanggal_lahir?.split('T')[0] || "",
            jenis_kelamin: userData?.profilePribadi?.jenis_kelamin || "",
            golongan_darah: userData?.profilePribadi?.golongan_darah || "",
            status_pernikahan: userData?.profilePribadi?.status_pernikahan || "",
            npwp: userData?.profilePribadi?.npwp || "",
            kecamatan: userData?.profilePribadi?.kecamatan || "",
            alamat_lengkap: userData?.profilePribadi?.alamat_lengkap || "",
            no_hp: userData?.profilePribadi?.no_hp || "",
            foto: userData?.profilePribadi?.foto,
          },
          profilePekerjaan: {
            id: userData?.profilePekerjaan?.id || undefined,
            departemen: userData?.profilePekerjaan?.departemen?.nama_departemen || "",
            tempat_kerja: userData?.profilePekerjaan?.tempatKerja?.nama_tempat || "",
            jabatan: userData?.profilePekerjaan?.jabatan?.nama_jabatan || "",
            nomor_induk_karyawan: userData?.profilePekerjaan?.nomor_induk_karyawan || "",
            tanggal_masuk: userData?.profilePekerjaan?.tanggal_masuk?.split('T')[0] || "",
            status: userData?.profilePekerjaan?.status || "",
          },
          orangTua: {
            id: userData?.orangTua?.id || undefined,
            nama_ayah: userData?.orangTua?.nama_ayah || "",
            pekerjaan_ayah: userData?.orangTua?.pekerjaan_ayah || "",
            nama_ibu: userData?.orangTua?.nama_ibu || "",
            pekerjaan_ibu: userData?.orangTua?.pekerjaan_ibu || "",
            alamat_orang_tua: userData?.orangTua?.alamat_orang_tua || "",
          },
          keluarga: userData?.keluarga || [],
          userSosialMedia: userData?.userSosialMedia?.map((item: UserSosialMedia) => ({
            ...item,
            nama_platform: item.sosial_media?.nama_platform || "",
          })) || [],
        };

        console.log("Merged profile data:", mergedData); // Debug log
        setProfileData(mergedData);
      } catch (error) {
        console.error("Error fetching profile data:", error);
      } finally {
        setIsLoading(false);
      }
    };

    const loadSosialMediaPlatforms = async () => {
      try {
        const response = await axios.get(`${process.env.NEXT_PUBLIC_API_URL}/api/profile/sosial-media-platforms`);
        setSosialMediaPlatforms(response.data.data || []);
      } catch (error) {
        console.error("Error fetching social media platforms:", error);
      }
    };

    const loadLocationData = async () => {
      try {
        // Fetch kota data
        const kotaResponse = await fetch("/kota-indonesia.json");
        const kotaData = await kotaResponse.json();
        setKotaData(kotaData);

        // Fetch kecamatan data
        const kecamatanResponse = await fetch("/kecamatan-indonesia.json");
        const kecamatanData = await kecamatanResponse.json();
        setKecamatanData(kecamatanData);
      } catch (error) {
        console.error("Error fetching location data:", error);
      }
    };

    loadData();
    loadSosialMediaPlatforms();
    loadLocationData();
  }, [isAuthenticated, user, router]);

  const handleSave = async () => {
    try {
      setIsSaving(true);
      const token = localStorage.getItem("auth_token");

      // Add _method field for PUT request
      const formData = new FormData();
      formData.append("_method", "PUT");
      
      // Add personal data
      formData.append("email", profileData.email);
      formData.append("nomor_induk_kependudukan", profileData.profilePribadi.nomor_induk_kependudukan);
      formData.append("nama_lengkap", profileData.profilePribadi.nama_lengkap);
      formData.append("tempat_lahir", profileData.profilePribadi.tempat_lahir);
      formData.append("tanggal_lahir", profileData.profilePribadi.tanggal_lahir);
      formData.append("jenis_kelamin", profileData.profilePribadi.jenis_kelamin);
      formData.append("golongan_darah", profileData.profilePribadi.golongan_darah);
      formData.append("status_pernikahan", profileData.profilePribadi.status_pernikahan);
      formData.append("npwp", profileData.profilePribadi.npwp);
      formData.append("kecamatan", profileData.profilePribadi.kecamatan);
      formData.append("alamat_lengkap", profileData.profilePribadi.alamat_lengkap);
      formData.append("no_hp", profileData.profilePribadi.no_hp);

      // Add orang tua data
      formData.append("nama_ayah", profileData.orangTua.nama_ayah);
      formData.append("pekerjaan_ayah", profileData.orangTua.pekerjaan_ayah);
      formData.append("nama_ibu", profileData.orangTua.nama_ibu);
      formData.append("pekerjaan_ibu", profileData.orangTua.pekerjaan_ibu);
      formData.append("alamat_orang_tua", profileData.orangTua.alamat_orang_tua);

      // Add keluarga data
      profileData.keluarga.forEach((keluarga, index) => {
        if (keluarga.id) formData.append(`id_keluarga[${index}]`, keluarga.id.toString());
        formData.append(`nama[${index}]`, keluarga.nama);
        formData.append(`hubungan[${index}]`, keluarga.hubungan);
        formData.append(`tanggal_lahir_keluarga[${index}]`, keluarga.tanggal_lahir);
        formData.append(`pekerjaan[${index}]`, keluarga.pekerjaan);
      });

      // Add social media data
      profileData.userSosialMedia.forEach((sosmed, index) => {
        if (sosmed.id) formData.append(`id_user_sosmed[${index}]`, sosmed.id.toString());
        formData.append(`id_platform[${index}]`, sosmed.id_platform.toString());
        formData.append(`username[${index}]`, sosmed.username);
        formData.append(`link[${index}]`, sosmed.link);
      });

      const response = await axios.post(`${process.env.NEXT_PUBLIC_API_URL}/api/profile/update`, formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "multipart/form-data",
        },
      });

      if (response.data) {
        router.push("/profile");
      }
    } catch (error) {
      console.error("Error saving profile:", error);
    } finally {
      setIsSaving(false);
    }
  };

  const addKeluarga = () => {
    setProfileData({
      ...profileData,
      keluarga: [
        ...profileData.keluarga,
        {
          nama: "",
          hubungan: "anak",
          tanggal_lahir: "",
          pekerjaan: "",
        },
      ],
    });
  };

  const removeKeluarga = (index: number) => {
    const newKeluarga = profileData.keluarga.filter((_, i) => i !== index);
    setProfileData({
      ...profileData,
      keluarga: newKeluarga,
    });
  };

  const addSosmed = () => {
    setProfileData({
      ...profileData,
      userSosialMedia: [
        ...profileData.userSosialMedia,
        {
          id_platform: sosialMediaPlatforms[0]?.id || 1,
          username: "",
          link: "",
        },
      ],
    });
  };

  const removeSosmed = (index: number) => {
    const newSosmed = profileData.userSosialMedia.filter((_, i) => i !== index);
    setProfileData({
      ...profileData,
      userSosialMedia: newSosmed,
    });
  };

  const updateKeluarga = (index: number, field: string, value: string) => {
    const newKeluarga = [...profileData.keluarga];
    newKeluarga[index] = {
      ...newKeluarga[index],
      [field]: value,
    };
    setProfileData({
      ...profileData,
      keluarga: newKeluarga,
    });
  };

  const updateSosmed = (index: number, field: string, value: string | number) => {
    const newSosmed = [...profileData.userSosialMedia];
    newSosmed[index] = {
      ...newSosmed[index],
      [field]: value,
    };
    setProfileData({
      ...profileData,
      userSosialMedia: newSosmed,
    });
  };

  const tabs = [
    { id: "personal", label: "Data Pribadi", icon: "👤" },
    { id: "work", label: "Pekerjaan", icon: "💼" },
    { id: "family", label: "Keluarga", icon: "👨‍👩‍👧‍👦" },
    { id: "social", label: "Sosial Media", icon: "📱" },
  ];

  if (isLoading) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div className="text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-4 border-blue-600 border-t-transparent mx-auto mb-4"></div>
          <p className="text-gray-600 font-medium">Memuat data profil...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      {/* Header */}
      <div className="bg-white shadow-sm sticky top-0 z-50">
        <div className="flex items-center justify-between p-4">
          <button 
            onClick={() => router.back()} 
            className="p-3 hover:bg-gray-100 rounded-full transition-colors duration-200"
            aria-label="Kembali"
          >
            <svg className="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <h1 className="text-xl font-bold text-gray-800">Edit Profil</h1>
          <button 
            onClick={handleSave} 
            disabled={isSaving} 
            className="px-6 py-2.5 bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 font-medium shadow-sm"
          >
            {isSaving ? "Menyimpan..." : "Simpan"}
          </button>
        </div>
      </div>

      {/* Profile Header */}
      <div className="bg-white shadow-sm mx-4 mt-4 rounded-2xl p-6">
        <div className="flex flex-col items-center">
          <div className="w-20 h-20 bg-gradient-to-br from-blue-400 to-indigo-600 rounded-full mb-4 flex items-center justify-center overflow-hidden shadow-lg">
            {profileData.profilePribadi.foto ? (
              <img src={`${process.env.NEXT_PUBLIC_API_URL}/storage/${profileData.profilePribadi.foto}`} alt="Profile" className="w-full h-full object-cover" />
            ) : (
              <span className="text-white text-2xl font-bold">
                {profileData.profilePribadi.nama_lengkap?.charAt(0).toUpperCase() || user?.email?.charAt(0).toUpperCase()}
              </span>
            )}
          </div>
          <h2 className="text-2xl font-bold text-gray-800 capitalize">
            {profileData.profilePribadi.nama_lengkap || user?.email || "User"}
          </h2>
          <p className="text-gray-500 text-sm mt-1">
            {profileData.profilePekerjaan.jabatan || user?.roles?.[0] || "Karyawan"}
          </p>
        </div>
      </div>

      {/* Tabs */}
      <div className="bg-white shadow-sm mx-4 mt-4 rounded-2xl p-1">
        <div className="grid grid-cols-4 gap-1">
          {tabs.map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`py-3 px-2 rounded-xl text-center font-medium transition-all duration-200 text-xs ${
                activeTab === tab.id
                  ? "bg-blue-600 text-white shadow-md"
                  : "text-gray-600 hover:bg-gray-100"
              }`}
            >
              <div className="text-lg mb-1">{tab.icon}</div>
              <div>{tab.label}</div>
            </button>
          ))}
        </div>
      </div>

      {/* Content */}
      <div className="p-4 pb-20">
        {activeTab === "personal" && (
          <div className="space-y-4">
            <div className="bg-white rounded-2xl shadow-sm p-6">
              <div className="mb-6 flex justify-between items-center">
                <div>
                  <h3 className="text-lg font-bold text-gray-800 mb-2">Informasi Pribadi</h3>
                  <p className="text-sm text-gray-500">Kelola data diri Anda</p>
                </div>
                {!isEditMode && (
                  <button
                    onClick={() => setIsEditMode(true)}
                    className="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors duration-200 font-medium shadow-sm"
                  >
                    <i className="bi bi-pencil mr-2"></i>Sunting
                  </button>
                )}
              </div>
              
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                  <input
                    type="email"
                    value={profileData.email}
                    onChange={(e) => setProfileData({ ...profileData, email: e.target.value })}
                    disabled={!isEditMode}
                    className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                  />
                </div>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                    <input
                      type="text"
                      value={profileData.profilePribadi.nama_lengkap}
                      onChange={(e) => setProfileData({
                        ...profileData,
                        profilePribadi: { ...profileData.profilePribadi, nama_lengkap: e.target.value }
                      })}
                      disabled={!isEditMode}
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">NIK</label>
                    <input
                      type="text"
                      value={profileData.profilePribadi.nomor_induk_kependudukan}
                      onChange={(e) => setProfileData({
                        ...profileData,
                        profilePribadi: { ...profileData.profilePribadi, nomor_induk_kependudukan: e.target.value }
                      })}
                      disabled={!isEditMode}
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                    />
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir</label>
                    <select
                      value={profileData.profilePribadi.tempat_lahir}
                      onChange={(e) => setProfileData({
                        ...profileData,
                        profilePribadi: { ...profileData.profilePribadi, tempat_lahir: e.target.value }
                      })}
                      disabled={!isEditMode}
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                    >
                      <option value="">Pilih Kota</option>
                      {kotaData.map((provinsi, index) => (
                        <optgroup key={index} label={provinsi.provinsi}>
                          {provinsi.kota.map((kota, kotaIndex) => (
                            <option key={kotaIndex} value={kota}>{kota}</option>
                          ))}
                        </optgroup>
                      ))}
                    </select>
                  </div>
                  
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
                    <input
                      type="date"
                      value={profileData.profilePribadi.tanggal_lahir}
                      onChange={(e) => setProfileData({
                        ...profileData,
                        profilePribadi: { ...profileData.profilePribadi, tanggal_lahir: e.target.value }
                      })}
                      disabled={!isEditMode}
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                    />
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin</label>
                    <select
                      value={profileData.profilePribadi.jenis_kelamin}
                      onChange={(e) => setProfileData({
                        ...profileData,
                        profilePribadi: { ...profileData.profilePribadi, jenis_kelamin: e.target.value }
                      })}
                      disabled={!isEditMode}
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                    >
                      <option value="pria">Pria</option>
                      <option value="wanita">Wanita</option>
                    </select>
                  </div>
                  
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Golongan Darah</label>
                    <select
                      value={profileData.profilePribadi.golongan_darah}
                      onChange={(e) => setProfileData({
                        ...profileData,
                        profilePribadi: { ...profileData.profilePribadi, golongan_darah: e.target.value }
                      })}
                      disabled={!isEditMode}
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                    >
                      <option value="a">A</option>
                      <option value="b">B</option>
                      <option value="ab">AB</option>
                      <option value="o">O</option>
                    </select>
                  </div>
                  
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Status Pernikahan</label>
                    <select
                      value={profileData.profilePribadi.status_pernikahan}
                      onChange={(e) => setProfileData({
                        ...profileData,
                        profilePribadi: { ...profileData.profilePribadi, status_pernikahan: e.target.value }
                      })}
                      disabled={!isEditMode}
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                    >
                      <option value="belum nikah">Belum Menikah</option>
                      <option value="sudah nikah">Sudah Menikah</option>
                    </select>
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">NPWP</label>
                    <input
                      type="text"
                      value={profileData.profilePribadi.npwp}
                      onChange={(e) => setProfileData({
                        ...profileData,
                        profilePribadi: { ...profileData.profilePribadi, npwp: e.target.value }
                      })}
                      disabled={!isEditMode}
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Nomor HP</label>
                    <input
                      type="text"
                      value={profileData.profilePribadi.no_hp}
                      onChange={(e) => setProfileData({
                        ...profileData,
                        profilePribadi: { ...profileData.profilePribadi, no_hp: e.target.value }
                      })}
                      disabled={!isEditMode}
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">Kecamatan</label>
                  <select
                    value={profileData.profilePribadi.kecamatan}
                    onChange={(e) => setProfileData({
                      ...profileData,
                      profilePribadi: { ...profileData.profilePribadi, kecamatan: e.target.value }
                    })}
                    disabled={!isEditMode}
                    className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                  >
                    <option value="">Pilih Kecamatan</option>
                    {kecamatanData.map((kecamatan, index) => (
                      <option key={index} value={kecamatan.name}>{kecamatan.name}</option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">Alamat Lengkap</label>
                  <textarea
                    value={profileData.profilePribadi.alamat_lengkap}
                    onChange={(e) => setProfileData({
                      ...profileData,
                      profilePribadi: { ...profileData.profilePribadi, alamat_lengkap: e.target.value }
                    })}
                    disabled={!isEditMode}
                    rows={3}
                    className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200 resize-none"
                  />
                </div>
              </div>
            </div>
          </div>
        )}

        {activeTab === "work" && (
          <div className="space-y-4">
            <div className="bg-white rounded-2xl shadow-sm p-6">
              <div className="mb-6">
                <h3 className="text-lg font-bold text-gray-800 mb-2">Informasi Pekerjaan</h3>
                <p className="text-sm text-gray-500">Data pekerjaan Anda (tidak dapat diubah)</p>
              </div>
              
              <div className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Departemen</label>
                    <input
                      type="text"
                      value={profileData.profilePekerjaan.departemen || ""}
                      disabled
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-100 text-gray-500"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Tempat Kerja</label>
                    <input
                      type="text"
                      value={profileData.profilePekerjaan.tempat_kerja || ""}
                      disabled
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-100 text-gray-500"
                    />
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Jabatan</label>
                    <input
                      type="text"
                      value={profileData.profilePekerjaan.jabatan || ""}
                      disabled
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-100 text-gray-500"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">NIP</label>
                    <input
                      type="text"
                      value={profileData.profilePekerjaan.nomor_induk_karyawan || ""}
                      disabled
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-100 text-gray-500"
                    />
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Tanggal Masuk</label>
                    <input
                      type="text"
                      value={profileData.profilePekerjaan.tanggal_masuk || ""}
                      disabled
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-100 text-gray-500"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <input
                      type="text"
                      value={profileData.profilePekerjaan.status || ""}
                      disabled
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-100 text-gray-500"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}

        {activeTab === "family" && (
          <div className="space-y-4">
            {/* Orang Tua */}
            <div className="bg-white rounded-2xl shadow-sm p-6">
              <div className="mb-6">
                <h3 className="text-lg font-bold text-gray-800 mb-2">Data Orang Tua</h3>
                <p className="text-sm text-gray-500">Informasi orang tua Anda</p>
              </div>
              
              <div className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Nama Ayah</label>
                    <input
                      type="text"
                      value={profileData.orangTua.nama_ayah}
                      onChange={(e) => setProfileData({
                        ...profileData,
                        orangTua: { ...profileData.orangTua, nama_ayah: e.target.value }
                      })}
                      disabled={!isEditMode}
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Pekerjaan Ayah</label>
                    <input
                      type="text"
                      value={profileData.orangTua.pekerjaan_ayah}
                      onChange={(e) => setProfileData({
                        ...profileData,
                        orangTua: { ...profileData.orangTua, pekerjaan_ayah: e.target.value }
                      })}
                      disabled={!isEditMode}
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                    />
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Nama Ibu</label>
                    <input
                      type="text"
                      value={profileData.orangTua.nama_ibu}
                      onChange={(e) => setProfileData({
                        ...profileData,
                        orangTua: { ...profileData.orangTua, nama_ibu: e.target.value }
                      })}
                      disabled={!isEditMode}
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Pekerjaan Ibu</label>
                    <input
                      type="text"
                      value={profileData.orangTua.pekerjaan_ibu}
                      onChange={(e) => setProfileData({
                        ...profileData,
                        orangTua: { ...profileData.orangTua, pekerjaan_ibu: e.target.value }
                      })}
                      disabled={!isEditMode}
                      className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">Alamat Orang Tua</label>
                  <textarea
                    value={profileData.orangTua.alamat_orang_tua}
                    onChange={(e) => setProfileData({
                      ...profileData,
                      orangTua: { ...profileData.orangTua, alamat_orang_tua: e.target.value }
                    })}
                    disabled={!isEditMode}
                    rows={3}
                    className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 transition-all duration-200 resize-none"
                  />
                </div>
              </div>
            </div>

            {/* Keluarga */}
            <div className="bg-white rounded-2xl shadow-sm p-6">
              <div className="mb-6 flex justify-between items-center">
                <div>
                  <h3 className="text-lg font-bold text-gray-800 mb-2">Data Keluarga</h3>
                  <p className="text-sm text-gray-500">Informasi keluarga Anda</p>
                </div>
                <button
                  onClick={addKeluarga}
                  className="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors duration-200 font-medium shadow-sm"
                >
                  + Tambah
                </button>
              </div>
              
              {profileData.keluarga.length === 0 ? (
                <div className="text-center py-12 bg-gray-50 rounded-xl">
                  <div className="text-gray-400 text-4xl mb-4">👨‍👩‍👧‍👦</div>
                  <p className="text-gray-500">Belum ada data keluarga</p>
                </div>
              ) : (
                <div className="space-y-4">
                  {profileData.keluarga.map((keluarga, index) => (
                    <div key={index} className="border border-gray-200 rounded-xl p-4 bg-gray-50">
                      <div className="flex justify-between items-center mb-4">
                        <h4 className="font-semibold text-gray-800">Anggota Keluarga {index + 1}</h4>
                        <button
                          onClick={() => removeKeluarga(index)}
                          className="text-red-600 hover:text-red-800 transition-colors duration-200"
                        >
                          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                      
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                          <input
                            type="text"
                            value={keluarga.nama}
                            onChange={(e) => updateKeluarga(index, "nama", e.target.value)}
                            disabled={!isEditMode}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                          />
                        </div>
                        
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Hubungan</label>
                          <select
                            value={keluarga.hubungan}
                            onChange={(e) => updateKeluarga(index, "hubungan", e.target.value)}
                            disabled={!isEditMode}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                          >
                            <option value="suami">Suami</option>
                            <option value="istri">Istri</option>
                            <option value="anak">Anak</option>
                            <option value="lainnya">Lainnya</option>
                          </select>
                        </div>
                        
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir</label>
                          <input
                            type="date"
                            value={keluarga.tanggal_lahir}
                            onChange={(e) => updateKeluarga(index, "tanggal_lahir", e.target.value)}
                            disabled={!isEditMode}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                          />
                        </div>
                        
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Pekerjaan</label>
                          <input
                            type="text"
                            value={keluarga.pekerjaan}
                            onChange={(e) => updateKeluarga(index, "pekerjaan", e.target.value)}
                            disabled={!isEditMode}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                          />
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        )}

        {activeTab === "social" && (
          <div className="space-y-4">
            <div className="bg-white rounded-2xl shadow-sm p-6">
              <div className="mb-6 flex justify-between items-center">
                <div>
                  <h3 className="text-lg font-bold text-gray-800 mb-2">Sosial Media</h3>
                  <p className="text-sm text-gray-500">Akun sosial media Anda</p>
                </div>
                <button
                  onClick={addSosmed}
                  className="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors duration-200 font-medium shadow-sm"
                >
                  + Tambah
                </button>
              </div>
              
              {profileData.userSosialMedia.length === 0 ? (
                <div className="text-center py-12 bg-gray-50 rounded-xl">
                  <div className="text-gray-400 text-4xl mb-4">📱</div>
                  <p className="text-gray-500">Belum ada data sosial media</p>
                </div>
              ) : (
                <div className="space-y-4">
                  {profileData.userSosialMedia.map((sosmed, index) => (
                    <div key={index} className="border border-gray-200 rounded-xl p-4 bg-gray-50">
                      <div className="flex justify-between items-center mb-4">
                        <h4 className="font-semibold text-gray-800">Sosial Media {index + 1}</h4>
                        <button
                          onClick={() => removeSosmed(index)}
                          className="text-red-600 hover:text-red-800 transition-colors duration-200"
                        >
                          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                      
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Platform</label>
                          <select
                            value={sosmed.id_platform}
                            onChange={(e) => updateSosmed(index, "id_platform", parseInt(e.target.value))}
                            disabled={!isEditMode}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                          >
                            {sosialMediaPlatforms.map((platform) => (
                              <option key={platform.id} value={platform.id}>{platform.nama_platform}</option>
                            ))}
                          </select>
                        </div>
                        
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Username</label>
                          <input
                            type="text"
                            value={sosmed.username}
                            onChange={(e) => updateSosmed(index, "username", e.target.value)}
                            disabled={!isEditMode}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                          />
                        </div>
                        
                        <div className="md:col-span-2">
                          <label className="block text-sm font-medium text-gray-700 mb-2">Link Profil</label>
                          <input
                            type="text"
                            value={sosmed.link}
                            onChange={(e) => updateSosmed(index, "link", e.target.value)}
                            disabled={!isEditMode}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                          />
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        )}
      </div>

      {/* Floating Edit Button */}
      <div className="fixed bottom-6 right-6 z-40">
        <button
          onClick={() => setIsEditMode(!isEditMode)}
          className={`px-6 py-3 rounded-full shadow-lg transition-all duration-200 font-medium ${
            isEditMode 
              ? "bg-gray-600 text-white hover:bg-gray-700" 
              : "bg-blue-600 text-white hover:bg-blue-700"
          }`}
        >
          {isEditMode ? "Batal Edit" : "Edit Profil"}
        </button>
      </div>
    </div>
  );
}
