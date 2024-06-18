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
        Schema::create('chapter_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('auth')->onDelete('cascade');
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->tinyInteger('video_complete_status')->default(0)->comment('0=>Incomplete, 1=>Completed');
            $table->tinyInteger('assessment_complete_status')->default(0)->comment('0=>Incomplete, 1=>Completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapter_logs');
    }
};
