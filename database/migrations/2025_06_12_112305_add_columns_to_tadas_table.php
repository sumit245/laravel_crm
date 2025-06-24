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
        Schema::table('tadas', function (Blueprint $table) {
            // Drop old columns
            // $table->dropColumn(['objective_tour', 'outcome_achieve']);

            // Add new columns
            // $table->string('visiting_to')->nullable()->after('outcome_achieve');
            // $table->string('purpose_of_visit')->nullable()->after('visiting_to');
            // $table->string('outcome_achieved')->nullable()->after('purpose_of_visit');
            // $table->date('date_of_departure')->nullable()->after('outcome_achieved');
            // $table->date('date_of_return')->nullable()->after('date_of_departure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tadas', function (Blueprint $table) {
            //
        });
    }
};
