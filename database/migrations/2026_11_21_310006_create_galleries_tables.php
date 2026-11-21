<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('galleries')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['gallery_id', 'sort']);
        });

        Schema::create('gallery_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('galleries')->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('title');
            $table->enum('translated_by', ['human', 'ai'])->default('human');
            $table->boolean('reviewed')->default(true);
            $table->timestamps();

            $table->unique(['gallery_id', 'locale']);
        });

        Schema::create('gallery_image_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_image_id')->constrained('gallery_images')->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('caption')->nullable();
            $table->enum('translated_by', ['human', 'ai'])->default('human');
            $table->boolean('reviewed')->default(true);
            $table->timestamps();

            $table->unique(['gallery_image_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_image_translations');
        Schema::dropIfExists('gallery_translations');
        Schema::dropIfExists('gallery_images');
        Schema::dropIfExists('galleries');
    }
};
