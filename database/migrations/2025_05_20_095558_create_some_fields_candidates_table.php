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
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('candidates', 'gender')) {
                $table->string('gender')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'marital_status')) {
                $table->string('marital_status')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'nationality')) {
                $table->string('nationality')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'language')) {
                $table->string('language')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'permanent_address')) {
                $table->text('permanent_address')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'education')) {
                $table->json('education')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'previous_employer')) {
                $table->string('previous_employer')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'notice_period')) {
                $table->string('notice_period')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'disabilities')) {
                $table->string('disabilities')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'currently_employed')) {
                $table->string('currently_employed')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'reason_for_leaving')) {
                $table->string('reason_for_leaving')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'other_info')) {
                $table->text('other_info')->nullable();
            }
            
            // New fields for S3 storage
            if (!Schema::hasColumn('candidates', 'photo_name')) {
                $table->string('photo_name')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'photo_s3_path')) {
                $table->string('photo_s3_path')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'document_paths')) {
                $table->json('document_paths')->nullable();
            }
            
            if (!Schema::hasColumn('candidates', 'signature')) {
                $table->string('signature')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Drop the columns added in the up method
            $columns = [
                'gender', 'marital_status', 'nationality', 'language', 'permanent_address',
                'emergency_contact_name', 'emergency_contact_phone', 'education',
                'previous_employer', 'notice_period', 'disabilities', 'currently_employed',
                'reason_for_leaving', 'other_info', 'photo_name', 'photo_s3_path', 
                'document_paths', 'signature'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('candidates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};