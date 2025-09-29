<?php

namespace Tests\Feature;

use App\Models\PengajuanCuti;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class pengajuanCutiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed & login sebagai user yang valid
        $this->seed(DatabaseSeeder::class);
        $this->user = User::where('email', 'tendik@gmail.com')->firstOrFail();
        $this->actingAs($this->user);
    }

    public function test_pengajuan_cuti_berhasil_tanpa_file()
    {
        $response = $this->post(route('pengajuan.cuti.tendik.store'), [
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'tipe_cuti' => 'cuti tahunan',
            'alasan_pendukung' => 'Libur keluarga',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('notifikasi', 'Berhasil mengajukan cuti');

        $this->assertDatabaseHas('pengajuan_cutis', [
            'id_user' => $this->user->id,
            'tipe_cuti' => 'cuti tahunan',
            'status_pengajuan' => 'ditinjau kepala sekolah',
        ]);
    }

    public function test_pengajuan_cuti_gagal_validasi()
    {
        $response = $this->from(route('pengajuan.cuti.tendik.page'))->post(route('pengajuan.cuti.tendik.store'), []);

        $response->assertRedirect(route('pengajuan.cuti.tendik.page'));
        $response->assertSessionHasErrors([
            'tanggal_mulai',
            'tanggal_selesai',
            'tipe_cuti',
        ]);

        $this->assertDatabaseCount('pengajuan_cutis', 0);
    }
}
