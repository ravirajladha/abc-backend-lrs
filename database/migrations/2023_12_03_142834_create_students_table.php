<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auth_id')->nullable()->constrained('auth', 'id');
            $table->string('student_unique_code', 32)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('profile_image')->nullable();
            $table->date('dob')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->integer('pincode')->nullable();

            // $table->string('gender')->nullable();

            // $table->string('father_name')->nullable();
            // $table->string('father_email')->nullable();
            // $table->string('father_number')->nullable();

            // $table->string('mother_name')->nullable();
            // $table->string('mother_email')->nullable();
            // $table->string('mother_number')->nullable();

            // $table->string('college')->nullable();
            // $table->string('college_sem')->nullable();
            // $table->date('college_start_date')->nullable();
            // $table->date('college_end_date')->nullable();

            // $table->json('hobbies')->nullable();
            // $table->json('achievements')->nullable();
            // $table->json('languages')->nullable();
            // $table->string('about')->nullable();

            // $table->string('first_name');
            // $table->string('last_name');
            // $table->date('dob');
            // $table->string('aadhaar');
            // $table->string('nationality');
            // $table->string('religion');
            // $table->string('mother_tongue');
            // $table->string('last_school_name');
            // $table->string('class');
            // $table->string('caste');
            // $table->string('blood_group');
            // $table->text('medical_issues');
            // $table->string('father_qualification');
            // $table->string('father_designation');
            // $table->string('father_company_name');
            // $table->string('father_salary');
            // $table->string('father_aadhaar');
            // $table->string('father_blood_group');
            // $table->string('mother_qualification');
            // $table->string('mother_designation');
            // $table->string('mother_company_name');
            // $table->string('mother_salary');
            // $table->string('mother_aadhaar');
            // $table->string('mother_blood_group');
            // $table->string('residential_address');
            // $table->string('residential_contact');
            // $table->string('disability');
            // $table->string('guardian_name');
            // $table->string('guardian_relationship');
            // $table->string('guardian_contact');


            // $table->text('remarks')->nullable();
            $table->tinyInteger('is_paid')->default(0)->comment('0=>not paid, 1=>paid');
            $table->tinyInteger('status')->default(1)->comment('0=>Inactive; 1=>Active');
            $table->foreignId('created_by')->nullable()->constrained('auth', 'id');
            $table->foreignId('updated_by')->nullable()->constrained('auth', 'id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
};
