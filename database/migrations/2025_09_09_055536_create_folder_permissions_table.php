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
        Schema::create('folder_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('folders')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('permission_type', ['read', 'write', 'delete', 'download', 'admin']);
            $table->foreignId('granted_by')->constrained('users');
            $table->timestamps();

            $table->unique(['folder_id', 'user_id', 'permission_type'], 'unique_user_folder_permission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folder_permissions');
    }
};
