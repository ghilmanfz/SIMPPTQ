<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'personil_id',
        'salary_base',
        'allowance',
        'deduction',
        'attendance_deduction',
        'teaching_honor',
        'total',
        'present_days',
        'absent_days',
        'late_days',
        'note',
    ];

    protected $casts = [
        'salary_base' => 'decimal:2',
        'allowance' => 'decimal:2',
        'deduction' => 'decimal:2',
        'attendance_deduction' => 'decimal:2',
        'teaching_honor' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function personil(): BelongsTo
    {
        return $this->belongsTo(Personil::class);
    }
}
