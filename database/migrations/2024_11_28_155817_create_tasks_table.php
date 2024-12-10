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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            // Foreign key to projects table
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            // Foreign key to sites table
            $table->unsignedBigInteger('site_id');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');

            // Foreign key to users table for vendor
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('task_name'); // Task name
            $table->enum('status', ['Pending', 'In Progress', 'Completed'])->default('Pending'); // Status
            $table->date('start_date')->nullable(); // Task start date
            $table->date('end_date')->nullable(); // Task end date
            $table->string('description')->nullable(); // Task description
            $table->string('approved_by')->nullable();
            $table->string('image')->nullable(); // Path to the uploaded picture
            $table->text('materials_consumed')->nullable(); // JSON or comma-separated materials

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
