<?php namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use App\Models\JobService;
use App\Models\Business;

class Service extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
    protected $casts = [
        'active' => 'boolean',
        'base_price' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'tax_rate' => 'decimal:2'
    ];

    public function jobServices()
    {
        return $this->hasMany(JobService::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
