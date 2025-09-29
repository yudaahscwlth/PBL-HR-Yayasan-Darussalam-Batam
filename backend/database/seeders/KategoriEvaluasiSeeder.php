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
            '<p>Administrasi</p>',
            '<p>Hubungan Dengan Atasan</p>',
            '<p>Hubungan Dengan Teman Sejawat</p>',
            '<p>Hubungan Dengan Peserta Didik</p>',
            '<p>Sikap dan Kerja sama</p>',
            '<p>Motivasi dan Inisiatif</p>',
            '<p>Disiplin</p>',
            '<p>Kualitas Kerja dan Prestasi Kerja</p>',
            '<p>Komitmen Terhadap Pekerjaan</p>',
            '<p>Kreativitas dan Inovasi</p>',
            '<p>Pengembangan keahlian, ilmu pengetahuan dan pendidikan</p>',
            '<p>Kegiatan Pengembangan Karakter </p><ul><li>Team Building and Upgrading</li><li>Workshop</li><li>Pengajian bulanan</li><li>Upacara 17 Agustus</li></ul><p><em>(Ketidakhadiran dalam kegiatan tersebut mengurangi nilai)</em></p>',
        ];

        foreach ($kategori as $item) {
            KategoriEvaluasi::create(['nama' => $item]);
        }
    }
}
