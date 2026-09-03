<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return redirect()->route('settings.billing');
    }

    public function billing()
    {
        $business = auth()->user()->business;
        if (!$business) {
            return back()->with('error', 'No business associated with your account.');
        }
        $settings = $business->getBillingSettings();
        
        return view('settings.billing', compact('settings'));
    }

    public function reception()
    {
        $business = auth()->user()->business;
        if (!$business) {
            return back()->with('error', 'No business associated with your account.');
        }
        $settings = $business->getBillingSettings();
        
        return view('settings.reception', compact('settings'));
    }

    public function updateBilling(Request $request)
    {
        $business = auth()->user()->business;
        if (!$business) {
            return back()->with('error', 'No business associated with your account.');
        }
        
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'tax_id' => 'nullable|string|max:50',
            'invoice_prefix' => 'required|string|max:10',
            'receipt_prefix' => 'required|string|max:10',
            'footer_text' => 'nullable|string|max:500',
            'terms_conditions' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'default_format' => 'required|in:a4,thermal',
            'logo_size_a4' => 'nullable|integer|min:30|max:150',
            'logo_size_thermal' => 'nullable|integer|min:20|max:80',
        ]);

        // Handle logo upload
        $currentSettings = $business->getBillingSettings();
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/logos'), $filename);
            $validated['logo_path'] = 'uploads/logos/' . $filename;
        } elseif ($request->has('logo_path') && empty($request->input('logo_path'))) {
            // Logo was removed
            $validated['logo_path'] = '';
        } else {
            // Keep existing logo
            $validated['logo_path'] = $currentSettings['logo_path'] ?? '';
        }

        // Handle checkbox values manually - if not present, set to false
        $validated['a4_enabled'] = $request->has('a4_enabled') ? true : false;
        $validated['thermal_enabled'] = $request->has('thermal_enabled') ? true : false;

        // Preserve reception background from current settings
        $validated['reception_background_image'] = $currentSettings['reception_background_image'] ?? '';

        $business->saveBillingSettings($validated);

        return back()->with('success', 'Billing settings updated successfully.');
    }

    public function updateReception(Request $request)
    {
        $business = auth()->user()->business;
        if (!$business) {
            return back()->with('error', 'No business associated with your account.');
        }
        
        $validated = $request->validate([
            'background_type' => 'required|in:image,color',
            'reception_background' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'background_color' => 'nullable|string|max:255',
            'custom_background_color' => 'nullable|string|max:20',
        ]);

        // Handle reception background upload
        $currentSettings = $business->getBillingSettings();
        
        if ($request->input('background_type') === 'image') {
            if ($request->hasFile('reception_background')) {
                $file = $request->file('reception_background');
                $filename = time() . '_bg_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/backgrounds'), $filename);
                $validated['reception_background_image'] = 'uploads/backgrounds/' . $filename;
            } elseif ($request->has('reception_background_image') && empty($request->input('reception_background_image'))) {
                // Background was removed
                $validated['reception_background_image'] = '';
            } else {
                // Keep existing background
                $validated['reception_background_image'] = $currentSettings['reception_background_image'] ?? '';
            }
            // Clear color settings when using image
            $validated['background_color'] = '';
            $validated['custom_background_color'] = '';
        } else {
            // Using color, clear image
            $validated['reception_background_image'] = '';
            
            // Check if a predefined color was selected from radio buttons
            $selectedPredefinedColor = $request->input('background_color');
            $customColor = $request->input('custom_background_color');
            
            // If a predefined color radio is selected, use it (priority over custom)
            if ($selectedPredefinedColor && $selectedPredefinedColor !== '') {
                $validated['background_color'] = $selectedPredefinedColor;
                $validated['custom_background_color'] = '';
            } 
            // Otherwise, use custom color if it's not the default placeholder
            elseif ($customColor && $customColor !== '#667eea') {
                $validated['custom_background_color'] = $customColor;
                $validated['background_color'] = '';
            } 
            // Fallback to default gradient
            else {
                $validated['background_color'] = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                $validated['custom_background_color'] = '';
            }
        }
        
        $validated['background_type'] = $request->input('background_type');

        // Preserve all billing settings from current settings
        $validated['company_name'] = $currentSettings['company_name'] ?? '';
        $validated['address'] = $currentSettings['address'] ?? '';
        $validated['phone'] = $currentSettings['phone'] ?? '';
        $validated['email'] = $currentSettings['email'] ?? '';
        $validated['website'] = $currentSettings['website'] ?? '';
        $validated['tax_id'] = $currentSettings['tax_id'] ?? '';
        $validated['invoice_prefix'] = $currentSettings['invoice_prefix'] ?? 'INV';
        $validated['receipt_prefix'] = $currentSettings['receipt_prefix'] ?? 'REC';
        $validated['footer_text'] = $currentSettings['footer_text'] ?? '';
        $validated['terms_conditions'] = $currentSettings['terms_conditions'] ?? '';
        $validated['logo_path'] = $currentSettings['logo_path'] ?? '';
        $validated['default_format'] = $currentSettings['default_format'] ?? 'a4';
        $validated['a4_enabled'] = $currentSettings['a4_enabled'] ?? true;
        $validated['thermal_enabled'] = $currentSettings['thermal_enabled'] ?? true;
        $validated['logo_size_a4'] = $currentSettings['logo_size_a4'] ?? 60;
        $validated['logo_size_thermal'] = $currentSettings['logo_size_thermal'] ?? 40;

        $business->saveBillingSettings($validated);

        return back()->with('success', 'Reception settings updated successfully.');
    }
}