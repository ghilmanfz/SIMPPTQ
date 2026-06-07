<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();         // mis. "7A"
            $table->string('tingkat')->nullable();    // mis. "7"
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('wali_kelas_id')->nullable()->constrained('personils')->nullOnDelete();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
