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
  //
  Schema::table('users', function (Blueprint $table) {
   $table->string('accountName')->nullable()->after('role');
   $table->string('accountNumber')->nullable()->after('accountName');
   $table->string('ifsc')->nullable()->after('accountNumber');
   $table->string('bankName')->nullable()->after('ifsc');
   $table->string('branch')->nullable()->after('bankName');
   $table->string('gstNumber')->nullable()->after('branch');
   $table->string('pan')->nullable()->after('gstNumber');
   $table->string('aadharNumber')->nullable()->after('pan');
  });
 }

 /**
  * Reverse the migrations.
  */
 public function down(): void
 {
  //
  Schema::table('users', function (Blueprint $table) {
   $table->dropColumn([
    'accountName',
    'accountNumber',
    'ifsc',
    'bankName',
    'branch',
    'gstNumber',
    'pan',
    'aadharNumber',
   ]);
  });
 }
};
