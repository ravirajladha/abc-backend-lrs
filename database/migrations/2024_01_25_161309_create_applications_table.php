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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('student_fname');
            $table->string('student_pname')->nullable();
            $table->date('student_dob')->nullable();
            $table->string('student_aadhaar')->nullable();
            $table->string('student_nationality')->nullable();
            $table->string('student_religion')->nullable();
            $table->string('student_mt')->nullable();
            $table->string('last_school_name')->nullable();
            $table->string('classname')->nullable();
            $table->string('branch')->nullable();
            $table->string('student_gender')->nullable();
            $table->string('student_caste')->nullable();
            $table->string('student_blood_group')->nullable();
            $table->text('issues')->nullable();
            $table->string('fname')->nullable();
            $table->string('f_qual')->nullable();
            $table->string('f_desig')->nullable();
            $table->string('f_mob')->nullable();
            $table->string('f_aadhar')->nullable();
            $table->string('f_comp')->nullable();
            $table->string('f_sal')->nullable();
            $table->string('f_tel')->nullable();
            $table->string('f_bld')->nullable();
            $table->string('f_email')->nullable();
            $table->string('m_name')->nullable();
            $table->string('m_qual')->nullable();
            $table->string('m_desig')->nullable();
            $table->string('m_mob')->nullable();
            $table->string('m_aadhar')->nullable();
            $table->string('m_comp')->nullable();
            $table->string('m_sal')->nullable();
            $table->string('m_tel')->nullable();
            $table->string('m_bld')->nullable();
            $table->string('m_email')->nullable();
            $table->text('res_add')->nullable();
            $table->string('res_phone')->nullable();
            $table->string('dis')->nullable();
            $table->string('rel_name')->nullable();
            $table->string('relation_ch')->nullable();
            $table->string('rel_phone')->nullable();
            $table->tinyInteger('whatsapp_status')->default(0)->comment('0=> Pending; 1=> Sent;');
            $table->tinyInteger('whatsapp_status_2')->default(0)->comment('0=> Pending; 1=> Sent;');
            $table->tinyInteger('whatsapp_status_3')->default(0)->comment('10th-Acids,bases,salts-1 - 0=> Pending; 1=> Sent;');
            $table->tinyInteger('whatsapp_status_4')->default(0)->comment('10th-Acids,bases,salts-2 - 0=> Pending; 1=> Sent;');
            $table->tinyInteger('application_status')->default(0)->comment('0=> Pending; 1=> School visit scheduled; 2=> Approved; 3=> Admitted; 4=> Waiting List; 5=> Black List;');
            $table->foreignId('status_updated_by')->nullable()->constrained('auth', 'id');
            $table->string('enquired_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
