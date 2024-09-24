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
        Schema::create('placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auth_id')->nullable()->constrained('auth', 'id');
            $table->foreignId('recruiter_id')->nullable()->constrained('auth', 'id');
            $table->foreignId('test_id')->nullable()->constrained('placement_tests', 'id');
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id');
            $table->string('title', 255);
            // $table->foreignId('subject_id')->nullable()->constrained('subjects', 'id');
            $table->string('subject_id', 255);
            $table->string('image', 255)->nullable();
            $table->string('annual_ctc', 150);
            $table->text('instruction')->nullable();
            $table->integer('passing_percentage'); 
            $table->text('location');
            $table->string('criteria');
            $table->text('description');
            $table->tinyInteger('status')->default(1)->comment('0 => Inactive; 1 => Active');
            $table->foreignId('created_by')->nullable()->constrained('auth', 'id');
            $table->foreignId('updated_by')->nullable()->constrained('auth', 'id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('placements');
    }
};
