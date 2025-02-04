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
        Schema::table('streetlights', function (Blueprint $table) {
            //
            $table->string('ward')->nullable()->change();
            $table->string('pole')->nullable()->change();
            $table->string('complete_pole_number')->nullable()->change();
            $table->string('uname')->nullable()->change();
            $table->string('SID')->nullable()->change();
            $table->tinyInteger('district_id')->nullable()->change();
            $table->tinyInteger('block_id')->nullable()->change();
            $table->tinyInteger('panchayat_id')->nullable()->change();
            $table->tinyInteger('ward_id')->nullable()->change();
            $table->tinyInteger('pole_id')->nullable()->change();
            $table->string('luminary_qr')->nullable()->change();
            $table->string('battery_qr')->nullable()->change();
            $table->string('panel_qr')->nullable()->change();
            $table->string('file')->nullable()->change();
            $table->decimal('lat', 10, 7)->nullable()->change();
            $table->decimal('lng', 10, 7)->nullable()->change();
            $table->string('beneficiary')->nullable()->change();
            $table->text('remark')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('streetlights', function (Blueprint $table) {
            $table->string('ward')->nullable(false)->change();
            $table->string('pole')->nullable(false)->change();
            $table->string('complete_pole_number')->nullable(false)->change();
            $table->string('uname')->nullable(false)->change();
            $table->string('SID')->nullable(false)->change();
            $table->tinyInteger('district_id')->nullable(false)->change();
            $table->tinyInteger('block_id')->nullable(false)->change();
            $table->tinyInteger('panchayat_id')->nullable(false)->change();
            $table->tinyInteger('ward_id')->nullable(false)->change();
            $table->tinyInteger('pole_id')->nullable(false)->change();
            $table->string('luminary_qr')->nullable(false)->change();
            $table->string('battery_qr')->nullable(false)->change();
            $table->string('panel_qr')->nullable(false)->change();
            $table->string('file')->nullable(false)->change();
            $table->decimal('lat', 10, 7)->nullable(false)->change();
            $table->decimal('lng', 10, 7)->nullable(false)->change();
            $table->string('beneficiary')->nullable(false)->change();
            $table->text('remark')->nullable(false)->change();
        });
    }
};
