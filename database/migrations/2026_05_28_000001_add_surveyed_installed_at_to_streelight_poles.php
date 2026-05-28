<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('streelight_poles', function (Blueprint $table) {
            $table->timestamp('surveyed_at')->nullable()->after('isSurveyDone');
            $table->timestamp('installed_at')->nullable()->after('isInstallationDone');
        });
    }

    public function down(): void
    {
        Schema::table('streelight_poles', function (Blueprint $table) {
            $table->dropColumn(['surveyed_at', 'installed_at']);
        });
    }
};
