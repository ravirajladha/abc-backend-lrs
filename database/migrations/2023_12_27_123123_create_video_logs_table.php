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
        Schema::create('student_video_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('auth', 'id')->nullable();
            $table->integer('video_id')->nullable();
            $table->decimal('watch_time', 10, 2)->nullable();
            $table->decimal('total_watch_time', 10, 2)->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=> Started, 1=> Completed');
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
        Schema::dropIfExists('student_video_logs');
    }
};
