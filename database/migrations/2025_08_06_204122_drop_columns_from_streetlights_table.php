<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This will drop old columns and add the new ones.
     */
    public function up(): void
    {
        Schema::table('streetlights', function (Blueprint $table) {
            // Drop the specified old columns
            $table->dropColumn([
                'complete_pole_number',
                'uname',
                'ward_id',
                'district_id',
                'block_id',
                'panchayat_id',
                'luminary_qr',
                'battery_qr',
                'panel_qr',
                'file',
                'lat',
                'lng',
                'remark',
                'SID'
            ]);

            // Add the new columns with specified data types
            // Note: addColumn() is not a valid method. You must define each column.
            $table->string('district_code')->nullable()->after('id');
            $table->string('block_code')->nullable()->after('district_code');
            $table->string('panchayat_code')->nullable()->after('block_code');
            $table->string('ward_type')->nullable()->after('panchayat_code');
        });
    }

    /**
     * Reverse the migrations.
     * This will drop the new columns and restore the old ones.
     */
    public function down(): void
    {
        Schema::table('streetlights', function (Blueprint $table) {
            // First, drop the new columns that were added in the up() method
            $table->dropColumn([
                'district_code',
                'block_code',
                'panchayat_code',
                'ward_type'
            ]);

            // Next, re-add the columns that were dropped in the up() method.
            // It's best practice to define them as they were originally.
            // The following are reasonable assumptions for the column types.
            $table->string('complete_pole_number')->nullable();
            $table->string('uname')->nullable();
            $table->unsignedBigInteger('ward_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('block_id')->nullable();
            $table->unsignedBigInteger('panchayat_id')->nullable();
            $table->string('luminary_qr')->nullable();
            $table->string('battery_qr')->nullable();
            $table->string('panel_qr')->nullable();
            $table->string('file')->nullable();
            $table->string('lat')->nullable(); // Or ->decimal('lat', 10, 7)->nullable();
            $table->string('lng')->nullable(); // Or ->decimal('lng', 10, 7)->nullable();
            $table->text('remark')->nullable();
            $table->string('SID')->nullable();
        });
    }
};
