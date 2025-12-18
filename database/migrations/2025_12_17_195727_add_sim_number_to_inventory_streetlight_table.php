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
        Schema::table('inventory_streetlight', function (Blueprint $table) {
            $table->string('sim_number', 200)->nullable()->after('serial_number');
        });

        // Note: Unique constraint for sim_number where item_code = 'SL02' will be enforced
        // at application level since MySQL doesn't support partial unique indexes
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_streetlight', function (Blueprint $table) {
            $table->dropColumn('sim_number');
        });
    }
};
