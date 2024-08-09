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
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->nullable()->constrained('tests', 'id');
         
            $table->foreignId('student_id')->nullable()->constrained('auth', 'id');
            $table->foreignId('subject_id')->nullable()->constrained('subjects', 'id');
            $table->foreignId('course_id')->nullable()->constrained('courses', 'id');
         
            $table->decimal('score', 10, 2)->nullable();
            $table->decimal('percentage', 10, 2)->nullable();
            $table->string('response_questions', 255)->nullable();
            $table->string('response_answers', 255)->nullable();
            $table->string('token', 255)->nullable();
            $table->boolean('token_status')->default(false)->nullable();
            $table->boolean('is_completed')->default(false)->nullable();
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
        Schema::dropIfExists('tests_results');
    }
};
