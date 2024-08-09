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
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->decimal('slash_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('referral_amount', 10, 2);
            $table->decimal('referrer_amount', 10, 2);
            $table->text('benefits');
            $table->text('description');
          
            $table->foreignId('updated_by')->nullable()->constrained('auth', 'id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
