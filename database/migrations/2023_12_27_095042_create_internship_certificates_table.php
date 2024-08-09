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
        Schema::create('internship_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_id')->nullable()->constrained('internships', 'id');
            $table->foreignId('student_id')->nullable()->constrained('auth', 'id');
            $table->string('certificate', 150)->nullable();
            $table->timestamp('start_datetime')->nullable();
            $table->timestamp('end_datetime')->nullable();
            $table->boolean('status')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_certificates');
    }
};
