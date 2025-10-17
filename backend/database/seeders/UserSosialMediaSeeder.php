<?php

namespace Database\Seeders;

use App\Models\SosialMedia;
use App\Models\User;
use App\Models\UserSosialMedia;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSosialMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil beberapa user dan platform sosial media dari database
        $users = User::all();
        $platforms = SosialMedia::all();

        // Pastikan ada data user dan platform sosial media
        if ($users->isEmpty() || $platforms->isEmpty()) {
            echo "Data user atau platform sosial media kosong.\n";
            return;
        }

        // Tambahkan data sosial media untuk setiap user
        foreach ($users as $user) {
            foreach ($platforms as $platform) {
                UserSosialMedia::create([
                    'id_user' => $user->id,
                    'id_platform' => $platform->id,
                    'username' => 'user' . $user->id . '_on_' . $platform->nama_platform,
                    'link' => 'https://' . strtolower($platform->nama_platform) . '.com/' . 'user' . $user->id,
                ]);
            }
        }
    }
}
