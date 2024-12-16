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
  Schema::table('tasks', function (Blueprint $table) {
   //
   $table->unsignedBigInteger('vendor_id')->nullable()->change(); // Foreign key for stores
   $table->unsignedBigInteger('project_id')->nullable()->change(); // Foreign key for stores
   $table->unsignedBigInteger('site_id')->nullable()->change(); // Foreign key for stores
   $table->string('activity')->nullable(); // Foreign key for stores

   $table->unsignedBigInteger('engineer_id')->nullable(); // Foreign key for stores

  });
 }

 /**
  * Reverse the migrations.
  */
 public function down(): void
 {
  Schema::table('tasks', function (Blueprint $table) {
   //
  });
 }
};
