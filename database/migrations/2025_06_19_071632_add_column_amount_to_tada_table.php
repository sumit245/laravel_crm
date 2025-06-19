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
            //
            $table->dropForeign(['journies_id']);
            $table->dropForeign(['hotel_id']);
            $table->dropColumn('journies_id');
            $table->dropColumn('hotel_id');
            $table->decimal('amount', 10, 2)->nullable()->after('miscellaneous');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tada', function (Blueprint $table) {
            //
        });
    }
};
