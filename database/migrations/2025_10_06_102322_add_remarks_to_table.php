<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('discussion_points_updates', function (Blueprint $table) {
            //
            // TODO: add two columns one for remarks by vertical head and other for remark by admin
            // Add two new nullable text columns for remarks
            $table->text('vertical_head_remark')->nullable()->after('update_text');
            $table->text('admin_remark')->nullable()->after('vertical_head_remark');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discussion_points_updates', function (Blueprint $table) {
            //
        });
    }
};
