<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rms_sync_batches', function (Blueprint $table) {
            $table->id();
            $table->string('source')->nullable();
            $table->json('scope')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('queued');
            $table->unsignedInteger('total_poles')->default(0);
            $table->unsignedInteger('processed_poles')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('requested_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rms_sync_batches');
    }
};

