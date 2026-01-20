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
        Schema::create('rms_push_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pole_id');
            $table->string('status'); // 'success' or 'error'
            $table->text('message')->nullable();
            $table->text('response_data')->nullable(); // JSON response from RMS API
            $table->string('district')->nullable();
            $table->string('block')->nullable();
            $table->string('panchayat')->nullable();
            $table->unsignedBigInteger('pushed_by')->nullable(); // User who pushed
            $table->timestamp('pushed_at');
            $table->timestamps();

            $table->foreign('pole_id')->references('id')->on('streelight_poles')->onDelete('cascade');
            $table->foreign('pushed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['pole_id', 'status']);
            $table->index(['district', 'block', 'panchayat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rms_push_logs');
    }
};
