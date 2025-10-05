<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kuisioner;
use Illuminate\Support\Facades\DB;

class KuisionerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data using delete instead of truncate
        Kuisioner::query()->delete();
        
        // Reset auto-increment (optional)
        DB::statement('ALTER TABLE kuisioner AUTO_INCREMENT = 1');

        // Define categories and their respective pertanyaan (questions)
        $kuisionerData = [
            // Kategori: Kinerja Kerja
            [
                'kategori' => 'Kinerja Kerja',
                'pertanyaan' => [
                    'Seberapa baik karyawan menyelesaikan tugas tepat waktu?',
                    'Bagaimana kualitas hasil kerja yang dihasilkan?',
                    'Seberapa efisien karyawan dalam menggunakan waktu kerja?',
                    'Bagaimana kemampuan karyawan dalam mencapai target yang ditetapkan?',
                    'Seberapa konsisten karyawan dalam menjaga standar kualitas kerja?',
                    'Bagaimana kemampuan karyawan dalam mengatasi beban kerja yang tinggi?',
                    'Seberapa baik karyawan dalam mengorganisir dan memprioritaskan tugas?',
                    'Bagaimana tingkat akurasi dalam menyelesaikan pekerjaan?'
                ]
            ],
            
            // Kategori: Komunikasi
            [
                'kategori' => 'Komunikasi',
                'pertanyaan' => [
                    'Seberapa efektif karyawan dalam berkomunikasi dengan rekan kerja?',
                    'Bagaimana kemampuan karyawan dalam menyampaikan ide dan pendapat?',
                    'Seberapa baik karyawan dalam mendengarkan dan memahami instruksi?',
                    'Bagaimana kemampuan karyawan dalam presentasi dan public speaking?',
                    'Seberapa jelas karyawan dalam memberikan laporan dan dokumentasi?',
                    'Bagaimana kemampuan karyawan dalam komunikasi tertulis (email, memo)?',
                    'Seberapa responsif karyawan dalam menanggapi komunikasi dari atasan?',
                    'Bagaimana kemampuan karyawan dalam menyelesaikan konflik komunikasi?'
                ]
            ],
            
            // Kategori: Kerja Sama Tim
            [
                'kategori' => 'Kerja Sama Tim',
                'pertanyaan' => [
                    'Seberapa baik karyawan dalam berkolaborasi dengan tim?',
                    'Bagaimana kontribusi karyawan dalam diskusi tim?',
                    'Seberapa suportif karyawan terhadap rekan kerja lainnya?',
                    'Bagaimana kemampuan karyawan dalam berbagi pengetahuan dengan tim?',
                    'Seberapa fleksibel karyawan dalam menyesuaikan diri dengan dinamika tim?',
                    'Bagaimana kemampuan karyawan dalam menyelesaikan tugas kelompok?',
                    'Seberapa aktif karyawan dalam memberikan feedback konstruktif?',
                    'Bagaimana sikap karyawan dalam menerima masukan dari rekan kerja?'
                ]
            ],
            
            // Kategori: Inisiatif dan Kreativitas
            [
                'kategori' => 'Inisiatif dan Kreativitas',
                'pertanyaan' => [
                    'Seberapa sering karyawan mengajukan ide-ide inovatif?',
                    'Bagaimana kemampuan karyawan dalam mencari solusi kreatif?',
                    'Seberapa proaktif karyawan dalam mengidentifikasi masalah?',
                    'Bagaimana kemampuan karyawan dalam mengembangkan metode kerja baru?',
                    'Seberapa berani karyawan dalam mengambil risiko yang terukur?',
                    'Bagaimana kontribusi karyawan dalam proses improvement?',
                    'Seberapa mandiri karyawan dalam mengambil keputusan?',
                    'Bagaimana kemampuan karyawan dalam berpikir out of the box?'
                ]
            ],
            
            // Kategori: Kepemimpinan
            [
                'kategori' => 'Kepemimpinan',
                'pertanyaan' => [
                    'Seberapa baik karyawan dalam memotivasi orang lain?',
                    'Bagaimana kemampuan karyawan dalam mengambil keputusan sulit?',
                    'Seberapa efektif karyawan dalam mendelegasikan tugas?',
                    'Bagaimana kemampuan karyawan dalam mengelola konflik?',
                    'Seberapa baik karyawan dalam memberikan arahan yang jelas?',
                    'Bagaimana kemampuan karyawan dalam mengembangkan bawahan?',
                    'Seberapa konsisten karyawan dalam memberikan feedback?',
                    'Bagaimana kemampuan karyawan dalam membangun visi tim?'
                ]
            ],
            
            // Kategori: Profesionalisme
            [
                'kategori' => 'Profesionalisme',
                'pertanyaan' => [
                    'Seberapa baik karyawan dalam menjaga etika kerja?',
                    'Bagaimana tingkat kedisiplinan karyawan dalam mengikuti aturan?',
                    'Seberapa konsisten karyawan dalam menjaga penampilan profesional?',
                    'Bagaimana kemampuan karyawan dalam menjaga kerahasiaan informasi?',
                    'Seberapa baik karyawan dalam mengelola emosi di tempat kerja?',
                    'Bagaimana sikap karyawan terhadap kritik dan feedback?',
                    'Seberapa bertanggung jawab karyawan terhadap pekerjaannya?',
                    'Bagaimana komitmen karyawan terhadap nilai-nilai perusahaan?'
                ]
            ],
            
            // Kategori: Pengembangan Diri
            [
                'kategori' => 'Pengembangan Diri',
                'pertanyaan' => [
                    'Seberapa aktif karyawan dalam mengikuti pelatihan dan seminar?',
                    'Bagaimana kemampuan karyawan dalam mempelajari hal-hal baru?',
                    'Seberapa terbuka karyawan terhadap perubahan dan inovasi?',
                    'Bagaimana upaya karyawan dalam meningkatkan skill dan kompetensi?',
                    'Seberapa baik karyawan dalam mengidentifikasi area pengembangan diri?',
                    'Bagaimana kemampuan karyawan dalam beradaptasi dengan teknologi baru?',
                    'Seberapa konsisten karyawan dalam melakukan self-evaluation?',
                    'Bagaimana motivasi karyawan untuk berkembang secara profesional?'
                ]
            ],
            
            // Kategori: Pelayanan Pelanggan
            [
                'kategori' => 'Pelayanan Pelanggan',
                'pertanyaan' => [
                    'Seberapa responsif karyawan dalam melayani pelanggan?',
                    'Bagaimana kemampuan karyawan dalam menyelesaikan keluhan pelanggan?',
                    'Seberapa ramah dan sopan karyawan dalam berinteraksi dengan pelanggan?',
                    'Bagaimana kemampuan karyawan dalam memahami kebutuhan pelanggan?',
                    'Seberapa proaktif karyawan dalam memberikan solusi kepada pelanggan?',
                    'Bagaimana tingkat kepuasan pelanggan terhadap layanan karyawan?',
                    'Seberapa baik karyawan dalam menjaga hubungan jangka panjang dengan pelanggan?',
                    'Bagaimana kemampuan karyawan dalam upselling dan cross-selling?'
                ]
            ],
        ];

        // Insert kuisioner data
        foreach ($kuisionerData as $category) {
            foreach ($category['pertanyaan'] as $index => $question) {
                Kuisioner::create([
                    'kategori' => $category['kategori'],
                    'pertanyaan' => $question,
                    'bobot' => $this->getRandomBobot(),
                    'aktif' => $this->getRandomStatus(),
                    'created_at' => now()->subDays(rand(0, 30)),
                    'updated_at' => now()->subDays(rand(0, 15)),
                ]);
            }
        }

        // Add some additional random questions for variety
        $additionalQuestions = [
            [
                'kategori' => 'Manajemen Waktu',
                'pertanyaan' => 'Seberapa efektif karyawan dalam mengelola waktu dan prioritas?',
                'bobot' => 3.2,
                'aktif' => true
            ],
            [
                'kategori' => 'Manajemen Waktu',
                'pertanyaan' => 'Bagaimana kemampuan karyawan dalam menghindari prokrastinasi?',
                'bobot' => 2.8,
                'aktif' => true
            ],
            [
                'kategori' => 'Adaptabilitas',
                'pertanyaan' => 'Seberapa cepat karyawan beradaptasi dengan perubahan kebijakan?',
                'bobot' => 4.1,
                'aktif' => false
            ],
            [
                'kategori' => 'Adaptabilitas',
                'pertanyaan' => 'Bagaimana fleksibilitas karyawan dalam menghadapi situasi darurat?',
                'bobot' => 4.5,
                'aktif' => true
            ],
            [
                'kategori' => 'Teknologi',
                'pertanyaan' => 'Seberapa mahir karyawan dalam menggunakan software dan aplikasi kerja?',
                'bobot' => 3.7,
                'aktif' => true
            ],
            [
                'kategori' => 'Teknologi',
                'pertanyaan' => 'Bagaimana kemampuan karyawan dalam troubleshooting masalah teknis?',
                'bobot' => 3.0,
                'aktif' => false
            ],
        ];

        foreach ($additionalQuestions as $question) {
            Kuisioner::create([
                'kategori' => $question['kategori'],
                'pertanyaan' => $question['pertanyaan'],
                'bobot' => $question['bobot'],
                'aktif' => $question['aktif'],
                'created_at' => now()->subDays(rand(0, 60)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ]);
        }

        $this->command->info('Kuisioner seeder completed successfully!');
        $this->command->info('Total kuisioner created: ' . Kuisioner::count());
        $this->command->info('Active kuisioner: ' . Kuisioner::where('aktif', true)->count());
        $this->command->info('Inactive kuisioner: ' . Kuisioner::where('aktif', false)->count());
        $this->command->info('Total categories: ' . Kuisioner::distinct('kategori')->count());
    }

    /**
     * Generate random weight/bobot for kuisioner
     */
    private function getRandomBobot(): float
    {
        $weights = [1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0, 5.5, 6.0];
        return $weights[array_rand($weights)];
    }

    /**
     * Generate random active status
     */
    private function getRandomStatus(): bool
    {
        // 80% chance to be active, 20% chance to be inactive
        return rand(1, 10) <= 8;
    }
}