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
        Schema::table('inventory_dispatch', function (Blueprint $table) {
            // Adding isDispatch column 
            $table->boolean('isDispatched')->default(false)->after('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_dispatch', function (Blueprint $table) {
            //
        });
    }
};
