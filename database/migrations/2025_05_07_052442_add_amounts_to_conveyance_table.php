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
        Schema::table('conveyances', function (Blueprint $table) {
            //
            $table->decimal('amount', 10, 2)->nullable()->after('kilometer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conveyance', function (Blueprint $table) {
            //
        });
    }
};
