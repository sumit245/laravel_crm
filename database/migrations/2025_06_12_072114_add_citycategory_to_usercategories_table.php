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
            $table->unsignedBigInteger('city_category_id')->nullable()->after('description');

            $table->foreign('city_category_id')
                  ->references('id')
                  ->on('cities')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usercategories', function (Blueprint $table) {
            //
        });
    }
};
