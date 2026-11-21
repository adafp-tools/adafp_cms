<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('file');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->enum('category', ['manifesto', 'constitution', 'report', 'statement', 'other'])->default('other');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'published_at']);
        });

        Schema::create('document_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('translated_by', ['human', 'ai'])->default('human');
            $table->boolean('reviewed')->default(true);
            $table->timestamps();

            $table->unique(['document_id', 'locale']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_translations');
        Schema::dropIfExists('documents');
    }
};
