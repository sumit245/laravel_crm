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
        Schema::table('candidates', function (Blueprint $table) {
//            $table->string('marital_status')->after('gender')->nullable();
  //          $table->string('nationality')->after('marital_status')->nullable();
    //        $table->string('language')->after('nationality')->nullable();
      //      $table->text('permanent_address')->after('language')->nullable();
        //    $table->string('emergency_contact_name')->after('permanent_address')->nullable();
          //  $table->string('emergency_contact_phone')->after('emergency_contact_name')->nullable();
           // $table->json('education')->after('emergency_contact_phone')->nullable();
           // $table->string('previous_employer')->after('education')->nullable();
           // $table->string('notice_period')->after('previous_employer')->nullable();
            //$table->string('disabilities')->after('notice_period')->nullable();
           // $table->string('currently_employed')->after('disabilities')->nullable();
           // $table->string('reason_for_leaving')->after('currently_employed')->nullable();
            //$table->text('other_info')->after('reason_for_leaving')->nullable();
           // $table->string('signature')->after('other_info')->nullable();
           // $table->string('photo_name')->after('signature')->nullable();
           // $table->string('photo_s3_path')->after('photo_name')->nullable();
           // $table->json('document_paths')->after('photo_s3_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            //
        });
    }
};
