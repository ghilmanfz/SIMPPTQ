<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personil_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personil_id')->constrained()->cascadeOnDelete();
            $table->string('name');           // mis. "KTP", "Ijazah"
            $table->string('file_path');      // disimpan di disk privat (bukan publik)
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personil_documents');
    }
};
