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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Who performed the action (nullable for system/guest events)
            $table->unsignedBigInteger('user_id')->nullable();

            // Optional project scope for faster filtering
            $table->unsignedBigInteger('project_id')->nullable();

            // High-level classification
            $table->string('module', 64); // e.g. auth, project, site, task, inventory, pole, billing, rms, hrm
            $table->string('action', 64); // e.g. login, created, updated, deleted, imported, exported

            // Optional polymorphic target of the event
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();

            // Human-readable summary
            $table->string('description', 255)->nullable();

            // Structured payloads
            $table->json('changes')->nullable(); // before/after diffs
            $table->json('extra')->nullable();   // module-specific metadata

            // Request/environment metadata
            $table->string('ip_address', 45)->nullable(); // IPv4 / IPv6
            $table->text('user_agent')->nullable();
            $table->string('request_id', 100)->nullable();
            $table->string('batch_id', 100)->nullable();

            $table->timestamps();

            // Indexes for common filters
            $table->index(['entity_type', 'entity_id', 'created_at'], 'activity_logs_entity_created_at_index');
            $table->index(['user_id', 'created_at'], 'activity_logs_user_created_at_index');
            $table->index(['module', 'action', 'created_at'], 'activity_logs_module_action_created_at_index');
            $table->index(['project_id', 'created_at'], 'activity_logs_project_created_at_index');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
