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
        Schema::create('elabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->nullable()->constrained('classes', 'id');
            $table->foreignId('subject_id')->nullable()->constrained('subjects', 'id');
            // $table->foreignId('chapter_id')->nullable()->constrained('chapters', 'id');
            $table->string('title');
            $table->text('description')->nullable();
           
            $table->string('code_language')->comment('0=> Java; 1=> Python; 2=> C; 3=> SQL')->nullable();
            // $table->text('code')->nullable();
           
            $table->string('io_format')->nullable();
            $table->text('constraints')->nullable();
            $table->text('io_sample')->nullable();
            $table->text('pseudo_code')->nullable();
            $table->text('testcase')->nullable();
            $table->text('template1')->nullable();
            $table->text('template2')->nullable();
            $table->boolean('active')->default(true);
            $table->text('data_harness_code')->nullable();
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
        Schema::dropIfExists('elabs');
    }
};
