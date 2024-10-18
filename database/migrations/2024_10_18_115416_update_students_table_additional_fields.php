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
        Schema::table('students', function (Blueprint $table) {
            $table->string('gender')->nullable();

            $table->string('father_name')->nullable();
            $table->string('father_email')->nullable();
            $table->string('father_number')->nullable();

            $table->string('mother_name')->nullable();
            $table->string('mother_email')->nullable();
            $table->string('mother_number')->nullable();

            $table->foreignId('college_id')->nullable()->constrained('colleges', 'id');
            $table->string('college_sem')->nullable();
            $table->date('college_start_date')->nullable();
            $table->date('college_end_date')->nullable();

            $table->json('hobbies')->nullable();
            $table->json('achievements')->nullable();
            $table->json('languages')->nullable();
            $table->string('about')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'father_name',
                'father_email',
                'father_number',
                'mother_name',
                'mother_email',
                'mother_number',
                'college',
                'college_sem',
                'college_start_date',
                'college_end_date',
                'hobbies',
                'achievements',
                'languages',
                'about'
            ]);
        });
    }
};
