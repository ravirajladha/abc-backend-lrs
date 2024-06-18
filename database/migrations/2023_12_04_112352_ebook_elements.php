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
        Schema::create('ebook_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ebook_section_id')->nullable()->constrained('ebook_sections', 'id');
            $table->foreignId('ebook_element_type_id')->nullable()->constrained('ebook_element_types', 'id');
            $table->string('heading', 255)->nullable();
            $table->text('paragraph')->nullable();
            $table->string('heading_type', 100)->nullable();
            $table->string('image')->nullable();
            $table->text('code')->nullable();
            $table->text('memory')->nullable();
            $table->text('output')->nullable();
            $table->string('image_type', 100)->nullable();
            $table->string('image_heading_1', 100)->nullable();
            $table->string('image_subheading_1', 100)->nullable();
            $table->string('image_heading_2', 100)->nullable();
            $table->string('image_subheading_2', 100)->nullable();
            $table->string('image_text_1', 100)->nullable();
            $table->string('image_subtext_1', 100)->nullable();
            $table->text('image_desc_1')->nullable();
            $table->string('image_text_2', 100)->nullable();
            $table->string('image_subtext_2', 100)->nullable();
            $table->text('image_desc_2')->nullable();
            $table->string('image_text_3', 100)->nullable();
            $table->string('image_subtext_3', 100)->nullable();
            $table->text('image_desc_3')->nullable();
            $table->string('image_text_4', 100)->nullable();
            $table->string('image_subtext_4', 100)->nullable();
            $table->text('image_desc_4')->nullable();
            $table->string('image_text_5', 100)->nullable();
            $table->string('image_subtext_5', 100)->nullable();
            $table->text('image_desc_5')->nullable();
            $table->string('image_text_6', 100)->nullable();
            $table->string('image_subtext_6', 100)->nullable();
            $table->text('image_desc_6')->nullable();
            $table->string('image_text_7', 100)->nullable();
            $table->string('image_subtext_7', 100)->nullable();
            $table->text('image_desc_7')->nullable();
            $table->string('image_text_8', 100)->nullable();
            $table->string('image_subtext_8', 100)->nullable();
            $table->text('image_desc_8')->nullable();
            $table->string('image_text_9', 100)->nullable();
            $table->string('image_subtext_9', 100)->nullable();
            $table->text('image_desc_9')->nullable();
            $table->string('image_text_10', 100)->nullable();
            $table->string('image_subtext_10', 100)->nullable();
            $table->text('image_desc_10')->nullable();
            $table->string('list_type', 100)->nullable();
            $table->string('list_heading', 100)->nullable();
            $table->text('list_points')->nullable();
            $table->text('table_data')->nullable();
            $table->string('example_text', 100)->nullable();
            $table->text('example_description')->nullable();
            $table->text('practice_description')->nullable();
            $table->string('example_image_text', 100)->nullable();
            $table->string('button_text', 100)->nullable();
            $table->tinyInteger('single_button_type')->nullable();
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
