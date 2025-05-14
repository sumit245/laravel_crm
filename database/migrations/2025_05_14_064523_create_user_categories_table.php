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
        Schema::create('user_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_code', 255)->nullable(); // category_code, nullable
            $table->string('name', 100)->nullable(); // name, nullable
            $table->string('description', 255)->nullable(); // description, nullable
            $table->json('allowed_vehicles')->nullable(); // allowed_vehicles, JSON type
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_categories');
    }
};
