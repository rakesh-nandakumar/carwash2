<?php

namespace App\Enums;

enum LoyaltyTransactionType: string
{
    case EARNED = 'earned';
    case REDEEMED = 'redeemed';
    case EXPIRED = 'expired';
    case ADJUSTED = 'adjusted';

    public function getLabel(): string
    {
        return match($this) {
            self::EARNED => 'Earned',
            self::REDEEMED => 'Redeemed',
            self::EXPIRED => 'Expired',
            self::ADJUSTED => 'Adjusted',
        };
    }

    public function isPositive(): bool
    {
        return in_array($this, [self::EARNED, self::ADJUSTED]);
    }
}
