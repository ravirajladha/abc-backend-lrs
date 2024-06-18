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
        Schema::create('qna_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->nullable()->constrained('subjects', 'id');
            $table->foreignId('qna_id')->nullable()->constrained('qna', 'id');
            $table->foreignId('sender_id')->nullable()->constrained('auth', 'id');
            $table->foreignId('receiver_id')->nullable()->constrained('auth', 'id');
            $table->text('response')->nullable();
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
        Schema::dropIfExists('qna_log');
    }
};
