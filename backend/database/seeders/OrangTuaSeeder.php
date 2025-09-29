<?php

namespace Database\Seeders;

use App\Models\OrangTua;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrangTuaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        foreach (User::all() as $user) {
            OrangTua::create([
                'id_user' => $user->id,
                'nama_ayah' => 'Bapak ',
                'pekerjaan_ayah' => 'Pegawai Swasta',
                'nama_ibu' => 'Ibu ',
                'pekerjaan_ibu' => 'Ibu Rumah Tangga',
                'alamat_orang_tua' => 'Jl. Keluarga No. ' . rand(1, 100),
            ]);
        }
    }
}
