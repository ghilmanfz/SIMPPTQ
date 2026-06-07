<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\Branding;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Branding::defaults() as $key => $value) {
            // firstOrCreate: jangan menimpa nilai yang sudah diubah admin saat re-seed.
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
