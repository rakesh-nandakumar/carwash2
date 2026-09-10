<?php

namespace App\Http\Controllers;

use App\Models\Till;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TillManagementController extends Controller
{
    public function index(Request $request)
    {
        $tills = Till::with('currentUser')
            ->where('tenant_id', Auth::user()->tenant_id)
            ->orderBy('name')
            ->paginate(20);

        return view('tills.index', compact('tills'));
    }

    public function create()
    {
        return view('tills.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:tills,code,NULL,id,tenant_id,' . Auth::user()->tenant_id],
            'description' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean', 'nullable'],
        ]);

        $data['tenant_id'] = Auth::user()->tenant_id;
        $data['is_active'] = $request->has('is_active') ? true : false;

        Till::create($data);

        return redirect()
            ->route('tills.index')
            ->with('success', 'Till created successfully.');
    }

    public function show(Till $till)
    {
        $this->authorizeTillAccess($till);

        $till->load(['closures' => function ($query) {
            $query->with('user')->latest()->limit(10);
        }]);

        return view('tills.show', compact('till'));
    }

    public function edit(Till $till)
    {
        $this->authorizeTillAccess($till);

        return view('tills.edit', compact('till'));
    }

    public function update(Request $request, Till $till)
    {
        $this->authorizeTillAccess($till);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:tills,code,' . $till->id . ',id,tenant_id,' . Auth::user()->tenant_id],
            'description' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean', 'nullable'],
        ]);

        $data['is_active'] = $request->has('is_active') ? true : false;

        $till->update($data);

        return redirect()
            ->route('tills.index')
            ->with('success', 'Till updated successfully.');
    }

    public function destroy(Till $till)
    {
        $this->authorizeTillAccess($till);

        // Allow administrators to delete tills even with open shifts
        if ($till->isOpen() && !auth()->user()->hasPermissionTo('settings.access')) {
            return back()->with('error', 'Cannot delete a till that has an open shift.');
        }

        $till->delete();

        return redirect()
            ->route('tills.index')
            ->with('success', 'Till deleted successfully.');
    }

    public function selectTill(Request $request)
    {
        $request->validate([
            'till_id' => ['required', 'exists:tills,id'],
        ]);

        $till = Till::findOrFail($request->till_id);
        $userId = Auth::id();
        $userName = Auth::user()->name;

        error_log("TILL SELECT: User {$userName} (ID: {$userId}) -> Till {$till->name} (ID: {$till->id})");

        if ($till->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'You do not have access to this till.');
        }

        if (!$till->is_active) {
            return back()->with('error', 'This till is not active.');
        }

        // Check if till is in use by another user
        if ($till->isInUse() && $till->current_user_id !== $userId) {
            error_log("TILL CONFLICT: Till {$till->name} already in use by user ID: {$till->current_user_id}");
            // Check if the current user's session is stale (inactive for 30+ minutes)
            try {
                if ($till->isStale()) {
                    error_log("TILL STALE: Releasing stale session for till {$till->name}");
                    // Release the stale session
                    $till->release();
                } else {
                    $currentUser = $till->currentUser;
                    error_log("TILL BUSY: Till {$till->name} actively used by {$currentUser->name}");
                    return back()->with('error', "This till is currently in use by {$currentUser->name}. Please select a different till.");
                }
            } catch (\Exception $e) {
                error_log("TILL ERROR: " . $e->getMessage());
                // If there's an error checking staleness, treat it as not stale for safety
                $currentUser = $till->currentUser;
                return back()->with('error', "This till is currently in use by {$currentUser->name}. Please select a different till.");
            }
        }

        // Release previous till if user had one selected
        $previousTillId = Auth::user()->session_till_id;
        if ($previousTillId && $previousTillId != $till->id) {
            error_log("TILL RELEASE: Previous till ID: {$previousTillId}");
            $previousTill = Till::find($previousTillId);
            if ($previousTill && $previousTill->current_user_id == $userId) {
                $previousTill->release();
                error_log("TILL RELEASED: Previous till {$previousTill->name}");
            }
        }

        // Mark new till as in use
        error_log("TILL ASSIGN: Marking till {$till->name} for user {$userName}");
        $till->markAsInUse($userId);
        
        // Only update session_till_id, never assign permanent till for users with settings.access
        $updateData = ['session_till_id' => $till->id];
        
        if (Auth::user()->hasPermissionTo('settings.access')) {
            // Remove permanent till assignment for users with settings.access
            if (Auth::user()->permanent_till_id) {
                $updateData['permanent_till_id'] = null;
                error_log("TILL PERMANENT REMOVED: Removing permanent till for admin user {$userName}");
            }
        } else {
            // For users without settings.access, assign permanent till if they don't have one
            if (!Auth::user()->permanent_till_id) {
                $updateData['permanent_till_id'] = $till->id;
                error_log("TILL PERMANENT: Assigning permanent till for user {$userName}");
            }
        }
        
        Auth::user()->update($updateData);

        // Verify the update
        $updatedTill = Till::find($till->id);
        error_log("TILL VERIFY: Updated till {$updatedTill->name} - current_user_id: {$updatedTill->current_user_id}, last_activity: {$updatedTill->last_activity_at}");

        error_log("TILL SUCCESS: User {$userName} now using till {$till->name}");

        $redirectUrl = $request->input('redirect_to', back()->getTargetUrl());
        return redirect($redirectUrl)->with('success', "Selected till: {$till->name}");
    }

    public function clearTillSelection()
    {
        $user = Auth::user();
        $tillId = $user->session_till_id;

        if ($tillId) {
            $till = Till::find($tillId);
            if ($till && $till->current_user_id == $user->id) {
                $till->release();
            }
        }

        $user->update(['session_till_id' => null]);

        return back()->with('success', 'Till selection cleared.');
    }

    public function getTillStatus()
    {
        try {
            $currentUserId = Auth::id();
            $currentUserName = Auth::user()->name;
            error_log("STATUS REQ: User {$currentUserName} (ID: {$currentUserId})");

            // Update current user's till activity timestamp
            $currentTillId = Auth::user()->session_till_id;
            if ($currentTillId) {
                $currentTill = Till::find($currentTillId);
                if ($currentTill && $currentTill->current_user_id == $currentUserId) {
                    try {
                        $currentTill->update(['last_activity_at' => now()]);
                    } catch (\Exception $e) {
                        error_log('Error updating till activity: ' . $e->getMessage());
                    }
                }
            }

            $tills = Till::where('tenant_id', Auth::user()->tenant_id)
                ->where('is_active', true)
                ->with('currentUser')
                ->get()
                ->map(function ($till) use ($currentUserId) {
                    try {
                        $lastActivity = $till->last_activity_at ? $till->last_activity_at->toIso8601String() : null;
                    } catch (\Exception $e) {
                        $lastActivity = null;
                    }
                    
                    $tillData = [
                        'id' => $till->id,
                        'name' => $till->name,
                        'code' => $till->code,
                        'location' => $till->location,
                        'is_in_use' => $till->isInUse(),
                        'current_user_id' => $till->current_user_id,
                        'current_user_name' => $till->currentUser ? $till->currentUser->name : null,
                        'is_my_till' => $till->current_user_id == $currentUserId,
                        'last_activity_at' => $lastActivity,
                    ];
                    
                    if ($tillData['is_in_use']) {
                        error_log("STATUS: Till {$till->name} used by {$tillData['current_user_name']} (ID: {$tillData['current_user_id']})");
                    }
                    
                    return $tillData;
                });

            error_log("STATUS: Returning {$tills->count()} tills for user {$currentUserName}");

            return response()->json([
                'tills' => $tills,
                'current_user_id' => $currentUserId,
            ]);
        } catch (\Exception $e) {
            error_log('STATUS ERROR: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'tills' => [],
                'current_user_id' => Auth::id(),
            ], 500);
        }
    }

    private function authorizeTillAccess(Till $till)
    {
        if ($till->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'You do not have access to this till.');
        }
    }

    public function userTillAssignments()
    {
        $allUsers = \App\Models\User::where('tenant_id', Auth::user()->tenant_id)
            ->where('active', true)
            ->with(['permanentTill', 'roles.permissions'])
            ->orderBy('name')
            ->get();

        // Filter out users with settings.access permission
        $users = $allUsers->filter(function($user) {
            return !$user->hasPermissionTo('settings.access');
        });

        $tills = Till::where('tenant_id', Auth::user()->tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('tills.user-assignments', compact('users', 'tills'));
    }

    public function updateUserTillAssignment(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'till_id' => ['nullable', 'exists:tills,id'],
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        
        if ($user->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'You do not have access to this user.');
        }

        if ($request->till_id) {
            $till = Till::findOrFail($request->till_id);
            if ($till->tenant_id !== Auth::user()->tenant_id) {
                abort(403, 'You do not have access to this till.');
            }
        }

        // Prevent assigning permanent tills to users with settings.access
        if ($user->hasPermissionTo('settings.access') && $request->till_id) {
            return back()->with('error', 'Users with admin privileges cannot be assigned permanent tills. They can switch tills freely.');
        }

        $user->update(['permanent_till_id' => $request->till_id]);

        return back()->with('success', 'User till assignment updated successfully.');
    }

    public function clearAdminPermanentTills()
    {
        $users = \App\Models\User::where('tenant_id', Auth::user()->tenant_id)
            ->where('active', true)
            ->with(['permanentTill', 'roles.permissions'])
            ->orderBy('name')
            ->get();

        // Filter users with settings.access permission and permanent till assignments
        $adminUsersWithPermanentTills = $users->filter(function($user) {
            return $user->hasPermissionTo('settings.access') && $user->permanent_till_id;
        });

        $count = 0;
        foreach ($adminUsersWithPermanentTills as $user) {
            $user->update(['permanent_till_id' => null]);
            $count++;
        }

        return back()->with('success', "Removed permanent till assignments from {$count} admin users.");
    }
}