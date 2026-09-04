<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

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
        return $this->belongsToMany(
            Role::class,
            'role_user'
        );
    }

    public function permissionOverrides(): HasMany
    {
        return $this->hasMany(UserPermissionOverride::class);
    }

    public function isFullAdmin(): bool
    {
        return $this->roles()
            ->where('is_active', true)
            ->where('is_full_admin', true)
            ->exists();
    }

    /**
     * Backwards compatibility.
     */
    public function isAdmin(): bool
    {
        return $this->isFullAdmin();
    }

    public function effectivePermissions(): array
    {
        if (! $this->active) {
            return [];
        }

        return Cache::remember(
            $this->permissionCacheKey(),
            now()->addMinutes(30),
            function () {
                $rolePermissions = $this->roles()
                    ->where('is_active', true)
                    ->with('permissions')
                    ->get()
                    ->pluck('permissions')
                    ->flatten()
                    ->pluck('slug')
                    ->unique()
                    ->values()
                    ->all();

                $overrides = $this->permissionOverrides()
                    ->with('permission')
                    ->get();

                $allows = $overrides
                    ->where('type', 'allow')
                    ->pluck('permission.slug')
                    ->filter()
                    ->values()
                    ->all();

                $denies = $overrides
                    ->where('type', 'deny')
                    ->pluck('permission.slug')
                    ->filter()
                    ->values()
                    ->all();

                return collect($rolePermissions)
                    ->merge($allows)
                    ->unique()
                    ->diff($denies)
                    ->values()
                    ->all();
            }
        );
    }

    public function hasPermissionTo(string $permission): bool
    {
        if (! $this->active) {
            return false;
        }

        if ($this->isFullAdmin()) {
            return true;
        }

        return in_array(
            $permission,
            $this->effectivePermissions(),
            true
        );
    }

    /**
     * Backwards compatibility while old routes/views are migrated.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->hasPermissionTo(
            $this->normalizeLegacyPermission($permission)
        );
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    public function clearPermissionCache(): void
    {
        Cache::forget($this->permissionCacheKey());
    }

    public function permissionCacheKey(): string
    {
        return "user.{$this->id}.permissions";
    }

    public function assignRole(string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)
            ->where('tenant_id', $this->tenant_id)
            ->where('business_id', $this->business_id)
            ->firstOrFail();

        $this->roles()->syncWithoutDetaching([
            $role->id,
        ]);

        $this->clearPermissionCache();
    }

    public function removeRole(string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)
            ->where('tenant_id', $this->tenant_id)
            ->where('business_id', $this->business_id)
            ->firstOrFail();

        $this->roles()->detach($role->id);

        $this->clearPermissionCache();
    }

    private function normalizeLegacyPermission(string $permission): string
    {
        static $map = [
            'view_reception' => 'reception.access',
            'view_dashboard' => 'dashboard.access',
            'view_live_job_board' => 'live_job_board.access',

            'view_job_cards' => 'job_cards.access',
            'create_job_cards' => 'job_cards.create',
            'edit_job_cards' => 'job_cards.edit',
            'delete_job_cards' => 'job_cards.delete',
            'change_status_job_cards' => 'job_cards.change_status',
            'request_additional_work_job_cards' => 'job_cards.request_additional_work',
            'approve_job_cards' => 'job_cards.approve',
            'consume_parts_job_cards' => 'job_cards.consume_parts',
            'edit_inspection_job_cards' => 'job_cards.edit_inspection',

            'view_customers' => 'customers.access',
            'create_customers' => 'customers.create',
            'edit_customers' => 'customers.edit',
            'delete_customers' => 'customers.delete',

            'view_vehicles' => 'vehicles.access',
            'create_vehicles' => 'vehicles.create',
            'edit_vehicles' => 'vehicles.edit',
            'delete_vehicles' => 'vehicles.delete',

            'view_appointments' => 'appointments.access',
            'create_appointments' => 'appointments.create',
            'edit_appointments' => 'appointments.edit',
            'delete_appointments' => 'appointments.delete',

            'view_item_master' => 'inventory.access',
            'create_item_master' => 'inventory.create',
            'edit_item_master' => 'inventory.edit',
            'delete_item_master' => 'inventory.delete',
            'adjust_stock_item_master' => 'inventory.adjust_stock',

            'view_stock_adjustments' => 'stock_adjustments.access',
            'create_stock_adjustments' => 'stock_adjustments.create',
            'reverse_stock_adjustments' => 'stock_adjustments.reverse',

            'view_categories' => 'categories.access',
            'create_categories' => 'categories.create',
            'edit_categories' => 'categories.edit',
            'delete_categories' => 'categories.delete',

            'view_services' => 'services.access',
            'create_services' => 'services.create',
            'edit_services' => 'services.edit',
            'delete_services' => 'services.delete',

            'view_invoices' => 'invoices.access',
            'pay_invoices' => 'invoices.pay',
            'print_invoices' => 'invoices.print',

            'view_cashier' => 'cashier.access',
            'search_cashier' => 'cashier.search',
            'payment_cashier' => 'cashier.payment',
            'print_options_cashier' => 'cashier.print_options',

            'view_reports' => 'reports.access',

            'view_users' => 'users.access',
            'create_users' => 'users.create',
            'edit_users' => 'users.edit',
            'delete_users' => 'users.delete',

            'view_settings' => 'settings.access',
            'edit_billing_settings' => 'settings.edit_billing',
        ];

        return $map[$permission] ?? $permission;
    }
}