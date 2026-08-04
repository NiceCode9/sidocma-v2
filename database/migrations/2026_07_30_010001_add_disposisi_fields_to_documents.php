<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('needs_disposisi')->default(false)->after('is_letter');
            $table->date('disposisi_tgl_naskah')->nullable()->after('needs_disposisi');
            $table->dateTime('disposisi_masuk_tu')->nullable()->after('disposisi_tgl_naskah');
            $table->string('disposisi_tgl_no_naskah')->nullable()->after('disposisi_masuk_tu');
            $table->string('disposisi_asal_naskah')->nullable()->after('disposisi_tgl_no_naskah');
            $table->text('disposisi_informasi_naskah')->nullable()->after('disposisi_asal_naskah');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'needs_disposisi',
                'disposisi_tgl_naskah',
                'disposisi_masuk_tu',
                'disposisi_tgl_no_naskah',
                'disposisi_asal_naskah',
                'disposisi_informasi_naskah',
            ]);
        });
    }
};