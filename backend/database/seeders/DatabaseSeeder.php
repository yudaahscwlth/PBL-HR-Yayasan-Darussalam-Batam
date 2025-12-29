<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            PermissionSeeder::class,
            JabatanSeeder::class,
            DepartementSeeder::class,
            TempatKerjaSeeder::class,
            StructuredAccountSeeder::class,
            KeluargaSeeder::class,
            OrangTuaSeeder::class,
            SosialMediaSeeder::class,
            UserSosialMediaSeeder::class,
            TahunAjaranSeeder::class,
            KategoriEvaluasiSeeder::class,
            JamKerjaSeeder::class,
        ]);
    }
}
