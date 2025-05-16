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
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop existing columns
            $table->string('icon')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            //

            $table->dropColumn(['vehicle_name', 'category', 'sub_category', 'rate']);

            // Recreate the old columns
            $table->string('vehicle_name');
            $table->enum('category', ['bike', 'car', 'public']);
            $table->string('sub_category')->nullable();
            $table->decimal('rate', 8, 2);
        });
    }
};
