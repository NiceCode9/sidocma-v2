<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disposisi_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disposisi_id')->constrained('disposisis')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units');
            $table->json('instruksi');
            $table->boolean('paraf')->default(false);
            $table->timestamp('paraf_at')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposisi_targets');
    }
};
