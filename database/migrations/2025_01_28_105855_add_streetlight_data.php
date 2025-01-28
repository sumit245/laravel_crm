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
        //
        Schema::create('streetlights', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('task_id');
            $table->string('state');
            $table->string('district');
            $table->string('block');
            $table->string('panchayat');
            $table->string('ward');
            $table->string('pole');
            $table->string('complete_pole_number')->nullable();
            $table->string('uname');
            $table->string('SID')->unique();
            $table->tinyInteger('district_id');
            $table->tinyInteger('block_id');
            $table->tinyInteger('panchayat_id');
            $table->tinyInteger('ward_id');
            $table->tinyInteger('pole_id');
            $table->string('luminary_qr')->nullable();
            $table->string('battery_qr')->nullable();
            $table->string('panel_qr')->nullable();
            $table->string('file')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('beneficiary')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streetlights');
    }
};
