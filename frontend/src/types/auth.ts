// User roles based on backend roles
export type UserRole = "superadmin" | "kepala yayasan" | "direktur pendidikan" | "kepala hrd" | "staff hrd" | "kepala departemen" | "kepala sekolah" | "tenaga pendidik";

// User interface based on Laravel User model
export interface User {
  id: number;
  email: string;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
  roles?: UserRole[];
  permissions?: string[];
  profile_pribadi?: ProfilePribadiExtended;
  profile_pekerjaan?: ProfilePekerjaan;
  orang_tua?: OrangTua;
  keluarga?: Keluarga[];
  user_sosial_media?: UserSosialMedia[];
}

// Profile interfaces based on Laravel models
export interface ProfilePribadi {
  id: number;
  id_user: number;
  nama_lengkap: string;
  nama_panggilan?: string;
  tempat_lahir?: string;
  tanggal_lahir?: string;
  jenis_kelamin?: "L" | "P";
  agama?: string;
  status_perkawinan?: string;
  alamat?: string;
  no_telepon?: string;
  no_handphone?: string;
  created_at: string;
  updated_at: string;
}

export interface ProfilePekerjaan {
  id: number;
  id_user: number;
  nip?: string;
  id_jabatan?: number;
  id_departemen?: number;
  id_tempat_kerja?: number;
  tanggal_masuk?: string;
  status_pegawai?: string;
  created_at: string;
  updated_at: string;
  jabatan?: Jabatan;
  departemen?: Departemen;
  tempat_kerja?: TempatKerja;
}

export interface Jabatan {
  id: number;
  nama_jabatan: string;
  created_at: string;
  updated_at: string;
}

export interface Departemen {
  id: number;
  nama_departemen: string;
  id_kepala_departemen?: number;
  created_at: string;
  updated_at: string;
}

export interface TempatKerja {
  id: number;
  nama_tempat_kerja: string;
  alamat?: string;
  created_at: string;
  updated_at: string;
}

// Extended profile interfaces for profile page
export interface ProfilePribadiExtended extends ProfilePribadi {
  nomor_induk_kependudukan?: string;
  npwp?: string;
  golongan_darah?: string;
  status_pernikahan?: string;
  kecamatan?: string;
  alamat_lengkap?: string;
  no_hp?: string;
  foto?: string;
}

export interface OrangTua {
  id?: number;
  id_user?: number;
  nama_ayah?: string;
  pekerjaan_ayah?: string;
  nama_ibu?: string;
  pekerjaan_ibu?: string;
  alamat_orang_tua?: string;
  created_at?: string;
  updated_at?: string;
}

export interface Keluarga {
  id?: number;
  id_user?: number;
  nama: string;
  hubungan: string;
  tanggal_lahir: string;
  pekerjaan: string;
  created_at?: string;
  updated_at?: string;
}

export interface UserSosialMedia {
  id?: number;
  id_user?: number;
  id_platform: number;
  username: string;
  link: string;
  created_at?: string;
  updated_at?: string;
  sosial_media?: SosialMedia;
}

export interface SosialMedia {
  id: number;
  nama_platform: string;
  created_at: string;
  updated_at: string;
}

// Login request/response interfaces
export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  success: boolean;
  message: string;
  user?: User;
  token?: string;
  redirect_to?: string;
}

// API response wrapper
export interface ApiResponse<T = unknown> {
  success: boolean;
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
  // For auth responses
  user?: User;
  token?: string;
  redirect_to?: string;
}

// Auth state interface
export interface AuthState {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
  login: (credentials: LoginRequest) => Promise<void>;
  logout: () => void;
  clearError: () => void;
  checkAuth: () => Promise<void>;
}

// Role-based access control
export interface RolePermissions {
  [key: string]: string[];
}

// Permission check helper type
export type Permission = string;
