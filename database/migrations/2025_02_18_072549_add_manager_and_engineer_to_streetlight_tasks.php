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
            // Add manager_id and engineer_id columns
            $table->unsignedBigInteger('manager_id')->nullable()->after('vendor_id');


            // Optionally, add foreign key constraints if manager and engineer are users
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('streetlight_tasks', function (Blueprint $table) {
            // Remove manager_id and engineer_id columns
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['engineer_id']);
            $table->dropColumn(['manager_id', 'engineer_id']);
        });
    }
};
