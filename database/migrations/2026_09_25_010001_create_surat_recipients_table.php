<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surats')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['surat_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_recipients');
    }
};