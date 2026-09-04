@extends('layouts.app')

@section('content')
<div class="edit-user-page">

    <div class="page-header">
        <h1>Edit User</h1>
        <p>Update user information and permissions</p>
    </div>

    @if($errors->any())
        <div class="alert error">
            <strong>Please fix the following errors:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')

        {{-- ==================== User Information ==================== --}}
        <div class="card">
            <div class="card-header">
                <div class="card-icon user">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <h2>User Information</h2>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required placeholder="Full name">
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="email@example.com">
                </div>

                <div class="form-group">
                    <label>Password <span class="hint">(leave blank to keep current)</span></label>
                    <input type="password" name="password" placeholder="Create new password">
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" placeholder="Confirm new password">
                </div>
            </div>
        </div>

        {{-- ==================== Role Assignment ==================== --}}
        <div class="card">
            <div class="card-header">
                <div class="card-icon role">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h2>Role Assignment</h2>
            </div>

            <div class="form-group" style="max-width: 420px;">
                <label>Assign Role</label>
                <select name="roles[]" id="role-select" class="role-select">
                    <option value="">Select a role</option>
                    @foreach($roles as $role)
                        <option
                            value="{{ $role->id }}"
                            @selected(in_array($role->id, old('roles', $userRoles)))
                        >
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <label class="active-checkbox">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" @checked(old('active', $user->active))>
                <span class="checkmark"></span>
                Active User
            </label>
        </div>

        {{-- ==================== Custom Permissions ==================== --}}
        <div class="card">
            <div class="card-header">
                <div class="card-icon permissions">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                    </svg>
                </div>
                <div>
                    <h2>Custom Permissions</h2>
                    <p class="subtitle">Select specific permissions (optional – override role permissions)</p>
                </div>
            </div>

            <div class="permissions-table">
                <div class="permissions-header">
                    <div class="col-module">MODULES</div>
                    <div class="col-permissions">PERMISSIONS</div>
                </div>

                @foreach($permissions as $module => $modulePermissions)
                    <div class="permissions-row" data-module="{{ $module }}">
                        <div class="col-module">
                            <label class="module-checkbox">
                                <input type="checkbox" class="module-select-all">
                                <span class="checkmark"></span>
                                {{ ucfirst(str_replace('_', ' ', $module)) }}
                            </label>
                        </div>

                        <div class="col-permissions">
                            @foreach($modulePermissions as $permission)
                                @php
                                    $state = old(
                                        "permission_overrides.{$permission->id}",
                                        $overrides[$permission->id] ?? ''
                                    );
                                @endphp

                                <label class="permission-pill">
                                    <input
                                        type="checkbox"
                                        class="permission-checkbox"
                                        data-permission-id="{{ $permission->id }}"
                                        data-permission-slug="{{ $permission->slug }}"
                                        value="1"
                                        {{ $state === 'allow' || $state === '' ? '' : '' }}
                                    >

                                    <input
                                        type="hidden"
                                        class="override-input"
                                        data-permission-id="{{ $permission->id }}"
                                        name="permission_overrides[{{ $permission->id }}]"
                                        value="{{ $state }}"
                                        {{ $state === '' ? 'disabled' : '' }}
                                    >

                                    <span class="pill-check">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </span>

                                    <span class="permission-name">{{ $permission->name }}</span>
                                    <span class="permission-state"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ==================== Actions ==================== --}}
        <div class="form-actions">
            <button type="submit" class="btn-primary">
                Update User
            </button>
            <a href="{{ route('users.index') }}" class="btn-cancel">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Cancel
            </a>
        </div>
    </form>
</div>

<style>
    .edit-user-page {
        max-width: 960px;
        margin: 0 auto;
        padding: 20px 16px 60px;
    }

    .page-header {
        margin-bottom: 28px;
    }

    .page-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e1b4b;
        margin: 0 0 4px 0;
    }

    .page-header p {
        margin: 0;
        color: #6b7280;
        font-size: 0.95rem;
    }

    /* Card */
    .card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 28px 32px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
    }

    .card-header h2 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e1b4b;
        margin: 0;
    }

    .card-header .subtitle {
        margin: 2px 0 0 0;
        font-size: 0.85rem;
        color: #6b7280;
        font-weight: 400;
    }

    .card-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .card-icon.user {
        background: #ede9fe;
        color: #7c3aed;
    }

    .card-icon.role {
        background: #dbeafe;
        color: #2563eb;
    }

    .card-icon.permissions {
        background: #fce7f3;
        color: #db2777;
    }

    /* Form */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
    }

    .form-group .hint {
        font-weight: 400;
        color: #9ca3af;
        font-size: 0.8rem;
    }

    .form-group input,
    .role-select {
        width: 100%;
        padding: 11px 14px;
        font-size: 0.95rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        color: #111827;
        transition: border-color 0.15s, box-shadow 0.15s;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .role-select:focus {
        outline: none;
        border-color: #8b5cf6;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.12);
    }

    .role-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 40px;
    }

    /* Active checkbox */
    .active-checkbox {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
        font-size: 0.95rem;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        user-select: none;
    }

    .active-checkbox input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .active-checkbox .checkmark {
        width: 18px;
        height: 18px;
        border: 2px solid #d1d5db;
        border-radius: 5px;
        background: #fff;
        position: relative;
        transition: all 0.15s;
        flex-shrink: 0;
    }

    .active-checkbox input:checked + .checkmark {
        background: #7c3aed;
        border-color: #7c3aed;
    }

    .active-checkbox input:checked + .checkmark::after {
        content: "";
        position: absolute;
        left: 5px;
        top: 1px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    /* Permissions Table */
    .permissions-table {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }

    .permissions-header {
        display: grid;
        grid-template-columns: 200px 1fr;
        background: #7c3aed;
        color: white;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .permissions-header .col-module,
    .permissions-header .col-permissions {
        padding: 12px 20px;
    }

    .permissions-row {
        display: grid;
        grid-template-columns: 200px 1fr;
        border-bottom: 1px solid #f3f4f6;
        background: #fff;
    }

    .permissions-row:last-child {
        border-bottom: none;
    }

    .permissions-row:nth-child(even) {
        background: #fafafa;
    }

    .col-module {
        padding: 16px 20px;
        display: flex;
        align-items: flex-start;
        border-right: 1px solid #f3f4f6;
    }

    .col-permissions {
        padding: 14px 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    /* Module checkbox */
    .module-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        user-select: none;
    }

    .module-checkbox input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .module-checkbox .checkmark {
        width: 18px;
        height: 18px;
        border: 2px solid #d1d5db;
        border-radius: 5px;
        background: #fff;
        position: relative;
        transition: all 0.15s;
        flex-shrink: 0;
    }

    .module-checkbox input:checked + .checkmark {
        background: #7c3aed;
        border-color: #7c3aed;
    }

    .module-checkbox input:checked + .checkmark::after {
        content: "";
        position: absolute;
        left: 5px;
        top: 1px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    /* Permission Pills */
    .permission-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px 6px 8px;
        background: #f5f3ff;
        border: 1px solid #ddd6fe;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 500;
        color: #5b21b6;
        cursor: pointer;
        transition: all 0.15s;
        user-select: none;
        position: relative;
    }

    .permission-pill:hover {
        background: #ede9fe;
        border-color: #c4b5fd;
    }

    .permission-pill input[type="checkbox"],
    .permission-pill input[type="hidden"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        pointer-events: none;
    }

    .permission-pill .pill-check {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #ddd6fe;
        display: flex;
        align-items: center;
        justify-content: center;
        color: transparent;
        transition: all 0.15s;
        flex-shrink: 0;
    }

    .permission-pill .permission-name {
        white-space: nowrap;
    }

    .permission-pill .permission-state {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        opacity: 0.85;
        margin-left: 2px;
    }

    /* States */
    .permission-pill.inherited {
        background: #7c3aed;
        border-color: #7c3aed;
        color: white;
    }

    .permission-pill.inherited .pill-check {
        background: rgba(255, 255, 255, 0.25);
        color: white;
    }

    .permission-pill.allowed {
        background: #16a34a;
        border-color: #16a34a;
        color: white;
    }

    .permission-pill.allowed .pill-check {
        background: rgba(255, 255, 255, 0.25);
        color: white;
    }

    /* Actions */
    .form-actions {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-top: 8px;
    }

    .btn-primary {
        background: #f97316;
        color: white;
        border: none;
        padding: 12px 28px;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.15s;
    }

    .btn-primary:hover {
        background: #ea580c;
    }

    .btn-cancel {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #6b7280;
        font-size: 0.95rem;
        font-weight: 500;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 8px;
        transition: all 0.15s;
    }

    .btn-cancel:hover {
        background: #f3f4f6;
        color: #374151;
    }

    /* Alert */
    .alert.error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
    }

    .alert.error strong {
        display: block;
        margin-bottom: 6px;
    }

    .alert.error ul {
        margin: 0;
        padding-left: 18px;
    }

    /* Responsive */
    @media (max-width: 700px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .permissions-header,
        .permissions-row {
            grid-template-columns: 1fr;
        }

        .col-module {
            border-right: none;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 10px;
        }

        .card {
            padding: 20px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const roleSelect = document.getElementById('role-select');

    const roles = @json(
        $roles->mapWithKeys(function ($role) {
            return [
                $role->id => $role->permissions
                    ->pluck('slug')
                    ->values()
                    ->all()
            ];
        })
    );

    // Existing overrides from the server
    const existingOverrides = @json($overrides ?? []);

    const permissionCheckboxes =
        document.querySelectorAll('.permission-checkbox');

    function getSelectedRolePermissions() {
        const inherited = new Set();

        Array.from(roleSelect.selectedOptions).forEach(option => {
            const roleId = option.value;
            const rolePermissions = roles[roleId] || [];

            rolePermissions.forEach(permission => {
                inherited.add(permission);
            });
        });

        return inherited;
    }

    function updatePermission(checkbox, inherited) {
        const slug = checkbox.dataset.permissionSlug;
        const permissionId = checkbox.dataset.permissionId;

        const override = document.querySelector(
            `.override-input[data-permission-id="${permissionId}"]`
        );
        const pill = checkbox.closest('.permission-pill');
        const isInherited = inherited.has(slug);

        pill.classList.remove('inherited', 'allowed');

        // Role provides permission + checked → Inherited
        if (isInherited && checkbox.checked) {
            pill.classList.add('inherited');
            pill.querySelector('.permission-state').textContent = 'Inherited';
            override.disabled = true;
            override.value = '';
            return;
        }

        // Role does NOT provide permission + checked → Allowed
        if (!isInherited && checkbox.checked) {
            pill.classList.add('allowed');
            pill.querySelector('.permission-state').textContent = 'Allowed';
            override.disabled = false;
            override.value = 'allow';
            return;
        }

        // Role provides permission + unchecked → silently deny
        if (isInherited && !checkbox.checked) {
            pill.querySelector('.permission-state').textContent = '';
            override.disabled = false;
            override.value = 'deny';
            return;
        }

        // No role permission + unchecked → no override
        pill.querySelector('.permission-state').textContent = '';
        override.disabled = true;
        override.value = '';
    }

    function refreshPermissions() {
        const inherited = getSelectedRolePermissions();

        permissionCheckboxes.forEach(checkbox => {
            updatePermission(checkbox, inherited);
        });

        updateModuleCheckboxes();
    }

    function updateModuleCheckboxes() {
        document.querySelectorAll('.permissions-row').forEach(row => {
            const moduleCheckbox = row.querySelector('.module-select-all');
            const permissions = row.querySelectorAll('.permission-checkbox');

            if (!moduleCheckbox || !permissions.length) return;

            const checked = Array.from(permissions).filter(input => input.checked).length;

            moduleCheckbox.checked = checked === permissions.length;
            moduleCheckbox.indeterminate = checked > 0 && checked < permissions.length;
        });
    }

    // Role changed → auto-check role permissions
    roleSelect.addEventListener('change', function () {
        const inherited = getSelectedRolePermissions();

        permissionCheckboxes.forEach(checkbox => {
            if (inherited.has(checkbox.dataset.permissionSlug)) {
                checkbox.checked = true;
            } else {
                checkbox.checked = false;
            }
        });

        refreshPermissions();
    });

    // Individual permission changed
    permissionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            refreshPermissions();
        });
    });

    // Module select-all
    document.querySelectorAll('.module-select-all').forEach(moduleCheckbox => {
        moduleCheckbox.addEventListener('change', function () {
            const row = this.closest('.permissions-row');

            row.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
            });

            refreshPermissions();
        });
    });

    // ========== INITIAL LOAD ==========
    // Set the correct checked state based on role + existing overrides
    (function initPermissions() {
        const inherited = getSelectedRolePermissions();

        permissionCheckboxes.forEach(checkbox => {
            const permissionId = checkbox.dataset.permissionId;
            const slug = checkbox.dataset.permissionSlug;
            const overrideValue = existingOverrides[permissionId] || '';

            if (overrideValue === 'allow') {
                // Explicitly allowed
                checkbox.checked = true;
            } else if (overrideValue === 'deny') {
                // Explicitly denied
                checkbox.checked = false;
            } else {
                // No override → follow the role
                checkbox.checked = inherited.has(slug);
            }
        });

        refreshPermissions();
    })();

});
</script>
@endsection