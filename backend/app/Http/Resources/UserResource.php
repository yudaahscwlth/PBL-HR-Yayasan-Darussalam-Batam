<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'roles' => $this->roles->map(function ($role) {
                return ['name' => $role->name];
            }),
            'profile_pribadi' => $this->profilePribadi ? [
                'id' => $this->profilePribadi->id,
                'id_user' => $this->profilePribadi->id_user,
                'nama_lengkap' => $this->profilePribadi->nama_lengkap,
                'nomor_induk_kependudukan' => $this->profilePribadi->nomor_induk_kependudukan,
                'tempat_lahir' => $this->profilePribadi->tempat_lahir,
                'tanggal_lahir' => $this->profilePribadi->tanggal_lahir,
                'jenis_kelamin' => $this->profilePribadi->jenis_kelamin,
                'golongan_darah' => $this->profilePribadi->golongan_darah,
                'status_pernikahan' => $this->profilePribadi->status_pernikahan,
                'npwp' => $this->profilePribadi->npwp,
                'kecamatan' => $this->profilePribadi->kecamatan,
                'alamat_lengkap' => $this->profilePribadi->alamat_lengkap,
                'no_hp' => $this->profilePribadi->no_hp,
                'nomor_rekening' => $this->profilePribadi->nomor_rekening,
                'foto' => $this->profilePribadi->foto,
                'created_at' => $this->profilePribadi->created_at,
                'updated_at' => $this->profilePribadi->updated_at,
            ] : null,
            'profile_pekerjaan' => $this->profilePekerjaan ? [
                'id' => $this->profilePekerjaan->id,
                'id_user' => $this->profilePekerjaan->id_user,
                'nomor_induk_karyawan' => $this->profilePekerjaan->nomor_induk_karyawan,
                'id_jabatan' => $this->profilePekerjaan->id_jabatan,
                'id_departemen' => $this->profilePekerjaan->id_departemen,
                'id_tempat_kerja' => $this->profilePekerjaan->id_tempat_kerja,
                'tanggal_masuk' => $this->profilePekerjaan->tanggal_masuk,
                'status' => $this->profilePekerjaan->status,
                'status_pegawai' => $this->profilePekerjaan->status, // Alias for backward compatibility
                'created_at' => $this->profilePekerjaan->created_at,
                'updated_at' => $this->profilePekerjaan->updated_at,
                'jabatan' => $this->profilePekerjaan->jabatan ? [
                    'id' => $this->profilePekerjaan->jabatan->id,
                    'nama_jabatan' => $this->profilePekerjaan->jabatan->nama_jabatan,
                ] : null,
                'departemen' => $this->profilePekerjaan->departemen ? [
                    'id' => $this->profilePekerjaan->departemen->id,
                    'nama_departemen' => $this->profilePekerjaan->departemen->nama_departemen,
                ] : null,
                'tempat_kerja' => $this->profilePekerjaan->tempatKerja ? [
                    'id' => $this->profilePekerjaan->tempatKerja->id,
                    'nama_tempat' => $this->profilePekerjaan->tempatKerja->nama_tempat, // Assuming nama_tempat based on previous context
                    'nama_tempat_kerja' => $this->profilePekerjaan->tempatKerja->nama_tempat, // Alias for frontend compatibility
                ] : null,
            ] : null,
            'orang_tua' => $this->orangTua ? [
                'nama_ayah' => $this->orangTua->nama_ayah,
                'pekerjaan_ayah' => $this->orangTua->pekerjaan_ayah,
                'nama_ibu' => $this->orangTua->nama_ibu,
                'pekerjaan_ibu' => $this->orangTua->pekerjaan_ibu,
                'alamat_orang_tua' => $this->orangTua->alamat_orang_tua,
            ] : null,
            'keluarga' => $this->keluarga->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'hubungan' => $item->hubungan,
                    'tanggal_lahir' => $item->tanggal_lahir,
                    'pekerjaan' => $item->pekerjaan,
                ];
            }),
            'user_sosial_media' => $this->userSosialMedia->map(function ($item) {
                return [
                    'id' => $item->id,
                    'id_platform' => $item->id_platform,
                    'username' => $item->username,
                    'link' => $item->link,
                    'sosial_media' => $item->sosialMedia ? [
                        'nama_platform' => $item->sosialMedia->nama_platform
                    ] : null,
                ];
            }),
        ];
    }
}
