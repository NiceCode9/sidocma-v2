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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->bigInteger('file_size');
            $table->string('mime_type', 100);
            $table->string('file_extension', 10);
            $table->foreignId('folder_id')->constrained('folders')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('document_categories')->onDelete('set null');
            $table->string('document_number', 100)->nullable();
            $table->string('version', 20)->default('1.0');
            $table->enum('status', ['draft', 'approved', 'archived', 'deleted'])->default('draft');
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_confidential')->default(false);
            $table->date('expiry_date')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['document_number']);
            $table->index(['expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
