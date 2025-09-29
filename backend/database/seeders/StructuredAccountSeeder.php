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
        $tempatKerjas = TempatKerja::all();
        $departemens = Departemen::all();
        $jabatans = Jabatan::all();

        if ($tempatKerjas->isEmpty() || $departemens->isEmpty() || $jabatans->isEmpty()) {
            $this->command->warn('Seeder dibatalkan: Tempat kerja, departemen, atau jabatan kosong.');
            return;
        }

        $this->command->info('Memulai pembuatan 31 akun terstruktur...');

        $accountCount = 0;

        // 1. Superadmin (Kantor Yayasan)
        $superadmin = $this->createUser('superadmin', 'Superadmin', $tempatKerjas->first()->id, $departemens->first()->id, 'kepala yayasan');
        if ($superadmin) $accountCount++;

        // 2. Kepala Yayasan (Kantor Yayasan)
        $kepalaYayasan = $this->createUser('kepala yayasan', 'Kepala Yayasan', $tempatKerjas->first()->id, $departemens->first()->id, 'kepala yayasan');
        if ($kepalaYayasan) $accountCount++;

        // 3. Direktur Pendidikan (Kantor Yayasan)
        $dirpen = $this->createUser('direktur pendidikan', 'Direktur Pendidikan', $tempatKerjas->first()->id, $departemens->first()->id, 'direktur pendidikan');
        if ($dirpen) $accountCount++;

        // 4. Kepala HRD (Kantor Yayasan)
        $kepalaHrd = $this->createUser('kepala hrd', 'Kepala HRD', $tempatKerjas->first()->id, $departemens->first()->id, 'kepala hrd');
        if ($kepalaHrd) $accountCount++;

        // 5. 8 Staff HRD (Semua Tempat Kerja)
        foreach ($tempatKerjas as $tempatKerja) {
            $staffHrd = $this->createUser('staff hrd', 'Staff HRD ' . $tempatKerja->nama_tempat, $tempatKerja->id, $departemens->first()->id, 'staff hrd');
            if ($staffHrd) $accountCount++;
        }

        // 6. 3 Kepala Departemen (Kantor Yayasan)
        $kepalaDepartemenUsers = [];
        foreach ($departemens as $departemen) {
            $kepalaDept = $this->createUser('kepala departemen', 'Kepala Departemen ' . $departemen->nama_departemen, $tempatKerjas->first()->id, $departemen->id, 'kepala departemen');
            if ($kepalaDept) {
                $accountCount++;
                $kepalaDepartemenUsers[] = $kepalaDept;
            }
        }

        // Update departemen dengan kepala departemen yang baru dibuat
        foreach ($departemens as $index => $departemen) {
            if (isset($kepalaDepartemenUsers[$index])) {
                $departemen->update([
                    'id_kepala_departemen' => $kepalaDepartemenUsers[$index]->id
                ]);
            }
        }

        // 7. 8 Kepala Sekolah (Semua Tempat Kerja)
        foreach ($tempatKerjas as $tempatKerja) {
            $kepalaSekolah = $this->createUser('kepala sekolah', 'Kepala Sekolah ' . $tempatKerja->nama_tempat, $tempatKerja->id, $departemens->first()->id, 'kepala sekolah');
            if ($kepalaSekolah) $accountCount++;
        }

        // 8. 8 Tenaga Pendidik (Semua Tempat Kerja)
        foreach ($tempatKerjas as $tempatKerja) {
            $tenagaPendidik = $this->createUser('tenaga pendidik', 'Tenaga Pendidik ' . $tempatKerja->nama_tempat, $tempatKerja->id, $departemens->first()->id, 'tenaga pendidik');
            if ($tenagaPendidik) $accountCount++;
        }

        $this->command->info("=== SELESAI ===");
        $this->command->info("Berhasil membuat {$accountCount} akun terstruktur.");
        $this->command->info("Total user sekarang: " . User::count());
    }

    /**
     * Create user with profile
     */
    private function createUser($roleName, $displayName, $tempatKerjaId, $departemenId, $jabatanName)
    {
        try {
            // Generate unique email
            $email = $this->generateEmail($roleName, $tempatKerjaId, $departemenId);
            
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
     * Generate email based on role, tempat kerja, and departemen
     */
    private function generateEmail($roleName, $tempatKerjaId, $departemenId)
    {
        // Clean role name for email
        $roleClean = str_replace(' ', '', strtolower($roleName));
        
        // Get tempat kerja name (simplified)
        $tempatKerjaNames = [
            1 => 'kantor',
            2 => 'tk',
            3 => 'sd', 
            4 => 'smp',
            5 => 'sma',
            6 => 'smk',
            7 => 'poltek',
            8 => 'xplay'
        ];
        
        $tempatKerjaName = $tempatKerjaNames[$tempatKerjaId] ?? 'kantor';
        
        return "{$roleClean}_{$tempatKerjaName}_{$departemenId}@gmail.com";
    }
}
