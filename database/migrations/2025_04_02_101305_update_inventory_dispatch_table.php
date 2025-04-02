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
        Schema::table('inventory_dispatch', function (Blueprint $table) {
            //Remove old columns
            $table->dropColumn(['inventory_id', 'quantity']);

             // Add new columns
             $table->string('item_code')->after('vendor_id');
             $table->string('item')->after('item_code');
             $table->integer('total_quantity')->after('item');
             $table->decimal('rate', 10, 2)->after('total_quantity'); // 10 digits, 2 decimal places
             $table->string('make')->nullable()->after('rate');
             $table->string('model')->nullable()->after('make');
             $table->json('serial_number')->nullable()->after('model'); // JSON column for multiple values
         
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_dispatch', function (Blueprint $table) {
            //
        });
    }
};
