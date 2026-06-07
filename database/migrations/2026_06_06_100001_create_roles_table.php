<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();   // kunci sistem, mis. "superadmin"
            $table->string('label');             // nama tampilan, mis. "Super Admin"
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false); // role inti tidak boleh dihapus
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
