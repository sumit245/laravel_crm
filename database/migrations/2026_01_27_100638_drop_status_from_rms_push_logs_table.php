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
        Schema::table('rms_push_logs', function (Blueprint $table) {
            $table->dropIndex(['pole_id', 'status']);
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rms_push_logs', function (Blueprint $table) {
            $table->string('status')->nullable()->after('pole_id');
            $table->index(['pole_id', 'status']);
        });
    }
};
