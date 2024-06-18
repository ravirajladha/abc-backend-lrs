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
        Schema::create('ebook_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ebook_id')->nullable()->constrained('ebooks', 'id');
            $table->foreignId('ebook_module_id')->nullable()->constrained('ebook_modules', 'id');
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
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
        Schema::dropIfExists('ebook_sections');
    }
};
