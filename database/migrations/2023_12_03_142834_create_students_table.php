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
       
            $table->string('class_id', 32)->nullable();
         
            $table->string('student_unique_code', 32)->nullable();
            $table->string('name');
            $table->string('roll_number', 32)->nullable();
            $table->string('profile_image')->nullable();
            $table->date('dob')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->integer('pincode')->nullable();
            $table->text('remarks')->nullable();
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
