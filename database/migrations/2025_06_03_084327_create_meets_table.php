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
        Schema::create('meets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('agenda')->nullable();
            $table->string('meet_link');
            $table->enum('platform', ['Google Meet', 'Zoom', 'Teams', 'Other']);
            $table->date('meet_date');
            $table->time('meet_time');
            $table->enum('type', ['Review', 'Planning', 'Discussion']);
            $table->json('user_ids'); // Store selected user IDs as JSON
            $table->text('notes')->nullable(); // For outcomes/whiteboard content
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meets');
    }
};
