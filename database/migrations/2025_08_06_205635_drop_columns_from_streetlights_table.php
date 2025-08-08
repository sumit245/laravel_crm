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
        Schema::table('streetlights', function (Blueprint $table) {
            //
            $table->dropColumn([
                'isSurveyDone',
                'isNetworkAvailable',
<<<<<<< HEAD
                'isInstallationDone'
=======
                'isInstallationDone',
                // 'pole_id'
>>>>>>> 4f2e9ee6d4295205f1bab72d37ad5891e37d1395
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('streetlights', function (Blueprint $table) {
            //
        });
    }
};
