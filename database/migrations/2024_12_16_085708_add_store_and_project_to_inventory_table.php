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
   // Adding foreign key constraints (optional but recommended)
   $table->string('category')->nullable()->after('store_id'); // Foreign key for stores
   $table->string('sub_category')->nullable()->after('category'); // Foreign key for stores
   $table->decimal('total', 10, 2)->nullable(); // Foreign key for stores

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
