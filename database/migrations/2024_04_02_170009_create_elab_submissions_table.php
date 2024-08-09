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
        Schema::create('elab_submissions', function (Blueprint $table) {
            $table->id();
   
            $table->foreignId('student_id')->nullable()->constrained('auth', 'id');
            $table->foreignId('elab_id')->nullable()->constrained('elabs');
            $table->foreignId('video_id')->nullable()->constrained('videos', 'id');
            $table->foreignId('mini_project_id')->nullable()->constrained('mini_projects', 'id');
            $table->foreignId('mini_project_task_id')->nullable()->constrained('mini_project_tasks', 'id');
            $table->foreignId('internship_id')->nullable()->constrained('internships', 'id');
            $table->foreignId('internship_task_id')->nullable()->constrained('internship_tasks', 'id');
            $table->foreignId('course_id')->nullable()->constrained('courses', 'id');
            $table->text('code')->nullable();
            $table->string('time', 255)->nullable();
            $table->string('memory', 255)->nullable();
            $table->tinyInteger('code_language')->comment('java,c,sql')->nullable();
            $table->string('code_level', 255)->nullable();
            $table->string('time_taken', 255)->nullable();
            // $table->time('time_taken')->nullable();
            $table->time('start_timestamp')->nullable();
            $table->time('end_timestamp')->nullable();
            $table->tinyInteger('status')->nullable();
            // $table->tinyInteger('status')->default(0)->comment('0=> Pending; 1=> Started; 2=> Completed;');
            $table->tinyInteger('type')->nullable()->comment('0=> Mini Project Task; 1=> Video Task');
            $table->tinyInteger('type_id')->nullable()->comment('0=> Mini Project Task; 1=> Video Task');
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
        Schema::dropIfExists('elab_submissions');
    }
};
