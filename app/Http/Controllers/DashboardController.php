<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Behavior;
use App\Models\LeaveRequest;
use App\Models\Payslip;
use App\Models\Personil;
use App\Models\PresensiPersonil;
use App\Models\Santri;
use App\Models\SantriPresence;
use App\Models\SwapRequest;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Halaman publik (landing page).
     */
    public function landing(): View
    {
        $announcements = Announcement::where('is_active', true)
            ->where('target', 'Semua')
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('landing', compact('announcements'));
    }

    /**
     * Dashboard sesuai role & permission.
     */
    public function index(): View
    {
        $user = auth()->user();
        $today = Carbon::today();
        $personil = $user->personil;

        // Ringkasan untuk admin / pimpinan.
        $stats = [
            'personil' => Personil::where('is_active', true)->count(),
            'santri' => Santri::where('status', 'Aktif')->count(),
            'present_today' => PresensiPersonil::whereDate('date', $today)->whereNotNull('check_in_time')->count(),
            'pending_leaves' => LeaveRequest::where('status', 'Diajukan')->count(),
            'pending_swaps' => SwapRequest::where('status', 'Diajukan')->count(),
            'santri_present_today' => SantriPresence::whereDate('date', $today)->count(),
        ];

        // Pengumuman yang relevan dengan role user.
        $announcements = Announcement::where('is_active', true)
            ->where(fn ($q) => $q->where('target', 'Semua')->orWhere('target', $user->role?->name))
            ->latest('published_at')
            ->take(5)
            ->get();

        // Ringkasan personal (untuk semua user yang punya data personil).
        $personal = null;
        if ($personil) {
            $dayName = hari_indo($today);
            $personal = [
                'presence_today' => $personil->presensi()->whereDate('date', $today)->first(),
                'schedules_today' => $personil->isPengajar()
                    ? $personil->jadwals()->with(['kelas', 'mapel', 'sesi'])->where('day', $dayName)->get()
                    : collect(),
                'pending_leaves' => $personil->leaveRequests()->where('status', 'Diajukan')->count(),
                'pending_swaps' => $personil->swapRequests()->where('status', 'Diajukan')->count(),
                'latest_payslip' => Payslip::where('personil_id', $personil->id)
                    ->whereHas('period', fn ($q) => $q->where('status', 'Final'))
                    ->with('period')
                    ->latest('id')
                    ->first(),
            ];
        }

        // Data tren 7 hari untuk grafik pimpinan.
        $trend = $this->attendanceTrend();

        return view('dashboard', compact('stats', 'announcements', 'personal', 'trend'));
    }

    /**
     * Tren kehadiran personil 7 hari terakhir untuk grafik monitoring.
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    private function attendanceTrend(): array
    {
        $labels = [];
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d/m');
            $data[] = PresensiPersonil::whereDate('date', $date)->whereNotNull('check_in_time')->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
