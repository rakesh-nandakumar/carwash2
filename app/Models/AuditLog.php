<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
        'is_flagged' => 'boolean',
    ];
}
