<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralAdmin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Platform operator management — who may sign in to master control. Keeps the
 * two self-lockout guards: you can never disable or delete the account you
 * are currently signed in as, and the last active operator can never be
 * removed.
 */
class CentralAdminController extends Controller
{
    public function index()
    {
        return view('central.admins.index', [
            'admins' => CentralAdmin::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('central_admins', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        CentralAdmin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()->route('central.admins.index')->with('success', 'Operator created.');
    }

    public function update(Request $request, CentralAdmin $admin)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:150'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('central_admins', 'email')->ignore($admin->id)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // Self-lockout guard: you can't revoke your own access.
        $isSelf = $admin->id === $request->user('central')->id;
        if ($isSelf && isset($data['is_active']) && $data['is_active'] === false) {
            abort(422, 'You cannot deactivate your own account.');
        }

        // The last active operator is the platform's own lifeline.
        if (! ($data['is_active'] ?? true)
            && $admin->is_active
            && $this->isLastActiveAdmin($admin->id)) {
            abort(422, 'The last active platform operator cannot be deactivated.');
        }

        $admin->update($data);

        return back()->with('success', 'Operator updated.');
    }

    public function destroy(Request $request, CentralAdmin $admin)
    {
        abort_if($admin->id === $request->user('central')->id, 422, 'You cannot delete your own account.');

        abort_if(
            $admin->is_active && $this->isLastActiveAdmin($admin->id),
            422,
            'The last active platform operator cannot be deleted.',
        );

        $admin->delete();

        return back()->with('success', 'Operator deleted.');
    }

    private function isLastActiveAdmin(int $ignoreId): bool
    {
        return CentralAdmin::query()
            ->where('is_active', true)
            ->where('id', '!=', $ignoreId)
            ->doesntExist();
    }
}
