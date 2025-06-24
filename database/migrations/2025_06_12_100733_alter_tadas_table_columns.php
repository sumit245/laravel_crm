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
            $table->dropColumn([
                'otherexpense',
                'description_category',
                'category',
                'vehicle_no',
                'Rent',
                'rate_per_km',
                'total_km',
                'to_city',
                'from_city',
                'transport',
                'end_journey_pnr',
                'end_journey_time',
                'end_journey',
                'start_journey_time',
                'start_journey',
                'start_journey_pnr',
                'meeting_visit'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // Recreate dropped columns if needed in a rollback
            $table->string('otherexpense')->nullable();
            $table->string('description_category')->nullable();
            $table->enum('category', ['0', '1', '2'])->nullable(); // adjust type as per original
            $table->string('vehicle_no')->nullable();
            $table->string('Rent')->nullable();
            $table->decimal('rate_per_km', 8, 2)->nullable();
            $table->integer('total_km')->nullable();
            $table->string('to_city')->nullable();
            $table->string('from_city')->nullable();
            $table->string('transport')->nullable();
            $table->string('end_journey_pnr')->nullable();
            $table->time('end_journey_time')->nullable();
            $table->date('end_journey')->nullable();
            $table->time('start_journey_time')->nullable();
            $table->date('start_journey')->nullable();
            $table->string('start_journey_pnr')->nullable();
            $table->string('meeting_visit')->nullable();
        });
    }
};
