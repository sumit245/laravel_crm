<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill vendor_id for existing installed poles from their task's vendor_id
        DB::statement("
            UPDATE streelight_poles sp
            INNER JOIN streetlight_tasks st ON sp.task_id = st.id
            SET sp.vendor_id = st.vendor_id
            WHERE sp.isInstallationDone = 1
            AND sp.vendor_id IS NULL
            AND st.vendor_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse backfill operation - data would be lost
        // This is intentional as we want to preserve the vendor_id once set
    }
};
