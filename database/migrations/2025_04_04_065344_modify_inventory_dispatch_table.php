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
        Schema::table('inventory_dispatch', function (Blueprint $table) {
            $table->boolean('is_consumed')->default(false)->after('isDispatched');
            $table->unsignedBigInteger('streetlight_pole_id')->nullable()->after('is_consumed');

            // Foreign key constraint
            $table->foreign('streetlight_pole_id')
                ->references('id')
                ->on('streetlight_poles')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('inventory_dispatch', function (Blueprint $table) {
            //
            $table->dropForeign(['streetlight_pole_id']);
            $table->dropColumn(['is_consumed', 'streetlight_pole_id']);
        });
    }
};
