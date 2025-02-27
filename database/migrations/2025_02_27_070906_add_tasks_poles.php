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
        Schema::table('streetlight_tasks', function (Blueprint $table) {
            // Drop the wrong site_id reference
            $table->dropForeign(['site_id']);
            $table->dropColumn('site_id');
            $table->dropColumn('pole_id');

            // Add pole_id instead
            $table->foreignId('pole_id')->constrained('poles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('streetlight_tasks', function (Blueprint $table) {
            $table->dropForeign(['pole_id']);
            $table->dropColumn('pole_id');

            // Re-add site_id (rollback)
            $table->foreignId('site_id')->constrained('streetlights')->onDelete('cascade');
        });
    }
};
