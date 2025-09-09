<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->unique()->after('name');
            $table->string('employee_id', 50)->unique()->after('username');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null')->after('email');
            $table->boolean('is_active')->default(true)->after('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'unit_id', 'is_active']);
        });
    }
};
