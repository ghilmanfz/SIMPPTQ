<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function edit(): View
    {
        return view('branding.edit');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'logo_type' => ['required', 'in:text,image'],
            'logo_text' => ['nullable', 'string', 'max:5'],
            'pondok_name' => ['required', 'string', 'max:100'],
            'pondok_tagline' => ['nullable', 'string', 'max:150'],
            'landing_hero_title' => ['required', 'string', 'max:150'],
            'landing_hero_title_highlight' => ['nullable', 'string', 'max:150'],
            'landing_hero_desc' => ['nullable', 'string', 'max:1000'],
            'landing_hero_image' => ['required', 'in:default,custom'],
            'landing_stats_personnel' => ['nullable', 'string', 'max:20'],
            'landing_stats_santri' => ['nullable', 'string', 'max:20'],
            'landing_stats_halaqah' => ['nullable', 'string', 'max:20'],
            'landing_stats_accuracy' => ['nullable', 'string', 'max:20'],
            'logo_image_file' => ['nullable', 'image', 'max:1024'],
            'hero_image_file' => ['nullable', 'image', 'max:3072'],
        ]);

        // Unggah logo bila ada.
        if ($request->hasFile('logo_image_file')) {
            $this->replaceFile('logo_image', $request->file('logo_image_file')->store('branding', 'public'));
        } elseif ($request->boolean('remove_logo_image')) {
            $this->replaceFile('logo_image', '');
        }

        // Unggah hero kustom bila ada.
        if ($request->hasFile('hero_image_file')) {
            $this->replaceFile('landing_hero_image_custom', $request->file('hero_image_file')->store('branding', 'public'));
        }

        unset($data['logo_image_file'], $data['hero_image_file']);
        Setting::putMany($data);

        return back()->with('success', 'Pengaturan branding & landing page berhasil disimpan.');
    }

    /**
     * Simpan path baru ke setting sambil menghapus file lama dari storage.
     */
    private function replaceFile(string $key, string $newPath): void
    {
        $old = Setting::get($key);
        if (! empty($old)) {
            Storage::disk('public')->delete($old);
        }
        Setting::put($key, $newPath);
    }
}
