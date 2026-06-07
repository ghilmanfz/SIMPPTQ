<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personil_id')->constrained()->cascadeOnDelete();
            $table->string('type');                  // Sakit / Cuti Tahunan / Izin / Dinas
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->string('document_path')->nullable();
            $table->string('status')->default('Diajukan'); // Diajukan / Disetujui / Ditolak
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('note')->nullable();      // catatan approver
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
