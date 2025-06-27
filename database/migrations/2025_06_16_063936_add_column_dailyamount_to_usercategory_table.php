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
        Schema::table('user_categories', function (Blueprint $table) {
            //
            $table->dropColumn('room_min_price');
            $table->dropColumn('room_max_price');
            $table->decimal('dailyamount',10,2)->after('allowed_vehicles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usercategory', function (Blueprint $table) {
            //
        });
    }
};
