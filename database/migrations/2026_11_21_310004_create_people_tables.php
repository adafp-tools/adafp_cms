<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('photo_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->enum('group', ['leadership', 'executive', 'parliament', 'other'])->default('other');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('show_email')->default(false);
            $table->boolean('show_phone')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['group', 'sort']);
        });

        Schema::create('person_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('position')->nullable();
            $table->text('bio')->nullable();
            $table->enum('translated_by', ['human', 'ai'])->default('human');
            $table->boolean('reviewed')->default(true);
            $table->timestamps();

            $table->unique(['person_id', 'locale']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_translations');
        Schema::dropIfExists('people');
    }
};
