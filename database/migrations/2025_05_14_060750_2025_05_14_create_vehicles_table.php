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
        //
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('vehicle_name', 100)->nullable(); // Vehicle name, nullable
            $table->string('category', 100); // Category, not nullable
            $table->string('sub_category', 100)->nullable(); // Sub-category, nullable
            $table->float('rate'); // Rate, not nullable
            $table->timestamps(); // Created at and updated at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
         Schema::dropIfExists('vehicles');
    }
};
