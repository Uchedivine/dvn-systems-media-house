<?php

namespace App\Models\Landlord;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Tenant;

class Studio extends Tenant
{
    use SoftDeletes;

    protected $table = 'studios';

    protected $fillable = [
        'name', 'slug', 'subdomain', 'custom_domain', 'database',
        'owner_name', 'owner_email', 'owner_phone',
        'plan', 'subscription_status',
        'trial_ends_at', 'last_payment_at', 'next_payment_due',
        'payment_overdue_days', 'failed_payment_attempts',
        'suspended_at', 'frozen_at',
        'paystack_customer_code', 'paystack_subscription_code',
        'status',
    ];

    protected $casts = [
        'trial_ends_at'     => 'datetime',
        'last_payment_at'   => 'datetime',
        'next_payment_due'  => 'datetime',
        'suspended_at'      => 'datetime',
        'frozen_at'         => 'datetime',
    ];

    public function settings()
    {
        return $this->hasOne(StudioSettings::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscriptionPayments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }
}
