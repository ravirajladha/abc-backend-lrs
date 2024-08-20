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
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
      
            $table->foreignId('subject_id')->nullable()->constrained('subjects', 'id');
            $table->foreignId('course_id')->nullable()->constrained('courses', 'id');
            $table->string('title', 255);
            // $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->text('instruction')->nullable();
            $table->string('question_ids', 255)->nullable();
            $table->integer('no_of_questions')->nullable();
            $table->decimal('total_score', 10, 2)->nullable();
            $table->decimal('time_limit', 10, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
                
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
        Schema::dropIfExists('tests');
    }
};
