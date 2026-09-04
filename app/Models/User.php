<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Services\TenantModules;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use BelongsToTenant, HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'business_id',
        'name',
        'email',
        'password',
        'role',
        'branch_id',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function isAdmin(): bool
    {
        return $this->roles()->where('slug', 'super_admin')->exists()
            || in_array($this->role, ['super_admin', 'owner']);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return in_array($permission, $this->permissionSlugs(), true);
    }

    /**
     * hasPermission() AND the module-licensing gate. The licensing check is
     * deliberately OUTSIDE hasPermission() — isAdmin() short-circuits the
     * permission check for anyone whose `users.role` string is super_admin or
     * owner, but licensing outranks the tenant's own permissions: a disabled
     * module is 403'd for a super_admin too.
     */
    public function canAccess(string $permission): bool
    {
        if (! $this->hasPermission($permission)) {
            return false;
        }

        $module = TenantModules::moduleKeyForPermission($permission);

        if ($module !== null && ! TenantModules::isEnabled($module)) {
            return false;
        }

        return true;
    }

    public function assignRole(string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        $this->roles()->syncWithoutDetaching([$role->id]);
        $this->flushPermissionCache();
    }

    public function removeRole(string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        $this->roles()->detach($role->id);
        $this->flushPermissionCache();
    }

    public function flushPermissionCache(): void
    {
        Cache::forget("user.{$this->id}.permissions");
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * @return list<string>
     */
    private function permissionSlugs(): array
    {
        return Cache::remember("user.{$this->id}.permissions", 3600, function () {
            return $this->roles()
                ->with('permissions')
                ->get()
                ->pluck('permissions')
                ->flatten()
                ->pluck('slug')
                ->toArray();
        });
    }
}
