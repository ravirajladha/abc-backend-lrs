<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mini_projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subject_id')->nullable()->constrained('subjects', 'id');
            $table->foreignId('course_id')->nullable()->constrained('courses', 'id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('auth', 'id');
            $table->foreignId('updated_by')->nullable()->constrained('auth', 'id');
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
        Schema::dropIfExists('mini_projects');
    }
};
