<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('district_codes', function (Blueprint $table) {
            $table->id();
            $table->integer('district_code')->unique();
            $table->string('district_name');
            $table->timestamps();
        });
        // Insert district data
        DB::table('district_codes')->insert([
            ['district_code' => 209, 'district_name' => 'ARARIA'],
            ['district_code' => 240, 'district_name' => 'ARWAL'],
            ['district_code' => 235, 'district_name' => 'AURANGABAD'],
            ['district_code' => 225, 'district_name' => 'BANKA'],
            ['district_code' => 222, 'district_name' => 'BEGUSARAI'],
            ['district_code' => 224, 'district_name' => 'BHAGALPUR'],
            ['district_code' => 231, 'district_name' => 'BHOJPUR'],
            ['district_code' => 232, 'district_name' => 'BUXAR'],
            ['district_code' => 215, 'district_name' => 'DARBHANGA'],
            ['district_code' => 236, 'district_name' => 'GAYA'],
            ['district_code' => 217, 'district_name' => 'GOPALGANJ'],
            ['district_code' => 238, 'district_name' => 'JAMUI'],
            ['district_code' => 239, 'district_name' => 'JEHANABAD'],
            ['district_code' => 233, 'district_name' => 'KAIMUR'],
            ['district_code' => 212, 'district_name' => 'KATIHAR'],
            ['district_code' => 223, 'district_name' => 'KHAGARIA'],
            ['district_code' => 210, 'district_name' => 'KISHANGANJ'],
            ['district_code' => 227, 'district_name' => 'LAKHISARAI'],
            ['district_code' => 213, 'district_name' => 'MADHEPURA'],
            ['district_code' => 207, 'district_name' => 'MADHUBANI'],
            ['district_code' => 226, 'district_name' => 'MUNGER'],
            ['district_code' => 216, 'district_name' => 'MUZAFFARPUR'],
            ['district_code' => 229, 'district_name' => 'NALANDA'],
            ['district_code' => 237, 'district_name' => 'NAWADA'],
            ['district_code' => 203, 'district_name' => 'PASCHIM CHAMPARAN'],
            ['district_code' => 230, 'district_name' => 'PATNA'],
            ['district_code' => 204, 'district_name' => 'PURBI CHAMPARAN'],
            ['district_code' => 211, 'district_name' => 'PURNIA'],
            ['district_code' => 234, 'district_name' => 'ROHTAS'],
            ['district_code' => 214, 'district_name' => 'SAHARSA'],
            ['district_code' => 221, 'district_name' => 'SAMASTIPUR'],
            ['district_code' => 219, 'district_name' => 'SARAN'],
            ['district_code' => 228, 'district_name' => 'SHEIKHPURA'],
            ['district_code' => 205, 'district_name' => 'SHEOHAR'],
            ['district_code' => 206, 'district_name' => 'SITAMARHI'],
            ['district_code' => 218, 'district_name' => 'SIWAN'],
            ['district_code' => 208, 'district_name' => 'SUPAUL'],
            ['district_code' => 220, 'district_name' => 'VAISHALI'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('district_codes');
    }
};
