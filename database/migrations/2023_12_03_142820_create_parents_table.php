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
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auth_id')->nullable()->constrained('auth', 'id');
            $table->foreignId('school_id')->nullable()->constrained('schools', 'id');
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('profession')->nullable();
            $table->string('address')->nullable();
            $table->integer('pincode')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('profile_image')->nullable();
            $table->date('dob')->nullable();
            $table->string('relationship')->nullable();
            $table->string('parent_code', 32);
            $table->text('remarks')->nullable();
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
