<?php

namespace App\Http\Controllers;

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

        foreach (Personil::where('is_active', true)->get() as $personil) {
            $presences = PresensiPersonil::where('personil_id', $personil->id)
                ->whereBetween('date', [$period->start_date, $period->end_date])->get();

            $presentDays = $presences->whereNotNull('check_in_time')->count();
            $lateDays = $presences->where('status', 'Terlambat')->count();
            $attendanceDeduction = $lateDays * self::LATE_PENALTY;

            $base = (float) $personil->salary_base;
            $allowance = (float) $personil->salary_allowance;
            $deduction = (float) $personil->salary_deduction;
            $total = $base + $allowance - $deduction - $attendanceDeduction;

            Payslip::updateOrCreate(
                ['payroll_period_id' => $period->id, 'personil_id' => $personil->id],
                [
                    'salary_base' => $base,
                    'allowance' => $allowance,
                    'deduction' => $deduction,
                    'attendance_deduction' => $attendanceDeduction,
                    'teaching_honor' => 0,
                    'total' => $total,
                    'present_days' => $presentDays,
                    'absent_days' => 0,
                    'late_days' => $lateDays,
                ]
            );
        }

        return back()->with('success', 'Penggajian berhasil diproses. Silakan periksa pratinjau sebelum finalisasi.');
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
