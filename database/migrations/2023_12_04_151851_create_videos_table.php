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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();    
            $table->foreignId('subject_id')->nullable()->constrained('subjects', 'id');
            $table->foreignId('course_id')->nullable()->constrained('courses', 'id');
            $table->foreignId('chapter_id')->nullable()->constrained('chapters', 'id');
            $table->foreignId('assessment_id')->nullable()->constrained('assessments', 'id');
            $table->foreignId('elab_id')->nullable()->constrained('elabs', 'id');
            $table->foreignId('ebook_id')->nullable()->constrained('ebooks', 'id');
            $table->foreignId('ebook_module_id')->nullable()->constrained('ebook_modules', 'id');
            $table->string('ebook_sections', 255)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url', 255);
            $table->string('image')->nullable();
            $table->string('resource')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0=>Inactive; 1=>Active');
            $table->foreignId('created_by')->nullable()->constrained('auth', 'id');
            $table->foreignId('updated_by')->nullable()->constrained('auth', 'id');
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
        Schema::dropIfExists('videos');
    }
};
