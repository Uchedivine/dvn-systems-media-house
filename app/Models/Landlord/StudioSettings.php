<?php

namespace App\Models\Landlord;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class StudioSettings extends Model
{
    use UsesLandlordConnection;

    protected $guarded = ['id'];

    protected $casts = [
        'slot_times' => 'array',
    ];

    protected $attributes = [
        'slot_times' => '["09:00","13:00","16:00"]',
    ];

    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }
}
