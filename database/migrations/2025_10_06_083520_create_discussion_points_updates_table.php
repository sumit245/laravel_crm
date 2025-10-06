<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('discussion_points_updates', function (Blueprint $table) {
            //
            $table->id();
            $table->foreignId('discussion_point_id')->constrained('discussion_points')->onDelete('cascade');
            $table->text('update_text')->comment('The main content of the update or note.');
            $table->text('vertical_head_remark')->nullable();
            $table->text('admin_remark')->nullable();
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
