<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE streetlight_tasks MODIFY status ENUM('Pending', 'In Progress', 'Blocked', 'Completed') NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE streetlight_tasks MODIFY status ENUM('Pending', 'Completed') NOT NULL DEFAULT 'Pending'");
    }
};
