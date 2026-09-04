<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * A platform operator — the "master control" principal. A wholly separate
 * authenticatable from App\Models\User: it authenticates only via the
 * `central` guard, is never subject to any TenantScope, and manages tenants
 * and their settings/modules on their behalf.
 */
class CentralAdmin extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\CentralAdminFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];
}
