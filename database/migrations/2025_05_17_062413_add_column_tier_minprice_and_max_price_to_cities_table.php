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
        Schema::table('cities', function (Blueprint $table) {
            //
            $table->enum('tier', ['1', '2', '3'])->nullable()->after('state_id'); // replace 'column_name' with the actual previous column if needed
            $table->decimal('room_min_price', 10, 2)->nullable()->after('tier');
            $table->decimal('room_max_price', 10, 2)->nullable()->after('room_min_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            //
        });
    }
};
