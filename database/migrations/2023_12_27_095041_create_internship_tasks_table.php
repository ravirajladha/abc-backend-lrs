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
        Schema::create('internship_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_id')->nullable()->constrained('internships', 'id');
            $table->foreignId('elab_id')->nullable()->constrained('elabs', 'id');
            $table->string('name', 150)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
       
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
        Schema::dropIfExists('internship_tasks');
    }
};
