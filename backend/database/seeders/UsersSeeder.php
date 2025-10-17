<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data user dan role yang ingin di-assign
        $users = [
            [
                'email' => 'superadmin@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'superadmin',
            ],
            [
                'email' => 'kepalayayasan@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'kepala yayasan',
            ],
            [
                'email' => 'dirpen@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'direktur pendidikan',
            ],
            [
                'email' => 'kepalahrd@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'kepala hrd',
            ],
            [
                'email' => 'hrd@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'staff hrd',
            ],
            [
                'email' => 'kadep@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'kepala departemen',
            ],
            [
                'email' => 'kepsek@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'kepala sekolah',
            ],
            [
                'email' => 'tendik@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'tenaga pendidik',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate([
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            // Assign role
            $role = Role::firstOrCreate(['name' => $data['role']]);
            $user->assignRole($role);
        }
    }
}
