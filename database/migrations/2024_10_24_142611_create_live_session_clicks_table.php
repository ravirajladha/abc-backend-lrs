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
        Schema::create('live_session_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('auth')->onDelete('cascade');
            $table->foreignId('session_id')->constrained('zoom_call_urls')->onDelete('cascade');
            $table->timestamp('clicked_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_session_clicks');
    }
};
