<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService
{
    /**
     * Ambil semua pengaturan sebagai key-value array.
     */
    public function getAllSettings(): array
    {
        return Setting::pluck('value', 'key')->toArray();
    }

    /**
     * Update sekelompok pengaturan berdasarkan key-value array.
     */
    public function updateSettings(array $keyValues): void
    {
        foreach ($keyValues as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
