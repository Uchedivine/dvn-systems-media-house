<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->string('paystack_reference')->nullable()->unique();
            $table->enum('status', ['pending', 'verified', 'failed']);
            $table->enum('type', ['setup_fee', 'monthly', 'annual', 'reactivation']);
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};