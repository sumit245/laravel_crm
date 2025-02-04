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
        Schema::table('streetlights', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable(); // Add project_id field
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade'); // Foreign key constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('streetlights', function (Blueprint $table) {
            $table->dropForeign(['project_id']); // Drop the foreign key
            $table->dropColumn('project_id'); // Drop the project_id column
        });
    }
};
