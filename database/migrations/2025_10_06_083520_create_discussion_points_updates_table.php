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
        Schema::create('discussion_points_updates', function (Blueprint $table) {
            //
            $table->id();
            $table->foreignId('discussion_point_id')->constrained('discussion_points')->onDelete('cascade');
            $table->text('update_text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discussion_points_updates', function (Blueprint $table) {
            //
        });
    }
};
