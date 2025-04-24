<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('streelight_poles', function (Blueprint $table) {
            //
            // Modify status to ENUM
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->change();

            // Add new nullable boolean column
            $table->boolean('isRMSConnected')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('streelight_poles', function (Blueprint $table) {
            //
            // Revert status to string (assuming it was string originally)
            $table->string('status')->change();

            // Drop the new column
            $table->dropColumn('isRMSConnected');
        });
    }
};
