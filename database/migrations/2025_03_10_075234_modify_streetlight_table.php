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
        // Schema::table('streetlights', function (Blueprint $table) {
        //
        // $table->dropColumn([
        //     'isNetworkAvailable',
        //     'isInstallationDone',
        //     'complete_pole_number',
        //     'uname',
        //     'district_id',
        //     'pole_id',
        //     'block_id',
        //     'panchayat_id',
        //     'ward_id',
        //     'luminary_qr',
        //     'battery_qr',
        //     'panel_qr',
        //     'lat',
        //     'lng',
        //     'remark'
        // ]);
        // });
        // For MariaDB, we need to change column instead of rename
        Schema::table('streetlights', function (Blueprint $table) {
            $table->string('total_poles')->nullable();
            $table->dropColumn('pole');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('streetlight', function (Blueprint $table) {
            //
        });
    }
};
