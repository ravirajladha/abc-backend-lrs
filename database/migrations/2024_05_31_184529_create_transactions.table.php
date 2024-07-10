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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('auth');
            $table->string('transaction_id')->unique();
            $table->decimal('amount', 10, 2);
          
            $table->enum('status', ['failed', 'pending', 'success']);
            $table->string('payment_method', 50);
            $table->text('error_message')->nullable();
            $table->string('referral_code', 50)->nullable();
            $table->decimal('referral_amount', 10, 2)->nullable();
            $table->decimal('referrer_amount', 10, 2)->nullable();
            $table->string('ip_address', 45);
            $table->text('browser_info');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
