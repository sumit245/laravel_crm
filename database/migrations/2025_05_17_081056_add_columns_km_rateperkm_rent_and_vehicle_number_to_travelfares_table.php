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
        Schema::table('travelfares', function (Blueprint $table) {
            //
            $table->integer('add_total_km')->after('modeoftravel');
            $table->integer('add_rate_per_km')->after('add_total_km');
            $table->integer('add_rent')->after('add_rate_per_km');
            $table->string('add_vehicle_no')->after('add_rent');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travelfares', function (Blueprint $table) {
            //
        });
    }
};
