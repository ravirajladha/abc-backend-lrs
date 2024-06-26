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
        Schema::create('mini_project_students', function (Blueprint $table) {
         
            $table->id();
            $table->foreignId('mini_project_id')->nullable()->constrained('mini_projects', 'id');
            $table->foreignId('student_id')->nullable()->constrained('auth', 'id');
            $table->foreignId('subject_id')->nullable()->constrained('subjects', 'id');
            $table->timestamp('start_datetime')->nullable();
            $table->timestamp('end_datetime')->nullable();
            $table->boolean('status')->default(false);

            $table->string('certificate', 150)->nullable();
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mini_project_students');
    }
};
