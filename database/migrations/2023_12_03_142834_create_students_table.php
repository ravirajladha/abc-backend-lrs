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
            $table->foreignId('class_id')->nullable()->constrained('classes', 'id');
            $table->foreignId('section_id')->nullable()->constrained('sections', 'id');
            $table->foreignId('school_id')->nullable()->constrained('schools', 'id');
            $table->foreignId('parent_id')->nullable()->constrained('parents', 'id');
            $table->tinyInteger('student_type')->default(0)->comment('0=>Student, 1=>Outsider');
            $table->string('name');
            $table->string('roll_number', 32)->nullable();
            $table->string('profile_image')->nullable();
            $table->date('dob')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->integer('pincode')->nullable();
            $table->text('remarks')->nullable();
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
