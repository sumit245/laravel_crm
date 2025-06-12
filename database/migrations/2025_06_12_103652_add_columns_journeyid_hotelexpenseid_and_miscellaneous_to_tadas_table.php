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
            // $table->json('miscellaneous')->nullable()->after('outcome_achieve');

            // $table->unsignedBigInteger('journies_id')->nullable()->after('miscellaneous');
            // $table->foreign('journies_id')
            //     ->references('id')
            //     ->on('journies')
            //     ->onDelete('set null');

            // $table->unsignedBigInteger('hotel_id')->nullable()->after('journies_id');
            // $table->foreign('hotel_id')
            //     ->references('id')
            //     ->on('hotelexpenses')
            //     ->onDelete('set null');
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
