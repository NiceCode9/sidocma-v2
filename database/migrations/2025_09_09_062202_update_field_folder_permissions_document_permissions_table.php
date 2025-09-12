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
        Schema::table('folder_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->after('user_id')->nullable()->constrained('roles')->onDelete('cascade');
            $table->foreignId('unit_id')->after('role_id')->nullable()->constrained('units')->onDelete('cascade');
            $table->unique(['folder_id', 'role_id', 'permission_type'], 'unique_role_folder_permission');
            $table->unique(['folder_id', 'unit_id', 'permission_type'], 'unique_unit_folder_permission');
        });

        Schema::table('document_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->after('user_id')->nullable()->constrained('roles')->onDelete('cascade');
            $table->foreignId('unit_id')->after('role_id')->nullable()->constrained('units')->onDelete('cascade');
            $table->unique(['document_id', 'role_id', 'permission_type'], 'unique_role_document_permission');
            $table->unique(['document_id', 'unit_id', 'permission_type'], 'unique_unit_document_permission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folder_permissions', function (Blueprint $table) {
            $table->dropUnique('unique_role_folder_permission');
            $table->dropUnique('unique_unit_folder_permission');
            $table->dropForeign(['role_id']);
            $table->dropForeign(['unit_id']);
            $table->dropColumn('role_id');
            $table->dropColumn('unit_id');
        });

        Schema::table('document_permissions', function (Blueprint $table) {
            $table->dropUnique('unique_role_document_permission');
            $table->dropUnique('unique_unit_document_permission');
            $table->dropForeign(['role_id']);
            $table->dropForeign(['unit_id']);
            $table->dropColumn('role_id');
            $table->dropColumn('unit_id');
        });
    }
};
