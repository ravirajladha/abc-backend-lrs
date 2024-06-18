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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->nullable()->constrained('jobs', 'id');
            $table->foreignId('test_id')->nullable()->constrained('job_tests', 'id');
            
            $table->foreignId('school_id')->nullable()->constrained('schools', 'id');
            $table->foreignId('class_id')->nullable()->constrained('classes', 'id');
            $table->foreignId('student_id')->nullable()->constrained('auth', 'id');
            // $table->foreignId('job_test_id')->nullable()->constrained('job_tests', 'id');
            $table->decimal('score', 10, 2)->nullable();
            $table->decimal('percentage', 10, 2)->nullable();
            $table->string('response_questions', 255)->nullable();
            $table->string('response_answers', 255)->nullable();
            $table->string('token', 255)->nullable();
            $table->boolean('token_status')->default(false)->nullable();
            $table->boolean('is_completed')->default(false)->nullable();
            $table->boolean('is_pass')->default(false)->nullable();
            $table->boolean('is_test')->default(true)->nullable();

            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 => Disabled; 1 => Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
