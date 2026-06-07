<?php

namespace Database\Seeders;

use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\Personil;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();
        $adminUser = User::where('email', 'petugas@nuruliman.net')->first();

        // Periode lalu (sudah Final) — agar slip gaji bisa langsung dilihat personil.
        $lastMonth = $today->copy()->subMonthNoOverflow();
        $final = PayrollPeriod::updateOrCreate(
            ['name' => 'Gaji ' . $this->monthLabel($lastMonth)],
            [
                'start_date' => $lastMonth->copy()->startOfMonth()->toDateString(),
                'end_date' => $lastMonth->copy()->endOfMonth()->toDateString(),
                'status' => 'Final',
                'finalized_at' => $lastMonth->copy()->endOfMonth(),
                'finalized_by' => $adminUser?->id,
            ]
        );

        foreach (Personil::where('is_active', true)->get() as $personil) {
            $base = (float) $personil->salary_base;
            $allowance = (float) $personil->salary_allowance;
            $deduction = (float) $personil->salary_deduction;

            Payslip::updateOrCreate(
                ['payroll_period_id' => $final->id, 'personil_id' => $personil->id],
                [
                    'salary_base' => $base,
                    'allowance' => $allowance,
                    'deduction' => $deduction,
                    'attendance_deduction' => 0,
                    'teaching_honor' => 0,
                    'total' => $base + $allowance - $deduction,
                    'present_days' => 26,
                    'absent_days' => 0,
                    'late_days' => 0,
                ]
            );
        }

        // Periode berjalan (Draft) — diproses lewat menu Penggajian.
        PayrollPeriod::updateOrCreate(
            ['name' => 'Gaji ' . $this->monthLabel($today)],
            [
                'start_date' => $today->copy()->startOfMonth()->toDateString(),
                'end_date' => $today->copy()->endOfMonth()->toDateString(),
                'status' => 'Draft',
            ]
        );
    }

    private function monthLabel(Carbon $date): string
    {
        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return $months[(int) $date->format('n')] . ' ' . $date->format('Y');
    }
}
