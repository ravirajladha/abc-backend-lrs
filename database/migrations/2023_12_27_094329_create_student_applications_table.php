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
        Schema::create('student_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('auth', 'id')->nullable();
            $table->foreignId('parent_id')->constrained('auth', 'id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('dob');
            $table->string('aadhaar');
            $table->string('nationality');
            $table->string('religion');
            $table->string('mother_tongue');
            $table->string('last_school_name');
            $table->string('class');
            $table->string('gender');
            $table->string('caste');
            $table->string('blood_group');
            $table->text('medical_issues');
            $table->string('father_name');
            $table->string('father_qualification');
            $table->string('father_designation');
            $table->string('father_company_name');
            $table->string('father_salary');
            $table->string('father_aadhaar');
            $table->string('father_contact');
            $table->string('father_blood_group');
            $table->string('father_email');
            $table->string('mother_name');
            $table->string('mother_qualification');
            $table->string('mother_designation');
            $table->string('mother_company_name');
            $table->string('mother_salary');
            $table->string('mother_aadhaar');
            $table->string('mother_contact');
            $table->string('mother_blood_group');
            $table->string('mother_email');
            $table->string('residential_address');
            $table->string('residential_contact');
            $table->string('disability');
            $table->string('guardian_name');
            $table->string('guardian_relationship');
            $table->string('guardian_contact');
            $table->tinyInteger('application_status')->default(0)->comment('0=> Pending; 1=> Approved; 2=> Rejected;');
            $table->tinyInteger('created_by')->default(0)->comment('0=> School; 1=> Parent; 2=> Guest;');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_applications');
    }
};
