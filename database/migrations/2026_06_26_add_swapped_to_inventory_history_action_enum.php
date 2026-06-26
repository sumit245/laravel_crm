<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds 'swapped' to the inventory_history.action enum.
 *
 * InventoryController::swapInventory() already tries to log action='swapped' but
 * the enum only declared 7 values — the insert was silently failing on every swap.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE inventory_history MODIFY COLUMN action
            ENUM('created','dispatched','returned','replaced','consumed','locked','unlocked','swapped')
            NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE inventory_history MODIFY COLUMN action
            ENUM('created','dispatched','returned','replaced','consumed','locked','unlocked')
            NOT NULL");
    }
};
