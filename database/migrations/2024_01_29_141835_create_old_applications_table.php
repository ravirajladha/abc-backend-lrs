<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('old_applications', function (Blueprint $table) {
            $table->id();
            $table->string('month')->nullable();
            $table->string('year')->nullable();
            $table->string('enquiry_date')->nullable();
            $table->string('student_name')->nullable();
            $table->string('enquiry_class_old')->nullable();
            $table->string('enquiry_class')->nullable();
            $table->string('class_expected_in_2024_25')->nullable();
            $table->string('dob')->nullable();
            $table->string('f_name')->nullable();
            $table->string('m_name')->nullable();
            $table->string('f_contact')->nullable();
            $table->string('m_contact')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->nullable();
            $table->string('heard_about_us')->nullable();
            $table->string('prev_school')->nullable();
            $table->string('application_date')->nullable();
            $table->string('admission_date')->nullable();
            $table->string('admission_enquiry_for')->nullable();
            $table->string('data')->nullable();
            $table->string('age_as_01_06_2023')->nullable();
            $table->string('entrance_test_date')->nullable();
            $table->string('entrance_test_result')->nullable();
            $table->string('remarks')->nullable();
            $table->string('data_2')->nullable();
            $table->string('already_enrolled')->nullable();
            $table->tinyInteger('whatsapp_status')->default(0)->comment('0=> Pending; 1=> Sent;');
            $table->tinyInteger('whatsapp_status_2')->default(0)->comment('0=> Pending; 1=> Sent;');
            $table->tinyInteger('whatsapp_status_3')->default(0)->comment('10th-Acids,bases,salts-1 - 0=> Pending; 1=> Sent;');
            $table->tinyInteger('whatsapp_status_4')->default(0)->comment('10th-Acids,bases,salts-2 - 0=> Pending; 1=> Sent;');
            $table->tinyInteger('application_status')->default(0)->comment('0=> Pending; 1=> School visit scheduled; 2=> Approved; 3=> Admitted; 4=> Waiting List; 5=> Black List;');
            $table->foreignId('status_updated_by')->nullable()->constrained('auth', 'id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('old_applications');
    }
};
