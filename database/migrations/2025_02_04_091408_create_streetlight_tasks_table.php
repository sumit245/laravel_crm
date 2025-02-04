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
        Schema::create('streetlight_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained('streetlights')->onDelete('cascade');
            $table->foreignId('engineer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('vendor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['Pending', 'Completed'])->default('Pending'); // Status
            $table->string('approved_by')->nullable();
            $table->json('materials_consumed')->nullable(); // JSON or comma-separated materials
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streetlight_tasks');
    }
};
