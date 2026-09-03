<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService
{
    public function get(string $group, string $key, $default = null)
    {
        return Setting::get($group, $key, $default);
    }

    public function set(string $group, string $key, $value): void
    {
        Setting::set($group, $key, $value);
    }

    public function getGroup(string $group): array
    {
        return Setting::where('group', $group)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    public function setGroup(string $group, array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($group, $key, $value);
        }
    }

    public function initializeDefaultSettings(): void
    {
        $defaults = [
            'general' => [
                'business_name' => 'AutoCare Pro',
                'business_address' => '',
                'business_phone' => '',
                'business_email' => '',
                'tax_rate' => 0,
                'currency' => 'USD',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
            ],
            'loyalty' => [
                'points_per_rupee' => 0.1,
                'rupees_per_point' => 1.0,
                'points_expiry_months' => 12,
                'membership_discount_percent' => 10,
                'enabled' => true,
            ],
            'whatsapp' => [
                'provider' => 'meta',
                'api_url' => 'https://graph.facebook.com/v17.0',
                'api_token' => '',
                'phone_number_id' => '',
                'enabled' => false,
            ],
            'sms' => [
                'provider' => 'twilio',
                'api_url' => '',
                'api_token' => '',
                'from_number' => '',
                'enabled' => false,
            ],
            'email' => [
                'from_address' => 'noreply@autocare.com',
                'from_name' => 'AutoCare Pro',
                'enabled' => true,
            ],
            'notifications' => [
                'vehicle_received' => true,
                'inspection_completed' => true,
                'additional_work_required' => true,
                'service_started' => true,
                'waiting_for_parts' => true,
                'service_completed' => true,
                'vehicle_ready' => true,
                'payment_received' => true,
            ],
            'inventory' => [
                'low_stock_alert_threshold' => 10,
                'auto_reorder_enabled' => false,
                'default_supplier_id' => null,
            ],
            'billing' => [
                'auto_invoice_on_completion' => true,
                'require_payment_before_delivery' => false,
                'allow_partial_payments' => true,
                'invoice_prefix' => 'INV',
            ],
            'appointments' => [
                'default_duration_minutes' => 60,
                'allow_overlapping' => false,
                'reminder_hours_before' => 24,
                'auto_reminder_enabled' => true,
            ],
        ];

        foreach ($defaults as $group => $settings) {
            foreach ($settings as $key => $value) {
                Setting::firstOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value, 'type' => gettype($value)]
                );
            }
        }
    }

    public function getBusinessSettings(): array
    {
        return array_merge(
            $this->getGroup('general'),
            $this->getGroup('billing')
        );
    }

    public function updateBusinessSettings(array $data): void
    {
        $this->setGroup('general', [
            'business_name' => $data['business_name'] ?? null,
            'business_address' => $data['business_address'] ?? null,
            'business_phone' => $data['business_phone'] ?? null,
            'business_email' => $data['business_email'] ?? null,
            'tax_rate' => $data['tax_rate'] ?? 0,
            'currency' => $data['currency'] ?? 'USD',
        ]);

        $this->setGroup('billing', [
            'auto_invoice_on_completion' => $data['auto_invoice_on_completion'] ?? true,
            'require_payment_before_delivery' => $data['require_payment_before_delivery'] ?? false,
            'allow_partial_payments' => $data['allow_partial_payments'] ?? true,
        ]);
    }

    public function getNotificationSettings(): array
    {
        return $this->getGroup('notifications');
    }

    public function updateNotificationSettings(array $data): void
    {
        $this->setGroup('notifications', $data);
    }

    public function getCommunicationSettings(): array
    {
        return array_merge(
            $this->getGroup('whatsapp'),
            $this->getGroup('sms'),
            $this->getGroup('email')
        );
    }

    public function updateCommunicationSettings(array $data): void
    {
        if (isset($data['whatsapp'])) {
            $this->setGroup('whatsapp', $data['whatsapp']);
        }
        if (isset($data['sms'])) {
            $this->setGroup('sms', $data['sms']);
        }
        if (isset($data['email'])) {
            $this->setGroup('email', $data['email']);
        }
    }

    public function getLoyaltySettings(): array
    {
        return $this->getGroup('loyalty');
    }

    public function updateLoyaltySettings(array $data): void
    {
        $this->setGroup('loyalty', $data);
    }

    public function getInventorySettings(): array
    {
        return $this->getGroup('inventory');
    }

    public function updateInventorySettings(array $data): void
    {
        $this->setGroup('inventory', $data);
    }
}
