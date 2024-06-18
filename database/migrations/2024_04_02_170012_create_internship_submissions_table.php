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
        Schema::create('internship_submissions', function (Blueprint $table) {

            $table->id();
            $table->foreignId('elab_id')->nullable()->constrained('elabs', 'id');
            $table->foreignId('internship_id')->nullable()->constrained('internships', 'id');
            $table->foreignId('internship_task_id')->nullable()->constrained('internship_tasks', 'id');
            $table->foreignId('internship_certificate_id')->nullable()->constrained('internship_certificates', 'id'); // Add cascade deletion
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
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_submissions');
    }
};
