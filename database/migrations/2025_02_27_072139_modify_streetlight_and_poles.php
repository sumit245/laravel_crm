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
        // Schema::table('streelight_poles', function (Blueprint $table) {
        //     $table->string('beneficiary')->nullable()->after('isSurveyDone');
        //     $table->string('remarks')->nullable()->after('beneficiary');
        // });

        Schema::table('streetlights', function (Blueprint $table) {
            $table->dropColumn('beneficiary');
            $table->dropColumn('remarks');
            $table->renameColumn('pole', 'number_of_poles'); // ✅ Rename column
            $table->integer('number_of_surveyed_poles')->default(0)->after('pole');
            $table->integer('number_of_installed_poles')->default(0)->after('number_of_surveyed_poles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
