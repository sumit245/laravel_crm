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
  //
  if (Schema::hasTable('projects')) {
   Schema::table('projects', function (Blueprint $table) {
    $table->decimal('project_capacity', 10, 2);
    $table->date('end_date');
    $table->string('description');
    $table->decimal('rate', 10, 2)->change();
    $table->decimal('total', 10, 2);
   });

  }

 }

 /**
  * Reverse the migrations.
  */
 public function down(): void
 {
  //
 }
};
