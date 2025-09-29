<?php

namespace Database\Seeders;

use App\Models\SosialMedia;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SosialMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            'WhatsApp',
            'Instagram',
            'Facebook',
            'Twitter',
            'LinkedIn',
            'Telegram',
            'YouTube',
            'TikTok',
        ];

        foreach ($platforms as $platform) {
            SosialMedia::create([
                'nama_platform' => $platform,
            ]);
        }
    }
}
