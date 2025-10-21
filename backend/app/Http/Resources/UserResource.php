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
            'profile_pribadi' => $this->whenLoaded('profilePribadi', function () {
                return [
                    'id' => $this->profilePribadi->id,
                    'id_user' => $this->profilePribadi->id_user,
                    'nama_lengkap' => $this->profilePribadi->nama_lengkap,
                    'nama_panggilan' => $this->profilePribadi->nama_panggilan,
                    'tempat_lahir' => $this->profilePribadi->tempat_lahir,
                    'tanggal_lahir' => $this->profilePribadi->tanggal_lahir,
                    'jenis_kelamin' => $this->profilePribadi->jenis_kelamin,
                    'agama' => $this->profilePribadi->agama,
                    'status_perkawinan' => $this->profilePribadi->status_perkawinan,
                    'alamat' => $this->profilePribadi->alamat,
                    'no_telepon' => $this->profilePribadi->no_telepon,
                    'no_handphone' => $this->profilePribadi->no_handphone,
                    'created_at' => $this->profilePribadi->created_at,
                    'updated_at' => $this->profilePribadi->updated_at,
                ];
            }),
            'profile_pekerjaan' => $this->whenLoaded('profilePekerjaan', function () {
                return [
                    'id' => $this->profilePekerjaan->id,
                    'id_user' => $this->profilePekerjaan->id_user,
                    'nip' => $this->profilePekerjaan->nip,
                    'id_jabatan' => $this->profilePekerjaan->id_jabatan,
                    'id_departemen' => $this->profilePekerjaan->id_departemen,
                    'id_tempat_kerja' => $this->profilePekerjaan->id_tempat_kerja,
                    'tanggal_masuk' => $this->profilePekerjaan->tanggal_masuk,
                    'status_pegawai' => $this->profilePekerjaan->status_pegawai,
                    'created_at' => $this->profilePekerjaan->created_at,
                    'updated_at' => $this->profilePekerjaan->updated_at,
                    'jabatan' => $this->when($this->profilePekerjaan->relationLoaded('jabatan') && $this->profilePekerjaan->jabatan, [
                        'id' => $this->profilePekerjaan->jabatan->id,
                        'nama_jabatan' => $this->profilePekerjaan->jabatan->nama_jabatan,
                        'created_at' => $this->profilePekerjaan->jabatan->created_at,
                        'updated_at' => $this->profilePekerjaan->jabatan->updated_at,
                    ]),
                    'departemen' => $this->when($this->profilePekerjaan->relationLoaded('departemen') && $this->profilePekerjaan->departemen, [
                        'id' => $this->profilePekerjaan->departemen->id,
                        'nama_departemen' => $this->profilePekerjaan->departemen->nama_departemen,
                        'id_kepala_departemen' => $this->profilePekerjaan->departemen->id_kepala_departemen,
                        'created_at' => $this->profilePekerjaan->departemen->created_at,
                        'updated_at' => $this->profilePekerjaan->departemen->updated_at,
                    ]),
                    'tempat_kerja' => $this->when($this->profilePekerjaan->relationLoaded('tempatKerja') && $this->profilePekerjaan->tempatKerja, [
                        'id' => $this->profilePekerjaan->tempatKerja->id,
                        'nama_tempat_kerja' => $this->profilePekerjaan->tempatKerja->nama_tempat_kerja,
                        'alamat' => $this->profilePekerjaan->tempatKerja->alamat,
                        'created_at' => $this->profilePekerjaan->tempatKerja->created_at,
                        'updated_at' => $this->profilePekerjaan->tempatKerja->updated_at,
                    ]),
                ];
            }),
        ];
    }
}
