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
        Schema::table('tadas', function (Blueprint $table) {
            //
             $table->dropColumn(['pickup_date']);

            // Add new time columns
            $table->time('start_journey_time')->nullable()->after('start_journey');
            $table->time('end_journey_time')->nullable()->after('end_journey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tadas', function (Blueprint $table) {
            //
        });
    }
};
