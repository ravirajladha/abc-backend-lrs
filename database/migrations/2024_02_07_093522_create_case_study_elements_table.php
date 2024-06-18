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
        Schema::create('case_study_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_study_section_id')->nullable()->constrained('case_study_sections', 'id');
            $table->integer('case_study_element_type_id');
            $table->text('paragraph')->nullable();
            $table->string('list_type', 50)->nullable();
            $table->string('list_heading', 255)->nullable();
            $table->text('list_points')->nullable();
            $table->text('list_description')->nullable();
            $table->text('appendices_heading')->nullable();
            $table->text('appendices_sub_heading')->nullable();
            $table->text('appendices_desc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_study_elements');
    }
};
