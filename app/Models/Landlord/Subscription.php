<?php

namespace App\Models\Landlord;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class Subscription extends Model
{
    use UsesLandlordConnection;

    protected $guarded = ['id'];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
    ];

    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }
}
