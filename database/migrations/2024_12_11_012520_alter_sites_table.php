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
  if (Schema::hasTable('sites')) {
   Schema::table('sites', function (Blueprint $table) {
    $table->unsignedBigInteger('state')->nullable()->change();
    $table->unsignedBigInteger('district')->nullable()->change();
    $table->unsignedBigInteger('project_id')->nullable()->change();
    $table->unsignedBigInteger('ic_vendor_name')->nullable()->change();
    $table->unsignedBigInteger('site_engineer')->nullable()->change();
    $table->foreign('state')->references('id')->on('states')->onDelete('cascade');
    $table->foreign('district')->references('id')->on('cities')->onDelete('cascade');
    $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
    $table->foreign('ic_vendor_name')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('site_engineer')->references('id')->on('users')->onDelete('cascade');

   });
  } else {
   Schema::create('sites', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('state')->nullable()->change();
    $table->unsignedBigInteger('district')->nullable()->change();
    $table->string('site_name'); // Site name
    $table->string('location');
    $table->string('project_capacity')->nullable(); // Capacity in some unit (e.g., kW)
    $table->string('ca_number')->nullable(); // Consumer Agreement Number
    $table->string('contact_no')->nullable(); // Contact number

    $table->unsignedBigInteger('project_id')->nullable()->change();
    $table->unsignedBigInteger('ic_vendor_name')->nullable()->change();
    $table->unsignedBigInteger('site_engineer')->nullable();
    $table->string('sanction_load')->nullable(); // Sanctioned load
    $table->string('meter_number')->nullable(); // Meter number
    $table->enum('load_enhancement_status', ['Yes', 'No'])->default('No'); // Load enhancement status
    $table->enum('site_survey_status', ['Pending', 'Done'])->default('Pending'); // Survey status
    $table->string('net_meter_sr_no')->nullable(); // Net meter serial number
    $table->string('solar_meter_sr_no')->nullable(); // Solar meter serial number
    $table->date('material_inspection_date')->nullable(); // Inspection date
    $table->date('spp_installation_date')->nullable(); // Solar Power Plant installation date
    $table->date('commissioning_date')->nullable(); // Commissioning date
    $table->text('remarks')->nullable(); // Additional remarks

    $table->timestamps();

    $table->foreign('state')->references('id')->on('states')->onDelete('cascade');
    $table->foreign('district')->references('id')->on('cities')->onDelete('cascade');
    $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
    $table->foreign('ic_vendor_name')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('site_engineer')->references('id')->on('users')->onDelete('cascade');

   });
  }
 }

 /**
  * Reverse the migrations.
  */
 public function down(): void
 {
  Schema::dropIfExists('sites');

 }
};
