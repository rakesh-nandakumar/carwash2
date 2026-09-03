@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Edit User</h1>
        <p>Update user information and permissions</p>
    </div>
</div>

<div class="panel">
    <form method="post" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')
        
        <h2>👤 User Information</h2>
        <div class="grid2">
            <label>
                Full Name
                <input type="text" name="name" value="{{ $user->name }}" required placeholder="Enter full name">
            </label>
            <label>
                Email Address
                <input type="email" name="email" value="{{ $user->email }}" required placeholder="Enter email address">
            </label>
        </div>
        <div class="grid2">
            <label>
                Password (leave blank to keep current)
                <input type="password" name="password" placeholder="Create new password">
            </label>
            <label>
                Confirm Password
                <input type="password" name="password_confirmation" placeholder="Confirm new password">
            </label>
        </div>
        
        <div style="margin-top: 30px;">
            <h2>🎭 Role Assignment</h2>
            <label>
                Assign Role
                <select name="role">
                    <option value="">-- No Predefined Role --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->slug }}" {{ in_array($role->slug, $userRoles) ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </label>
            
            <label class="checkbox-label">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" {{ $user->active ? 'checked' : '' }}>
                <span>Active User</span>
            </label>
        </div>
        
        <div style="margin-top: 30px;">
            <h2>🔐 Custom Permissions</h2>
            <p style="color: #666; font-size: 0.9em; margin-bottom: 15px;">Select specific permissions (optional - overrides role permissions)</p>
        
            <div class="permissions-container">
                <div class="permissions-header">
                    <div class="permissions-header-left">
                        <strong>Module</strong>
                    </div>
                    <div class="permissions-header-right">
                        <strong>Permissions</strong>
                    </div>
                </div>
                @foreach($permissions as $module => $modulePermissions)
                    <div class="permission-module">
                        <div class="permission-module-left">
                            <label class="module-checkbox-label">
                                <input type="checkbox" class="module-checkbox" data-module="{{ $module }}">
                                <span>{{ ucfirst($module) }}</span>
                            </label>
                        </div>
                        <div class="permission-module-right">
                            <div class="permissions-list">
                                @foreach($modulePermissions as $permission)
                                    <label class="permission-item">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="permission-checkbox" data-module="{{ $module }}" 
                                               {{ in_array($permission->id, $userPermissions) ? 'checked' : '' }}>
                                        <span>{{ $permission->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary">Update User</button>
            <a href="{{ route('users.index') }}" class="btn-cancel">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Cancel
            </a>
        </div>
    </form>
</div>

<style>
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.checkbox-label input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-label span {
    font-weight: 500;
    color: #333;
}

.permissions-container {
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
    background: white;
}

.permissions-header {
    display: flex;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 15px 20px;
    color: white;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.permissions-header-left {
    width: 200px;
    flex-shrink: 0;
}

.permissions-header-right {
    flex: 1;
}

.permission-module {
    display: flex;
    border-bottom: 1px solid #e0e0e0;
    transition: background-color 0.2s ease;
}

.permission-module:last-child {
    border-bottom: none;
}

.permission-module:hover {
    background-color: #f8f9fa;
}

.permission-module-left {
    width: 200px;
    flex-shrink: 0;
    padding: 15px 20px;
    background: #fafafa;
    border-right: 1px solid #e0e0e0;
}

.permission-module-right {
    flex: 1;
    padding: 15px 20px;
}

.module-checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
    cursor: pointer;
    transition: color 0.2s ease;
}

.module-checkbox-label:hover {
    color: #667eea;
}

.module-checkbox-label input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.permissions-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.permission-item {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background: #f8f9fa;
    border: 2px solid #e0e0e0;
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.permission-item:hover {
    border-color: #667eea;
    background: #f0f8ff;
}

.permission-item input {
    width: 16px;
    height: 16px;
    margin-right: 8px;
    cursor: pointer;
}

.permission-item:has(input:checked) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    color: white;
}

.permission-item input:checked + span {
    color: white;
    font-weight: 500;
}

/* Form actions */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 40px;
    gap: 12px;
}

.btn-cancel {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.15s;
}

.btn-cancel:hover {
    background: #fecaca;
    color: #b91c1c;
}

@media (max-width: 640px) {
    .form-actions {
        flex-direction: column;
        gap: 10px;
    }
    .form-actions .primary,
    .form-actions .btn-cancel {
        width: 100%;
        text-align: center;
        justify-content: center;
    }
}
</style>

<script>
// Initialize module checkboxes based on current permissions
document.querySelectorAll('.module-checkbox').forEach(checkbox => {
    const module = checkbox.dataset.module;
    const permissionCheckboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
    const allChecked = Array.from(permissionCheckboxes).every(pc => pc.checked);
    checkbox.checked = allChecked;
    
    // Set initial module background
    const moduleDiv = checkbox.closest('.permission-module');
    const anyChecked = Array.from(permissionCheckboxes).some(pc => pc.checked);
    if (anyChecked) {
        moduleDiv.style.backgroundColor = '#e8f0fe';
    }
});

// Module checkbox functionality - select/deselect all permissions in a module
document.querySelectorAll('.module-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const module = this.dataset.module;
        const permissionCheckboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
        permissionCheckboxes.forEach(pc => pc.checked = this.checked);
        
        // Add visual feedback
        const moduleDiv = this.closest('.permission-module');
        if (this.checked) {
            moduleDiv.style.backgroundColor = '#e8f0fe';
        } else {
            moduleDiv.style.backgroundColor = '';
        }
    });
});

// Update module checkbox when individual permissions change
document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const module = this.dataset.module;
        const moduleCheckbox = document.querySelector(`.module-checkbox[data-module="${module}"]`);
        const permissionCheckboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
        const allChecked = Array.from(permissionCheckboxes).every(pc => pc.checked);
        moduleCheckbox.checked = allChecked;
        
        // Visual feedback for module
        const moduleDiv = this.closest('.permission-module');
        const anyChecked = Array.from(permissionCheckboxes).some(pc => pc.checked);
        if (anyChecked) {
            moduleDiv.style.backgroundColor = '#e8f0fe';
        } else {
            moduleDiv.style.backgroundColor = '';
        }
    });
});
</script>
@endsection