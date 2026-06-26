<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a composite index on (store_id, project_id, isDispatched) to inventory_dispatch.
 *
 * The GROUP_CONCAT subquery in StoreController::show() and inventoryData() filters by
 * exactly these three columns before grouping by serial_number. Without this index MySQL
 * does a full table scan. The leading store_id/project_id columns narrow the working set
 * to a single store before the GROUP BY and GROUP_CONCAT sort runs.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_dispatch')) {
            return;
        }

        Schema::table('inventory_dispatch', function (Blueprint $table) {
            $table->index(
                ['store_id', 'project_id', 'isDispatched'],
                'idx_dispatch_store_project_dispatched'
            );
        });
    }

    public function down(): void
    {
        Schema::table('inventory_dispatch', function (Blueprint $table) {
            $table->dropIndex('idx_dispatch_store_project_dispatched');
        });
    }
};
