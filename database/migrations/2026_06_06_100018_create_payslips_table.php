<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('personil_id')->constrained()->cascadeOnDelete();
            $table->decimal('salary_base', 12, 2)->default(0);
            $table->decimal('allowance', 12, 2)->default(0);
            $table->decimal('deduction', 12, 2)->default(0);
            $table->decimal('attendance_deduction', 12, 2)->default(0); // potongan dari ketidakhadiran
            $table->decimal('teaching_honor', 12, 2)->default(0);       // honor jam mengajar (jika dipakai)
            $table->decimal('total', 12, 2)->default(0);
            $table->unsignedInteger('present_days')->default(0);
            $table->unsignedInteger('absent_days')->default(0);
            $table->unsignedInteger('late_days')->default(0);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['payroll_period_id', 'personil_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
