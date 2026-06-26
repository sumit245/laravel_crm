<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE inventory_history MODIFY COLUMN action
            ENUM('created','dispatched','returned','replaced','consumed','locked','unlocked','swapped','transferred') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE inventory_history MODIFY COLUMN action
            ENUM('created','dispatched','returned','replaced','consumed','locked','unlocked','swapped') NOT NULL");
    }
};
