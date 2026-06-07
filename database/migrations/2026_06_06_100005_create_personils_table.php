<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personils', function (Blueprint $table) {
            $table->id();
            // Satu personil boleh terhubung ke satu akun user (boleh juga tanpa akun).
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('nik')->nullable()->unique();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('unit_kerja')->nullable();
            // Status kerja hanya memengaruhi laporan & penggajian (bukan menu).
            $table->string('status_kerja')->default('Tetap');
            // Fungsi kerja menentukan kelayakan modul operasional (jadwal, tukar jam).
            $table->enum('fungsi_kerja', ['Non-Pengajar', 'Pengajar', 'Dua Fungsi'])->default('Non-Pengajar');
            $table->decimal('salary_base', 12, 2)->default(0);
            $table->decimal('salary_allowance', 12, 2)->default(0);
            $table->decimal('salary_deduction', 12, 2)->default(0);
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personils');
    }
};
