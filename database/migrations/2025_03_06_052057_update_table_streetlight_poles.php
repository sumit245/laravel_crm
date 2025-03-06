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
            $table->string('ward')->nullable()->after('complete_pole_number'); // Place after a relevant column
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
