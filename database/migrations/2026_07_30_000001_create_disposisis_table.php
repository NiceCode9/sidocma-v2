<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disposisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->nullable()->constrained('surats')->nullOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('no_agenda')->unique();
            $table->date('tanggal_naskah');
            $table->dateTime('masuk_tu');
            $table->string('tgl_no_naskah');
            $table->string('asal_naskah');
            $table->text('isi_informasi');
            $table->enum('sifat', ['sangat_segera', 'segera', 'rahasia', 'biasa'])->default('biasa');
            $table->text('catatan_lain')->nullable();
            $table->date('batas_waktu')->nullable();
            $table->enum('status', ['draft', 'diproses', 'selesai'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposisis');
    }
};
