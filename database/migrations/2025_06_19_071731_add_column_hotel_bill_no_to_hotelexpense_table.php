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
        Schema::table('hotelexpenses', function (Blueprint $table) {
            //
            $table->string('hotel_bill_no')->nullable()->after('hotel_bill');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotelexpense', function (Blueprint $table) {
            //
        });
    }
};
