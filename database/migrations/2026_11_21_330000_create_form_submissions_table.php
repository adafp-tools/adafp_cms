<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->enum('form_type', ['contact', 'join']);
            $table->char('locale', 5);
            $table->string('name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->date('dob')->nullable();
            $table->text('address')->nullable();
            $table->string('party_role', 100)->nullable();
            $table->boolean('consent')->default(false);
            $table->timestamp('consent_at')->nullable();
            $table->char('ip_hash', 64);
            $table->timestamp('read_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('form_type');
            $table->index('email');
            $table->index('created_at');
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
