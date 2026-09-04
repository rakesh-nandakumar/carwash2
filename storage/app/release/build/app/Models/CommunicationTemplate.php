<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class CommunicationTemplate extends Model
{ use BelongsToTenant;
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'auto_send' => 'boolean',
    ];
}
