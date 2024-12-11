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
  Schema::create('stores', function (Blueprint $table) {
   $table->id();
   $table->unsignedBigInteger('project_id')->nullable();
   $table->string('store_name')->nullable();
   $table->string('address')->nullable();
   $table->unsignedBigInteger('store_incharge_id')->nullable();
   $table->timestamps();

   $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
   $table->foreign('store_incharge_id')->references('id')->on('users')->onDelete('cascade');

  });
 }

 /**
  * Reverse the migrations.
  */
 public function down(): void
 {
  Schema::dropIfExists('stores');
 }
};
