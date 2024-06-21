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
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->nullable()->constrained('classes', 'id');
            $table->foreignId('subject_id')->nullable()->constrained('subjects', 'id');
            $table->foreignId('chapter_id')->nullable()->constrained('chapters', 'id');
            $table->text('text');
            $table->text('explanation')->nullable();
            $table->string('image')->nullable();
            $table->text('code')->nullable();
            $table->text('option_one');
            $table->text('option_two');
            $table->text('option_three');
            $table->text('option_four');
            $table->string('answer_key');
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
        Schema::dropIfExists('assessment_questions');
    }
};
