<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{ use BelongsToTenant;
    protected $fillable = ['tenant_id', 'name', 'business_id'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
