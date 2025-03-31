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
        Schema::table('streetlights', function (Blueprint $table) {
            //
            Schema::table('streetlights', function (Blueprint $table) {
                $table->integer('mukhiya_contact')->after('ward'); // Replace 'existing_column_name' as needed
            });
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('streetlights', function (Blueprint $table) {
            //
        });
    }
};
