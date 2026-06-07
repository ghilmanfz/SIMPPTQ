<?php

namespace App\Http\Controllers;

use App\Models\Behavior;
use App\Models\Kelas;
use App\Models\LeaveRequest;
use App\Models\Personil;
use App\Models\PresensiPersonil;
use App\Models\Santri;
use App\Models\SantriPresence;
use App\Models\Visit;
use App\Support\Branding;
use App\Support\ExcelExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $start = $request->filled('start') ? Carbon::parse($request->start) : Carbon::today()->startOfMonth();
        $end = $request->filled('end') ? Carbon::parse($request->end) : Carbon::today()->endOfMonth();

        $report = [
            'range' => ['start' => $start, 'end' => $end],

            'personnel' => [
                'total' => Personil::count(),
                'aktif' => Personil::where('is_active', true)->count(),
                'pengajar' => Personil::whereIn('fungsi_kerja', ['Pengajar', 'Dua Fungsi'])->count(),
                'non_pengajar' => Personil::where('fungsi_kerja', 'Non-Pengajar')->count(),
                'by_status' => Personil::selectRaw('status_kerja, count(*) as total')->groupBy('status_kerja')->pluck('total', 'status_kerja'),
            ],

            'attendance' => [
                'hadir' => PresensiPersonil::whereBetween('date', [$start, $end])->whereNotNull('check_in_time')->count(),
                'terlambat' => PresensiPersonil::whereBetween('date', [$start, $end])->where('status', 'Terlambat')->count(),
            ],

            'leaves' => LeaveRequest::whereBetween('start_date', [$start, $end])
                ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),

            'santri' => [
                'total' => Santri::count(),
                'aktif' => Santri::where('status', 'Aktif')->count(),
                'by_status' => Santri::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
                'by_kelas' => Kelas::withCount('santris')->orderBy('name')->get(),
            ],

            'santri_presence' => SantriPresence::whereBetween('date', [$start, $end])->count(),

            'behavior' => [
                'pelanggaran' => Behavior::whereBetween('date', [$start, $end])->where('type', 'Pelanggaran')->count(),
                'kebaikan' => Behavior::whereBetween('date', [$start, $end])->where('type', 'Kebaikan')->count(),
            ],

            'visits' => Visit::whereBetween('visit_at', [$start, $end->copy()->endOfDay()])->count(),
        ];

        return view('laporan.index', compact('report'));
    }

    /**
     * Ekspor ringkasan laporan strategis (sesuai rentang tanggal) ke Excel.
     */
    public function export(Request $request): StreamedResponse
    {
        $start = $request->filled('start') ? Carbon::parse($request->start) : Carbon::today()->startOfMonth();
        $end = $request->filled('end') ? Carbon::parse($request->end) : Carbon::today()->endOfMonth();

        $rows = [
            ['Personil', 'Total Personil', Personil::count()],
            ['Personil', 'Personil Aktif', Personil::where('is_active', true)->count()],
            ['Personil', 'Pengajar', Personil::whereIn('fungsi_kerja', ['Pengajar', 'Dua Fungsi'])->count()],
            ['Personil', 'Non-Pengajar', Personil::where('fungsi_kerja', 'Non-Pengajar')->count()],
            ['Presensi', 'Kehadiran Personil', PresensiPersonil::whereBetween('date', [$start, $end])->whereNotNull('check_in_time')->count()],
            ['Presensi', 'Keterlambatan', PresensiPersonil::whereBetween('date', [$start, $end])->where('status', 'Terlambat')->count()],
            ['Santri', 'Total Santri', Santri::count()],
            ['Santri', 'Santri Aktif', Santri::where('status', 'Aktif')->count()],
            ['Santri', 'Presensi Santri', SantriPresence::whereBetween('date', [$start, $end])->count()],
            ['Perilaku', 'Pelanggaran', Behavior::whereBetween('date', [$start, $end])->where('type', 'Pelanggaran')->count()],
            ['Perilaku', 'Kebaikan', Behavior::whereBetween('date', [$start, $end])->where('type', 'Kebaikan')->count()],
            ['Kunjungan', 'Kunjungan Wali', Visit::whereBetween('visit_at', [$start, $end->copy()->endOfDay()])->count()],
        ];

        return ExcelExporter::download(
            'LAPORAN STRATEGIS — '.(Branding::data()['pondok_name'] ?? 'PPTQ'),
            ['Kategori', 'Indikator', 'Nilai'],
            $rows,
            [
                'sheetTitle' => 'Laporan',
                'subtitle' => 'Periode: '.$start->format('d-m-Y').' s/d '.$end->format('d-m-Y'),
                'filename' => 'laporan-strategis-'.now()->format('Ymd-His').'.xlsx',
                'center' => ['C'],
            ],
        );
    }
}
