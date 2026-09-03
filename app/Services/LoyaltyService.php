<?php

namespace App\Services;

use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use App\Models\Membership;
use App\Models\Customer;
use App\Models\Invoice;
use App\Enums\LoyaltyTransactionType;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    public function getAccount(int $customerId): LoyaltyAccount
    {
        return LoyaltyAccount::firstOrCreate(
            ['customer_id' => $customerId],
            [
                'points' => 0,
                'tier' => 'Bronze',
            ]
        );
    }

    public function earnPoints(int $customerId, float $amount, ?string $referenceType = null, ?int $referenceId = null): LoyaltyTransaction
    {
        return DB::transaction(function () use ($customerId, $amount, $referenceType, $referenceId) {
            $account = $this->getAccount($customerId);
            
            // Calculate points based on settings
            $pointsPerRupee = (float) \App\Models\Setting::get('loyalty', 'points_per_rupee', 0.1);
            $pointsEarned = $amount * $pointsPerRupee;
            
            $account->points += $pointsEarned;
            $account->save();
            
            // Update tier based on points
            $this->updateTier($account);
            
            return LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'type' => LoyaltyTransactionType::EARNED,
                'points' => $pointsEarned,
                'balance_after' => $account->points,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => "Points earned from purchase",
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function redeemPoints(int $customerId, float $pointsToRedeem, ?string $referenceType = null, ?int $referenceId = null): LoyaltyTransaction
    {
        return DB::transaction(function () use ($customerId, $pointsToRedeem, $referenceType, $referenceId) {
            $account = $this->getAccount($customerId);
            
            if ($account->points < $pointsToRedeem) {
                abort(422, 'Insufficient loyalty points');
            }
            
            $account->points -= $pointsToRedeem;
            $account->save();
            
            return LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'type' => LoyaltyTransactionType::REDEEMED,
                'points' => -$pointsToRedeem,
                'balance_after' => $account->points,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => "Points redeemed",
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function adjustPoints(int $customerId, float $points, string $reason): LoyaltyTransaction
    {
        return DB::transaction(function () use ($customerId, $points, $reason) {
            $account = $this->getAccount($customerId);
            
            $account->points += $points;
            if ($account->points < 0) {
                abort(422, 'Cannot have negative points');
            }
            $account->save();
            
            $this->updateTier($account);
            
            return LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'type' => LoyaltyTransactionType::ADJUSTED,
                'points' => $points,
                'balance_after' => $account->points,
                'description' => $reason,
                'created_by' => auth()->id(),
            ]);
        });
    }

    private function updateTier(LoyaltyAccount $account): void
    {
        $tiers = [
            'Bronze' => 0,
            'Silver' => 1000,
            'Gold' => 5000,
            'Platinum' => 10000,
        ];
        
        $newTier = 'Bronze';
        foreach ($tiers as $tier => $requiredPoints) {
            if ($account->points >= $requiredPoints) {
                $newTier = $tier;
            }
        }
        
        if ($account->tier !== $newTier) {
            $account->tier = $newTier;
            $account->save();
        }
    }

    public function getPointsValue(float $points): float
    {
        $rupeesPerPoint = (float) \App\Models\Setting::get('loyalty', 'rupees_per_point', 1.0);
        return $points * $rupeesPerPoint;
    }

    public function createMembership(int $customerId, string $name, float $price, int $totalUses, ?int $validMonths = null): Membership
    {
        return Membership::create([
            'customer_id' => $customerId,
            'name' => $name,
            'total_uses' => $totalUses,
            'used_uses' => 0,
            'expires_at' => $validMonths ? now()->addMonths($validMonths) : null,
        ]);
    }

    public function useMembership(int $membershipId): Membership
    {
        return DB::transaction(function () use ($membershipId) {
            $membership = Membership::findOrFail($membershipId);
            
            if ($membership->expires_at && $membership->expires_at < now()) {
                abort(422, 'Membership has expired');
            }
            
            if ($membership->used_uses >= $membership->total_uses) {
                abort(422, 'Membership has no remaining uses');
            }
            
            $membership->used_uses += 1;
            $membership->save();
            
            return $membership;
        });
    }

    public function getCustomerLoyaltySummary(int $customerId): array
    {
        $account = $this->getAccount($customerId);
        $customer = Customer::with('memberships')->findOrFail($customerId);
        
        return [
            'points' => $account->points,
            'tier' => $account->tier,
            'points_value' => $this->getPointsValue($account->points),
            'memberships' => $customer->memberships->map(function ($membership) {
                return [
                    'name' => $membership->name,
                    'total_uses' => $membership->total_uses,
                    'used_uses' => $membership->used_uses,
                    'remaining_uses' => $membership->total_uses - $membership->used_uses,
                    'expires_at' => $membership->expires_at?->format('Y-m-d'),
                    'is_expired' => $membership->expires_at && $membership->expires_at < now(),
                ];
            }),
            'transaction_history' => LoyaltyTransaction::where('loyalty_account_id', $account->id)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'type' => $transaction->type,
                        'points' => $transaction->points,
                        'balance_after' => $transaction->balance_after,
                        'description' => $transaction->description,
                        'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
        ];
    }

    public function processInvoiceForLoyalty(int $invoiceId): void
    {
        $invoice = Invoice::with('customer')->findOrFail($invoiceId);
        
        // Earn points from the purchase
        $this->earnPoints(
            $invoice->customer_id,
            $invoice->total,
            'invoice',
            $invoiceId
        );
    }

    public function expireOldPoints(): void
    {
        $expiryMonths = (int) \App\Models\Setting::get('loyalty', 'points_expiry_months', 12);
        
        $oldTransactions = LoyaltyTransaction::where('type', LoyaltyTransactionType::EARNED)
            ->where('created_at', '<', now()->subMonths($expiryMonths))
            ->whereDoesntHave('loyaltyAccount', function ($query) {
                $query->where('tier', 'Platinum'); // Platinum members don't expire
            })
            ->get();
        
        foreach ($oldTransactions as $transaction) {
            $this->adjustPoints(
                $transaction->loyaltyAccount->customer_id,
                -$transaction->points,
                'Points expired after ' . $expiryMonths . ' months'
            );
            
            $transaction->update(['type' => LoyaltyTransactionType::EXPIRED]);
        }
    }
}
