<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class WhatsappController extends Controller
{
    public function index(): View
    {
        return view('whatsapp.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'whatsapp_token' => ['nullable', 'string', 'max:255'],
            'whatsapp_sender' => ['nullable', 'string', 'max:30'],
        ]);

        Setting::putMany($data);

        return back()->with('success', 'Konfigurasi WhatsApp Fonnte berhasil disimpan.');
    }

    /**
     * Kirim pesan uji coba melalui Fonnte (panggilan HTTP nyata).
     */
    public function test(Request $request): RedirectResponse
    {
        $request->validate([
            'target' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $token = Setting::get('whatsapp_token');
        if (empty($token)) {
            Setting::put('whatsapp_connected', '0');

            return back()->with('error', 'Token Fonnte belum diisi. Simpan token terlebih dahulu.');
        }

        try {
            $response = Http::asForm()
                ->withHeaders(['Authorization' => $token])
                ->timeout(20)
                ->post('https://api.fonnte.com/send', [
                    'target' => $request->target,
                    'message' => $request->message,
                ]);

            $ok = $response->successful() && ($response->json('status') ?? false);
            Setting::put('whatsapp_connected', $ok ? '1' : '0');

            if ($ok) {
                return back()->with('success', 'Pesan uji berhasil dikirim melalui Fonnte Gateway.');
            }

            $reason = $response->json('reason') ?? 'Periksa token & nomor pengirim Anda.';

            return back()->with('error', "Gagal mengirim: {$reason}");
        } catch (\Throwable $e) {
            Setting::put('whatsapp_connected', '0');

            return back()->with('error', 'Tidak dapat terhubung ke Fonnte: ' . $e->getMessage());
        }
    }
}
