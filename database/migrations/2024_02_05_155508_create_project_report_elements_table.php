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
        Schema::create('project_report_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_report_section_id')->nullable()->constrained('project_report_sections', 'id');
            $table->integer('project_report_element_type_id');
            $table->text('paragraph')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_report_elements');
    }
};
