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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('location');
         
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0=>Inactive; 1=>Active');
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
        Schema::dropIfExists('recruiters');
    }
};
