<?php

namespace App\Http\Controllers;

use App\Models\JadwalException;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\Personil;
use App\Models\PresensiPersonil;
use App\Support\Branding;
use App\Support\ExcelExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollController extends Controller
{
    /** Potongan per keterlambatan (rupiah). */
    private const LATE_PENALTY = 25000;

    public function index(): View
    {
        $periods = PayrollPeriod::withCount('payslips')->latest('start_date')->get();

        return view('penggajian.index', compact('periods'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);
        $data['status'] = 'Draft';

        PayrollPeriod::create($data);

        return back()->with('success', 'Periode penggajian berhasil dibuat.');
    }

    public function show(PayrollPeriod $period): View
    {
        $period->load(['payslips.personil', 'finalizer']);

        return view('penggajian.show', compact('period'));
    }

    /**
     * Ekspor seluruh slip gaji satu periode ke Excel.
     */
    public function export(PayrollPeriod $period): StreamedResponse
    {
        $period->load('payslips.personil');

        $no = 0;
        $rows = $period->payslips
            ->sortBy(fn ($p) => $p->personil?->name)
            ->map(fn ($p) => [
                ++$no,
                $p->personil?->name ?? '-',
                $p->personil?->jabatan ?? '-',
                (float) $p->salary_base,
                (float) $p->allowance,
                (float) $p->teaching_honor,
                (float) $p->deduction,
                (float) $p->attendance_deduction,
                (float) $p->total,
                $p->present_days,
                $p->late_days,
            ]);

        return ExcelExporter::download(
            'PENGGAJIAN '.strtoupper($period->name).' — '.(Branding::data()['pondok_name'] ?? 'PPTQ'),
            ['No', 'Nama Personil', 'Jabatan', 'Gaji Pokok', 'Tunjangan', 'Honor Mengajar', 'Potongan', 'Potongan Absen', 'Total Diterima', 'Hadir', 'Terlambat'],
            $rows,
            [
                'sheetTitle' => 'Penggajian',
                'subtitle' => 'Periode: '.$period->start_date->format('d-m-Y').' s/d '.$period->end_date->format('d-m-Y').' • Status: '.$period->status,
                'filename' => 'penggajian-'.str($period->name)->slug().'-'.now()->format('Ymd').'.xlsx',
                'money' => ['D', 'E', 'F', 'G', 'H', 'I'],
                'center' => ['A', 'J', 'K'],
            ],
        );
    }

    public function process(PayrollPeriod $period): RedirectResponse
    {
        if ($period->isFinal()) {
            return back()->with('error', 'Periode sudah difinalisasi dan tidak dapat diproses ulang.');
        }

        // Hitung kemunculan tiap hari (ISO 1=Senin .. 7=Ahad) sepanjang periode.
        $dayMap = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Ahad' => 7];
        $dayCount = array_fill(1, 7, 0);
        for ($d = $period->start_date->copy(); $d->lte($period->end_date); $d->addDay()) {
            $dayCount[$d->dayOfWeekIso]++;
        }

        // Semua pengecualian "Tukar" pada periode (untuk pengalihan honor pengganti).
        $exceptions = JadwalException::with('jadwal')
            ->where('type', 'Tukar')
            ->whereBetween('date', [$period->start_date, $period->end_date])->get();

        foreach (Personil::where('is_active', true)->with('jadwals')->get() as $personil) {
            $presences = PresensiPersonil::where('personil_id', $personil->id)
                ->whereBetween('date', [$period->start_date, $period->end_date])->get();

            $presentDays = $presences->whereNotNull('check_in_time')->count();
            $lateDays = $presences->where('status', 'Terlambat')->count();
            $attendanceDeduction = $lateDays * self::LATE_PENALTY;

            // Jumlah sesi mengajar efektif dalam periode.
            $sessions = 0;
            foreach ($personil->jadwals as $jadwal) {
                $sessions += $dayCount[$dayMap[$jadwal->day] ?? 0] ?? 0;
            }
            // Kurangi sesi yang digantikan orang lain (personil ini tidak mengajar).
            $sessions -= $exceptions->filter(fn ($e) => $e->jadwal?->personil_id === $personil->id)->count();
            // Tambah sesi di mana personil ini menjadi guru pengganti.
            $sessions += $exceptions->where('substitute_personil_id', $personil->id)->count();
            $sessions = max(0, $sessions);

            $base = (float) $personil->salary_base;
            $allowance = (float) $personil->salary_allowance;
            $deduction = (float) $personil->salary_deduction;
            $teachingHonor = $sessions * (float) $personil->honor_per_sesi;
            $total = $base + $allowance + $teachingHonor - $deduction - $attendanceDeduction;

            Payslip::updateOrCreate(
                ['payroll_period_id' => $period->id, 'personil_id' => $personil->id],
                [
                    'salary_base' => $base,
                    'allowance' => $allowance,
                    'deduction' => $deduction,
                    'attendance_deduction' => $attendanceDeduction,
                    'teaching_honor' => $teachingHonor,
                    'total' => $total,
                    'present_days' => $presentDays,
                    'absent_days' => 0,
                    'late_days' => $lateDays,
                ]
            );
        }

        return back()->with('success', 'Penggajian berhasil diproses (termasuk honor mengajar). Periksa pratinjau sebelum finalisasi.');
    }

    public function finalize(PayrollPeriod $period): RedirectResponse
    {
        if ($period->payslips()->count() === 0) {
            return back()->with('error', 'Proses penggajian terlebih dahulu sebelum finalisasi.');
        }

        $period->update([
            'status' => 'Final',
            'finalized_at' => Carbon::now(),
            'finalized_by' => auth()->id(),
        ]);

        return back()->with('success', 'Penggajian difinalisasi & dikunci. Slip gaji kini dapat dilihat personil.');
    }

    /**
     * Slip gaji milik personil yang sedang login (hanya periode final).
     */
    public function slip(): View
    {
        $personil = auth()->user()->personil;

        $payslips = $personil
            ? Payslip::with('period')
                ->where('personil_id', $personil->id)
                ->whereHas('period', fn ($q) => $q->where('status', 'Final'))
                ->get()
                ->sortByDesc(fn ($p) => $p->period->start_date)
            : collect();

        return view('penggajian.slip', compact('payslips', 'personil'));
    }
}
