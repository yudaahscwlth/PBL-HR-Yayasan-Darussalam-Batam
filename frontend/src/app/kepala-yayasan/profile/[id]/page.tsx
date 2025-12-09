"use client";

import { useState, useEffect } from "react";
import { useRouter, useParams } from "next/navigation";
import axios from "axios";
import { User, Briefcase, Users, Share2, Lock, ArrowLeft } from "lucide-react";
import AccessControl from "@/components/AccessControl";
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

export default function KepalaYayasanProfileDetailPage() {
  const router = useRouter();
  const params = useParams();
  const userId = params.id;

  const [isFetching, setIsFetching] = useState(true);
  const [profileData, setProfileData] = useState<ProfileData | null>(null);

  useEffect(() => {
    if (!userId) return;

    const fetchUser = async () => {
      setIsFetching(true);
      try {
        const token = localStorage.getItem("auth_token");
        const apiUrl =
          process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";
        const response = await axios.get(`${apiUrl}/api/users/${userId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        setProfileData(response.data.data);
      } catch (error) {
        console.error("Error fetching user:", error);
        router.push("/kepala-yayasan/kelola/pegawai");
      } finally {
        setIsFetching(false);
      }
    };

    fetchUser();
  }, [userId, router]);

  const formatDate = (dateString: string) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
      year: "numeric",
      month: "long",
      day: "numeric",
    });
  };

  const formatStatus = (status: string) => {
    const statusMap: Record<string, string> = {
      aktif: "Aktif",
      nonaktif: "Nonaktif",
      tetap: "Tetap",
      kontrak: "Kontrak",
      honorer: "Honorer",
      magang: "Magang",
      pensiun: "Pensiun",
      cuti: "Cuti",
      skorsing: "Skorsing",
    };
    return statusMap[status] || status;
  };

  const cardClass =
    "bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300";
  const sectionHeaderClass =
    "px-6 py-5 border-b border-slate-50 flex items-center gap-3 bg-white";
  const infoLabelClass = "text-xs font-semibold text-slate-500 mb-1";
  const infoValueClass = "text-sm font-medium text-slate-800";

  if (isFetching) {
    return (
      <div className="min-h-screen bg-slate-50 flex items-center justify-center">
        <div className="flex flex-col items-center gap-3">
          <div className="w-8 h-8 border-4 border-sky-800 border-t-transparent rounded-full animate-spin"></div>
          <span className="text-slate-500 font-medium">
            Memuat data pegawai...
          </span>
        </div>
      </div>
    );
  }

  if (!profileData) {
    return (
      <div className="min-h-screen bg-slate-50 flex items-center justify-center">
        <div className="text-center">
          <p className="text-slate-500 font-medium">
            Data pegawai tidak ditemukan
          </p>
          <button
            onClick={() => router.push("/kepala-yayasan/kelola/pegawai")}
            className="mt-4 px-4 py-2 bg-sky-800 text-white rounded-lg hover:bg-sky-900 transition-colors"
          >
            Kembali
          </button>
        </div>
      </div>
    );
  }

  return (
    <AccessControl allowedRoles={["kepala yayasan"]}>
      <div className="min-h-screen bg-gray-100 font-sans pb-10">
        <div className="bg-white border-b border-slate-200 sticky top-0 z-30">
          <div className="container mx-auto px-4 py-4 flex items-center justify-between">
            <button
              onClick={() => router.push("/kepala-yayasan/profile")}
              className="flex items-center gap-2 text-slate-600 hover:text-sky-800 transition-colors font-medium"
            >
              <ArrowLeft className="w-5 h-5" />
              <span>Kembali</span>
            </button>
            <h1 className="text-lg font-bold text-slate-800">
              Detail Profil Pegawai
            </h1>
            <div className="w-20"></div>
          </div>
        </div>

        <main className="container mx-auto px-4 py-8 max-w-6xl">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div className="lg:col-span-4 space-y-6 lg:sticky lg:top-24 h-fit">
              <div
                className={`${cardClass} flex flex-col items-center text-center relative`}
              >
                <div className="h-32 w-full absolute top-0 left-0 bg-gradient-to-b from-sky-800 to-sky-600"></div>
                <div className="px-6 pb-8 pt-16 w-full flex flex-col items-center relative z-10">
                  <div className="relative">
                    <div className="p-1 bg-white rounded-full shadow-lg">
                      <img
                        src={
                          profileData.profile_pribadi?.foto
                            ? `${API_CONFIG.BASE_URL}/storage/${profileData.profile_pribadi.foto}`
                            : "/assets/img/profile-img.jpg"
                        }
                        alt="Profile"
                        className="w-32 h-32 rounded-full object-cover border-4 border-white shadow-sm"
                      />
                    </div>
                  </div>
                  <h2 className="mt-4 text-xl font-bold text-slate-800">
                    {profileData.profile_pribadi?.nama_lengkap ||
                      "Nama Pegawai"}
                  </h2>
                  <p className="text-slate-500 font-medium">
                    {profileData.profile_pekerjaan?.jabatan?.nama_jabatan ||
                      "Jabatan"}
                  </p>
                  <div className="mt-3 inline-flex items-center px-4 py-1.5 rounded-full bg-sky-50 text-sky-900 text-xs font-semibold uppercase tracking-wide">
                    {formatStatus(profileData.profile_pekerjaan?.status || "")}
                  </div>
                </div>
              </div>

              <div className={cardClass}>
                <div className={sectionHeaderClass}>
                  <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                    <Lock className="w-5 h-5" />
                  </div>
                  <div>
                    <h3 className="font-bold text-slate-800 text-lg">
                      Informasi Akun
                    </h3>
                    <p className="text-slate-500 text-xs font-medium">
                      Data akun login
                    </p>
                  </div>
                </div>
                <div className="p-6 space-y-4">
                  <div>
                    <label className={infoLabelClass}>Email</label>
                    <div className={infoValueClass}>
                      {profileData.email || "-"}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>Role</label>
                    <div className={infoValueClass}>
                      {profileData.roles?.[0]?.name || "-"}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div className="lg:col-span-8 space-y-6">
              <section className={cardClass}>
                <div className={sectionHeaderClass}>
                  <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                    <Briefcase className="w-5 h-5" />
                  </div>
                  <div>
                    <h3 className="font-bold text-slate-800 text-lg">
                      Data Pekerjaan
                    </h3>
                    <p className="text-slate-500 text-xs font-medium">
                      Informasi posisi dan penempatan
                    </p>
                  </div>
                </div>
                <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label className={infoLabelClass}>Departemen</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pekerjaan?.departemen
                        ?.nama_departemen || "-"}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>Jabatan</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pekerjaan?.jabatan?.nama_jabatan ||
                        "-"}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>Tempat Kerja</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pekerjaan?.tempat_kerja
                        ?.nama_tempat || "-"}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>Status Pegawai</label>
                    <div className={infoValueClass}>
                      {formatStatus(
                        profileData.profile_pekerjaan?.status || ""
                      )}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>NIK Karyawan</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pekerjaan?.nomor_induk_karyawan ||
                        "-"}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>Tanggal Masuk</label>
                    <div className={infoValueClass}>
                      {formatDate(
                        profileData.profile_pekerjaan?.tanggal_masuk || ""
                      )}
                    </div>
                  </div>
                </div>
              </section>

              <section className={cardClass}>
                <div className={sectionHeaderClass}>
                  <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                    <User className="w-5 h-5" />
                  </div>
                  <div>
                    <h3 className="font-bold text-slate-800 text-lg">
                      Informasi Pribadi
                    </h3>
                    <p className="text-slate-500 text-xs font-medium">
                      Data diri lengkap pegawai
                    </p>
                  </div>
                </div>
                <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label className={infoLabelClass}>Nama Lengkap</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pribadi?.nama_lengkap || "-"}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>NIK (KTP)</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pribadi?.nomor_induk_kependudukan ||
                        "-"}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>No. Handphone</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pribadi?.no_hp || "-"}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>NPWP</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pribadi?.npwp || "-"}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>Tempat Lahir</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pribadi?.tempat_lahir || "-"}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>Tanggal Lahir</label>
                    <div className={infoValueClass}>
                      {formatDate(
                        profileData.profile_pribadi?.tanggal_lahir || ""
                      )}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>Jenis Kelamin</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pribadi?.jenis_kelamin === "pria"
                        ? "Pria"
                        : profileData.profile_pribadi?.jenis_kelamin ===
                          "wanita"
                        ? "Wanita"
                        : "-"}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>Status Pernikahan</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pribadi?.status_pernikahan ===
                      "belum nikah"
                        ? "Belum Menikah"
                        : profileData.profile_pribadi?.status_pernikahan ===
                          "sudah nikah"
                        ? "Sudah Menikah"
                        : "-"}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>Golongan Darah</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pribadi?.golongan_darah || "-"}
                    </div>
                  </div>
                  <div>
                    <label className={infoLabelClass}>Kecamatan</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pribadi?.kecamatan || "-"}
                    </div>
                  </div>
                  <div className="md:col-span-2">
                    <label className={infoLabelClass}>Alamat Lengkap</label>
                    <div className={infoValueClass}>
                      {profileData.profile_pribadi?.alamat_lengkap || "-"}
                    </div>
                  </div>
                </div>
              </section>

              {profileData.orang_tua && (
                <section className={cardClass}>
                  <div className={sectionHeaderClass}>
                    <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                      <Users className="w-5 h-5" />
                    </div>
                    <div>
                      <h3 className="font-bold text-slate-800 text-lg">
                        Data Orang Tua
                      </h3>
                      <p className="text-slate-500 text-xs font-medium">
                        Informasi mengenai orang tua
                      </p>
                    </div>
                  </div>
                  <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label className={infoLabelClass}>Nama Ayah</label>
                      <div className={infoValueClass}>
                        {profileData.orang_tua?.nama_ayah || "-"}
                      </div>
                    </div>
                    <div>
                      <label className={infoLabelClass}>Pekerjaan Ayah</label>
                      <div className={infoValueClass}>
                        {profileData.orang_tua?.pekerjaan_ayah || "-"}
                      </div>
                    </div>
                    <div>
                      <label className={infoLabelClass}>Nama Ibu</label>
                      <div className={infoValueClass}>
                        {profileData.orang_tua?.nama_ibu || "-"}
                      </div>
                    </div>
                    <div>
                      <label className={infoLabelClass}>Pekerjaan Ibu</label>
                      <div className={infoValueClass}>
                        {profileData.orang_tua?.pekerjaan_ibu || "-"}
                      </div>
                    </div>
                    <div className="md:col-span-2">
                      <label className={infoLabelClass}>Alamat Orang Tua</label>
                      <div className={infoValueClass}>
                        {profileData.orang_tua?.alamat_orang_tua || "-"}
                      </div>
                    </div>
                  </div>
                </section>
              )}

              {profileData.keluarga && profileData.keluarga.length > 0 && (
                <section className={cardClass}>
                  <div className={sectionHeaderClass}>
                    <div className="flex items-center gap-3">
                      <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                        <Users className="w-5 h-5" />
                      </div>
                      <div>
                        <h3 className="font-bold text-slate-800 text-lg">
                          Data Keluarga
                        </h3>
                        <p className="text-slate-500 text-xs font-medium">
                          Daftar anggota keluarga inti
                        </p>
                      </div>
                    </div>
                  </div>
                  <div className="p-6 space-y-4">
                    {profileData.keluarga.map((keluarga, index) => (
                      <div
                        key={index}
                        className="bg-slate-50 p-5 rounded-2xl border border-slate-200"
                      >
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                          <div>
                            <label className={infoLabelClass}>Nama</label>
                            <div className={infoValueClass}>
                              {keluarga.nama || "-"}
                            </div>
                          </div>
                          <div>
                            <label className={infoLabelClass}>Hubungan</label>
                            <div className={infoValueClass}>
                              {keluarga.hubungan === "suami"
                                ? "Suami"
                                : keluarga.hubungan === "istri"
                                ? "Istri"
                                : keluarga.hubungan === "anak"
                                ? "Anak"
                                : keluarga.hubungan || "-"}
                            </div>
                          </div>
                          <div>
                            <label className={infoLabelClass}>
                              Tanggal Lahir
                            </label>
                            <div className={infoValueClass}>
                              {formatDate(keluarga.tanggal_lahir || "")}
                            </div>
                          </div>
                          <div>
                            <label className={infoLabelClass}>Pekerjaan</label>
                            <div className={infoValueClass}>
                              {keluarga.pekerjaan || "-"}
                            </div>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                </section>
              )}

              {profileData.user_sosial_media &&
                profileData.user_sosial_media.length > 0 && (
                  <section className={cardClass}>
                    <div className={sectionHeaderClass}>
                      <div className="flex items-center gap-3">
                        <div className="p-2 bg-sky-50 rounded-lg text-sky-800">
                          <Share2 className="w-5 h-5" />
                        </div>
                        <div>
                          <h3 className="font-bold text-slate-800 text-lg">
                            Sosial Media
                          </h3>
                          <p className="text-slate-500 text-xs font-medium">
                            Tautan akun sosial media
                          </p>
                        </div>
                      </div>
                    </div>
                    <div className="p-6 space-y-4">
                      {profileData.user_sosial_media.map((sosmed, index) => (
                        <div
                          key={index}
                          className="bg-slate-50 p-5 rounded-2xl border border-slate-200"
                        >
                          <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                              <label className={infoLabelClass}>Platform</label>
                              <div className={infoValueClass}>
                                {sosmed.sosial_media?.nama_platform || "-"}
                              </div>
                            </div>
                            <div>
                              <label className={infoLabelClass}>Username</label>
                              <div className={infoValueClass}>
                                {sosmed.username || "-"}
                              </div>
                            </div>
                            <div>
                              <label className={infoLabelClass}>Link URL</label>
                              <div className={infoValueClass}>
                                {sosmed.link ? (
                                  <a
                                    href={sosmed.link}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-sky-800 hover:text-sky-900 underline"
                                  >
                                    {sosmed.link}
                                  </a>
                                ) : (
                                  "-"
                                )}
                              </div>
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>
                  </section>
                )}
            </div>
          </div>
        </main>
      </div>
    </AccessControl>
  );
}


