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
        Schema::create('streelight_poles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->boolean('isSurveyDone')->default(false);
            $table->boolean('isNetworkAvailable')->default(false);
            $table->boolean('isInstallationDone')->default(false);
            $table->string('complete_pole_number')->nullable();
            $table->string('luminary_qr')->nullable();
            $table->string('battery_qr')->nullable();
            $table->string('panel_qr')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('file')->nullable(); // Survey/Installation images
            $table->timestamps();
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streetlight_poles');
    }
};
