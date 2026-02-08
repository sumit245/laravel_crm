<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rms_push_logs', function (Blueprint $table) {
            // Drop foreign key first because it relies on the index
            $table->dropForeign(['pole_id']);

            // Drop the composite index
            $table->dropIndex(['pole_id', 'status']);

            // Add index for pole_id (since we are keeping the FK)
            $table->index('pole_id');

            // Re-add the foreign key
            $table->foreign('pole_id')->references('id')->on('streelight_poles')->onDelete('cascade');

            // Finally drop the column
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rms_push_logs', function (Blueprint $table) {
            $table->string('status')->nullable()->after('pole_id');
            $table->index(['pole_id', 'status']);
        });
    }
};
