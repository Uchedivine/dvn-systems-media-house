<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained();
            $table->enum('plan', ['basic', 'growth']);
            $table->decimal('amount', 10, 2);
            $table->enum('billing_cycle', ['monthly', 'annual']);
            $table->enum('status', ['active', 'cancelled', 'expired']);
           $table->dateTime('current_period_start');
$table->dateTime('current_period_end');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};