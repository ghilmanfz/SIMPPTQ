<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swap_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_personil_id')->constrained('personils')->cascadeOnDelete();
            $table->foreignId('target_personil_id')->nullable()->constrained('personils')->nullOnDelete(); // guru pengganti
            $table->date('date');                    // tanggal sesi yang ditukar
            $table->text('reason');
            // Diajukan / Disetujui / Ditolak / Diterapkan / Dibatalkan
            $table->string('status')->default('Diajukan');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swap_requests');
    }
};
