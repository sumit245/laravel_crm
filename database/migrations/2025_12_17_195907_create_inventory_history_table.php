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
        Schema::create('inventory_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->enum('inventory_type', ['rooftop', 'streetlight'])->nullable();
            $table->enum('action', ['created', 'dispatched', 'returned', 'replaced', 'consumed', 'locked', 'unlocked']);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('set null');
            $table->integer('quantity_before')->nullable();
            $table->integer('quantity_after')->nullable();
            $table->string('serial_number', 255)->nullable();
            $table->json('metadata')->nullable(); // For storing complete_pole_number, site_id, etc.
            $table->timestamps();

            // Indexes for better query performance
            $table->index('inventory_id');
            $table->index('serial_number');
            $table->index('project_id');
            $table->index('store_id');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_history');
    }
};
