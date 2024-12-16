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
   $table->string('task_name')->nullable()->change(); // Foreign key for stores

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
