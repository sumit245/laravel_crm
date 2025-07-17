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
            $fields = [
                'drawing_approval',
                'inspection',
                'material_supplied',
                'structure_installation',
                'structure_foundation',
                'pv_module_installation',
                'inverter_installation',
                'dcdb_acdb_installaation',
                'dc_cabelling',
                'ac_cabelling',
                'ac_cable_termination',
                'dc_earthing',
                'ac_earthing',
                'lighntning_arrestor',
                'remote_monitoring_unit',
                'fire_safety',
                'net_meter_registration',
                'meter_installaton_commission',
                'performance_guarantee_test',
                'handover_status',
            ];

            foreach ($fields as $field) {
                $table->enum($field, ['yes', 'no'])->default('no')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $fields = [
                'drawing_approval',
                'inspection',
                'material_supplied',
                'structure_installation',
                'structure_foundation',
                'pv_module_installation',
                'inverter_installation',
                'dcdb_acdb_installaation',
                'dc_cabelling',
                'ac_cabelling',
                'ac_cable_termination',
                'dc_earthing',
                'ac_earthing',
                'lighntning_arrestor',
                'remote_monitoring_unit',
                'fire_safety',
                'net_meter_registration',
                'meter_installaton_commission',
                'performance_guarantee_test',
                'handover_status',
            ];

            foreach ($fields as $field) {
                $table->dropColumn($field);
            }
        });
    }
};
