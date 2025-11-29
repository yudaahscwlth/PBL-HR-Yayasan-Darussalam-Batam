"use client";

import { useState, useEffect, useRef } from "react";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import api from "@/lib/api";
import { API_CONFIG } from "@/config/api";
import { 
  User, Briefcase, Users, Share2, Lock, 
  Save, Plus, Trash2, Camera, ChevronRight,
  MapPin, Mail, Phone, CreditCard, ArrowLeft
} from "lucide-react";
import AccessControl from "@/components/AccessControl";

interface ProfileData {
  id: number;
  email: string;
  profile_pribadi: {
    nama_lengkap: string;
    nomor_induk_kependudukan: string;
    npwp: string;
    tempat_lahir: string;
    tanggal_lahir: string;
    jenis_kelamin: string;
    status_pernikahan: string;
    golongan_darah: string;
    kecamatan: string;
    alamat_lengkap: string;
    no_hp: string;
    foto: string | null;
  };

  profile_pekerjaan: {
    nomor_induk_karyawan: string;
    tanggal_masuk: string;
    status: string;
    departemen: { nama_departemen: string };
    tempat_kerja: { nama_tempat: string };
    jabatan: { nama_jabatan: string };
  };

  orang_tua: {
    nama_ayah: string;
    pekerjaan_ayah: string;
    nama_ibu: string;
    pekerjaan_ibu: string;
    alamat_orang_tua: string;
  } | null;
  keluarga: Array<{
    id: number;
    nama: string;
    hubungan: string;
    tanggal_lahir: string;
    pekerjaan: string;
  }>;
  user_sosial_media: Array<{
    id: number;
    id_platform: number;
    username: string;
    link: string;
    // Diubah dari sosialMedia ke sosial_media
    sosial_media: { nama_platform: string };
  }>;
}

interface ProfileEditProps {
  allowedRoles: string[];
}

const calculateDuration = (startDate: string | undefined) => {
  if (!startDate) return "-";
  const start = new Date(startDate);
  const now = new Date();
  let years = now.getFullYear() - start.getFullYear();
  let months = now.getMonth() - start.getMonth();

  if (months < 0) {
    years--;
    months += 12;
  }

  if (years > 0) {
    return `${years} Tahun ${months} Bulan`;
  }
  return `${months} Bulan`;
};

export default function ProfileEdit({ allowedRoles }: ProfileEditProps) {
  const { user } = useAuthStore();
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [profileData, setProfileData] = useState<ProfileData | null>(null);
  const [activeTab, setActiveTab] = useState<"edit" | "password">("edit");
  
  // Form states (Keys sudah snake_case, tetap dipertahankan)
  const [formData, setFormData] = useState({
    // Data Diri
    email: "",
    nomor_induk_kependudukan: "",
    nama_lengkap: "",
    npwp: "",
    tempat_lahir: "",
    tanggal_lahir: "",
    jenis_kelamin: "",
    status_pernikahan: "",
    golongan_darah: "",
    kecamatan: "",
    alamat_lengkap: "",
    no_hp: "",
    foto: null as File | null,
    
    // Data Orang Tua
    nama_ayah: "",
    pekerjaan_ayah: "",
    nama_ibu: "",
    pekerjaan_ibu: "",
    alamat_orang_tua: "",
  });

  const [keluargaList, setKeluargaList] = useState<Array<{
    id?: number;
    nama: string;
    hubungan: string;
    tanggal_lahir: string;
    pekerjaan: string;
  }>>([]);

  const [sosmedList, setSosmedList] = useState<Array<{
    id?: number;
    id_platform: number;
    username: string;
    link: string;
    sosial_media: { nama_platform: string };
  }>>([]);

  const [kecamatanList, setKecamatanList] = useState<Array<{ id: string; name: string }>>([]);

  useEffect(() => {
    const fetchKecamatan = async () => {
      try {
        const response = await fetch("/kecamatan-indonesia.json");
        const data = await response.json();
        setKecamatanList(data);
      } catch (error) {
        console.error("Error fetching kecamatan data:", error);
      }
    };
    fetchKecamatan();

  }, []);

  const [kotaList, setKotaList] = useState<Array<{ provinsi: string; kota: string[] }>>([]);

  useEffect(() => {
    const fetchKota = async () => {
      try {
        const response = await fetch("/kota-indonesia.json");
        const data = await response.json();
        setKotaList(data);
      } catch (error) {
        console.error("Error fetching kota data:", error);
      }
    };
    fetchKota();
  }, []);

  const [passwordForm, setPasswordForm] = useState({
    password_lama: "",
    password_baru: "",
    konf_password: "",
  });

  const [toastMessage, setToastMessage] = useState<{
    type: "success" | "error" | "warning" | "info";
    message: string;
    show: boolean;
  }>({
    type: "success",
    message: "",
    show: false,
  });

  const [lastToastTime, setLastToastTime] = useState(0);

  const showToast = (type: "success" | "error" | "warning" | "info", message: string) => {
    const now = Date.now();
    if (now - lastToastTime < 3000) return; // Prevent spamming
    setLastToastTime(now);
    setToastMessage({ type, message, show: true });
    setTimeout(() => {
      setToastMessage((prev) => ({ ...prev, show: false }));
    }, 5000);
  };

  const fetchProfileData = async () => {
    try {
      const response = await api.get("/profile/complete");
      const responseData = response.data.data;
      const userData: ProfileData = responseData.user; // Asumsi response API sudah snake_case
      
      setProfileData(userData);
      
      // Update akses property menggunakan snake_case
      setFormData({
        email: userData.email || "",
        nomor_induk_kependudukan: userData.profile_pribadi?.nomor_induk_kependudukan || "",
        nama_lengkap: userData.profile_pribadi?.nama_lengkap || "",
        npwp: userData.profile_pribadi?.npwp || "",
        tempat_lahir: userData.profile_pribadi?.tempat_lahir || "",
        tanggal_lahir: userData.profile_pribadi?.tanggal_lahir || "",
        jenis_kelamin: userData.profile_pribadi?.jenis_kelamin || "",
        status_pernikahan: userData.profile_pribadi?.status_pernikahan || "",
        golongan_darah: userData.profile_pribadi?.golongan_darah || "",
        kecamatan: userData.profile_pribadi?.kecamatan || "",
        alamat_lengkap: userData.profile_pribadi?.alamat_lengkap || "",
        no_hp: userData.profile_pribadi?.no_hp || "",
        foto: null,
        nama_ayah: userData.orang_tua?.nama_ayah || "",
        pekerjaan_ayah: userData.orang_tua?.pekerjaan_ayah || "",
        nama_ibu: userData.orang_tua?.nama_ibu || "",
        pekerjaan_ibu: userData.orang_tua?.pekerjaan_ibu || "",
        alamat_orang_tua: userData.orang_tua?.alamat_orang_tua || "",
      });

      setKeluargaList(userData.keluarga || []);
      setSosmedList(userData.user_sosial_media || []); // snake_case
    } catch (error) {
      console.error("Error fetching profile:", error);
    }
  };

  useEffect(() => {
    fetchProfileData();
  }, []);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData({ ...formData, [name]: value });
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setFormData({ ...formData, foto: e.target.files[0] });
    }
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    
    try {
      const submitData = new FormData();
      submitData.append("_method", "PUT");
      Object.entries(formData).forEach(([key, value]) => {
        if (value !== null && value !== "") {
          if (key === "foto" && value instanceof File) {
            submitData.append(key, value);
          } else {
            submitData.append(key, value as string);
          }
        }
      });

      keluargaList.forEach((item, index) => {
        submitData.append(`id_keluarga[${index}]`, item.id?.toString() || "");
        submitData.append(`nama[${index}]`, item.nama);
        submitData.append(`hubungan[${index}]`, item.hubungan);
        submitData.append(`tanggal_lahir_keluarga[${index}]`, item.tanggal_lahir);
        submitData.append(`pekerjaan[${index}]`, item.pekerjaan);
      });

      sosmedList.forEach((item, index) => {
        submitData.append(`id_user_sosmed[${index}]`, item.id?.toString() || "");
        submitData.append(`id_platform[${index}]`, item.id_platform.toString());
        submitData.append(`username[${index}]`, item.username);
        submitData.append(`link[${index}]`, item.link);
      });

      await api.post("/profile/update", submitData, {
        headers: { "Content-Type": "multipart/form-data" },
      });

      showToast("success", "Profile berhasil diperbarui!");
      fetchProfileData();
    } catch (error: any) {
      console.error("Error updating profile:", error);
      showToast("error", error.response?.data?.message || "Gagal memperbarui profile");
    } finally {
      setIsLoading(false);
    }
  };

  const handlePasswordChange = async (e: React.FormEvent) => {
    e.preventDefault();
    if (passwordForm.password_baru !== passwordForm.konf_password) {
      showToast("error", "Konfirmasi password tidak cocok!");
      return;
    }

    setIsLoading(true);
    try {
      await api.put("/profile/password/update", {
        current_password: passwordForm.password_lama,
        new_password: passwordForm.password_baru,
        new_password_confirmation: passwordForm.konf_password,
      });

      showToast("success", "Password berhasil diubah!");
      setPasswordForm({ password_lama: "", password_baru: "", konf_password: "" });
      setActiveTab("edit");
    } catch (error: any) {
      console.error("Error changing password:", error);
      showToast("error", error.response?.data?.message || "Gagal mengubah password");
    } finally {
      setIsLoading(false);
    }
  };

  const addKeluarga = () => setKeluargaList([...keluargaList, { nama: "", hubungan: "suami", tanggal_lahir: "", pekerjaan: "" }]);
  const removeKeluarga = (index: number) => setKeluargaList(keluargaList.filter((_, i) => i !== index));
  const updateKeluarga = (index: number, field: string, value: string) => {
    const updated = [...keluargaList];
    updated[index] = { ...updated[index], [field]: value };
    setKeluargaList(updated);
  };

  const addSosmed = () => setSosmedList([...sosmedList, { id_platform: 1, username: "", link: "", sosial_media: { nama_platform: "" } }]);
  const removeSosmed = (index: number) => setSosmedList(sosmedList.filter((_, i) => i !== index));
  const updateSosmed = (index: number, field: string, value: string | number) => {
    const updated = [...sosmedList];
    updated[index] = { ...updated[index], [field]: value };
    setSosmedList(updated);
  };

  // --- Helper Classes ---
  const inputClass = "w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-800/20 focus:border-sky-800 transition-all duration-200 text-slate-700 bg-white placeholder-slate-400 text-sm font-medium hover:border-slate-300";
  const labelClass = "block text-sm font-semibold text-slate-700 mb-2";
  const readOnlyClass = "w-full px-4 py-3 border border-slate-100 rounded-xl bg-slate-50/50 text-slate-500 text-sm cursor-not-allowed font-medium flex items-center gap-2";
  const cardClass = "bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300";
  const sectionHeaderClass = "px-6 py-5 border-b border-slate-50 flex items-center gap-3 bg-white";

  if (!profileData) {
    return (
      <div className="min-h-screen bg-slate-50 flex items-center justify-center">
        <div className="flex flex-col items-center gap-3">
            <div className="w-8 h-8 border-4 border-sky-800 border-t-transparent rounded-full animate-spin"></div>
            <span className="text-slate-500 font-medium">Memuat data...</span>
        </div>
      </div>
    );
  }

  return (
    <AccessControl allowedRoles={allowedRoles}>
      <div className="min-h-screen bg-gray-100 font-sans pb-10">
      {/* Header */}
      <div className="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div className="container mx-auto px-4 py-4 flex items-center justify-between">
            <button 
                onClick={() => router.back()} 
                className="flex items-center gap-2 text-slate-600 hover:text-sky-800 transition-colors font-medium"
            >
                <ArrowLeft className="w-5 h-5" />
                <span>Kembali</span>
            </button>
            <h1 className="text-lg font-bold text-slate-800">Ubah Profile</h1>
            <div className="w-20"></div>
        </div>
      </div>

      <main className="container mx-auto px-4 py-8 max-w-6xl">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {/* Sidebar / Profile Summary */}
            <div className="lg:col-span-4 space-y-6 lg:sticky lg:top-24 h-fit">
                <div className={`${cardClass} flex flex-col items-center text-center relative`}>
                    <div className="h-32 w-full absolute top-0 left-0"></div>
                    <div className="px-6 pb-8 pt-16 w-full flex flex-col items-center relative z-10">
                        <div 
                            className="relative group cursor-pointer"
                            onClick={() => fileInputRef.current?.click()}
                        >
                            <div className="p-1 bg-white rounded-full shadow-lg">
                                <img
                                    src={
                                        formData.foto instanceof File 
                                        ? URL.createObjectURL(formData.foto)
                                        : (profileData.profile_pribadi?.foto ? `${API_CONFIG.BASE_URL}/storage/${profileData.profile_pribadi.foto}` : "/assets/img/profile-img.jpg")
                                    }
                                    alt="Profile"
                                    className="w-32 h-32 rounded-full object-cover border-4 border-white shadow-sm transition-opacity group-hover:opacity-90"
                                />
                            </div>
                            <div className="absolute bottom-2 right-2 bg-sky-800 p-2.5 rounded-full shadow-lg border-2 border-white transition-transform group-hover:scale-110 hover:bg-sky-900">
                                 <Camera className="w-4 h-4 text-white" />
                            </div>
                            <input 
                                type="file" 
                                ref={fileInputRef} 
                                onChange={handleFileChange} 
                                accept="image/*" 
                                className="hidden" 
                            />
                        </div>
                        {/* Menggunakan snake_case di sini */}
                        <h2 className="mt-4 text-xl font-bold text-slate-800">{profileData.profile_pribadi?.nama_lengkap}</h2>
                        <p className="text-slate-500 font-medium">{profileData.profile_pekerjaan?.jabatan?.nama_jabatan}</p>
                        <div className="mt-3 inline-flex items-center px-4 py-1.5 rounded-full bg-sky-50 text-sky-900 text-xs font-semibold uppercase tracking-wide">
                            {profileData.profile_pekerjaan?.status}
                        </div>
                    </div>
                </div>

                {/* Tab Navigation */}
                <div className={`${cardClass} p-3`}>
                    <div className="flex flex-col gap-2">
                        <button
                            onClick={() => setActiveTab("edit")}
                            className={`flex items-center gap-3 px-5 py-4 rounded-xl text-sm font-semibold transition-all duration-200 ${
                                activeTab === "edit" 
                                ? "bg-sky-50 text-sky-800 shadow-sm border border-sky-100" 
                                : "text-slate-600 hover:bg-slate-50 hover:text-slate-900"
                            }`}
                        >
                            <User className={`w-5 h-5 ${activeTab === "edit" ? "text-sky-800" : "text-slate-400"}`} /> 
                            Ubah Profile
                            {activeTab === "edit" && <ChevronRight className="w-4 h-4 ml-auto text-sky-400" />}
                        </button>
                        <button
                            onClick={() => setActiveTab("password")}
                            className={`flex items-center gap-3 px-5 py-4 rounded-xl text-sm font-semibold transition-all duration-200 ${
                                activeTab === "password" 
                                ? "bg-sky-50 text-sky-800 shadow-sm border border-sky-100" 
                                : "text-slate-600 hover:bg-slate-50 hover:text-slate-900"
                            }`}
                        >
                            <Lock className={`w-5 h-5 ${activeTab === "password" ? "text-sky-800" : "text-slate-400"}`} /> 
                            Ubah Kata Sandi
                            {activeTab === "password" && <ChevronRight className="w-4 h-4 ml-auto text-sky-400" />}
                        </button>
                    </div>
                </div>
            </div>

            {/* Main Content Form */}
            <div className="lg:col-span-8">
                {activeTab === "edit" ? (
                    <form onSubmit={handleSave} encType="multipart/form-data" className="space-y-6">
                        
                        {/* Data Pekerjaan (Read Only) */}
                        <section className={cardClass}>
                            <div className={sectionHeaderClass}>
                                <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                                    <Briefcase className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-slate-800 text-lg">Data Pekerjaan</h3>
                                    <p className="text-slate-500 text-xs font-medium">Informasi terkait posisi dan departemen Anda</p>
                                </div>
                            </div>
                            <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className={labelClass}>Departemen</label>
                                    <div className="relative">
                                        <input type="text" value={profileData.profile_pekerjaan?.departemen?.nama_departemen || "-"} className={readOnlyClass} disabled />
                                        <Lock className="w-4 h-4 text-slate-400 absolute right-4 top-3.5" />
                                    </div>
                                </div>
                                <div>
                                    <label className={labelClass}>Jabatan</label>
                                    <div className="relative">
                                        <input type="text" value={profileData.profile_pekerjaan?.jabatan?.nama_jabatan || "-"} className={readOnlyClass} disabled />
                                        <Lock className="w-4 h-4 text-slate-400 absolute right-4 top-3.5" />
                                    </div>
                                </div>
                                <div>
                                    <label className={labelClass}>NIK Karyawan</label>
                                    <div className="relative">
                                        <input type="text" value={profileData.profile_pekerjaan?.nomor_induk_karyawan || "-"} className={readOnlyClass} disabled />
                                        <Lock className="w-4 h-4 text-slate-400 absolute right-4 top-3.5" />
                                    </div>
                                </div>
                                <div>
                                    <label className={labelClass}>Tanggal Masuk</label>
                                    <div className="relative">
                                        <input type="text" value={profileData.profile_pekerjaan?.tanggal_masuk || "-"} className={readOnlyClass} disabled />
                                        <Lock className="w-4 h-4 text-slate-400 absolute right-4 top-3.5" />
                                    </div>
                                </div>
                                <div>
                                    <label className={labelClass}>Tempat Bekerja</label>
                                    <div className="relative">
                                        <input type="text" value={profileData.profile_pekerjaan?.tempat_kerja?.nama_tempat || "-"} className={readOnlyClass} disabled />
                                        <Lock className="w-4 h-4 text-slate-400 absolute right-4 top-3.5" />
                                    </div>
                                </div>
                                <div>
                                    <label className={labelClass}>Lama Pengabdian</label>
                                    <div className="relative">
                                        <input type="text" value={calculateDuration(profileData.profile_pekerjaan?.tanggal_masuk)} className={readOnlyClass} disabled />
                                        <Lock className="w-4 h-4 text-slate-400 absolute right-4 top-3.5" />
                                    </div>
                                </div>
                            </div>
                        </section>

                        {/* Informasi Pribadi */}
                        <section className={cardClass}>
                             <div className={sectionHeaderClass}>
                                <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                                    <User className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-slate-800 text-lg">Informasi Pribadi</h3>
                                    <p className="text-slate-500 text-xs font-medium">Data diri dan kontak yang dapat dihubungi</p>
                                </div>
                            </div>
                            <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className={labelClass}>Email</label>
                                    <div className="relative">
                                        <input type="email" name="email" value={formData.email} onChange={handleInputChange} className={`${inputClass} pl-10`} />
                                        <Mail className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
                                    </div>
                                </div>
                                <div>
                                    <label className={labelClass}>No. Handphone</label>
                                    <div className="relative">
                                        <input type="text" name="no_hp" value={formData.no_hp} onChange={handleInputChange} className={`${inputClass} pl-10`} />
                                        <Phone className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
                                    </div>
                                </div>
                                <div>
                                    <label className={labelClass}>NIK (KTP)</label>
                                    <div className="relative">
                                        <input type="text" name="nomor_induk_kependudukan" value={formData.nomor_induk_kependudukan} onChange={handleInputChange} className={`${inputClass} pl-10`} />
                                        <CreditCard className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
                                    </div>
                                </div>
                                <div>
                                    <label className={labelClass}>NPWP</label>
                                    <input type="text" name="npwp" value={formData.npwp} onChange={handleInputChange} className={inputClass} />
                                </div>
                                <div>
                                    <label className={labelClass}>Nama Lengkap</label>
                                    <input type="text" name="nama_lengkap" value={formData.nama_lengkap} onChange={handleInputChange} className={inputClass} />
                                </div>
                                <div>
                                    <label className={labelClass}>Tempat Lahir</label>
                                    <div className="relative">
                                        <select 
                                            name="tempat_lahir" 
                                            value={formData.tempat_lahir} 
                                            onChange={handleInputChange} 
                                            className={`${inputClass} pl-10 appearance-none`}
                                        >
                                            <option value="">Pilih Tempat Lahir</option>
                                            {kotaList.map((prov, index) => (
                                                <optgroup key={index} label={prov.provinsi}>
                                                    {prov.kota.map((kota, kIndex) => (
                                                        <option key={`${index}-${kIndex}`} value={kota}>
                                                            {kota}
                                                        </option>
                                                    ))}
                                                </optgroup>
                                            ))}
                                        </select>
                                        <MapPin className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none" />
                                    </div>
                                </div>
                                <div>
                                    <label className={labelClass}>Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" value={formData.tanggal_lahir} onChange={handleInputChange} className={inputClass} />
                                </div>
                                <div>
                                    <label className={labelClass}>Jenis Kelamin</label>
                                    <select name="jenis_kelamin" value={formData.jenis_kelamin} onChange={handleInputChange} className={inputClass}>
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="pria">Pria</option>
                                        <option value="wanita">Wanita</option>
                                    </select>
                                </div>
                                <div>
                                    <label className={labelClass}>Kecamatan</label>
                                    <select 
                                        name="kecamatan" 
                                        value={formData.kecamatan} 
                                        onChange={handleInputChange} 
                                        className={inputClass}
                                    >
                                        <option value="">Pilih Kecamatan</option>
                                        {kecamatanList.map((kec) => (
                                            <option key={kec.id} value={kec.name}>
                                                {kec.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className={labelClass}>Status Pernikahan</label>
                                    <select name="status_pernikahan" value={formData.status_pernikahan} onChange={handleInputChange} className={inputClass}>
                                        <option value="">Pilih Status</option>
                                        <option value="TK">Belum Menikah</option>
                                        <option value="K0">Menikah (0 Anak)</option>
                                        <option value="K1">Menikah (1 Anak)</option>
                                        <option value="K2">Menikah (2 Anak)</option>
                                        <option value="K3">Menikah (3 Anak)</option>
                                    </select>
                                </div>
                                <div>
                                    <label className={labelClass}>Golongan Darah</label>
                                    <select name="golongan_darah" value={formData.golongan_darah} onChange={handleInputChange} className={inputClass}>
                                        <option value="">Pilih Golongan Darah</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="AB">AB</option>
                                        <option value="O">O</option>
                                    </select>
                                </div>
                                <div className="md:col-span-2">
                                    <label className={labelClass}>Alamat Lengkap</label>
                                    <textarea name="alamat_lengkap" value={formData.alamat_lengkap} onChange={handleInputChange} className={inputClass} rows={3} />
                                </div>
                            </div>
                        </section>

                        {/* Data Orang Tua */}
                        <section className={cardClass}>
                             <div className={sectionHeaderClass}>
                                <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                                    <Users className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-slate-800 text-lg">Data Orang Tua</h3>
                                    <p className="text-slate-500 text-xs font-medium">Informasi mengenai orang tua Anda</p>
                                </div>
                            </div>
                            <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className={labelClass}>Nama Ayah</label>
                                    <input type="text" name="nama_ayah" value={formData.nama_ayah} onChange={handleInputChange} className={inputClass} />
                                </div>
                                <div>
                                    <label className={labelClass}>Pekerjaan Ayah</label>
                                    <input type="text" name="pekerjaan_ayah" value={formData.pekerjaan_ayah} onChange={handleInputChange} className={inputClass} />
                                </div>
                                <div>
                                    <label className={labelClass}>Nama Ibu</label>
                                    <input type="text" name="nama_ibu" value={formData.nama_ibu} onChange={handleInputChange} className={inputClass} />
                                </div>
                                <div>
                                    <label className={labelClass}>Pekerjaan Ibu</label>
                                    <input type="text" name="pekerjaan_ibu" value={formData.pekerjaan_ibu} onChange={handleInputChange} className={inputClass} />
                                </div>
                                <div className="md:col-span-2">
                                    <label className={labelClass}>Alamat Orang Tua</label>
                                    <textarea name="alamat_orang_tua" value={formData.alamat_orang_tua} onChange={handleInputChange} className={inputClass} rows={2} />
                                </div>
                            </div>
                        </section>

                        {/* Data Keluarga */}
                        <section className={cardClass}>
                             <div className={`${sectionHeaderClass} justify-between`}>
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                                        <Users className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h3 className="font-bold text-slate-800 text-lg">Data Keluarga</h3>
                                        <p className="text-slate-500 text-xs font-medium">Daftar anggota keluarga inti</p>
                                    </div>
                                </div>
                                <button type="button" onClick={addKeluarga} className="px-4 py-2 bg-sky-50 text-sky-800 hover:bg-sky-100 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                                    <Plus className="w-4 h-4" /> Tambah
                                </button>
                            </div>
                            <div className="p-6 space-y-4">
                                {keluargaList.length === 0 ? (
                                    <div className="text-center py-8 text-slate-400 bg-slate-50/50 rounded-xl border-2 border-dashed border-slate-200">
                                        <p className="font-medium">Belum ada data keluarga</p>
                                        <p className="text-xs mt-1">Klik tombol tambah untuk memasukkan data</p>
                                    </div>
                                ) : (
                                    keluargaList.map((keluarga, index) => (
                                        <div key={index} className="bg-slate-50 p-5 rounded-2xl border border-slate-200 relative group hover:border-sky-200 transition-colors">
                                            <button type="button" onClick={() => removeKeluarga(index)} className="absolute top-4 right-4 text-slate-400 hover:text-red-500 p-2 hover:bg-red-50 rounded-lg transition-all">
                                                <Trash2 className="w-4 h-4" />
                                            </button>
                                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mt-2">
                                                <div>
                                                    <label className="text-xs font-semibold text-slate-500 block mb-2">Nama</label>
                                                    <input type="text" value={keluarga.nama} onChange={(e) => updateKeluarga(index, "nama", e.target.value)} className={inputClass} required placeholder="Nama Anggota" />
                                                </div>
                                                <div>
                                                    <label className="text-xs font-semibold text-slate-500 block mb-2">Hubungan</label>
                                                    <select value={keluarga.hubungan} onChange={(e) => updateKeluarga(index, "hubungan", e.target.value)} className={inputClass}>
                                                        <option value="suami">Suami</option>
                                                        <option value="istri">Istri</option>
                                                        <option value="anak">Anak</option>
                                                        <option value="lainnya">Lainnya</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label className="text-xs font-semibold text-slate-500 block mb-2">Tgl Lahir</label>
                                                    <input type="date" value={keluarga.tanggal_lahir} onChange={(e) => updateKeluarga(index, "tanggal_lahir", e.target.value)} className={inputClass} required />
                                                </div>
                                                <div>
                                                    <label className="text-xs font-semibold text-slate-500 block mb-2">Pekerjaan</label>
                                                    <input type="text" value={keluarga.pekerjaan} onChange={(e) => updateKeluarga(index, "pekerjaan", e.target.value)} className={inputClass} required placeholder="Pekerjaan" />
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        </section>

                        {/* Social Media */}
                        <section className={cardClass}>
                             <div className={`${sectionHeaderClass} justify-between`}>
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                                        <Share2 className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h3 className="font-bold text-slate-800 text-lg">Sosial Media</h3>
                                        <p className="text-slate-500 text-xs font-medium">Tautkan akun sosial media Anda</p>
                                    </div>
                                </div>
                                <button type="button" onClick={addSosmed} className="px-4 py-2 bg-sky-50 text-sky-800 hover:bg-sky-100 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                                    <Plus className="w-4 h-4" /> Tambah
                                </button>
                            </div>
                            <div className="p-6 space-y-4">
                                {sosmedList.length === 0 ? (
                                    <div className="text-center py-8 text-slate-400 bg-slate-50/50 rounded-xl border-2 border-dashed border-slate-200">
                                        <p className="font-medium">Belum ada sosial media</p>
                                        <p className="text-xs mt-1">Klik tombol tambah untuk memasukkan data</p>
                                    </div>
                                ) : (
                                    sosmedList.map((sosmed, index) => (
                                        <div key={index} className="bg-slate-50 p-5 rounded-2xl border border-slate-200 relative group hover:border-sky-200 transition-colors">
                                            <button type="button" onClick={() => removeSosmed(index)} className="absolute top-4 right-4 text-slate-400 hover:text-red-500 p-2 hover:bg-red-50 rounded-lg transition-all">
                                                <Trash2 className="w-4 h-4" />
                                            </button>
                                            <div className="grid grid-cols-1 md:grid-cols-3 gap-5 mt-2">
                                                <div>
                                                    <label className="text-xs font-semibold text-slate-500 block mb-2">Platform</label>
                                                    <select value={sosmed.id_platform} onChange={(e) => updateSosmed(index, "id_platform", parseInt(e.target.value))} className={inputClass}>
                                                        <option value="1">Facebook</option>
                                                        <option value="2">Instagram</option>
                                                        <option value="3">Twitter</option>
                                                        <option value="4">LinkedIn</option>
                                                        <option value="5">TikTok</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label className="text-xs font-semibold text-slate-500 block mb-2">Username</label>
                                                    <input type="text" value={sosmed.username} onChange={(e) => updateSosmed(index, "username", e.target.value)} className={inputClass} placeholder="@username" required />
                                                </div>
                                                <div>
                                                    <label className="text-xs font-semibold text-slate-500 block mb-2">Link URL</label>
                                                    <input type="text" value={sosmed.link} onChange={(e) => updateSosmed(index, "link", e.target.value)} className={inputClass} placeholder="https://..." required />
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        </section>

                        {/* Sticky Action Button for Mobile */}
                        <div className="fixed bottom-0 left-0 right-0 p-4 bg-white/80 backdrop-blur-md border-t border-slate-200 lg:static lg:bg-transparent lg:border-none lg:p-0 z-40">
                            <button
                                type="submit"
                                disabled={isLoading}
                                className="w-full lg:w-auto px-8 py-3.5 bg-sky-800 text-white rounded-xl hover:bg-sky-900 disabled:opacity-70 disabled:cursor-not-allowed font-semibold flex items-center justify-center gap-2 transition-all transform active:scale-95"
                            >
                                {isLoading ? (
                                    <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                                ) : (
                                    <Save className="w-5 h-5" />
                                )}
                                {isLoading ? "Menyimpan..." : "Simpan Perubahan"}
                            </button>
                        </div>
                    </form>
                ) : (
                    // Password Tab
                    <div className={cardClass}>
                         <div className={sectionHeaderClass}>
                            <Lock className="w-5 h-5 text-sky-800" />
                            <h3 className="font-semibold text-slate-800">Keamanan Akun</h3>
                        </div>
                        <form onSubmit={handlePasswordChange} className="p-6">
                            <div className="max-w-xl mx-auto space-y-6">
                                <div className="p-4 bg-sky-50 text-sky-900 text-sm rounded-lg flex items-start gap-3">
                                    <div className="mt-0.5"><Lock className="w-4 h-4"/></div>
                                    <p>Pastikan password baru Anda menggunakan kombinasi huruf besar, kecil, dan angka untuk keamanan maksimal.</p>
                                </div>
                                <div>
                                    <label className={labelClass}>Kata Sandi Sekarang</label>
                                    <input type="password" name="password_lama" value={passwordForm.password_lama} onChange={(e) => setPasswordForm({ ...passwordForm, password_lama: e.target.value })} className={inputClass} required />
                                </div>
                                <div>
                                    <label className={labelClass}>Kata Sandi Baru</label>
                                    <input type="password" name="password_baru" value={passwordForm.password_baru} onChange={(e) => setPasswordForm({ ...passwordForm, password_baru: e.target.value })} className={inputClass} required />
                                </div>
                                <div>
                                    <label className={labelClass}>Ulangi Kata Sandi Baru</label>
                                    <input type="password" name="konf_password" value={passwordForm.konf_password} onChange={(e) => setPasswordForm({ ...passwordForm, konf_password: e.target.value })} className={inputClass} required />
                                </div>
                                <div className="pt-4">
                                    <button
                                        type="submit"
                                        disabled={isLoading}
                                        className="w-full px-6 py-3 bg-sky-800 text-white rounded-lg hover:bg-sky-900 disabled:opacity-50 font-semibold shadow-md transition-all flex justify-center items-center gap-2"
                                    >
                                        {isLoading ? "Memproses..." : "Update Password"}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                )}
            </div>
        </div>
      </main>

      {/* Toast Notification */}
      {toastMessage.show && (
        <div className="fixed top-4 right-4 z-50 max-w-sm">
          <div
            className={`p-4 rounded-lg shadow-lg border-l-4 ${
              toastMessage.type === "success"
                ? "bg-green-50 border-green-500 text-green-800"
                : toastMessage.type === "error"
                ? "bg-red-50 border-red-500 text-red-800"
                : toastMessage.type === "warning"
                ? "bg-yellow-50 border-yellow-500 text-yellow-800"
                : "bg-blue-50 border-blue-500 text-blue-800"
            }`}
          >
            <div className="flex items-start">
              <div className="flex-shrink-0">
                {toastMessage.type === "success" && <span className="text-green-500 text-xl">✅</span>}
                {toastMessage.type === "error" && <span className="text-red-500 text-xl">❌</span>}
                {toastMessage.type === "warning" && <span className="text-yellow-500 text-xl">⚠️</span>}
                {toastMessage.type === "info" && <span className="text-blue-500 text-xl">ℹ️</span>}
              </div>
              <div className="ml-3 flex-1">
                <p className="text-sm font-medium whitespace-pre-line">{toastMessage.message}</p>
              </div>
              <div className="ml-4 flex-shrink-0">
                <button
                  onClick={() => setToastMessage((prev) => ({ ...prev, show: false }))}
                  className={`inline-flex rounded-md p-1.5 ${
                    toastMessage.type === "success"
                      ? "text-green-500 hover:bg-green-100"
                      : toastMessage.type === "error"
                      ? "text-red-500 hover:bg-red-100"
                      : toastMessage.type === "warning"
                      ? "text-yellow-500 hover:bg-yellow-100"
                      : "text-blue-500 hover:bg-blue-100"
                  }`}
                >
                  <span className="sr-only">Close</span>
                  <svg className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
      </div>
    </AccessControl>
  );
}
