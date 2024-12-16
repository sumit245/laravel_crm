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
  Schema::table('inventory', function (Blueprint $table) {
   //
   $table->unsignedBigInteger('store_id')->nullable()->after('id'); // Foreign key for stores
   $table->unsignedBigInteger('project_id')->nullable()->after('store_id'); // Foreign key for projects

// Adding foreign key constraints (optional but recommended)
   $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
   $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');

  });
 }

 /**
  * Reverse the migrations.
  */
 public function down(): void
 {
  Schema::table('inventory', function (Blueprint $table) {
   //
  });
 }
};
