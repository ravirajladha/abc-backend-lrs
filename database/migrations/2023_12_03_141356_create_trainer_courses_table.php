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
        Schema::create('trainer_courses', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('school_id')->nullable()->constrained('schools', 'id');
            $table->foreignId('trainer_id')->nullable()->constrained('trainers', 'id');
            $table->foreignId('course_id')->nullable()->constrained('courses', 'id');
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
        //
    }
};