<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_translations', function (Blueprint $table) {
            $table->string('meta_description', 160)->nullable()->after('body');
        });

        Schema::table('post_revisions', function (Blueprint $table) {
            $table->string('meta_description', 160)->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('post_translations', function (Blueprint $table) {
            $table->dropColumn('meta_description');
        });

        Schema::table('post_revisions', function (Blueprint $table) {
            $table->dropColumn('meta_description');
        });
    }
};
