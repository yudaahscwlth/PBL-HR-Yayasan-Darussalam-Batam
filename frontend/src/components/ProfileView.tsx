"use client";

import { useState, useEffect } from "react";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import api from "@/lib/api";
import { API_CONFIG } from "@/config/api";
import { 
  User, Briefcase, Users, Share2, LogOut, Edit,
  MapPin, Mail, Phone, CreditCard, Calendar, Heart
} from "lucide-react";
import AccessControl from "@/components/AccessControl";
import BottomNavbar from "@/components/BottomNavbar";

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
    sosial_media: { nama_platform: string };
  }>;
}

interface ProfileViewProps {
  allowedRoles: string[];
  editPath: string;
}

export default function ProfileView({ allowedRoles, editPath }: ProfileViewProps) {
  const { user, logout } = useAuthStore();
  const router = useRouter();
  const [profileData, setProfileData] = useState<ProfileData | null>(null);

  useEffect(() => {
    const fetchProfileData = async () => {
      try {
        const response = await api.get("/profile/complete");
        setProfileData(response.data.data.user);
      } catch (error) {
        console.error("Error fetching profile:", error);
      }
    };
    fetchProfileData();
  }, []);

  const handleLogout = async () => {
    try {
      await logout();
      router.push("/");
    } catch (error) {
      console.error("Logout error:", error);
    }
  };

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

  const cardClass = "bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden";
  const sectionHeaderClass = "px-6 py-4 border-b border-slate-50 flex items-center gap-3 bg-white";
  const labelClass = "text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1";
  const valueClass = "text-slate-800 font-medium text-sm";

  return (
    <AccessControl allowedRoles={allowedRoles}>
      <div className="min-h-screen bg-gray-100 font-sans pb-28">
        {/* Header Background */}
        <div className="bg-sky-800 h-48 w-full relative">
            <div className="absolute inset-0 bg-gradient-to-b from-transparent to-black/20"></div>
        </div>

        <div className="container mx-auto px-4 -mt-20 relative z-10 max-w-5xl">
            {/* Profile Header Card */}
            <div className={`${cardClass} mb-6`}>
                <div className="p-6 flex flex-col md:flex-row items-center md:items-end gap-6">
                    <div className="relative">
                        <div className="p-1 bg-white rounded-full shadow-lg">
                            <img
                                src={profileData.profile_pribadi?.foto ? `${API_CONFIG.BASE_URL}/storage/${profileData.profile_pribadi.foto}` : "/assets/img/profile-img.jpg"}
                                alt="Profile"
                                className="w-32 h-32 rounded-full object-cover border-4 border-white shadow-sm"
                            />
                        </div>
                    </div>
                    <div className="flex-1 text-center md:text-left mb-2">
                        <h1 className="text-2xl font-bold text-slate-800">{profileData.profile_pribadi?.nama_lengkap}</h1>
                        <p className="text-slate-500 font-medium">{profileData.profile_pekerjaan?.jabatan?.nama_jabatan}</p>
                        <div className="flex items-center justify-center md:justify-start gap-4 mt-3 text-sm text-slate-500">
                            <div className="flex items-center gap-1.5">
                                <Briefcase className="w-4 h-4" />
                                <span>{profileData.profile_pekerjaan?.departemen?.nama_departemen}</span>
                            </div>
                            <div className="flex items-center gap-1.5">
                                <MapPin className="w-4 h-4" />
                                <span>{profileData.profile_pekerjaan?.tempat_kerja?.nama_tempat}</span>
                            </div>
                        </div>
                    </div>
                    <div className="flex gap-3">
                        <button 
                            onClick={() => router.push(editPath)}
                            className="px-4 py-2 bg-sky-50 text-sky-800 hover:bg-sky-100 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2"
                        >
                            <Edit className="w-4 h-4" /> Edit Profile
                        </button>
                        <button 
                            onClick={handleLogout}
                            className="px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2"
                        >
                            <LogOut className="w-4 h-4" /> Logout
                        </button>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Left Column */}
                <div className="space-y-6">
                    {/* Contact Info */}
                    <div className={cardClass}>
                        <div className={sectionHeaderClass}>
                            <User className="w-5 h-5 text-sky-800" />
                            <h3 className="font-bold text-slate-800">Kontak & Pribadi</h3>
                        </div>
                        <div className="p-6 space-y-4">
                            <div>
                                <p className={labelClass}>Email</p>
                                <div className="flex items-center gap-2 text-slate-800">
                                    <Mail className="w-4 h-4 text-slate-400" />
                                    <span className={valueClass}>{profileData.email}</span>
                                </div>
                            </div>
                            <div>
                                <p className={labelClass}>No. HP</p>
                                <div className="flex items-center gap-2 text-slate-800">
                                    <Phone className="w-4 h-4 text-slate-400" />
                                    <span className={valueClass}>{profileData.profile_pribadi?.no_hp || "-"}</span>
                                </div>
                            </div>
                            <div>
                                <p className={labelClass}>Tempat, Tgl Lahir</p>
                                <div className="flex items-center gap-2 text-slate-800">
                                    <Calendar className="w-4 h-4 text-slate-400" />
                                    <span className={valueClass}>
                                        {profileData.profile_pribadi?.tempat_lahir}, {profileData.profile_pribadi?.tanggal_lahir}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p className={labelClass}>Status Pernikahan</p>
                                <div className="flex items-center gap-2 text-slate-800">
                                    <Heart className="w-4 h-4 text-slate-400" />
                                    <span className={valueClass}>{profileData.profile_pribadi?.status_pernikahan || "-"}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Social Media */}
                    <div className={cardClass}>
                        <div className={sectionHeaderClass}>
                            <Share2 className="w-5 h-5 text-sky-800" />
                            <h3 className="font-bold text-slate-800">Sosial Media</h3>
                        </div>
                        <div className="p-6 space-y-3">
                            {profileData.user_sosial_media.length > 0 ? (
                                profileData.user_sosial_media.map((sosmed, idx) => (
                                    <a 
                                        key={idx} 
                                        href={sosmed.link} 
                                        target="_blank" 
                                        rel="noopener noreferrer"
                                        className="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-sky-50 transition-colors group"
                                    >
                                        <div className="p-2 bg-white rounded-lg shadow-sm group-hover:scale-110 transition-transform">
                                            <Share2 className="w-4 h-4 text-sky-800" />
                                        </div>
                                        <div>
                                            <p className="text-xs font-semibold text-slate-500">{sosmed.sosial_media?.nama_platform}</p>
                                            <p className="text-sm font-medium text-slate-800">{sosmed.username}</p>
                                        </div>
                                    </a>
                                ))
                            ) : (
                                <p className="text-slate-400 text-sm text-center py-4">Belum ada sosial media</p>
                            )}
                        </div>
                    </div>
                </div>

                {/* Right Column */}
                <div className="lg:col-span-2 space-y-6">
                    {/* Employment Data */}
                    <div className={cardClass}>
                         <div className={sectionHeaderClass}>
                            <Briefcase className="w-5 h-5 text-sky-800" />
                            <h3 className="font-bold text-slate-800">Data Pekerjaan</h3>
                        </div>
                        <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p className={labelClass}>Nomor Induk Karyawan</p>
                                <p className={valueClass}>{profileData.profile_pekerjaan?.nomor_induk_karyawan}</p>
                            </div>
                            <div>
                                <p className={labelClass}>Status Karyawan</p>
                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {profileData.profile_pekerjaan?.status}
                                </span>
                            </div>
                            <div>
                                <p className={labelClass}>Tanggal Masuk</p>
                                <p className={valueClass}>{profileData.profile_pekerjaan?.tanggal_masuk}</p>
                            </div>
                            <div>
                                <p className={labelClass}>Masa Kerja</p>
                                <p className={valueClass}>-</p> {/* Hitung masa kerja jika perlu */}
                            </div>
                        </div>
                    </div>

                    {/* Family Data */}
                    <div className={cardClass}>
                         <div className={sectionHeaderClass}>
                            <Users className="w-5 h-5 text-sky-800" />
                            <h3 className="font-bold text-slate-800">Data Keluarga</h3>
                        </div>
                        <div className="p-6">
                            {profileData.keluarga.length > 0 ? (
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {profileData.keluarga.map((keluarga, idx) => (
                                        <div key={idx} className="p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                                            <div className="flex items-center justify-between mb-2">
                                                <p className="font-semibold text-slate-800">{keluarga.nama}</p>
                                                <span className="text-xs font-medium px-2 py-1 bg-white rounded-md shadow-sm text-slate-600 uppercase">
                                                    {keluarga.hubungan}
                                                </span>
                                            </div>
                                            <div className="space-y-1 text-xs text-slate-500">
                                                <p>Lahir: {keluarga.tanggal_lahir}</p>
                                                <p>Pekerjaan: {keluarga.pekerjaan}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-slate-400 text-sm text-center py-4">Belum ada data keluarga</p>
                            )}
                        </div>
                    </div>

                    {/* Address */}
                    <div className={cardClass}>
                        <div className={sectionHeaderClass}>
                            <MapPin className="w-5 h-5 text-sky-800" />
                            <h3 className="font-bold text-slate-800">Alamat Lengkap</h3>
                        </div>
                        <div className="p-6">
                            <p className="text-slate-700 leading-relaxed">
                                {profileData.profile_pribadi?.alamat_lengkap}
                            </p>
                            <p className="mt-2 text-sm text-slate-500">
                                Kecamatan: {profileData.profile_pribadi?.kecamatan}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <BottomNavbar />
      </div>
    </AccessControl>
  );
}
