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
        Schema::create('auth', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('phone_number')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->tinyInteger('type')->default(1)->comment('0=>Admin; 1=>Student; 2=>Trainer; 3=> Recruiter; 4=> Internship Admin;');
            $table->tinyInteger('status')->default(1)->comment('0=>Disabled; 1=>Active');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth');
    }
};
