<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('streelight_poles', function (Blueprint $table) {
            $table->longText('survey_image')->nullable()->change();
            $table->longText('submission_image')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('streelight_poles', function (Blueprint $table) {
            $table->string('survey_image', 255)->nullable()->change();
            $table->string('submission_image', 255)->nullable()->change();
        });
    }
};
