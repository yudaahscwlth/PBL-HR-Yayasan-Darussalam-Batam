<?php

namespace Tests\Feature;

use App\Models\KategoriEvaluasi;
use App\Models\TahunAjaran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class evaluationPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class); // Pastikan seeder memuat role & data dasar jika perlu
    }

    public function test_store_evaluasi_successfully()
    {
        $penilai = User::where('email','hrd@gmail.com')->firstOrFail();
        $pendidik = User::where('email','kadep@gmail.com')->firstOrFail();
        $tahunAjaran = TahunAjaran::where('nama','2025/2026')->firstOrFail();

        $kategori1 = KategoriEvaluasi::where('id','1')->firstOrFail();
        $kategori2 = KategoriEvaluasi::where('id','2')->firstOrFail();

        $this->actingAs($penilai);

        $response = $this->post(route('evaluasi.store'), [
            'id_user' => $pendidik->id,
            'id_tahun_ajaran' => $tahunAjaran->id,
            'nilai' => [
                $kategori1->id => 4,
                $kategori2->id => 5,
            ],
            'catatan' => 'Perlu ditingkatkan pada bagian komunikasi.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('notifikasi', 'Evaluasi berhasil disimpan.');

        $this->assertDatabaseHas('evaluasis', [
            'id_user' => $pendidik->id,
            'id_penilai' => $penilai->id,
            'id_kategori' => $kategori1->id,
            'nilai' => 4,
        ]);

        $this->assertDatabaseHas('evaluasis', [
            'id_user' => $pendidik->id,
            'id_kategori' => $kategori2->id,
            'nilai' => 5,
        ]);
    }

    public function test_store_evaluasi_validation_fails()
    {
        $penilai = User::where('email','hrd@gmail.com')->firstOrFail();
        $this->actingAs($penilai);

        $response = $this->from(route('evaluasi.pegawai.page'))
            ->post(route('evaluasi.store'), []);

        $response->assertRedirect(route('evaluasi.pegawai.page'));
        $response->assertSessionHasErrors(['id_user', 'id_tahun_ajaran', 'nilai']);
        $this->assertDatabaseCount('evaluasis', 0);
    }
}
