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
  if (Schema::hasTable('sites')) {
   Schema::table('sites', function (Blueprint $table) {
    $table->unsignedBigInteger('project_id'); // Foreign key to projects

   });
  }
  Schema::create('sites', function (Blueprint $table) {
   $table->id();
   $table->unsignedBigInteger('project_id'); // Foreign key to projects
   $table->string('site_name'); // Site name
   $table->string('state');
   $table->string('district'); // Changed `dist` to `district` for better clarity
   $table->string('location');
   $table->string('project_capacity')->nullable(); // Capacity in some unit (e.g., kW)
   $table->string('ca_number')->nullable(); // Consumer Agreement Number
   $table->string('contact_no')->nullable(); // Contact number
   $table->string('ic_vendor_name')->nullable(); // Vendor name
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

   // Foreign key constraint
   $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
  });
 }

 /**
  * Reverse the migrations.
  */
 public function down(): void
 {
  Schema::dropIfExists('sites');
 }
};
