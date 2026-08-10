<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            // Izinkan duplikat no_agenda (untuk disposisi yang diteruskan)
            $table->dropUnique('disposisis_no_agenda_unique');

            // Jejak disposisi asal (hasil teruskan)
            $table->foreignId('forwarded_from')->nullable()->after('approved_by')->constrained('disposisis')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->dropForeign(['forwarded_from']);
            $table->dropColumn('forwarded_from');
            $table->unique('no_agenda', 'disposisis_no_agenda_unique');
        });
    }
};
