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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auth_id')->nullable()->constrained('auth', 'id');
            $table->string('name')->nullable();
            $table->string('accreditation_no', 64)->nullable();
            $table->string('logo')->nullable();
            $table->integer('year_of_establishment')->nullable();
            $table->string('phone_number', 255)->nullable();
            $table->string('address')->nullable();
            $table->integer('pincode')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('website_url', 255)->nullable();
            $table->string('legal_name', 255)->nullable();
            $table->text('office_address')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->tinyInteger('school_type')->default(1)->comment('0=>private, 1=>public');

            $table->tinyInteger('type')->default(1)->comment('0=> State; 1=> CBSE; 2=> ICSE;')->nullable();
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
