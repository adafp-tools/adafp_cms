<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('location')->nullable();
            $table->string('online_url')->nullable();
            $table->enum('status', ['draft', 'published', 'scheduled'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'starts_at']);
        });

        Schema::create('event_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('location_label')->nullable();
            $table->enum('translated_by', ['human', 'ai'])->default('human');
            $table->boolean('reviewed')->default(true);
            $table->timestamps();

            $table->unique(['event_id', 'locale']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_translations');
        Schema::dropIfExists('events');
    }
};
