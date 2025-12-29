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
        Schema::create('target_deletion_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->unique(); // UUID for job identification
            $table->json('task_ids'); // Array of task IDs to delete
            $table->json('processed_task_ids')->default('[]'); // Track progress
            $table->integer('total_tasks');
            $table->integer('processed_tasks')->default(0);
            $table->integer('total_poles')->default(0);
            $table->integer('processed_poles')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'paused'])->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('user_id'); // Who initiated the deletion
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('job_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_deletion_jobs');
    }
};
