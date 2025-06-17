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
        Schema::table('user_categories', function (Blueprint $table) {
            //
            $table->dropForeign(['city_category_id']);
            $table->dropColumn('city_category_id');
            $table->enum('city_category', ['0', '1', '2'])->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usercategory', function (Blueprint $table) {
            //
        });
    }
};
