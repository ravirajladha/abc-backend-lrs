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
        Schema::create('mini_project_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elab_id')->nullable()->constrained('elabs', 'id');
            $table->foreignId('mini_project_student_id')->nullable()->constrained('mini_project_students', 'id')
            ; // Add cascade deletion
            $table->foreignId('mini_project_id')->nullable()->constrained('mini_projects', 'id');
            $table->foreignId('mini_project_task_id')->nullable()->constrained('mini_project_tasks', 'id');
            $table->foreignId('elab_submission_id')->nullable()->constrained('elab_submissions', 'id');
            $table->foreignId('subject_id')->nullable()->constrained('subjects', 'id');
            $table->foreignId('school_id')->nullable()->constrained('schools', 'id');
            $table->foreignId('student_id')->nullable()->constrained('auth', 'id');
            $table->tinyInteger('status')->default(0)->comment('0=> Pending; 1 => Started; 2=> Completed');
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
        Schema::dropIfExists('mini_project_submissions');
    }
};
