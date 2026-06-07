<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengecualian jadwal pada tanggal tertentu (hasil tukar jam / libur),
     * sehingga jadwal master TIDAK pernah diubah/dihapus langsung.
     */
    public function up(): void
    {
        Schema::create('jadwal_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('type')->default('Tukar'); // Tukar / Libur / Pengganti
            $table->foreignId('substitute_personil_id')->nullable()->constrained('personils')->nullOnDelete();
            $table->foreignId('swap_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_exceptions');
    }
};
