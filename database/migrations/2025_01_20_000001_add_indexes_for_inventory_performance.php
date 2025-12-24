<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add indexes to optimize inventory queries
     */
    public function up(): void
    {
        // Add indexes to inventory_streetlight for faster queries
        if (Schema::hasTable('inventory_streetlight')) {
            Schema::table('inventory_streetlight', function (Blueprint $table) {
                // Composite index for the most common WHERE clause
                if (!$this->indexExists('inventory_streetlight', 'inventory_streetlight_project_store_idx')) {
                    $table->index(['project_id', 'store_id'], 'inventory_streetlight_project_store_idx');
                }
                // Index on serial_number for JOINs
                if (!$this->indexExists('inventory_streetlight', 'inventory_streetlight_serial_number_idx')) {
                    $table->index('serial_number', 'inventory_streetlight_serial_number_idx');
                }
            });
        }

        // Add indexes to inventory_dispatch for faster JOINs
        if (Schema::hasTable('inventory_dispatch')) {
            Schema::table('inventory_dispatch', function (Blueprint $table) {
                // Composite index for serial_number + isDispatched (used in JOIN condition)
                if (!$this->indexExists('inventory_dispatch', 'inventory_dispatch_serial_dispatched_idx')) {
                    $table->index(['serial_number', 'isDispatched'], 'inventory_dispatch_serial_dispatched_idx');
                }
                // Index on vendor_id for JOINs
                if (!$this->indexExists('inventory_dispatch', 'inventory_dispatch_vendor_id_idx')) {
                    $table->index('vendor_id', 'inventory_dispatch_vendor_id_idx');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_streetlight', function (Blueprint $table) {
            $table->dropIndex('inventory_streetlight_project_store_idx');
            $table->dropIndex('inventory_streetlight_serial_number_idx');
        });

        Schema::table('inventory_dispatch', function (Blueprint $table) {
            $table->dropIndex('inventory_dispatch_serial_dispatched_idx');
            $table->dropIndex('inventory_dispatch_vendor_id_idx');
        });
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        // SQLite doesn't have information_schema; use sqlite_master
        if ($driver === 'sqlite') {
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM sqlite_master WHERE type='index' AND tbl_name = ? AND name = ?",
                [$table, $indexName]
            );
            return isset($result[0]->count) && $result[0]->count > 0;
        }

        // MySQL / MariaDB: use information_schema
        if ($driver === 'mysql') {
            $databaseName = $connection->getDatabaseName();
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$databaseName, $table, $indexName]
            );
            return isset($result[0]->count) && $result[0]->count > 0;
        }

        // Fallback: assume index doesn't exist
        return false;
    }
};

