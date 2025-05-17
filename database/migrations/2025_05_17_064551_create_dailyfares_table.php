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
        Schema::create('dailyfares', function (Blueprint $table) {
            $table->id();
            $table->string('place');
            $table->string('HotelBillNo');
            $table->date('date_of_stay');
            $table->float('amount', 8, 2);
             // Foreign key to tadas table
            $table->unsignedBigInteger('tada_id');
            $table->foreign('tada_id')->references('id')->on('tadas')->onDelete('cascade');
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dailyfares');
    }
};
