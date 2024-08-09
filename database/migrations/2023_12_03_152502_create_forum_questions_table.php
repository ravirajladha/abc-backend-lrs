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
        Schema::create('forum_questions', function (Blueprint $table) {
            $table->id();
          
            $table->foreignId('student_id')->nullable()->constrained('auth', 'id');
            $table->text('question')->nullable();
            $table->integer('likes_count')->default(0);
            $table->integer('dislikes_count')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0 => Disabled; 1 => Active');
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
        Schema::dropIfExists('forum_questions');
    }
};
