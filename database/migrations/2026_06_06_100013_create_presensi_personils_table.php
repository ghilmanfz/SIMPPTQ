<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi_personils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personil_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lokasi_presensi_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->decimal('check_in_lat', 10, 7)->nullable();
            $table->decimal('check_in_lng', 10, 7)->nullable();
            $table->decimal('check_out_lat', 10, 7)->nullable();
            $table->decimal('check_out_lng', 10, 7)->nullable();
            $table->string('status')->default('Hadir'); // Tepat Waktu / Terlambat
            $table->string('note')->nullable();
            $table->timestamps();

            // Cegah duplikasi presensi pada hari yang sama.
            $table->unique(['personil_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_personils');
    }
};
