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
        Schema::create('dinacharya_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('auth', 'id')->comment('Auth Student Id');
            $table->foreignId('school_id')->nullable()->constrained('auth', 'id')->comment('Auth School Id');
            $table->foreignId('parent_id')->nullable()->constrained('auth', 'id')->comment('Auth Parent Id');
            $table->foreignId('image_id')->nullable()->constrained('student_images', 'id');
            $table->foreignId('quote_id')->nullable()->constrained('quotes', 'id');
           
            // $table->string('image_path');
            // $table->foreignId('created_by')->nullable()->constrained('auth', 'id');
            $table->tinyInteger('status')->default(1)->comment('0=>failed, 1=>success');
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
