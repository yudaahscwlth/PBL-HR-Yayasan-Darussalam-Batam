"use client";

import { useState, useEffect, useRef } from "react";
import { useRouter, useParams } from "next/navigation";
import axios from "axios";
import { 
  User, Briefcase, Users, Share2, Lock, 
  Save, Plus, Trash2, Camera, ChevronRight,
  MapPin, Mail, Phone, CreditCard, ArrowLeft,
  Building, BadgeCheck
} from "lucide-react";
import AccessControl from "@/components/AccessControl";
import toast from "react-hot-toast";
import { API_CONFIG } from "@/config/api";

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
    id_departemen: number;
    id_tempat_kerja: number;
    id_jabatan: number;
    departemen: { id: number; nama_departemen: string };
    tempat_kerja: { id: number; nama_tempat: string };
    jabatan: { id: number; nama_jabatan: string };
  };
  roles: { name: string }[];
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
    sosial_media: { nama_platform: string };
  }>;
}

export default function KepalaDepartemenDetailProfilePage() {
  const router = useRouter();
  const params = useParams();
  const userId = params.id;
  
  const [isLoading, setIsLoading] = useState(false);
  const [isFetching, setIsFetching] = useState(true);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [profileData, setProfileData] = useState<ProfileData | null>(null);

  // Dropdown Data
  const [jabatanList, setJabatanList] = useState<any[]>([]);
  const [departemenList, setDepartemenList] = useState<any[]>([]);
  const [tempatKerjaList, setTempatKerjaList] = useState<any[]>([]);
  const [rolesList, setRolesList] = useState<any[]>([]);
  const [kecamatanList, setKecamatanList] = useState<Array<{ id: string; name: string }>>([]);
  const [kotaList, setKotaList] = useState<Array<{ provinsi: string; kota: string[] }>>([]);

  // Form State
  const [formData, setFormData] = useState({
    // Account
    email: "",
    password: "", // Optional for update
    role: "",

    // Data Pribadi
    nama_lengkap: "",
    nik: "", // nomor_induk_kependudukan
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

    // Data Pekerjaan
    nik_karyawan: "", // nomor_induk_karyawan
    tanggal_masuk: "",
    id_jabatan: "",
    id_departemen: "",
    id_tempat_kerja: "",
    status: "", // status_pegawai

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

  // Fetch Dropdown Data
  useEffect(() => {
    const fetchDropdowns = async () => {
      try {
        const token = localStorage.getItem("auth_token");
        const headers = { Authorization: `Bearer ${token}` };
        const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';

        const [jabatanRes, departemenRes, tempatKerjaRes, kecamatanRes, kotaRes] = await Promise.all([
          axios.get(`${apiUrl}/api/jabatan`, { headers }),
          axios.get(`${apiUrl}/api/departemen`, { headers }),
          axios.get(`${apiUrl}/api/tempat-kerja`, { headers }),
          fetch("/kecamatan-indonesia.json").then(res => res.json()),
          fetch("/kota-indonesia.json").then(res => res.json())
        ]);

        setJabatanList(jabatanRes.data.data);
        setDepartemenList(departemenRes.data.data.departemen);
        setTempatKerjaList(tempatKerjaRes.data.data);
        // Roles might need adjustment depending on API
        setRolesList([
            { name: 'tenaga pendidik' },
            { name: 'kepala sekolah' },
        ]); 
        setKecamatanList(kecamatanRes);
        setKotaList(kotaRes);

      } catch (error) {
        console.error("Error fetching dropdowns:", error);
        toast.error("Gagal memuat data opsi");
      }
    };
    fetchDropdowns();
  }, []);

  // Fetch User Data
  useEffect(() => {
    if (!userId) return;

    const fetchUser = async () => {
      setIsFetching(true);
      try {
        const token = localStorage.getItem("auth_token");
        const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';
        const response = await axios.get(`${apiUrl}/api/users/${userId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });

        const user = response.data.data;
        setProfileData(user);

        // Populate Form
        setFormData({
            email: user.email || "",
            password: "",
            role: user.roles?.[0]?.name || "",
            
            nama_lengkap: user.profile_pribadi?.nama_lengkap || "",
            nik: user.profile_pribadi?.nomor_induk_kependudukan || "",
            npwp: user.profile_pribadi?.npwp || "",
            tempat_lahir: user.profile_pribadi?.tempat_lahir || "",
            tanggal_lahir: user.profile_pribadi?.tanggal_lahir || "",
            jenis_kelamin: user.profile_pribadi?.jenis_kelamin || "",
            status_pernikahan: user.profile_pribadi?.status_pernikahan || "",
            golongan_darah: user.profile_pribadi?.golongan_darah || "",
            kecamatan: user.profile_pribadi?.kecamatan || "",
            alamat_lengkap: user.profile_pribadi?.alamat_lengkap || "",
            no_hp: user.profile_pribadi?.no_hp || "",
            foto: null,

            nik_karyawan: user.profile_pekerjaan?.nomor_induk_karyawan || "",
            tanggal_masuk: user.profile_pekerjaan?.tanggal_masuk || "",
            id_jabatan: user.profile_pekerjaan?.jabatan?.id?.toString() || "",
            id_departemen: user.profile_pekerjaan?.departemen?.id?.toString() || "",
            id_tempat_kerja: user.profile_pekerjaan?.tempat_kerja?.id?.toString() || "",
            status: user.profile_pekerjaan?.status || "",

            nama_ayah: user.orang_tua?.nama_ayah || "",
            pekerjaan_ayah: user.orang_tua?.pekerjaan_ayah || "",
            nama_ibu: user.orang_tua?.nama_ibu || "",
            pekerjaan_ibu: user.orang_tua?.pekerjaan_ibu || "",
            alamat_orang_tua: user.orang_tua?.alamat_orang_tua || "",
        });

        setKeluargaList(user.keluarga || []);
        setSosmedList(user.user_sosial_media || []);

      } catch (error) {
        console.error("Error fetching user:", error);
        toast.error("Gagal memuat data pegawai");
        router.push("/kepala-departemen/kelola/pegawai");
      } finally {
        setIsFetching(false);
      }
    };

    fetchUser();
  }, [userId, router]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData({ ...formData, [name]: value });
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setFormData({ ...formData, foto: e.target.files[0] });
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

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    
    try {
      const token = localStorage.getItem("auth_token");
      const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';
      
      const submitData = new FormData();
      submitData.append("_method", "PUT");
      
      Object.entries(formData).forEach(([key, value]) => {
        // Exclude empty password and foto, but include role
        if (value !== null && value !== "" && key !== "foto" && key !== "password") {
           submitData.append(key, value as string);
        }
      });

      if (formData.foto instanceof File) {
        submitData.append("foto", formData.foto);
      }

      // Append Keluarga List
      keluargaList.forEach((item, index) => {
        if (item.id) submitData.append(`keluarga[${index}][id]`, item.id.toString());
        submitData.append(`keluarga[${index}][nama]`, item.nama);
        submitData.append(`keluarga[${index}][hubungan]`, item.hubungan);
        submitData.append(`keluarga[${index}][tanggal_lahir]`, item.tanggal_lahir);
        submitData.append(`keluarga[${index}][pekerjaan]`, item.pekerjaan);
      });

      // Append Sosmed List
      sosmedList.forEach((item, index) => {
        if (item.id) submitData.append(`user_sosial_media[${index}][id]`, item.id.toString());
        submitData.append(`user_sosial_media[${index}][id_platform]`, item.id_platform.toString());
        submitData.append(`user_sosial_media[${index}][username]`, item.username);
        submitData.append(`user_sosial_media[${index}][link]`, item.link);
      });

      await axios.post(`${apiUrl}/api/users/${userId}`, submitData, {
        headers: { 
            Authorization: `Bearer ${token}`,
            "Content-Type": "multipart/form-data" 
        }
      });

      toast.success("Data pegawai berhasil diperbarui");
      // Refresh data
      const response = await axios.get(`${apiUrl}/api/users/${userId}`, {
          headers: { Authorization: `Bearer ${token}` }
      });
      const user = response.data.data;
      setProfileData(user);
      setKeluargaList(user.keluarga || []);
      setSosmedList(user.user_sosial_media || []);

    } catch (error: any) {
      console.error("Error updating profile:", error);
      toast.error(error.response?.data?.message || "Gagal memperbarui data pegawai");
    } finally {
      setIsLoading(false);
    }
  };

  // Styles
  const inputClass = "w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-800/20 focus:border-sky-800 transition-all duration-200 text-slate-700 bg-white placeholder-slate-400 text-sm font-medium hover:border-slate-300";
  const labelClass = "block text-sm font-semibold text-slate-700 mb-2";
  const cardClass = "bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300";
  const sectionHeaderClass = "px-6 py-5 border-b border-slate-50 flex items-center gap-3 bg-white";

  if (isFetching) {
    return (
      <div className="min-h-screen bg-slate-50 flex items-center justify-center">
        <div className="flex flex-col items-center gap-3">
            <div className="w-8 h-8 border-4 border-sky-800 border-t-transparent rounded-full animate-spin"></div>
            <span className="text-slate-500 font-medium">Memuat data pegawai...</span>
        </div>
      </div>
    );
  }

  return (
    <AccessControl allowedRoles={["kepala departemen"]}>
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
              <h1 className="text-lg font-bold text-slate-800">Edit Pegawai</h1>
              <div className="w-20"></div>
          </div>
        </div>

        <main className="container mx-auto px-4 py-8 max-w-6xl">
            <form onSubmit={handleSave} className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {/* Sidebar / Profile Summary */}
                <div className="lg:col-span-4 space-y-6 lg:sticky lg:top-24 h-fit">
                    <div className={`${cardClass} flex flex-col items-center text-center relative`}>
                        <div className="h-32 w-full absolute top-0 left-0 bg-gradient-to-b from-sky-800 to-sky-600"></div>
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
                                            : (profileData?.profile_pribadi?.foto ? `${API_CONFIG.BASE_URL}/storage/${profileData.profile_pribadi.foto}` : "/assets/img/profile-img.jpg")
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
                            <h2 className="mt-4 text-xl font-bold text-slate-800">{formData.nama_lengkap || "Nama Pegawai"}</h2>
                            <p className="text-slate-500 font-medium">{profileData?.profile_pekerjaan?.jabatan?.nama_jabatan || "Jabatan"}</p>
                            <div className="mt-3 inline-flex items-center px-4 py-1.5 rounded-full bg-sky-50 text-sky-900 text-xs font-semibold uppercase tracking-wide">
                                {formData.status || "Status"}
                            </div>
                        </div>
                    </div>

                    {/* Account Settings Card */}
                    <div className={cardClass}>
                        <div className={sectionHeaderClass}>
                            <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                                <Lock className="w-5 h-5" />
                            </div>
                            <div>
                                <h3 className="font-bold text-slate-800 text-lg">Akun</h3>
                                <p className="text-slate-500 text-xs font-medium">Pengaturan akun login</p>
                            </div>
                        </div>
                        <div className="p-6 space-y-4">
                            <div>
                                <label className={labelClass}>Email</label>
                                <input type="email" name="email" value={formData.email} onChange={handleInputChange} className={inputClass} required />
                            </div>

                            <div>
                                <label className={labelClass}>Role</label>
                                <select name="role" value={formData.role} onChange={handleInputChange} className={inputClass}>
                                    <option value="">Pilih Role</option>
                                    {rolesList.map((role, idx) => (
                                        <option key={idx} value={role.name}>{role.name}</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Main Content Form */}
                <div className="lg:col-span-8 space-y-6">
                    
                    {/* Data Pekerjaan (Editable) */}
                    <section className={cardClass}>
                        <div className={sectionHeaderClass}>
                            <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                                <Briefcase className="w-5 h-5" />
                            </div>
                            <div>
                                <h3 className="font-bold text-slate-800 text-lg">Data Pekerjaan</h3>
                                <p className="text-slate-500 text-xs font-medium">Informasi posisi dan penempatan</p>
                            </div>
                        </div>
                        <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label className={labelClass}>Departemen</label>
                                <select name="id_departemen" value={formData.id_departemen} onChange={handleInputChange} className={inputClass} required>
                                    <option value="">Pilih Departemen</option>
                                    {departemenList.map((dept: any) => (
                                        <option key={dept.id} value={dept.id}>{dept.nama_departemen}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className={labelClass}>Jabatan</label>
                                <select name="id_jabatan" value={formData.id_jabatan} onChange={handleInputChange} className={inputClass} required>
                                    <option value="">Pilih Jabatan</option>
                                    {jabatanList.map((jab: any) => (
                                        <option key={jab.id} value={jab.id}>{jab.nama_jabatan}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className={labelClass}>Tempat Kerja</label>
                                <select name="id_tempat_kerja" value={formData.id_tempat_kerja} onChange={handleInputChange} className={inputClass} required>
                                    <option value="">Pilih Tempat Kerja</option>
                                    {tempatKerjaList.map((tk: any) => (
                                        <option key={tk.id} value={tk.id}>{tk.nama_tempat || tk.nama_tempat_kerja}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className={labelClass}>Status Pegawai</label>
                                <select name="status" value={formData.status} onChange={handleInputChange} className={inputClass} required>
                                    <option value="">Pilih Status</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                    <option value="tetap">Tetap</option>
                                    <option value="kontrak">Kontrak</option>
                                    <option value="honorer">Honorer</option>
                                    <option value="magang">Magang</option>
                                    <option value="pensiun">Pensiun</option>
                                    <option value="cuti">Cuti</option>
                                    <option value="skorsing">Skorsing</option>
                                </select>
                            </div>
                            <div>
                                <label className={labelClass}>NIK Karyawan</label>
                                <input type="text" name="nik_karyawan" value={formData.nik_karyawan} onChange={handleInputChange} className={inputClass} required />
                            </div>
                            <div>
                                <label className={labelClass}>Tanggal Masuk</label>
                                <input type="date" name="tanggal_masuk" value={formData.tanggal_masuk} onChange={handleInputChange} className={inputClass} required />
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
                                <p className="text-slate-500 text-xs font-medium">Data diri lengkap pegawai</p>
                            </div>
                        </div>
                        <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label className={labelClass}>Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value={formData.nama_lengkap} onChange={handleInputChange} className={inputClass} required />
                            </div>
                            <div>
                                <label className={labelClass}>NIK (KTP)</label>
                                <input type="text" name="nik" value={formData.nik} onChange={handleInputChange} className={inputClass} required />
                            </div>
                            <div>
                                <label className={labelClass}>No. Handphone</label>
                                <input type="text" name="no_hp" value={formData.no_hp} onChange={handleInputChange} className={inputClass} />
                            </div>
                            <div>
                                <label className={labelClass}>NPWP</label>
                                <input type="text" name="npwp" value={formData.npwp} onChange={handleInputChange} className={inputClass} />
                            </div>
                            <div>
                                <label className={labelClass}>Tempat Lahir</label>
                                <select 
                                    name="tempat_lahir" 
                                    value={formData.tempat_lahir} 
                                    onChange={handleInputChange} 
                                    className={`${inputClass} appearance-none`}
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
                                <label className={labelClass}>Status Pernikahan</label>
                                <select name="status_pernikahan" value={formData.status_pernikahan} onChange={handleInputChange} className={inputClass}>
                                    <option value="">Pilih Status</option>
                                    <option value="belum nikah">Belum Menikah</option>
                                    <option value="sudah nikah">Sudah Menikah</option>
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
                                <p className="text-slate-500 text-xs font-medium">Informasi mengenai orang tua</p>
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
                                    <p className="text-slate-500 text-xs font-medium">Tautkan akun sosial media</p>
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
                            className="w-full lg:w-auto px-8 py-3.5 bg-sky-800 text-white rounded-xl hover:bg-sky-900 disabled:opacity-70 disabled:cursor-not-allowed font-semibold flex items-center justify-center gap-2 transition-all transform active:scale-95 ml-auto shadow-lg shadow-sky-800/20"
                        >
                            {isLoading ? (
                                <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                            ) : (
                                <Save className="w-5 h-5" />
                            )}
                            {isLoading ? "Menyimpan..." : "Simpan Perubahan"}
                        </button>
                    </div>
                </div>
            </form>
        </main>
      </div>
    </AccessControl>
  );
}
