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
        Schema::create('term_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools', 'id');
            $table->foreignId('class_id')->nullable()->constrained('classes', 'id');
            $table->foreignId('subject_id')->nullable()->constrained('subjects', 'id');
            $table->tinyInteger('term_type')->default(1)->comment('1=> First; 2 => Second; 3=> Third');
            $table->string('title', 255);
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->text('instruction')->nullable();
            $table->string('question_ids', 255)->nullable();
            $table->integer('no_of_questions')->nullable();
            $table->decimal('total_score', 10, 2)->nullable();
            $table->decimal('time_limit', 10, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('status')->default(1)->nullable();

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
        Schema::dropIfExists('term_tests');
    }
};
