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
        Schema::create('zoom_call_urls', function (Blueprint $table) {
            $table->id();
            $table->date('date'); // Date of the Zoom call
            $table->time('time'); // Time of the Zoom call
            $table->string('url'); // Zoom URL
            $table->string('passcode'); // Zoom passcode
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->tinyInteger('status')->default(1)->comment('0=>Inactive; 1=>Active');
            $table->tinyInteger('session_type')->default(1)->comment('1=>Qna Session; 2=>Live Session');
            $table->foreignId('created_by')->nullable()->constrained('auth', 'id'); // Ensure this matches your actual user table
            $table->foreignId('updated_by')->nullable()->constrained('auth', 'id'); // Ensure this matches your actual user table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoom_call_urls');
    }
};
