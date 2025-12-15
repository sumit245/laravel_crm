<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change enum to varchar to support custom meeting types
        DB::statement("ALTER TABLE meets MODIFY COLUMN type VARCHAR(255) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to enum (only if needed)
        DB::statement("ALTER TABLE meets MODIFY COLUMN type ENUM('Review','Planning','Discussion') NOT NULL");
    }
};
