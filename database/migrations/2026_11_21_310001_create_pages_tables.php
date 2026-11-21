<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->enum('template', ['default', 'about', 'contact', 'join'])->default('default');
            $table->enum('status', ['draft', 'published', 'scheduled'])->default('draft');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('show_in_menu')->default(true);
            $table->foreignId('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('sort');
            $table->index('parent_id');
        });

        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('title');
            $table->longText('body')->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->enum('translated_by', ['human', 'ai'])->default('human');
            $table->boolean('reviewed')->default(true);
            $table->timestamps();

            $table->unique(['page_id', 'locale']);
            $table->index('locale');
        });

        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('title');
            $table->longText('body')->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['page_id', 'locale', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_revisions');
        Schema::dropIfExists('page_translations');
        Schema::dropIfExists('pages');
    }
};
