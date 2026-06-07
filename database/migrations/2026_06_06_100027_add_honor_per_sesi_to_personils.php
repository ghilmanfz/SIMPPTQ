<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personils', function (Blueprint $table): void {
            // Honor per sesi mengajar (rupiah). Dipakai untuk komponen honor di penggajian.
            $table->decimal('honor_per_sesi', 12, 2)->default(0)->after('salary_deduction');
        });
    }

    public function down(): void
    {
        Schema::table('personils', function (Blueprint $table): void {
            $table->dropColumn('honor_per_sesi');
        });
    }
};
