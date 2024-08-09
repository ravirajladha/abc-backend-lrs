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
        Schema::create('forum_answer_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->constrained('forum_answers');
            $table->foreignId('student_id')->constrained('auth');
            $table->tinyInteger('vote_type')->comment('1: Upvote, -1: Downvote');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_answer_votes');
    }
};
