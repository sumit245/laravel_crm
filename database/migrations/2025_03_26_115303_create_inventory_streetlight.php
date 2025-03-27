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
        Schema::create('inventory_streetlight', function (Blueprint $table) {
            //
            $table->id(); // Auto-incrementing ID
            $table->unsignedBigInteger('project_id'); // Foreign key for project
            $table->unsignedBigInteger('store_id'); // Foreign key for store
            $table->string('item_code'); // Column for item code
            $table->string('item'); // Column for item name
            $table->string('manufacturer'); // Column for manufacturer
            $table->string('make'); // Column for make
            $table->string('model'); // Column for model
            $table->string('serial_number'); // Column for serial number
            $table->string('hsn'); // Column for HSN
            $table->string('unit'); // Column for unit
            $table->decimal('rate', 10, 2); // Column for unit rate
            $table->integer('quantity'); // Column for quantity
            $table->decimal('total_value', 10, 2); // Column for total value
            $table->text('description')->nullable(); // Column for description
            $table->string('eway_bill')->nullable(); // Column for e-way bill
            $table->date('received_date')->nullable(); // Column for received date
            $table->timestamps(); // Created at and updated at timestamps

            // Optional: Add foreign key constraints if necessary
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_streetlight', function (Blueprint $table) {
            //
        });
    }
};
