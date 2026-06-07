<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('santri_id')->constrained('santris')->cascadeOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->nullOnDelete();
            // Penempatan | Pindah Kelas | Naik Kelas | Tinggal Kelas | Lulus | Keluar
            $table->string('action', 30);
            $table->string('from_kelas', 60)->nullable();
            $table->string('to_kelas', 60)->nullable();
            $table->string('note', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['santri_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_histories');
    }
};
