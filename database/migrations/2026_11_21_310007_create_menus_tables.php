<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->enum('location', ['header', 'footer']);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('location');
        });

        Schema::create('menu_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->char('locale', 5);
            $table->json('items');
            $table->enum('translated_by', ['human', 'ai'])->default('human');
            $table->boolean('reviewed')->default(true);
            $table->timestamps();

            $table->unique(['menu_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_translations');
        Schema::dropIfExists('menus');
    }
};
