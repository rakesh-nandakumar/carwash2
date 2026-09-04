<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleCategory extends Model
{ use BelongsToTenant;
    protected $guarded = [];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function servicePricings(): HasMany
    {
        return $this->hasMany(ServiceVehiclePricing::class);
    }
}
