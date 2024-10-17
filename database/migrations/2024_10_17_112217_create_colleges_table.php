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
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('city');
            $table->string('state');
            $table->text('address');
            $table->tinyInteger('status')->default(1)->comment('0=>Inactive; 1=>Active');

            $table->foreignId('created_by')->constrained('auth')->onDelete('cascade');  // Link to the trainer

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colleges');
    }
};
