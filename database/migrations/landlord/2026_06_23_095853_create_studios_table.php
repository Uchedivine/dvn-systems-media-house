<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studios', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();

            // Routing — kept on the parent row, not studio_settings,
            // so tenant lookup never needs a join.
            $table->string('subdomain')->unique();
            $table->string('custom_domain')->nullable()->unique();

            // Actual tenant DB name (e.g. "db_neural_studios"), set by
            // TenantProvisioningService at signup. Read by spatie's
            // SwitchTenantDatabaseTask on every request.
            $table->string('database')->nullable();

            $table->string('owner_name');
            $table->string('owner_email')->unique();
            $table->string('owner_phone');

            $table->enum('plan', ['basic', 'growth'])->default('basic');

            $table->enum('subscription_status', [
                'trialing', 'active', 'grace', 'suspended',
                'frozen', 'archived', 'cancelled',
            ])->default('trialing');

            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamp('next_payment_due')->nullable();
            $table->integer('payment_overdue_days')->default(0);
            $table->integer('failed_payment_attempts')->default(0);
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('frozen_at')->nullable();

            $table->string('paystack_customer_code')->nullable();
            $table->string('paystack_subscription_code')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studios');
    }
};