<?php

namespace Tests\Feature;

use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\TempatKerja;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class employeeManagementTest extends TestCase
{
    use RefreshDatabase;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        // Login sebagai admin
        $this->admin = User::where('email','hrd@gmail.com')->firstOrFail();
        $this->actingAs($this->admin);
    }

    public function test_tambah_pegawai_successfully()
    {
        $jabatan = Jabatan::where('nama_jabatan','tenaga pendidik')->first();
        $departemen = Departemen::where('nama_departemen','Keuangan')->first();
        $tempatKerja = TempatKerja::where('nama_tempat','Kantor Yayasan')->first();

        $response = $this->post(route('kelola.pegawai.store'), [
            'email' => 'pegawai@example.com',
            'password' => 'password123',
            'nama_lengkap' => 'John Doe',
            'nomor_induk_kependudukan' => '1234567890123456',
            'nomor_induk_karyawan' => 'KRY001',
            'tanggal_masuk' => '2023-01-01',
            'jabatan' => $jabatan->id,
            'departemen' => $departemen->id,
            'tempat_kerja' => $tempatKerja->id,
            'status_karyawan' => 'tetap',
            'roles' => 'tenaga pendidik',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'pegawai@example.com',
        ]);

        $this->assertDatabaseHas('profile_pribadi', [
            'nama_lengkap' => 'John Doe',
        ]);

        $this->assertDatabaseHas('profile_pekerjaans', [
            'nomor_induk_karyawan' => 'KRY001',
        ]);
    }

    public function test_hapus_massal_pegawai_successfully()
    {
        $pegawai1 = User::where('email','kepsek@gmail.com')->firstOrFail();
        $pegawai2 = User::where('email','kadep@gmail.com')->firstOrFail();

        $response = $this->delete(route('kelola.pegawai.mass.delete'), [
            'id' => $pegawai1->id . ',' . $pegawai2->id
        ]);

        $response->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $pegawai1->id]);
        $this->assertDatabaseMissing('users', ['id' => $pegawai2->id]);
    }

    public function test_hapus_massal_pegawai_dengan_id_tidak_valid()
    {
        $response = $this->delete(route(name: 'kelola.pegawai.mass.delete'), [
            'id' => '999999,888888'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('notifikasi', 'Tidak ada data yang dihapus!');
    }

}
