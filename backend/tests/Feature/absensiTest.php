<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\JamKerja;
use App\Models\TempatKerja;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class absensiTest extends TestCase
{
    use RefreshDatabase;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->user = User::where('email','superadmin@gmail.com')->firstOrFail();

        // Pastikan juga user punya tempat kerja & jam kerja jika diperlukan testnya
        $tempatKerja = $this->user->profilePekerjaan->tempatKerja ?? null;

        // dd($tempatKerja);

        if (!$tempatKerja) {
            $tempatKerja = TempatKerja::factory()->create([
                'latitude' => -6.2000,
                'longitude' => 106.8167,
            ]);

            $this->user->profilePekerjaan()->update([
                'id_tempat_kerja' => $tempatKerja->id,
            ]);
        }
    }

    public function test_user_can_check_in_successfully()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('absensi.check.in'), [
            'latitude' => -6.2001, // Dalam radius
            'longitude' => 106.8168,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('notifikasi', 'Check-in berhasil!');
        $this->assertDatabaseHas('absensis', [
            'id_user' => $this->user->id,
            'status' => 'hadir',
        ]);
    }

    public function test_user_cannot_check_in_twice()
    {
        Absensi::factory()->create([
            'id_user' => $this->user->id,
            'tanggal' => Carbon::now()->toDateString(),
            'check_in' => now(),
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('absensi.check.in'), [
            'latitude' => -6.2001,
            'longitude' => 106.8168,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('notifikasi', 'Anda sudah melakukan check-in hari ini.');
    }

    public function test_user_cannot_check_in_outside_radius()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('absensi.check.in'), [
            'latitude' => -6.2500, // jauh dari kantor
            'longitude' => 106.9000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('notifikasi');
        // $this->assertStringContainsString('di luar radius', session('notifikasi'));
    }

    public function test_user_cannot_check_in_when_office_location_missing()
    {
        // Hapus tempat kerja user
        $this->user->profilePekerjaan->update(['id_tempat_kerja' => null]);

        $this->actingAs($this->user);

        $response = $this->post(route('absensi.check.in'), [
            'latitude' => -6.2000,
            'longitude' => 106.8167,
        ]);

        $response->assertRedirect();
        // $response->assertSessionHas('notifikasi', 'Lokasi kantor tidak tersedia. Silakan hubungi HR.');
    }

    public function test_user_cannot_check_in_on_day_off()
    {
        // Tandai hari libur
        JamKerja::where('id_jabatan', $this->user->profilePekerjaan->id_jabatan)
            ->update(['is_libur' => true]);

        $this->actingAs($this->user);

        $response = $this->post(route('absensi.check.in'), [
            'latitude' => -6.2000,
            'longitude' => 106.8167,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('notifikasi', 'Hari ini adalah hari libur atau jam kerja belum diatur.');
    }
}
