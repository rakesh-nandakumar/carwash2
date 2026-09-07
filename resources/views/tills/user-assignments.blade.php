@extends('layouts.app')
@section('content')
<div class="user-till-assignments">
    <div class="assignments-header">
        <div>
            <h1>User Till Assignments</h1>
            <p>Manage permanent till assignments for cashiers. Users with admin privileges (settings.access) are not shown as they can switch tills freely.</p>
        </div>
        <form method="POST" action="{{ route('tills.clear-admin-permanent-tills') }}" onsubmit="return confirm('This will remove permanent till assignments from all users with admin privileges. Are you sure?');" style="display: inline;">
            @csrf
            <button type="submit" class="btn-clear-admin">Clear Admin Permanent Tills</button>
        </form>
    </div>

    <div class="assignments-table">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Current Till Assignment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td data-label="User">
                            <strong>{{ $user->name }}</strong>
                            <small>{{ $user->email }}</small>
                        </td>
                        <td data-label="Current Till Assignment">
                            @if($user->permanentTill)
                                <span class="till-assigned">
                                    {{ $user->permanentTill->name }} ({{ $user->permanentTill->code }})
                                </span>
                            @else
                                <span class="till-unassigned">No till assigned</span>
                            @endif
                        </td>
                        <td data-label="Actions">
                            <button type="button" onclick="openAssignmentModal({{ $user->id }}, '{{ $user->name }}', {{ $user->permanent_till_id ?? 'null' }})" class="btn-assign">
                                {{ $user->permanentTill ? 'Change Till' : 'Assign Till' }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="back-button-container">
        <a href="{{ route('tills.index') }}" class="btn-back">← Back to Tills</a>
    </div>
</div>

<!-- Assignment Modal -->
<div id="assignmentModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Assign Till</h2>
            <button type="button" onclick="closeAssignmentModal()">×</button>
        </div>
        <form method="POST" action="{{ route('tills.update-user-assignment') }}">
            @csrf
            <input type="hidden" name="user_id" id="modalUserId">
            
            <div class="form-group">
                <label>User</label>
                <input type="text" id="modalUserName" readonly class="readonly-input">
            </div>

            <div class="form-group">
                <label>Select Till</label>
                <select name="till_id" id="modalTillId" required>
                    <option value="">-- Select a Till --</option>
                    @foreach($tills as $till)
                        <option value="{{ $till->id }}">
                            {{ $till->name }} ({{ $till->code }})
                            @if($till->location)
                                - {{ $till->location }}
                            @endif
                        </option>
                    @endforeach
                </select>
                <small>Leave empty to remove till assignment</small>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeAssignmentModal()">Cancel</button>
                <button type="submit" class="btn-confirm">Save Assignment</button>
            </div>
        </form>
    </div>
</div>

<style>
.user-till-assignments {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.assignments-header {
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.back-button-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 100;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    padding: 12px 20px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-back:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
}

.assignments-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.assignments-header p {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

.assignments-table {
    background: white;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.assignments-table table {
    width: 100%;
    border-collapse: collapse;
}

.assignments-table thead {
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}

.assignments-table th {
    padding: 16px 24px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.assignments-table td {
    padding: 16px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.assignments-table tr:last-child td {
    border-bottom: none;
}

.assignments-table strong {
    display: block;
    font-size: 14px;
    color: #1e293b;
    font-weight: 600;
}

.assignments-table small {
    display: block;
    font-size: 12px;
    color: #94a3b8;
    margin-top: 2px;
}

.till-assigned {
    display: inline-block;
    padding: 4px 10px;
    background: #dcfce7;
    color: #166534;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.till-unassigned {
    display: inline-block;
    padding: 4px 10px;
    background: #f1f5f9;
    color: #64748b;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.btn-assign {
    padding: 8px 16px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-assign:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Modal Styles */
.modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 16px;
}

.modal.hidden {
    display: none;
}

.modal-content {
    background: white;
    border-radius: 16px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.modal-header h2 {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.modal-header button {
    background: none;
    border: none;
    font-size: 24px;
    line-height: 1;
    color: #94a3b8;
    cursor: pointer;
    padding: 0 4px;
}

.modal-header button:hover {
    color: #1e293b;
}

.modal-content form {
    padding: 24px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    background: #f8fafc;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.readonly-input {
    background: #f1f5f9;
    color: #64748b;
    cursor: not-allowed;
}

.form-group small {
    font-size: 12px;
    color: #94a3b8;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 8px;
}

.btn-cancel {
    padding: 10px 18px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: white;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.btn-cancel:hover {
    background: #f8fafc;
}

.btn-confirm {
    padding: 10px 18px;
    border-radius: 8px;
    border: none;
    background: #3b82f6;
    color: white;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.btn-confirm:hover {
    background: #2563eb;
}

.btn-clear-admin {
    padding: 10px 18px;
    background: #f59e0b;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-clear-admin:hover {
    background: #d97706;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

/* Responsive Styles */
@media (max-width: 768px) {
    .user-till-assignments {
        margin: 20px auto;
        padding: 0 16px;
        padding-bottom: 80px;
    }

    .assignments-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .assignments-header h1 {
        font-size: 24px;
    }

    .assignments-header p {
        font-size: 13px;
    }

    .assignments-table {
        border-radius: 12px;
    }

    .assignments-table thead {
        display: none;
    }

    .assignments-table tbody {
        display: flex;
        flex-direction: column;
    }

    .assignments-table tr {
        display: flex;
        flex-direction: column;
        border-bottom: 1px solid #e5e7eb;
        padding: 16px;
        gap: 12px;
    }

    .assignments-table td {
        padding: 0;
        border-bottom: none;
    }

    .assignments-table td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #64748b;
        font-size: 12px;
        display: block;
        margin-bottom: 4px;
    }

    .assignments-table strong {
        font-size: 16px;
    }

    .assignments-table small {
        font-size: 13px;
    }

    .btn-assign {
        width: 100%;
        padding: 12px 16px;
        font-size: 14px;
    }

    .btn-clear-admin {
        width: 100%;
        text-align: center;
        margin-top: 12px;
    }

    .back-button-container {
        bottom: 16px;
        right: 16px;
    }

    .btn-back {
        padding: 10px 16px;
        font-size: 13px;
    }

    .modal-content {
        margin: 16px;
        max-width: calc(100% - 32px);
    }

    .modal-header {
        padding: 16px 20px;
    }

    .modal-content form {
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .assignments-header h1 {
        font-size: 20px;
    }

    .till-assigned,
    .till-unassigned {
        font-size: 11px;
        padding: 3px 8px;
    }

    .back-button-container {
        bottom: 12px;
        right: 12px;
    }

    .btn-back {
        padding: 8px 14px;
        font-size: 12px;
    }
}
</style>

<script>
function openAssignmentModal(userId, userName, currentTillId) {
    document.getElementById('modalUserId').value = userId;
    document.getElementById('modalUserName').value = userName;
    document.getElementById('modalTillId').value = currentTillId || '';
    document.getElementById('assignmentModal').classList.remove('hidden');
}

function closeAssignmentModal() {
    document.getElementById('assignmentModal').classList.add('hidden');
}
</script>
@endsection