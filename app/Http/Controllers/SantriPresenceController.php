<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Santri;
use App\Models\SantriPresence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SantriPresenceController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $todayPresences = SantriPresence::with('santri', 'kelas')
            ->whereDate('date', $today)->latest('id')->get();

        $kelasList = Kelas::orderBy('name')->get();
        $santriList = Santri::where('status', 'Aktif')->orderBy('name')->get();

        return view('presensi-santri.index', compact('todayPresences', 'kelasList', 'santriList'));
    }

    public function scan(Request $request): RedirectResponse
    {
        $request->validate([
            'card_token' => ['nullable', 'string'],
            'santri_id' => ['nullable', 'exists:santris,id'],
            'kegiatan' => ['required', 'string', 'max:100'],
        ]);

        // Identifikasi santri dari token kartu (scan QR) atau pilihan manual.
        $santri = $request->filled('card_token')
            ? Santri::where('card_token', $request->card_token)->first()
            : Santri::find($request->santri_id);

        if (! $santri) {
            return back()->with('error', 'Kartu tidak valid / santri tidak ditemukan.');
        }
        if (! $santri->isActive()) {
            return back()->with('error', "Presensi ditolak: santri {$santri->name} berstatus {$santri->status}.");
        }

        $today = Carbon::today();
        $exists = SantriPresence::where('santri_id', $santri->id)
            ->whereDate('date', $today)
            ->where('kegiatan', $request->kegiatan)->exists();

        if ($exists) {
            return back()->with('error', "{$santri->name} sudah tercatat hadir pada {$request->kegiatan} hari ini.");
        }

        SantriPresence::create([
            'santri_id' => $santri->id,
            'kelas_id' => $santri->kelas_id,
            'date' => $today->toDateString(),
            'time' => Carbon::now()->format('H:i'),
            'kegiatan' => $request->kegiatan,
            'status' => 'Hadir',
            'recorded_by' => auth()->id(),
        ]);

        return back()->with('success', "Presensi {$santri->name} berhasil dicatat.");
    }
}
