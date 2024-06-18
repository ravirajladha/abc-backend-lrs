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
        Schema::create('student_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_auth_id')->nullable()->constrained('auth', 'id');
            $table->foreignId('student_id')->nullable()->constrained('students', 'id');
            $table->string('image_path');
            $table->foreignId('created_by')->nullable()->constrained('auth', 'id');
            $table->tinyInteger('status')->default(1)->comment('0=>inactive, 1=>active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_images');
    }
};
