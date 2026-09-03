<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationTemplate extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'auto_send' => 'boolean',
    ];
}
