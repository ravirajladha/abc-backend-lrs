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
        Schema::create('forum_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools', 'id');
            $table->foreignId('student_id')->nullable()->constrained('students', 'id');
            $table->foreignId('question_id')->nullable()->constrained('forum_questions', 'id');
            $table->text('answer')->nullable();
            $table->integer('vote_count')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0=> Disabled; 1 => Active');
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
        Schema::dropIfExists('forum_answers');
    }
};
