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
        Schema::create('hotelexpenses', function (Blueprint $table) {
            $table->id();
            $table->boolean('guest_house_available')->nullable();
            $table->unsignedBigInteger('tada_id')->nullable();
            $table->string('certificate_by_district_incharge')->nullable();
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->boolean('breakfast_included')->nullable();
            $table->string('hotel_bill')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('dining_cost', 10, 2)->nullable();
            $table->foreign('tada_id')
                    ->references('id')
                    ->on('tadas')
                    ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_hotelexpenses');
    }
};
