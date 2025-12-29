<?php

namespace Database\Seeders;

use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\ProfilePekerjaan;
use App\Models\ProfilePribadi;
use App\Models\TempatKerja;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class StructuredAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan data master sudah ada
        $tempatKerja = TempatKerja::first();
        $departemen = Departemen::first();
        $jabatans = Jabatan::all();

        if (!$tempatKerja || !$departemen || $jabatans->isEmpty()) {
            $this->command->warn('Seeder dibatalkan: Tempat kerja, departemen, atau jabatan kosong.');
            return;
        }

        $this->command->info('Memulai pembuatan 8 akun sesuai roles...');

        $accountCount = 0;

        // 1. Superadmin - gunakan jabatan Kepala Yayasan
        $superadmin = $this->createUser(
            'superadmin', 
            'Superadmin', 
            $tempatKerja->id, 
            $departemen->id, 
            'Kepala Yayasan'
        );
        if ($superadmin) $accountCount++;

        // 2. Kepala Yayasan
        $kepalaYayasan = $this->createUser(
            'kepala yayasan', 
            'Kepala Yayasan', 
            $tempatKerja->id, 
            $departemen->id, 
            'Kepala Yayasan'
        );
        if ($kepalaYayasan) $accountCount++;

        // 3. Direktur Pendidikan
        $dirpen = $this->createUser(
            'direktur pendidikan', 
            'Direktur Pendidikan', 
            $tempatKerja->id, 
            $departemen->id, 
            'Direktur Pendidikan'
        );
        if ($dirpen) $accountCount++;

        // 4. Kepala HRD
        $kepalaHrd = $this->createUser(
            'kepala hrd', 
            'Kepala HRD', 
            $tempatKerja->id, 
            $departemen->id, 
            'Kepala HRD'
        );
        if ($kepalaHrd) $accountCount++;

        // 5. Staff HRD
        $staffHrd = $this->createUser(
            'staff hrd', 
            'Staff HRD', 
            $tempatKerja->id, 
            $departemen->id, 
            'Staff HRD'
        );
        if ($staffHrd) $accountCount++;

        // 6. Kepala Departemen
        $kepalaDept = $this->createUser(
            'kepala departemen', 
            'Kepala Departemen', 
            $tempatKerja->id, 
            $departemen->id, 
            'Kepala Departemen'
        );
        if ($kepalaDept) {
            $accountCount++;
            // Update departemen dengan kepala departemen
            $departemen->update(['id_kepala_departemen' => $kepalaDept->id]);
        }

        // 7. Kepala Sekolah
        $kepalaSekolah = $this->createUser(
            'kepala sekolah', 
            'Kepala Sekolah', 
            $tempatKerja->id, 
            $departemen->id, 
            'Kepala Sekolah'
        );
        if ($kepalaSekolah) $accountCount++;

        // 8. Tenaga Pendidik
        $tenagaPendidik = $this->createUser(
            'tenaga pendidik', 
            'Tenaga Pendidik', 
            $tempatKerja->id, 
            $departemen->id, 
            'Tenaga Pendidik'
        );
        if ($tenagaPendidik) $accountCount++;

        $this->command->info("=== SELESAI ===");
        $this->command->info("Berhasil membuat {$accountCount} akun.");
        $this->command->info("Total user sekarang: " . User::count());
    }

    /**
     * Create user with profile
     */
    private function createUser($roleName, $displayName, $tempatKerjaId, $departemenId, $jabatanName)
    {
        try {
            // Generate unique email
            $email = $this->generateEmail($roleName);
            
            // Check if user already exists
            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                $this->command->warn("User sudah ada: {$email}");
                return $existingUser;
            }

            // Create user
            $user = User::create([
                'email' => $email,
                'password' => bcrypt('password'),
            ]);

            // Assign role
            $role = Role::firstOrCreate(['name' => $roleName]);
            $user->assignRole($role);

            // Create profile pribadi
            ProfilePribadi::create([
                'id_user' => $user->id,
                'nomor_induk_kependudukan' => mt_rand(1000000000000000, 9999999999999999),
                'nama_lengkap' => $displayName,
                'tempat_lahir' => 'Batam',
                'tanggal_lahir' => now()->subYears(rand(25, 55))->subDays(rand(1, 365)),
                'jenis_kelamin' => ['pria', 'wanita'][rand(0, 1)],
                'golongan_darah' => ['a', 'b', 'ab', 'o'][rand(0, 3)],
                'status_pernikahan' => ['belum nikah', 'sudah nikah'][rand(0, 1)],
                'npwp' => mt_rand(100000000000000, 999999999999999),
                'kecamatan' => 'Batu Aji',
                'alamat_lengkap' => 'Jl. Pendidikan No. ' . rand(1, 100),
                'no_hp' => '08' . rand(1111111111, 9999999999),
                'nomor_rekening' => rand(1000000000, 9999999999),
                'foto' => null,
            ]);

            // Get jabatan ID
            $jabatan = Jabatan::where('nama_jabatan', $jabatanName)->first();
            if (!$jabatan) {
                $this->command->error("Jabatan tidak ditemukan: {$jabatanName}");
                return null;
            }

            // Create profile pekerjaan
            ProfilePekerjaan::create([
                'id_user' => $user->id,
                'id_departemen' => $departemenId,
                'id_tempat_kerja' => $tempatKerjaId,
                'id_jabatan' => $jabatan->id,
                'nomor_induk_karyawan' => strtoupper(Str::random(6)),
                'tanggal_masuk' => now()->subYears(rand(1, 10))->subDays(rand(1, 365)),
                'status' => 'aktif',
            ]);

            $this->command->info("✓ Dibuat: {$displayName} ({$email})");
            return $user;

        } catch (\Exception $e) {
            $this->command->error("Error membuat user {$displayName}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate email based on role name
     */
    private function generateEmail($roleName)
    {
        // Clean role name for email
        $roleClean = str_replace(' ', '', strtolower($roleName));
        
        return "{$roleClean}@gmail.com";
    }
}
