<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'store_name', 'value' => 'Angkringan Maknyus'],
            ['key' => 'store_address', 'value' => 'Jl. Kebahagiaan No. 123, Yogyakarta'],
            ['key' => 'store_phone', 'value' => '081234567890'],
            ['key' => 'receipt_footer', 'value' => 'Terima kasih atas kunjungan Anda!\nKritik & Saran: 081234567890'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
