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
//    $table->decimal('rate', 10, 2)->nullable(); // Foreign key for stores

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
