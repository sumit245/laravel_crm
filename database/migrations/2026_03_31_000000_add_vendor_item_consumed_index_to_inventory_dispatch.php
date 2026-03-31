<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a composite index on (vendor_id, item_code, is_consumed) to inventory_dispatch.
 *
 * This directly supports the viewVendorInventory endpoint which:
 *   1. Aggregates COUNT(*) grouped by (vendor_id, item_code, is_consumed)
 *   2. Chunks dispatch rows filtered by (vendor_id, item_code, is_consumed)
 *
 * Without this index both queries do a full table scan, which is fatal at scale.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_dispatch')) {
            return;
        }

        Schema::table('inventory_dispatch', function (Blueprint $table) {
            // Covering index for the aggregate + chunk queries
            $table->index(
                ['vendor_id', 'item_code', 'is_consumed'],
                'idx_dispatch_vendor_item_consumed'
            );
        });
    }

    public function down(): void
    {
        Schema::table('inventory_dispatch', function (Blueprint $table) {
            $table->dropIndex('idx_dispatch_vendor_item_consumed');
        });
    }
};
