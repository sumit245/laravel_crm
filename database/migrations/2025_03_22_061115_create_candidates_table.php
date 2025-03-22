<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('date_of_offer')->nullable();
            $table->string('address')->nullable();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->date('doj')->nullable();
            $table->string('ctc')->nullable();
            $table->integer('experience')->nullable();
            $table->decimal('last_salary', 10, 2)->nullable();
            $table->string('document_path')->nullable();
            $table->string('status')->default('pending'); // pending, emailed, hired, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
