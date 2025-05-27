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
        Schema::create('travelfares', function (Blueprint $table) {
            $table->id();
            $table->string('from');
            $table->string('to');
            $table->date('departure_date');
            $table->time('departure_time');
            $table->date('arrival_date');
            $table->time('arrival_time');
            $table->string('modeoftravel');
            $table->float('amount', 8, 2);
            $table->unsignedBigInteger('tada_id'); // Reference to tadas table
            $table->foreign('tada_id')->references('id')->on('tadas')->onDelete('cascade');
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travelfares');
    }
};
