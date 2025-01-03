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
  Schema::table('sites', function (Blueprint $table) {
   //
   $table->decimal('survey_latitude', 10, 7)->nullable();
   $table->decimal('survey_longitude', 10, 7)->nullable();
   $table->decimal('actual_latitude', 10, 7)->nullable();
   $table->decimal('actual_longitude', 10, 7)->nullable();

  });
 }

 /**
  * Reverse the migrations.
  */
 public function down(): void
 {
  Schema::table('sites', function (Blueprint $table) {
   //
  });
 }
};
