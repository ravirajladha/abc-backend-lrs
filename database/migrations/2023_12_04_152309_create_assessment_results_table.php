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
        Schema::create('assessment_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->nullable()->constrained('videos', 'id');
            $table->foreignId('assessment_id')->nullable()->constrained('assessments', 'id');
            $table->foreignId('student_id')->nullable()->constrained('auth', 'id');
            $table->decimal('score', 10, 2);
            $table->decimal('percentage', 10, 2);
            $table->string('response_questions', 255)->nullable();
            $table->string('response_answers', 255)->nullable();
            // $table->string('response', 255)->nullable();
            $table->tinyInteger('is_passed')->comment('0=> Fail, 1=> Pass');
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
        Schema::dropIfExists('assessment_results');
    }
};
