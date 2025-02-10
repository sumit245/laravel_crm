<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add project assignment fields
            $table->bigInteger('project_id')->unsigned()->nullable()->after('role');
            $table->bigInteger('assigned_by')->unsigned()->nullable()->after('project_id');

            // Add document storage fields (for vendors & others)
            $table->string('pan_document')->nullable()->after('pan');
            $table->string('aadhar_document')->nullable()->after('aadharNumber');
            $table->string('cancelled_cheque_document')->nullable()->after('gstNumber');
            $table->string('gst_document')->nullable()->after('cancelled_cheque_document');
            $table->json('additional_documents')->nullable()->after('gst_document');

            // Foreign key relationships
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop added columns
            $table->dropForeign(['project_id']);
            $table->dropForeign(['assigned_by']);
            $table->dropColumn([
                'project_id',
                'assigned_by',
                'pan_document',
                'aadhar_document',
                'cancelled_cheque_document',
                'gst_document',
                'additional_documents',
            ]);
        });
    }
};
