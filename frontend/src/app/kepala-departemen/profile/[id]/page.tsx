"use client";

import { useState, useEffect, useRef } from "react";
import { useRouter, useParams } from "next/navigation";
import axios from "axios";
import {
    User, Briefcase, Users, Share2, Lock,
    ArrowLeft,
    Camera
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

    const [profileData, setProfileData] = useState<ProfileData | null>(null);
    const [isFetching, setIsFetching] = useState(true);

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

            } catch (error: any) {
                console.error("Error fetching user:", error);
                
                // Handle 403 Forbidden error - redirect to unauthorized page
                // Use replace instead of push to avoid infinite loop when clicking back
                if (error.response?.status === 403) {
                    router.replace('/unauthorized');
                    return;
                }
                
                // Handle 404 Not Found error - user doesn't exist or not accessible
                // Redirect to unauthorized page instead of showing error toast
                if (error.response?.status === 404) {
                    router.replace('/unauthorized');
                    return;
                }
                
                // For other errors, show toast and redirect to pegawai list
                toast.error("Gagal memuat data pegawai");
                router.push("/kepala-departemen/kelola/pegawai");
            } finally {
                setIsFetching(false);
            }
        };

        fetchUser();
    }, [userId, router]);

    // Styles
    const inputClass = "w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-800/20 focus:border-sky-800 transition-all duration-200 text-slate-700 bg-gray-50 placeholder-slate-400 text-sm font-medium disabled:cursor-not-allowed";
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

    if (!profileData) return null;

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
                        <h1 className="text-lg font-bold text-slate-800">Detail Pegawai</h1>
                        <div className="w-20"></div>
                    </div>
                </div>

                <main className="container mx-auto px-4 py-8 max-w-6xl">
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">

                        {/* Sidebar / Profile Summary */}
                        <div className="lg:col-span-4 space-y-6 lg:sticky lg:top-24 h-fit">
                            <div className={`${cardClass} flex flex-col items-center text-center relative`}>
                                <div className="h-32 w-full absolute top-0 left-0 bg-gradient-to-b from-sky-800 to-sky-600"></div>
                                <div className="px-6 pb-8 pt-16 w-full flex flex-col items-center relative z-10">
                                    <div className="relative group">
                                        <div className="p-1 bg-white rounded-full shadow-lg">
                                            <img
                                                src={
                                                    profileData?.profile_pribadi?.foto
                                                        ? `${API_CONFIG.BASE_URL}/storage/${profileData.profile_pribadi.foto}`
                                                        : "/assets/img/profile-img.jpg"
                                                }
                                                alt="Profile"
                                                className="w-32 h-32 rounded-full object-cover border-4 border-white shadow-sm"
                                            />
                                        </div>
                                    </div>
                                    <h2 className="mt-4 text-xl font-bold text-slate-800">{profileData?.profile_pribadi?.nama_lengkap || "Nama Pegawai"}</h2>
                                    <p className="text-slate-500 font-medium">{profileData?.profile_pekerjaan?.jabatan?.nama_jabatan || "Jabatan"}</p>
                                    <div className="mt-3 inline-flex items-center px-4 py-1.5 rounded-full bg-sky-50 text-sky-900 text-xs font-semibold uppercase tracking-wide">
                                        {profileData?.profile_pekerjaan?.status || "Status"}
                                    </div>
                                </div>
                            </div>

                            {/* Account Settings Card (Read Only) */}
                            <div className={cardClass}>
                                <div className={sectionHeaderClass}>
                                    <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                                        <Lock className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h3 className="font-bold text-slate-800 text-lg">Akun</h3>
                                        <p className="text-slate-500 text-xs font-medium">Informasi akun login</p>
                                    </div>
                                </div>
                                <div className="p-6 space-y-4">
                                    <div>
                                        <label className={labelClass}>Email</label>
                                        <input type="email" value={profileData?.email || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Role</label>
                                        <input type="text" value={profileData?.roles?.[0]?.name || ""} disabled className={inputClass} />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Main Content Form */}
                        <div className="lg:col-span-8 space-y-6">

                            {/* Data Pekerjaan */}
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
                                        <input type="text" value={profileData?.profile_pekerjaan?.departemen?.nama_departemen || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Jabatan</label>
                                        <input type="text" value={profileData?.profile_pekerjaan?.jabatan?.nama_jabatan || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Tempat Kerja</label>
                                        <input type="text" value={profileData?.profile_pekerjaan?.tempat_kerja?.nama_tempat || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Status Pegawai</label>
                                        <input type="text" value={profileData?.profile_pekerjaan?.status || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>NIK Karyawan</label>
                                        <input type="text" value={profileData?.profile_pekerjaan?.nomor_induk_karyawan || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Tanggal Masuk</label>
                                        <input type="text" value={profileData?.profile_pekerjaan?.tanggal_masuk || ""} disabled className={inputClass} />
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
                                        <input type="text" value={profileData?.profile_pribadi?.nama_lengkap || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>NIK (KTP)</label>
                                        <input type="text" value={profileData?.profile_pribadi?.nomor_induk_kependudukan || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>No. Handphone</label>
                                        <input type="text" value={profileData?.profile_pribadi?.no_hp || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>NPWP</label>
                                        <input type="text" value={profileData?.profile_pribadi?.npwp || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Tempat Lahir</label>
                                        <input type="text" value={profileData?.profile_pribadi?.tempat_lahir || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Tanggal Lahir</label>
                                        <input type="text" value={profileData?.profile_pribadi?.tanggal_lahir || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Jenis Kelamin</label>
                                        <input type="text" value={profileData?.profile_pribadi?.jenis_kelamin || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Status Pernikahan</label>
                                        <input type="text" value={profileData?.profile_pribadi?.status_pernikahan || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Golongan Darah</label>
                                        <input type="text" value={profileData?.profile_pribadi?.golongan_darah || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Kecamatan</label>
                                        <input type="text" value={profileData?.profile_pribadi?.kecamatan || ""} disabled className={inputClass} />
                                    </div>
                                    <div className="md:col-span-2">
                                        <label className={labelClass}>Alamat Lengkap</label>
                                        <textarea value={profileData?.profile_pribadi?.alamat_lengkap || ""} disabled className={inputClass} rows={3} />
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
                                        <input type="text" value={profileData?.orang_tua?.nama_ayah || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Pekerjaan Ayah</label>
                                        <input type="text" value={profileData?.orang_tua?.pekerjaan_ayah || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Nama Ibu</label>
                                        <input type="text" value={profileData?.orang_tua?.nama_ibu || ""} disabled className={inputClass} />
                                    </div>
                                    <div>
                                        <label className={labelClass}>Pekerjaan Ibu</label>
                                        <input type="text" value={profileData?.orang_tua?.pekerjaan_ibu || ""} disabled className={inputClass} />
                                    </div>
                                    <div className="md:col-span-2">
                                        <label className={labelClass}>Alamat Orang Tua</label>
                                        <textarea value={profileData?.orang_tua?.alamat_orang_tua || ""} disabled className={inputClass} rows={2} />
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
                                </div>
                                <div className="p-6 space-y-4">
                                    {(!profileData?.keluarga || profileData.keluarga.length === 0) ? (
                                        <div className="text-center py-8 text-slate-400 bg-slate-50/50 rounded-xl border-2 border-dashed border-slate-200">
                                            <p className="font-medium">Belum ada data keluarga</p>
                                        </div>
                                    ) : (
                                        profileData.keluarga.map((keluarga, index) => (
                                            <div key={index} className="bg-slate-50 p-5 rounded-2xl border border-slate-200 relative">
                                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mt-2 transition-all">
                                                    <div>
                                                        <label className="text-xs font-semibold text-slate-500 block mb-2">Nama</label>
                                                        <input type="text" value={keluarga.nama} disabled className={inputClass} />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs font-semibold text-slate-500 block mb-2">Hubungan</label>
                                                        <input type="text" value={keluarga.hubungan} disabled className={inputClass} />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs font-semibold text-slate-500 block mb-2">Tgl Lahir</label>
                                                        <input type="text" value={keluarga.tanggal_lahir} disabled className={inputClass} />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs font-semibold text-slate-500 block mb-2">Pekerjaan</label>
                                                        <input type="text" value={keluarga.pekerjaan} disabled className={inputClass} />
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
                                            <p className="text-slate-500 text-xs font-medium">Akun sosial media</p>
                                        </div>
                                    </div>
                                </div>
                                <div className="p-6 space-y-4">
                                    {(!profileData?.user_sosial_media || profileData.user_sosial_media.length === 0) ? (
                                        <div className="text-center py-8 text-slate-400 bg-slate-50/50 rounded-xl border-2 border-dashed border-slate-200">
                                            <p className="font-medium">Belum ada sosial media</p>
                                        </div>
                                    ) : (
                                        profileData.user_sosial_media.map((sosmed, index) => (
                                            <div key={index} className="bg-slate-50 p-5 rounded-2xl border border-slate-200 relative">
                                                <div className="grid grid-cols-1 md:grid-cols-3 gap-5 mt-2">
                                                    <div>
                                                        <label className="text-xs font-semibold text-slate-500 block mb-2">Platform</label>
                                                        <input type="text" value={sosmed.sosial_media.nama_platform} disabled className={inputClass} />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs font-semibold text-slate-500 block mb-2">Username</label>
                                                        <input type="text" value={sosmed.username} disabled className={inputClass} />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs font-semibold text-slate-500 block mb-2">Link URL</label>
                                                        <input type="text" value={sosmed.link} disabled className={inputClass} />
                                                    </div>
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </div>
                            </section>
                        </div>
                    </div>
                </main>
            </div>
        </AccessControl>
    );
}
