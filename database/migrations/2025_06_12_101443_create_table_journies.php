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
        Schema::create('journies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tada_id')->nullable();
            $table->boolean('tickets_provided_by_company')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('ticket')->nullable();
            $table->string('mode_of_transport')->nullable();
            $table->date('date_of_journey')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
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
        Schema::dropIfExists('table_journies');
    }
};
