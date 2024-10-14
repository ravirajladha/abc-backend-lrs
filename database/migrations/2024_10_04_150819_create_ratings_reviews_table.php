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
        Schema::create('ratings_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('auth')->onDelete('cascade');
            $table->integer('rating')->nullable();
            $table->text('review')->nullable();
            $table->text('trainer_reply')->nullable();
            $table->foreignId('trainer_id')->nullable()->constrained('auth')->onDelete('cascade');
            $table->tinyInteger('status')->default(1)->comment('0=>Inactive; 1=>Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings_reviews');
    }
};
