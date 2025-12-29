<?php

namespace Database\Seeders;

use App\Models\KategoriEvaluasi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriEvaluasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = [
            'Administrasi',
            'Hubungan Dengan Atasan',
            'Hubungan Dengan Teman Sejawat',
            'Hubungan Dengan Peserta Didik',
            'Sikap dan Kerja sama',
            'Motivasi dan Inisiatif',
            'Disiplin',
            'Kualitas Kerja dan Prestasi Kerja',
            'Komitmen Terhadap Pekerjaan',
            'Kreativitas dan Inovasi',
            'Pengembangan keahlian, ilmu pengetahuan dan pendidikan',
            'Kegiatan Pengembangan: Team Building and Upgrading, Workshop, Pengajian bulanan, Upacara 17 Agustus (Ketidakhadiran dalam kegiatan tersebut mengurangi nilai)',
        ];

        foreach ($kategori as $item) {
            KategoriEvaluasi::create(['nama' => $item]);
        }
    }
}
