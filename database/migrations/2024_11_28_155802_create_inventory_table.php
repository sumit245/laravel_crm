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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('productName');
            $table->string('brand');
            $table->string('description');
            $table->string('unit');
            $table->string('initialQuantity')->nullable();
            $table->string('quantityStock')->nullable();
            $table->date('materialDispatchDate')->nullable();
            $table->date('deliveryDate')->nullable();
            $table->date('receivedDate')->nullable();
            $table->string('allocationOfficer')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
            // Optional foreign keys
            $table->unsignedBigInteger('project_id')->nullable(); // Foreign key to projects table
            $table->unsignedBigInteger('site_id')->nullable(); // Foreign key to sites table
            // Define foreign key constraints
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
