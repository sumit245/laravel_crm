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
            $table->decimal('other_charges', 10, 2)->nullable()->after('hotel_bill_no');
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
