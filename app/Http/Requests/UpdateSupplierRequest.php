<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:150',
            'business_name' => 'nullable|string|max:200',
            'company' => 'nullable|string|max:200',
            'registration_number' => 'nullable|string|max:80',
            'tax_number' => 'nullable|string|max:80',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'website' => 'nullable|url|max:200',
            'address' => 'nullable|string',
            'payment_terms' => 'nullable|string|max:100',
            'payment_terms_id' => 'nullable|exists:settings,id',
            'credit_limit' => 'nullable|numeric|min:0|max:99999999.99',
            'notes' => 'nullable|string|max:1000',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'required|string|max:100',
            'contacts.*.designation' => 'nullable|string|max:100',
            'contacts.*.phone' => 'nullable|string|max:30',
            'contacts.*.email' => 'nullable|email|max:150',
            'contacts.*.is_primary' => 'nullable|boolean',
            'addresses' => 'nullable|array',
            'addresses.*.address_type' => 'required|string|in:billing,shipping',
            'addresses.*.street_line1' => 'required|string|max:200',
            'addresses.*.street_line2' => 'nullable|string|max:200',
            'addresses.*.city' => 'nullable|string|max:100',
            'addresses.*.state' => 'nullable|string|max:100',
            'addresses.*.postal_code' => 'nullable|string|max:20',
            'addresses.*.country' => 'nullable|string|max:100',
            'addresses.*.is_primary' => 'nullable|boolean',
            'bank_accounts' => 'nullable|array',
            'bank_accounts.*.bank_name' => 'required|string|max:100',
            'bank_accounts.*.branch_name' => 'nullable|string|max:100',
            'bank_accounts.*.account_number' => 'required|string|max:50',
            'bank_accounts.*.account_holder' => 'required|string|max:150',
            'bank_accounts.*.is_primary' => 'nullable|boolean',
        ];
    }
}
