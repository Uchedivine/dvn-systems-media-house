<?php

namespace App\Models\Landlord;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class SubscriptionPayment extends Model
{
    use UsesLandlordConnection;

    protected $guarded = ['id'];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_at'          => 'datetime',
    ];

    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }
}
