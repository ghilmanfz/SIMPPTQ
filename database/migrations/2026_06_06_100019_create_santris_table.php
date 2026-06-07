<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santris', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nis')->unique();
            $table->string('nisn')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->string('photo_path')->nullable();
            // Aktif / Lulus / Pindah / Keluar / Nonaktif
            $table->string('status')->default('Aktif');
            $table->text('address')->nullable();
            $table->string('wali_name')->nullable();
            $table->string('wali_phone')->nullable();
            $table->string('wali_relation')->nullable();
            $table->string('card_token')->nullable()->unique(); // token unik untuk QR kartu santri
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santris');
    }
};
