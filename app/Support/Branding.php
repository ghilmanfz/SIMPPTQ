<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

/**
 * Sumber tunggal untuk konfigurasi branding, landing page, dan WhatsApp.
 * Nilai default di sini juga dipakai oleh SettingSeeder agar konsisten.
 */
class Branding
{
    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            // Branding / logo
            'logo_type' => 'text',
            'logo_text' => 'NI',
            'logo_image' => '',
            'pondok_name' => 'PPTQ Nurul Iman',
            'pondok_tagline' => 'Sistem Manajemen Terpadu',

            // Landing page / hero
            'landing_hero_title' => 'Membangun Generasi',
            'landing_hero_title_highlight' => "Qur'ani & Unggul",
            'landing_hero_desc' => 'Selamat datang di Sistem Informasi Manajemen Terpusat PPTQ Nurul Iman. Solusi digital modern untuk mengelola data personil, kehadiran GPS, perkembangan santri, penggajian, dan operasional kepondokan secara real-time.',
            'landing_hero_image' => 'default',
            'landing_hero_image_custom' => '',
            'landing_stats_personnel' => '40+',
            'landing_stats_santri' => '350+',
            'landing_stats_halaqah' => '15+',
            'landing_stats_accuracy' => '100%',

            // WhatsApp Fonnte
            'whatsapp_token' => '',
            'whatsapp_sender' => '',
            'whatsapp_connected' => '0',
        ];
    }

    /**
     * Pengaturan lengkap (default ditimpa nilai dari database).
     *
     * @return array<string, string>
     */
    public static function data(): array
    {
        $defaults = self::defaults();

        if (! Schema::hasTable('settings')) {
            return $defaults;
        }

        return array_merge($defaults, Setting::allAsArray());
    }

    /**
     * URL gambar logo (mengembalikan null bila memakai logo teks / belum diunggah).
     */
    public static function logoImageUrl(): ?string
    {
        $path = self::data()['logo_image'] ?? '';

        return $path ? asset('storage/' . $path) : null;
    }

    /**
     * URL gambar hero kustom (null bila memakai mode default).
     */
    public static function heroImageUrl(): ?string
    {
        $data = self::data();
        if (($data['landing_hero_image'] ?? 'default') !== 'custom') {
            return null;
        }
        $path = $data['landing_hero_image_custom'] ?? '';

        return $path ? asset('storage/' . $path) : asset('pondok_hero_banner.png');
    }
}
