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
        Schema::table('streelight_poles', function (Blueprint $table) {
            //
            $table->string('sim_number')->nullable()->after('luminary_qr');
            $table->string('survey_image')->nullable()->after('lng');
            $table->string('submission_image')->nullable()->after('survey_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('streetlight_poles', function (Blueprint $table) {
            //
        });
    }
};
