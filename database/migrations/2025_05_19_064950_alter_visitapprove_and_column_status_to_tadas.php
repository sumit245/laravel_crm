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
            
            if (!Schema::hasColumn('tadas', 'visit_approve')) {
            $table->boolean('visit_approve')->nullable()->default(null);
        } else {
            // If it exists, you might want to modify it instead
            $table->boolean('visit_approve')->nullable()->default(null)->change();
        }

            $table->boolean('status')->nullable()->default(null)->after('visit_approve');
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
