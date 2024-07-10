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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->nullable()->constrained('classes', 'id');
            $table->foreignId('section_id')->nullable()->constrained('sections', 'id');
            $table->string('name');
            $table->string('image')->nullable();
            $table->text('description');
            $table->tinyInteger('subject_type')->default(1)->comment('1=> Default Subject; 2 => Super Subject; 3=> Sub Subject');
            $table->foreignId('super_subject_id')->nullable()->constrained('subjects', 'id');
            $table->text('benefits');
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
        //
    }
};
