<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri_presences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->date('date');
            $table->time('time')->nullable();
            $table->string('kegiatan')->default('Halaqah'); // sesi / kegiatan
            $table->string('status')->default('Hadir');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Cegah duplikasi scan pada sesi/kegiatan yang sama dalam satu hari.
            $table->unique(['santri_id', 'date', 'kegiatan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri_presences');
    }
};
