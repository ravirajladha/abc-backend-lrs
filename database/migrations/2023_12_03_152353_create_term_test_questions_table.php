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
        Schema::create('term_test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->nullable()->constrained('classes', 'id');
            $table->foreignId('subject_id')->nullable()->constrained('subjects', 'id');
            $table->text('question');
            $table->text('explanation')->nullable();
            $table->string('image')->nullable();
            $table->text('code')->nullable();
            $table->text('option_one')->nullable();
            $table->text('option_two')->nullable();
            $table->text('option_three')->nullable();
            $table->text('option_four')->nullable();
            $table->string('answer_key')->nullable();
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
        Schema::dropIfExists('term_tests_questions');
    }
};
