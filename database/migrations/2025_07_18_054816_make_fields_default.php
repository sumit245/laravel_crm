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
       // Only attempt to alter columns when the table exists and the current driver supports it
        if (Schema::hasTable('sites')) {
            $driver = Schema::getConnection()->getDriverName();
            // SQLite in-memory used during tests doesn't support column changes without doctrine/dbal
            if ($driver === 'sqlite') {
                // Skip column changes for sqlite testing environment
                return;
            }

            Schema::table('sites', function (Blueprint $table) {
                $table->string('breda_sl_no')->nullable()->change();
                $table->unsignedBigInteger('project_id')->nullable()->change();
                $table->string('site_name')->nullable()->change();
                $table->unsignedBigInteger('state')->nullable()->change();
                $table->unsignedBigInteger('district')->nullable()->change();
                $table->string('division', 50)->nullable()->change();
                $table->string('location')->nullable()->change();
                $table->string('project_capacity')->nullable()->change();
                $table->string('ca_number')->nullable()->change();
                $table->string('contact_no')->nullable()->change();
                $table->unsignedBigInteger('ic_vendor_name')->nullable()->change();
                $table->string('sanction_load')->nullable()->change();
                $table->string('meter_number')->nullable()->change();
                $table->enum('load_enhancement_status', ['Yes', 'No'])->default('No')->nullable()->change();
                $table->enum('site_survey_status', ['Pending', 'Done'])->default('Pending')->nullable()->change();
                $table->string('net_meter_sr_no')->nullable()->change();
                $table->string('bts_department_name', 50)->nullable()->change();
                $table->string('solar_meter_sr_no')->nullable()->change();
                $table->date('material_inspection_date')->nullable()->change();
                $table->date('spp_installation_date')->nullable()->change();
                $table->date('commissioning_date')->nullable()->change();
                $table->text('remarks')->nullable()->change();
                $table->unsignedBigInteger('site_engineer')->nullable()->change();
                $table->decimal('survey_latitude', 10, 7)->nullable()->change();
                $table->decimal('survey_longitude', 10, 7)->nullable()->change();
                $table->decimal('actual_latitude', 10, 7)->nullable()->change();
                $table->decimal('actual_longitude', 10, 7)->nullable()->change();
                $table->string('installation_status', 6)->nullable()->change();
            });
        }

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
