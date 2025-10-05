<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class PegawaiSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');
        
        // Daftar kota-kota di Indonesia
        $kota = [
            'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Makassar', 'Palembang', 
            'Yogyakarta', 'Malang', 'Denpasar', 'Balikpapan', 'Batam', 'Banjarmasin', 
            'Pontianak', 'Manado', 'Padang', 'Pekanbaru', 'Jambi', 'Bengkulu', 'Lampung'
        ];

        // Foto default untuk semua pegawai
        $fotoDefault = 'avatar-1.jpg';

        $usedEmails = []; // Array untuk menyimpan email yang sudah digunakan
        
        for ($i = 1; $i <= 50; $i++) {
            $nama = $faker->name;
            $jenisKelamin = $faker->randomElement(['L', 'P']);
            $tempatLahir = $faker->randomElement($kota);
            $tanggalLahir = $faker->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d');
            $tanggalMasuk = $faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d');
            
            // Generate email unik berdasarkan nama
            $emailName = strtolower(str_replace(' ', '.', $nama));
            $email = $emailName . '@company.com';
            
            // Pastikan email unik
            $counter = 1;
            while (in_array($email, $usedEmails)) {
                $email = $emailName . $counter . '@company.com';
                $counter++;
            }
            $usedEmails[] = $email;
            
            DB::table('pegawai')->insert([
                'nama' => $nama,
                'tempat_lahir' => $tempatLahir,
                'tanggal_lahir' => $tanggalLahir,
                'jenis_kelamin' => $jenisKelamin,
                'alamat' => $faker->address,
                'no_hp' => $faker->phoneNumber,
                'email' => $email,
                'id_jabatan' => 1, // Semua sebagai staff
                'id_departemen' => $faker->randomElement([1, 2]), // Hanya departemen 1 dan 2
                'tanggal_masuk' => $tanggalMasuk,
                'foto' => $fotoDefault,
                'jatahtahunan' => $faker->numberBetween(12, 24), // Jatah cuti tahunan 12-24 hari
            ]);
        }
    }
}

?>