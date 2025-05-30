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
        Schema::create('tadas', function (Blueprint $table) {
            $table->id();

            // New fields
            // $table->string('name', 100);
            // $table->string('department', 100);
            // $table->string('employee_id', 100);
            $table->unsignedBigInteger('user_id');
            $table->string('visit_approve', 50);
            $table->string('objective_tour', 255);
            $table->string('meeting_visit', 255);
            $table->string('outcome_achieve', 255);
            // consider renaming to 'designation'
            $table->date('start_journey');
            $table->date('end_journey');
            $table->string('transport', 50);
            $table->string('start_journey_pnr', 150);
            $table->string('from_city', 100);
            $table->string('to_city', 100);
            $table->string('end_journey_pnr', 150);
            $table->integer('total_km');
            $table->integer('rate_per_km');
            $table->integer('Rent');
            $table->string('vehicle_no', 100);
            $table->string('category', 100);
            $table->text('description_category');
            $table->date('pickup_date');
            
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
