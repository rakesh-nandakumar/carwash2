<?php

namespace App\Http\Controllers;

use App\Models\TillClosure;
use App\Services\CashMovementService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class TillClosureController extends Controller
{
    public function __construct(
        private CashMovementService $cashMovements,
        private AuditService $auditService
    ) {}

    public function showTillAction()
    {
        $till = $this->cashMovements->getSelectedTill();
        $currentClosure = $this->cashMovements->lastClosure($till);
        $isOpen = $currentClosure && !$currentClosure->closed_at;

        if ($isOpen) {
            $summary = $this->cashMovements->getCashMovementSummary($till, $currentClosure->opened_at);
            $expectedBalance = round(
                $currentClosure->opening_balance + $summary['net_change'],
                2
            );
            $previousClosingBalance = 0;
        } else {
            // When till is closed, show zero for all current shift values
            $summary = [
                'cash_sales' => 0,
                'card_sales' => 0,
                'mobile_money_sales' => 0,
                'bank_transfer_sales' => 0,
                'total_sales' => 0,
                'cash_in' => 0,
                'cash_out' => 0,
                'net_change' => 0,
            ];
            $expectedBalance = 0;
            $previousClosingBalance = $currentClosure ? $currentClosure->counted_balance : 0;
        }

        // Check if user has a permanent till assignment
        $user = auth()->user();
        $hasPermanentTill = $user->permanent_till_id !== null;
        
        // Users with manage settings permission should see all tills
        $canManageTills = $user->hasPermissionTo('settings.access');
        
        if ($canManageTills) {
            // Show all active tills for users with settings access
            $availableTills = \App\Models\Till::where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->get();
        } elseif ($hasPermanentTill) {
            // User has a permanent till - no selection needed
            $permanentTill = \App\Models\Till::find($user->permanent_till_id);
            $availableTills = collect([$permanentTill]);
        } elseif ($till && $till->current_user_id == $user->id) {
            // User has a till currently assigned to them (but not permanent) - use this one
            $availableTills = collect([$till]);
        } else {
            // First time user - show only available tills
            $availableTills = \App\Models\Till::where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->whereNull('current_user_id') // Only show available tills
                ->get();
        }

        // Get the most recent closure for this user to use as previous balance reference
        $userLastClosure = \App\Models\TillClosure::where('user_id', auth()->id())
            ->where('tenant_id', auth()->user()->tenant_id)
            ->whereNotNull('closed_at')
            ->latest('closed_at')
            ->first();

        $hasPreviousClosure = $userLastClosure !== null;
        // Use counted_balance (the manual amount entered when closing) as the previous closing balance
        $userPreviousClosingBalance = $hasPreviousClosure ? $userLastClosure->counted_balance : 0;

        return view('cashier.till-action', compact(
            'till',
            'currentClosure',
            'isOpen',
            'summary',
            'expectedBalance',
            'previousClosingBalance',
            'userPreviousClosingBalance',
            'availableTills',
            'hasPermanentTill',
            'hasPreviousClosure',
            'canManageTills'
        ));
    }

    public function handleTillAction(Request $request)
    {
        $till = $this->cashMovements->getSelectedTill();
        $currentClosure = $this->cashMovements->lastClosure($till);
        $isOpen = $currentClosure && !$currentClosure->closed_at;

        if ($isOpen) {
            // Closing the till
            \Log::info('Till closure request', [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            $data = $request->validate([
                'balance_option' => ['required', 'in:expected,manual'],
                'manual_balance' => ['nullable', 'numeric', 'min:0', 'required_if:balance_option,manual'],
                'variance_reason' => ['nullable', 'string', 'max:500'],
                'notes' => ['nullable', 'string', 'max:2000'],
                'denomination_breakdown' => ['nullable', 'array'],
            ]);

            $countedBalance = $data['balance_option'] === 'expected'
                ? $request->input('expected_balance')
                : (float) $data['manual_balance'];

            \Log::info('Closing till with balance', ['counted_balance' => $countedBalance]);

            // Calculate variance
            $expectedBalance = $request->input('expected_balance');
            $variance = $countedBalance - $expectedBalance;

            // Require variance reason if there's a variance
            if (abs($variance) > 0.01 && empty($data['variance_reason'])) {
                \Log::info('Variance reason required', ['variance' => $variance]);
                return back()
                    ->withInput()
                    ->withErrors(['variance_reason' => 'Please provide a reason for the variance.']);
            }

            $notes = $data['notes'] ?? null;

            try {
                \Log::info('Calling closeShift');
                $closure = $this->cashMovements->closeShift(
                    countedBalance: $countedBalance,
                    denominationBreakdown: $data['denomination_breakdown'] ?? null,
                    notes: $notes,
                    varianceReason: $data['variance_reason'] ?? null,
                    userId: auth()->id(),
                );
                \Log::info('Till closed successfully', ['closure_id' => $closure->id]);

                $this->auditService->log('till.closed', "Till #{$till->id} closed", 'info', 'tenant_user', auth()->user()->email, [
                    'till_id' => $till->id,
                    'till_name' => $till->name,
                    'closure_id' => $closure->id,
                    'opening_balance' => $closure->opening_balance,
                    'counted_balance' => $closure->counted_balance,
                    'expected_balance' => $closure->expected_balance,
                    'discrepancy' => $closure->discrepancy,
                    'notes' => $notes,
                ]);
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }

            // Keep the user's session till assignment so they can easily reopen the same till
            // The till itself is released (not in use) but the session remembers which till they used

            $discrepancyText = $closure->discrepancy == 0
                ? 'No discrepancy'
                : ($closure->discrepancy > 0
                    ? 'Overage: Rs. ' . number_format($closure->discrepancy, 2)
                    : 'Shortage: Rs. ' . number_format(abs($closure->discrepancy), 2));

            \Log::info('Redirecting to day report', ['closure_id' => $closure->id, 'route' => 'cashier.day-report']);

            return redirect()
                ->route('cashier.day-report', $closure)
                ->with('success', "Till closed successfully. {$discrepancyText}");
        } else {
            // Opening the till
            // Debug: Log incoming request data
            \Log::info('Till opening request', [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            $data = $request->validate([
                'till_id' => ['required', 'exists:tills,id'],
                'balance_option' => ['required', 'in:previous,manual'],
                'manual_balance' => ['nullable', 'numeric', 'min:0', 'required_if:balance_option,manual'],
                'variance_reason' => ['nullable', 'string', 'max:500'],
                'notes' => ['nullable', 'string', 'max:2000'],
            ], [
                'manual_balance.required_if' => 'Please enter an opening balance when selecting manual amount.',
                'till_id.required' => 'Please select a till.',
            ]);

            $user = auth()->user();

            // Get user's last closure for balance reference
            $userLastClosure = \App\Models\TillClosure::where('user_id', $user->id)
                ->where('tenant_id', $user->tenant_id)
                ->whereNotNull('closed_at')
                ->latest('closed_at')
                ->first();

            // Calculate opening balance based on selection
            if ($data['balance_option'] === 'previous' && $userLastClosure) {
                $openingBalance = $userLastClosure->counted_balance;
                $expectedBalance = $userLastClosure->counted_balance;
            } elseif ($data['balance_option'] === 'manual') {
                $openingBalance = (float) $data['manual_balance'];
                $expectedBalance = $userLastClosure ? $userLastClosure->counted_balance : 0;
            } else {
                $openingBalance = 0;
                $expectedBalance = 0;
            }

            // Calculate variance
            $variance = $openingBalance - $expectedBalance;

            // Require variance reason if there's a variance
            if (abs($variance) > 0.01 && empty($data['variance_reason'])) {
                return back()
                    ->withInput()
                    ->withErrors(['variance_reason' => 'Please provide a reason for the variance.']);
            }

            $notes = $data['notes'] ?? null;

            // Handle till selection
            $selectedTill = \App\Models\Till::findOrFail($data['till_id']);
            
            \Log::info('Till found', ['till_id' => $selectedTill->id, 'till_name' => $selectedTill->name]);
            
            // Verify tenant access
            if ($selectedTill->tenant_id !== $user->tenant_id) {
                \Log::error('Tenant access denied', ['user_tenant' => $user->tenant_id, 'till_tenant' => $selectedTill->tenant_id]);
                abort(403, 'You do not have access to this till.');
            }
            
            // Check if till is active
            if (!$selectedTill->is_active) {
                \Log::error('Till not active', ['till_id' => $selectedTill->id]);
                return back()->with('error', 'This till is not active.');
            }
            
            // Check if till is available (only if user doesn't already own this till and doesn't have settings access)
            if (!$user->permanent_till_id || $user->permanent_till_id != $selectedTill->id) {
                if (!$user->hasPermissionTo('settings.access') && $selectedTill->isInUse() && $selectedTill->current_user_id != $user->id) {
                    \Log::error('Till in use by another', ['till_id' => $selectedTill->id, 'current_user' => $selectedTill->current_user_id]);
                    return back()->with('error', 'This till is currently in use by another cashier.');
                }
            }

            // If user doesn't have a permanent till AND doesn't have settings access, assign this one permanently
            // Users with settings.access can use any till without making it permanent
            if (!$user->permanent_till_id && !$user->hasPermissionTo('settings.access')) {
                \Log::info('Assigning permanent till', ['user_id' => $user->id, 'till_id' => $selectedTill->id]);
                $user->update(['permanent_till_id' => $selectedTill->id]);

                $this->auditService->log('till.permanent_assigned', "Till #{$selectedTill->id} permanently assigned to user", 'info', 'tenant_user', $user->email, [
                    'till_id' => $selectedTill->id,
                    'till_name' => $selectedTill->name,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                ]);
            } elseif ($user->hasPermissionTo('settings.access') && $user->permanent_till_id) {
                // Remove permanent till assignment for users with settings.access
                \Log::info('Removing permanent till for admin user', ['user_id' => $user->id]);
                $user->update(['permanent_till_id' => null]);

                $this->auditService->log('till.permanent_removed', "Permanent till assignment removed for admin user", 'info', 'tenant_user', $user->email, [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                ]);
            }

            // Switch to the selected till
            $currentTill = $this->cashMovements->getSelectedTill();
            
            // Release current till if different
            if ($currentTill && $currentTill->id != $selectedTill->id) {
                if ($currentTill->current_user_id == $user->id) {
                    \Log::info('Releasing current till', ['current_till_id' => $currentTill->id]);
                    $currentTill->release();

                    $this->auditService->log('till.released', "Till #{$currentTill->id} released by user", 'info', 'tenant_user', $user->email, [
                        'till_id' => $currentTill->id,
                        'till_name' => $currentTill->name,
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                    ]);
                }
            }
            
            // Mark new till as in use
            \Log::info('Marking till as in use', ['till_id' => $selectedTill->id, 'user_id' => $user->id]);
            $selectedTill->markAsInUse($user->id);
            $user->update(['session_till_id' => $selectedTill->id]);

            $this->auditService->log('till.assigned', "Till #{$selectedTill->id} assigned to user", 'info', 'tenant_user', $user->email, [
                'till_id' => $selectedTill->id,
                'till_name' => $selectedTill->name,
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);

            // Handle balance selection - already calculated above (lines 178-185)
            \Log::info('Opening till with balance', ['balance' => $openingBalance]);

            try {
                \Log::info('Attempting to open shift', ['till_id' => $selectedTill->id, 'balance' => $openingBalance]);
                $closure = $this->cashMovements->openShift(
                    openingBalance: $openingBalance,
                    notes: $notes,
                    varianceReason: $data['variance_reason'] ?? null,
                    userId: $user->id,
                    till: $selectedTill,
                );
                \Log::info('Shift opened successfully', ['closure_id' => $closure->id]);

                $this->auditService->log('till.opened', "Till #{$selectedTill->id} opened", 'info', 'tenant_user', $user->email, [
                    'till_id' => $selectedTill->id,
                    'till_name' => $selectedTill->name,
                    'closure_id' => $closure->id,
                    'opening_balance' => $closure->opening_balance,
                    'notes' => $notes,
                ]);
            } catch (\RuntimeException $e) {
                \Log::error('Failed to open shift', ['error' => $e->getMessage()]);
                return back()->with('error', $e->getMessage());
            }

            \Log::info('Redirecting to cashier index');
            return redirect()
                ->route('cashier.index')
                ->with('success', 'Till opened successfully with opening balance: Rs. ' . number_format($closure->opening_balance, 2));
        }
    }

    public function history(Request $request)
    {
        $till = $this->cashMovements->getSelectedTill();
        $closures = $till->closures()
            ->with('user')
            ->orderBy('opened_at', 'desc')
            ->paginate(20);

        return view('cashier.shift-history', compact('till', 'closures'));
    }

    public function show(TillClosure $closure)
    {
        $till = $this->cashMovements->getSelectedTill();

        if ($closure->till_id !== $till->id) {
            abort(404);
        }

        $closure->load('user');

        return view('cashier.shift-show', compact('till', 'closure'));
    }

    public function printDayReport(Request $request, TillClosure $closure)
    {
        $till = $this->cashMovements->getSelectedTill();

        if ($closure->till_id !== $till->id) {
            abort(404, 'Closure not found for this till.');
        }

        if (!$closure->closed_at) {
            return back()->with('error', 'Shift must be closed to generate report.');
        }

        \Log::info('Day report data', [
            'closure_id' => $closure->id,
            'variance_reason' => $closure->variance_reason,
            'discrepancy' => $closure->discrepancy,
        ]);

        // Get all cash movements for this closure
        $cashMovements = \App\Models\CashMovement::where('till_closure_id', $closure->id)
            ->with('user')
            ->orderBy('created_at')
            ->get();

        // Get all payments for this closure period
        if ($closure->opened_at && $closure->closed_at) {
            $payments = \App\Models\Payment::where('created_at', '>=', $closure->opened_at)
                ->where('created_at', '<=', $closure->closed_at)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->with(['invoice', 'invoice.customer'])
                ->get();

            \Log::info('Payments for day report', [
                'closure_id' => $closure->id,
                'opened_at' => $closure->opened_at,
                'closed_at' => $closure->closed_at,
                'payment_count' => $payments->count(),
                'payment_total' => $payments->sum('amount'),
            ]);
        } else {
            $payments = collect(); // Return empty collection if dates are missing
        }

        // Group cash movements by type
        $cashInMovements = $cashMovements->where('type', 'in')->where('source', 'manual');
        $cashOutMovements = $cashMovements->where('type', 'out')->where('source', 'manual');

        // Count transactions
        $transactionCount = $payments->count();
        $cashInCount = $cashInMovements->count();
        $cashOutCount = $cashOutMovements->count();

        return view('cashier.day-report', compact(
            'till',
            'closure',
            'cashMovements',
            'cashInMovements',
            'cashOutMovements',
            'payments',
            'transactionCount',
            'cashInCount',
            'cashOutCount'
        ));
    }
}
