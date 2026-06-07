<?php

namespace App\Http\Controllers;

use App\Models\LokasiPresensi;
use App\Models\PresensiPersonil;
use App\Support\Branding;
use App\Support\ExcelExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PresensiPersonilController extends Controller
{
    /** Batas jam dianggap "Tepat Waktu". */
    private const ON_TIME_LIMIT = '07:30';

    public function index(): View
    {
        $personil = auth()->user()->personil;
        $today = Carbon::today();

        $todayPresence = $personil
            ? $personil->presensi()->whereDate('date', $today)->first()
            : null;

        $myLogs = $personil
            ? $personil->presensi()->with('lokasi')->latest('date')->take(15)->get()
            : collect();

        $lokasi = LokasiPresensi::where('is_active', true)->get();

        return view('presensi.index', compact('personil', 'todayPresence', 'myLogs', 'lokasi'));
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $personil = auth()->user()->personil;
        if (! $personil) {
            return back()->with('error', 'Akun Anda belum terhubung dengan data personil.');
        }

        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $today = Carbon::today();
        $existing = $personil->presensi()->whereDate('date', $today)->first();
        if ($existing && $existing->check_in_time) {
            return back()->with('error', 'Anda sudah melakukan Check-In hari ini.');
        }

        $lokasi = $this->findValidLocation($request->latitude, $request->longitude);
        if (! $lokasi) {
            return back()->with('error', 'Presensi ditolak! Koordinat Anda berada di luar radius lokasi presensi yang sah.');
        }

        $now = Carbon::now();
        $status = $now->format('H:i') <= self::ON_TIME_LIMIT ? 'Tepat Waktu' : 'Terlambat';

        PresensiPersonil::updateOrCreate(
            ['personil_id' => $personil->id, 'date' => $today->toDateString()],
            [
                'lokasi_presensi_id' => $lokasi->id,
                'check_in_time' => $now->format('H:i'),
                'check_in_lat' => $request->latitude,
                'check_in_lng' => $request->longitude,
                'status' => $status,
            ]
        );

        return back()->with('success', "Check-In berhasil dicatat pukul {$now->format('H:i')} di {$lokasi->name} ({$status}).");
    }

    public function checkOut(Request $request): RedirectResponse
    {
        $personil = auth()->user()->personil;
        if (! $personil) {
            return back()->with('error', 'Akun Anda belum terhubung dengan data personil.');
        }

        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $presence = $personil->presensi()->whereDate('date', Carbon::today())->first();
        if (! $presence || ! $presence->check_in_time) {
            return back()->with('error', 'Anda belum melakukan Check-In hari ini.');
        }
        if ($presence->check_out_time) {
            return back()->with('error', 'Anda sudah melakukan Check-Out hari ini.');
        }

        $now = Carbon::now();
        $presence->update([
            'check_out_time' => $now->format('H:i'),
            'check_out_lat' => $request->latitude,
            'check_out_lng' => $request->longitude,
        ]);

        return back()->with('success', "Check-Out berhasil dicatat pukul {$now->format('H:i')}.");
    }

    public function rekap(Request $request): View
    {
        $query = PresensiPersonil::with('personil', 'lokasi');

        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
        $query->whereDate('date', $date);

        $logs = $query->orderByDesc('date')->orderBy('check_in_time')->paginate(15)->withQueryString();

        return view('presensi.rekap', compact('logs', 'date'));
    }

    /**
     * Ekspor rekap presensi personil pada tanggal terpilih ke Excel.
     */
    public function exportRekap(Request $request): StreamedResponse
    {
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        $logs = PresensiPersonil::with('personil', 'lokasi')
            ->whereDate('date', $date)
            ->orderBy('check_in_time')
            ->get();

        $no = 0;
        $rows = $logs->map(fn ($l) => [
            ++$no,
            $l->personil?->name ?? '-',
            $l->personil?->jabatan ?? '-',
            $l->check_in_time ? substr($l->check_in_time, 0, 5) : '-',
            $l->check_out_time ? substr($l->check_out_time, 0, 5) : '-',
            $l->status,
            $l->lokasi?->name ?? '-',
            $l->note,
        ]);

        return ExcelExporter::download(
            'REKAP PRESENSI PERSONIL — '.(Branding::data()['pondok_name'] ?? 'PPTQ'),
            ['No', 'Nama Personil', 'Jabatan', 'Jam Masuk', 'Jam Pulang', 'Status', 'Lokasi', 'Catatan'],
            $rows,
            [
                'sheetTitle' => 'Rekap Presensi',
                'subtitle' => 'Tanggal: '.$date->format('d-m-Y').' • Total: '.$logs->count().' record',
                'filename' => 'rekap-presensi-'.$date->format('Ymd').'.xlsx',
                'center' => ['A', 'D', 'E', 'F'],
            ],
        );
    }

    /**
     * Cari lokasi aktif yang jaraknya masih dalam radius (validasi GPS sungguhan).
     */
    private function findValidLocation(float $lat, float $lng): ?LokasiPresensi
    {
        foreach (LokasiPresensi::where('is_active', true)->get() as $lokasi) {
            if ($this->haversine($lat, $lng, (float) $lokasi->latitude, (float) $lokasi->longitude) <= $lokasi->radius) {
                return $lokasi;
            }
        }

        return null;
    }

    /**
     * Jarak dua koordinat dalam meter (rumus haversine).
     */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
