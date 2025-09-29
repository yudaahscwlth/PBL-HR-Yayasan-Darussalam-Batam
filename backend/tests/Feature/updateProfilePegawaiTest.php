<?php

namespace Tests\Feature;

use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\ProfilePekerjaan;
use App\Models\ProfilePribadi;
use App\Models\TempatKerja;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class updateProfilePegawaiTest extends TestCase
{
    use RefreshDatabase;


    /**
     * Test ini memastikan bahwa pegawai dapat mengupdate profil mereka
     * dengan data yang valid termasuk penggantian email, informasi personal,
     * dan foto profil.
     *
     * Langkah pengujian:
     * - Setup user dan pegawai
     * - Simulasi login
     * - Kirim request PUT ke route update profil
     * - Verifikasi redirect dan notifikasi
     * - Periksa apakah data di database berubah
     * - Cek apakah file foto berhasil disimpan
     *
     *
     */

    public function test_user_can_update_profile_with_valid_data()
    {
        $this->seed(DatabaseSeeder::class);

        // Cari user dengan role tertentu, contoh 'tenaga pendidik'
        $user = User::whereHas('roles', function ($q) {
            $q->where('name', 'tenaga pendidik');
        })->first();


        $this->assertNotNull($user, "User dengan role 'tenaga pendidik' tidak ditemukan!");

        $this->actingAs($user);

        // Fake storage for file uploads
        Storage::fake('public');
        $file = UploadedFile::fake()->image('photo.jpg');

        // Kirim request update
        $response = $this->put(route('profile.update'), [
            'email' => 'new@gmail.com',
            'nomor_induk_kependudukan' => '12345678901',
            'nama_lengkap' => 'Nama Baru',
            'tempat_lahir' => 'Batam',
            // 'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'pria',
            'golongan_darah' => 'A',
            // 'status_pernikahan' => 'belum menikah',
            'npwp' => '1234567890',
            'kecamatan' => 'Batam Kota',
            'alamat_lengkap' => 'Jl. Contoh No.1',
            'no_hp' => '081234567890',
            'foto' => null,

            // Orang tua
            'nama_ayah' => 'Bapak A',
            'pekerjaan_ayah' => 'PNS',
            'nama_ibu' => 'Ibu B',
            'pekerjaan_ibu' => 'Guru',
            'alamat_orang_tua' => 'Jl. Ayah Ibu',

            // Keluarga
            'nama' => ['Anak 1'],
            'hubungan' => ['anak'],
            'tanggal_lahir_keluarga' => ['2010-01-01'],
            'pekerjaan' => ['Pelajar'],

            // Sosial Media
            'id_platform' => [1], // pastikan ada id platform 1 di database
            'username' => ['anakuser'],
            'link' => ['https://instagram.com/anakuser'],
        ]);
        // dd($response);

        $response->assertRedirect();
        $response->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@gmail.com',
        ]);

        $this->assertDatabaseHas('profile_pribadi', [
            'id_user' => $user->id,
            'nama_lengkap' => 'Nama Baru',
        ]);

        $this->assertDatabaseHas('orang_tuas', [
            'id_user' => $user->id,
            'nama_ayah' => 'Bapak A',
        ]);

        $this->assertDatabaseHas('keluargas', [
            'id_user' => $user->id,
            'nama' => 'Anak 1',
        ]);

        $this->assertDatabaseHas('user_sosial_media', [
            'id_user' => $user->id,
            'username' => 'anakuser',
        ]);
    }


}
