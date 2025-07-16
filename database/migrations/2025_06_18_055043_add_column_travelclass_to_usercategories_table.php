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
            $table->string('travel_class')->nullable()->after('allowed_vehicles'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usercategories', function (Blueprint $table) {
            //
        });
    }
};
