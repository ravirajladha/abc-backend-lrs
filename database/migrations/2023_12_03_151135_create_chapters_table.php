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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->nullable()->constrained('subjects', 'id');
            $table->foreignId('course_id')->nullable()->constrained('courses', 'id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->tinyInteger('lock_status')->default(0)->comment('0=>Locked, 1=>Unlocked');
            $table->foreignId('status_updated_by')->nullable()->constrained('auth', 'id');
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
        Schema::dropIfExists('chapters');
    }
};
