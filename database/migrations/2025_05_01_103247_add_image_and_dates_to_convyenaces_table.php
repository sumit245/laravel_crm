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
        Schema::table('conveyances', function (Blueprint $table) {
            //
            $table->string('image')->nullable()->after('vehicle_category'); // or place after any existing column
            $table->string('date')->nullable()->after('time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('convyenaces', function (Blueprint $table) {
            //
        });
    }
};
