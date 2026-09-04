@extends('central.layouts.central')

@section('title', 'Settings — '.$tenant->name)

@section('content')
    <h2>{{ $tenant->name }} — Settings</h2>
    <p class="sub">Edited by platform operators only. Values shown in <span class="override-badge overridden">Overridden</span> have been touched; everything else renders the catalog default.</p>

    <div class="tabs">
        <a href="{{ route('central.tenants.show', $tenant) }}">Overview</a>
        <a class="active" href="{{ route('central.tenants.settings', $tenant) }}">Settings</a>
        <a href="{{ route('central.tenants.modules', $tenant) }}">Modules</a>
        <a href="{{ route('central.tenants.audit', $tenant) }}">Audit</a>
    </div>

    <link rel="stylesheet" href="{{ asset('css/central-settings.css') }}">

    @php
        $val = fn (string $k, $d = null) => $settings[$k]['value'] ?? $d;
        $ovr = fn (string $k) => ($settings[$k]['overridden'] ?? false) ? 'overridden' : 'default';
    @endphp

    <div class="settings-tabs">
        <button class="tab-btn active" onclick="showTab('reception')" id="tab-reception">Reception</button>
        <button class="tab-btn" onclick="showTab('billing')" id="tab-billing">Billing & Branding</button>
        <button class="tab-btn" onclick="showTab('all')" id="tab-all">All settings</button>
    </div>

    {{-- ============ RECEPTION ============ --}}
    <div class="tab-content active" id="content-reception">
        <div class="panel">
            <form method="post" action="{{ route('central.tenants.settings.update', $tenant) }}" enctype="multipart/form-data" id="reception-form">
                @csrf
                <h2>🎨 Background Appearance <span class="override-badge {{ $ovr('reception.background_type') }}">{{ $ovr('reception.background_type') === 'overridden' ? 'Overridden' : 'Default' }}</span></h2>
                <div class="bg-type-selector">
                    <label class="bg-type-option">
                        <input type="radio" name="s[reception.background_type]" value="image" {{ $val('reception.background_type', 'image') === 'image' ? 'checked' : '' }} id="bg-type-image" onchange="toggleBackgroundType()">
                        <span class="bg-type-card"><span class="bg-type-icon">🖼️</span><span class="bg-type-label">Use Image</span><span class="bg-type-desc">Upload a custom background image</span></span>
                    </label>
                    <label class="bg-type-option">
                        <input type="radio" name="s[reception.background_type]" value="color" {{ $val('reception.background_type', 'image') === 'color' ? 'checked' : '' }} id="bg-type-color" onchange="toggleBackgroundType()">
                        <span class="bg-type-card"><span class="bg-type-icon">🎨</span><span class="bg-type-label">Use Color</span><span class="bg-type-desc">Choose a solid or gradient color</span></span>
                    </label>
                </div>

                <div class="bg-upload-section" id="bg-upload-section" {{ $val('reception.background_type', 'image') === 'color' ? 'style="display:none;"' : '' }}>
                    <h3>Background Image <span class="override-badge {{ $ovr('reception.background_image') }}">{{ $ovr('reception.background_image') === 'overridden' ? 'Overridden' : 'Default' }}</span></h3>
                    <div class="upload-zone" id="bg-dropzone">
                        @if($val('reception.background_image', ''))
                            <div class="current-image">
                                <img src="{{ \App\Support\Media::url($val('reception.background_image')) }}" alt="Current Background">
                                <button type="button" onclick="removeBackground()" class="remove-btn">Remove Image</button>
                            </div>
                        @else
                            <div class="upload-placeholder"><span class="upload-icon">📁</span><span class="upload-text">Click to upload or drag and drop</span><span class="upload-subtext">JPEG, PNG, JPG, GIF (Max 5MB)</span></div>
                        @endif
                        <input type="file" name="reception_background" id="bg-input" accept="image/*" style="display: none;">
                    </div>
                    <input type="hidden" name="s[reception.background_image]" value="{{ $val('reception.background_image', '') }}" id="bg-path-hidden">
                </div>

                <div class="bg-color-section" id="bg-color-section" {{ $val('reception.background_type', 'image') === 'image' ? 'style="display:none;"' : '' }}>
                    <h3>Background Color</h3>
                    <div class="color-presets">
                        @foreach([
                            'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' => 'Purple',
                            'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' => 'Pink',
                            'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)' => 'Blue',
                            'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)' => 'Green',
                            'linear-gradient(135deg, #fa709a 0%, #fee140 100%)' => 'Orange',
                            'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)' => 'Pastel',
                            'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)' => 'Dark Blue',
                            'linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%)' => 'Black',
                            'linear-gradient(135deg, #2d3436 0%, #636e72 100%)' => 'Gray',
                            '#667eea' => 'Purple Solid',
                            '#4facfe' => 'Blue Solid',
                            '#43e97b' => 'Green Solid',
                            '#fa709a' => 'Pink Solid',
                            '#fee140' => 'Yellow Solid',
                        ] as $color => $name)
                        <label class="color-option">
                            <input type="radio" name="s[reception.background_color]" value="{{ $color }}" {{ $val('reception.background_color', '') === $color ? 'checked' : '' }} onchange="updateColorPreview()">
                            <div class="color-swatch" style="background: {{ $color }};"><span class="color-check">✓</span></div>
                            <span class="color-name">{{ $name }}</span>
                        </label>
                        @endforeach
                    </div>
                    <div class="custom-color-section">
                        <label>Custom Color</label>
                        <div class="color-picker-wrapper">
                            <div class="color-preview-box" id="custom-color-preview" style="background: {{ $val('reception.custom_background_color', '#667eea') }};"></div>
                            <input type="color" name="s[reception.custom_background_color]" value="{{ $val('reception.custom_background_color', '#667eea') }}" id="custom-bg-color" onchange="updateCustomColorPreview()">
                            <input type="text" class="color-hex-input" value="{{ $val('reception.custom_background_color', '#667eea') }}" id="color-hex-value" oninput="updateColorFromHex()">
                        </div>
                    </div>
                </div>

                <h2>👁️ Live Preview</h2>
                <div class="preview-wrapper">
                    <div id="reception-preview" class="reception-preview-full">
                        @if($val('reception.background_type', 'image') === 'color' && $val('reception.background_color', ''))
                            <div class="preview-bg" style="background: {{ $val('reception.background_color') }};"></div>
                        @elseif($val('reception.background_type', 'image') === 'color' && $val('reception.custom_background_color', ''))
                            <div class="preview-bg" style="background: {{ $val('reception.custom_background_color') }};"></div>
                        @elseif($val('reception.background_image', null))
                            <div class="preview-bg" style="background-image: url('{{ \App\Support\Media::url($val('reception.background_image')) }}');"></div>
                        @else
                            <div class="preview-bg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                        @endif
                        <div class="preview-content">
                            <div class="reception-nav">
                                <button class="nav-toggle">☰</button>
                                <div class="nav-menu"><a href="#">Dashboard</a><a href="#">Job Cards</a><a href="#">Customers</a><a href="#">Settings</a></div>
                            </div>
                            <div class="reception-header"><h1>Vehicle Check-in</h1><p>Enter vehicle registration number to begin</p></div>
                            <div class="search-section"><div class="search-box"><span class="search-placeholder">Vehicle registration number...</span></div></div>
                        </div>
                    </div>
                </div>

                <div class="settings-save-bar">
                    <button class="primary">Save Reception Settings</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============ BILLING & BRANDING ============ --}}
    <div class="tab-content" id="content-billing">
        <div class="billing-grid">
            <div class="panel">
                <form method="post" action="{{ route('central.tenants.settings.update', $tenant) }}" enctype="multipart/form-data" id="billing-form">
                    @csrf
                    <h2>🏢 Company Information</h2>
                    <div class="grid2">
                        <label>Company Name <span class="override-badge {{ $ovr('business.company_name') }}">{{ $ovr('business.company_name') === 'overridden' ? 'Overridden' : 'Default' }}</span>
                            <input type="text" name="s[business.company_name]" value="{{ $val('business.company_name', '') }}" required id="company-name">
                        </label>
                        <label>Tax ID
                            <input type="text" name="s[business.tax_id]" value="{{ $val('business.tax_id', '') }}" id="tax-id">
                        </label>
                    </div>
                    <label>Address
                        <textarea name="s[business.address]" rows="2" id="address">{{ $val('business.address', '') }}</textarea>
                    </label>
                    <div class="grid3">
                        <label>Phone<input type="text" name="s[business.phone]" value="{{ $val('business.phone', '') }}" id="phone"></label>
                        <label>Email<input type="email" name="s[business.email]" value="{{ $val('business.email', '') }}" id="email"></label>
                        <label>Website<input type="url" name="s[business.website]" value="{{ $val('business.website', '') }}" id="website"></label>
                    </div>

                    <h2>💰 Currency & Timezone</h2>
                    <div class="grid3">
                        <label>Currency Code<input type="text" name="s[business.currency_code]" value="{{ $val('business.currency_code', 'LKR') }}"></label>
                        <label>Currency Symbol<input type="text" name="s[business.currency_symbol]" value="{{ $val('business.currency_symbol', 'Rs.') }}"></label>
                        <label>Timezone<input type="text" name="s[business.timezone]" value="{{ $val('business.timezone', 'Asia/Colombo') }}"></label>
                    </div>

                    <h2>📄 Invoice & Receipt Settings</h2>
                    <div class="grid2">
                        <label>Invoice Prefix<input type="text" name="s[billing.invoice_prefix]" value="{{ $val('billing.invoice_prefix', 'INV-') }}" required id="invoice-prefix"></label>
                        <label>Receipt Prefix<input type="text" name="s[billing.receipt_prefix]" value="{{ $val('billing.receipt_prefix', 'REC-') }}" required id="receipt-prefix"></label>
                        <label>Tax Rate %
                            <input type="number" step="0.01" min="0" max="100" name="s[billing.tax_rate]" value="{{ $val('billing.tax_rate', 0) }}">
                        </label>
                    </div>

                    <h2>🖨️ Print Format</h2>
                    <div class="grid2">
                        <label>Default Format
                            <select name="s[billing.default_print_format]" id="default-format">
                                <option value="a4" {{ $val('billing.default_print_format', 'a4') === 'a4' ? 'selected' : '' }}>A4 (Professional)</option>
                                <option value="thermal" {{ $val('billing.default_print_format', 'a4') === 'thermal' ? 'selected' : '' }}>80mm Thermal Receipt</option>
                            </select>
                        </label>
                        <div class="toggle-group">
                            <div class="toggle-item">
                                <span class="toggle-label">A4 Printing</span>
                                <label class="switch">
                                    <input type="hidden" name="s[billing.a4_enabled]" value="0">
                                    <input type="checkbox" name="s[billing.a4_enabled]" value="1" {{ $val('billing.a4_enabled', true) ? 'checked' : '' }} id="a4-enabled">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="toggle-item">
                                <span class="toggle-label">Thermal Printing</span>
                                <label class="switch">
                                    <input type="hidden" name="s[billing.thermal_enabled]" value="0">
                                    <input type="checkbox" name="s[billing.thermal_enabled]" value="1" {{ $val('billing.thermal_enabled', true) ? 'checked' : '' }} id="thermal-enabled">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <h2>📝 Document Content</h2>
                    <label>Footer Text<textarea name="s[billing.footer_text]" rows="2" id="footer-text">{{ $val('billing.footer_text', '') }}</textarea></label>
                    <label>Terms & Conditions<textarea name="s[billing.terms_conditions]" rows="4" id="terms-conditions">{{ $val('billing.terms_conditions', '') }}</textarea></label>

                    <h2>🖼️ Logo <span class="override-badge {{ $ovr('branding.logo_path') }}">{{ $ovr('branding.logo_path') === 'overridden' ? 'Overridden' : 'Default' }}</span></h2>
                    <div class="upload-zone" id="logo-dropzone">
                        @if($val('branding.logo_path', ''))
                            <div class="current-image">
                                <img src="{{ \App\Support\Media::url($val('branding.logo_path')) }}" alt="Current Logo" id="current-logo">
                                <button type="button" onclick="removeLogo()" class="remove-btn">Remove Logo</button>
                            </div>
                        @else
                            <div class="upload-placeholder"><span class="upload-icon">📁</span><span class="upload-text">Click to upload or drag and drop</span><span class="upload-subtext">JPEG, PNG, JPG, GIF (Max 2MB)</span></div>
                        @endif
                        <input type="file" name="logo" id="logo-input" accept="image/*" style="display: none;">
                    </div>
                    <input type="hidden" name="s[branding.logo_path]" value="{{ $val('branding.logo_path', '') }}" id="logo-path-hidden">

                    <label class="range-label">
                        Logo Size (A4 Invoice)
                        <input type="range" name="s[branding.logo_size_a4]" min="30" max="150" value="{{ $val('branding.logo_size_a4', 60) }}" id="logo-size-a4">
                        <small>Current: <span id="logo-size-a4-value">{{ $val('branding.logo_size_a4', 60) }}</span>px</small>
                    </label>
                    <label class="range-label">
                        Logo Size (Thermal Receipt)
                        <input type="range" name="s[branding.logo_size_thermal]" min="20" max="80" value="{{ $val('branding.logo_size_thermal', 40) }}" id="logo-size-thermal">
                        <small>Current: <span id="logo-size-thermal-value">{{ $val('branding.logo_size_thermal', 40) }}</span>px</small>
                    </label>

                    <div class="settings-save-bar">
                        <button class="primary">Save Billing Settings</button>
                    </div>
                </form>
            </div>

            <div class="panel preview-panel">
                <h2>👁️ Live Preview</h2>
                <div class="preview-toggle">
                    <button type="button" onclick="showPreview('a4')" id="btn-a4" class="secondary">A4 Format</button>
                    <button type="button" onclick="showPreview('thermal')" id="btn-thermal" class="secondary">Thermal Format</button>
                </div>

                <div id="preview-a4" class="preview-container" style="display: {{ $val('billing.default_print_format', 'a4') === 'a4' ? 'block' : 'none' }};">
                    <div class="invoice-preview a4-preview">
                        <div class="invoice-header">
                            <div class="invoice-left">
                                @if($val('branding.logo_path', ''))
                                    <img src="{{ \App\Support\Media::url($val('branding.logo_path')) }}" alt="Logo" class="preview-logo" style="max-height: {{ $val('branding.logo_size_a4', 60) }}px;">
                                @else
                                    <img src="" alt="Logo" class="preview-logo" style="max-height: {{ $val('branding.logo_size_a4', 60) }}px; display: none;">
                                @endif
                                <h1 id="preview-company-name-a4">{{ $val('business.company_name', '') }}</h1>
                                <p id="preview-address-a4">{{ $val('business.address', '') }}</p>
                                <p id="preview-phone-a4">@if($val('business.phone', ''))Phone: {{ $val('business.phone') }}@endif</p>
                                <p id="preview-email-a4">@if($val('business.email', ''))Email: {{ $val('business.email') }}@endif</p>
                                @if($val('business.tax_id', ''))<p>Tax ID: {{ $val('business.tax_id') }}</p>@endif
                            </div>
                            <div class="invoice-right">
                                <h2>INVOICE</h2>
                                <p><strong>Invoice #:</strong> <span id="preview-invoice-prefix-a4">{{ $val('billing.invoice_prefix', 'INV-') }}-001</span></p>
                                <p><strong>Date:</strong> {{ now()->format('d M Y') }}</p>
                                <p><strong>Due Date:</strong> {{ now()->addDays(30)->format('d M Y') }}</p>
                                <p><strong>Status:</strong> <span class="status-paid">Paid</span></p>
                            </div>
                        </div>
                        <div class="invoice-parties">
                            <div><h3>BILL TO</h3><p><strong>John Doe</strong></p><p>Phone: 0771234567</p></div>
                            <div><h3>VEHICLE DETAILS</h3><p><strong>ABC-1234</strong></p><p>Toyota Corolla - White</p></div>
                        </div>
                        <table class="invoice-table">
                            <thead><tr><th><span class="th-full">Description</span><span class="th-short">Item</span></th><th>Qty</th><th><span class="th-full">Unit Price</span><span class="th-short">Price</span></th><th>Tax</th><th>Total</th></tr></thead>
                            <tbody>
                            <tr><td>Full Car Wash Service</td><td>1</td><td>500.00</td><td>50.00</td><td>550.00</td></tr>
                            <tr><td>Interior Detailing</td><td>1</td><td>300.00</td><td>30.00</td><td>330.00</td></tr>
                            </tbody>
                        </table>
                        <div class="invoice-totals">
                            <div class="total-row"><span>Subtotal:</span><span>800.00</span></div>
                            <div class="total-row"><span>Tax:</span><span>80.00</span></div>
                            <div class="total-row grand"><span>Total Due:</span><span>880.00</span></div>
                            <div class="total-row"><span>Amount Received:</span><span>880.00</span></div>
                        </div>
                        @if($val('billing.terms_conditions', ''))
                            <div class="invoice-terms"><strong>Terms & Conditions:</strong><p id="preview-terms-a4">{{ $val('billing.terms_conditions') }}</p></div>
                        @endif
                        <div class="invoice-payment"><strong>Payment Information:</strong><p>We accept Cash, Card, and Bank Transfer</p>
                            @if($val('business.phone', ''))<p>For inquiries: {{ $val('business.phone') }}</p>@endif
                        </div>
                        <div class="invoice-footer">
                            <p id="preview-footer-a4">{{ $val('billing.footer_text', '') }}</p>
                            <p>Generated on {{ now()->format('d M Y H:i') }} · {{ $val('business.company_name', '') }}</p>
                            <p class="powered">Powered by Vellix Global - 0773208478</p>
                        </div>
                    </div>
                </div>

                <div id="preview-thermal" class="preview-container" style="display: {{ $val('billing.default_print_format', 'a4') === 'thermal' ? 'block' : 'none' }};">
                    <div class="invoice-preview thermal-preview">
                        <div class="thermal-header">
                            @if($val('branding.logo_path', ''))
                                <img src="{{ \App\Support\Media::url($val('branding.logo_path')) }}" alt="Logo" class="preview-logo" style="max-height: {{ $val('branding.logo_size_thermal', 40) }}px;">
                            @else
                                <img src="" alt="Logo" class="preview-logo" style="max-height: {{ $val('branding.logo_size_thermal', 40) }}px; display: none;">
                            @endif
                            <div class="thermal-company" id="preview-company-name-thermal">{{ $val('business.company_name', '') }}</div>
                            <div id="preview-address-thermal">{{ $val('business.address', '') }}</div>
                            @if($val('business.phone', ''))<div>Tel: {{ $val('business.phone') }}</div>@endif
                            @if($val('business.tax_id', ''))<div>Tax ID: {{ $val('business.tax_id') }}</div>@endif
                        </div>
                        <div class="thermal-divider"></div>
                        <div class="thermal-meta">
                            <div class="thermal-invoice" id="preview-invoice-prefix-thermal">{{ $val('billing.invoice_prefix', 'INV-') }}-001</div>
                            <div>{{ now()->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="thermal-info"><div><strong>Customer:</strong> John Doe</div><div><strong>Vehicle:</strong> ABC-1234</div></div>
                        <div class="thermal-divider"></div>
                        <table class="thermal-table">
                            <tr><td>Full Car Wash Service</td><td>1</td><td>500</td></tr>
                            <tr><td>Interior Detailing</td><td>1</td><td>300</td></tr>
                        </table>
                        <div class="thermal-divider"></div>
                        <div class="thermal-totals">
                            <div>Subtotal: 800</div><div>Tax: 80</div><div class="grand">TOTAL: Rs. 880</div>
                            <div>Received: Rs. 880</div><div>Return: Rs. 0</div>
                        </div>
                        <div class="thermal-divider"></div>
                        <div class="thermal-paid">PAID</div>
                        <div class="thermal-divider"></div>
                        <div class="thermal-footer">
                            <div id="preview-footer-thermal">{{ $val('billing.footer_text', '') }}</div>
                            <div>{{ now()->format('d/m/Y H:i') }}</div>
                            <div>Thank you for your business!</div>
                            <div class="powered">Powered by Vellix Global - 0773208478</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ ALL SETTINGS ============ --}}
    <div class="tab-content" id="content-all">
        <div class="panel">
            <form method="post" action="{{ route('central.tenants.settings.update', $tenant) }}">
                @csrf
                <h2>⚙️ All remaining settings</h2>
                <p style="font-size:13px;color:#64748b;margin-bottom:16px;">Keys the category tabs above don't cover — loyalty, whatsapp and anything added to the catalog later. Drive by <code>SettingsSeeder::definitions()</code>.</p>
                <div class="all-settings-grid">
                    @foreach(\Database\Seeders\SettingsSeeder::definitions() as $definition)
                        @php
                            $shown = ['reception.background_type','reception.background_image','reception.background_color','reception.custom_background_color','business.company_name','business.address','business.phone','business.email','business.website','business.tax_id','business.currency_code','business.currency_symbol','business.timezone','billing.invoice_prefix','billing.receipt_prefix','billing.footer_text','billing.terms_conditions','billing.default_print_format','billing.a4_enabled','billing.thermal_enabled','billing.tax_rate','branding.logo_path','branding.logo_size_a4','branding.logo_size_thermal'];
                        @endphp
                        @if(in_array($definition['key'], $shown, true)) @continue @endif
                        <div class="field">
                            <label>{{ $definition['label'] }} <span class="override-badge {{ $ovr($definition['key']) }}">{{ $ovr($definition['key']) === 'overridden' ? 'Overridden' : 'Default' }}</span>
                                @switch($definition['type'])
                                    @case('textarea')
                                        <textarea name="s[{{ $definition['key'] }}]" rows="3">{{ $val($definition['key']) }}</textarea>
                                        @break
                                    @case('number') @case('money') @case('percent')
                                        <input type="number" step="0.01" name="s[{{ $definition['key'] }}]" value="{{ $val($definition['key']) }}">
                                        @break
                                    @case('boolean')
                                        <input type="hidden" name="s[{{ $definition['key'] }}]" value="0">
                                        <input type="checkbox" name="s[{{ $definition['key'] }}]" value="1" {{ $val($definition['key'], false) ? 'checked' : '' }} style="width:auto;margin-top:8px;">
                                        @break
                                    @case('color')
                                        <input type="text" name="s[{{ $definition['key'] }}]" value="{{ $val($definition['key'], '') }}" placeholder="#0462d3" style="font-family:monospace;">
                                        @break
                                    @case('select')
                                        @php
                                            $options = [
                                                'billing.default_print_format' => ['a4' => 'A4', 'thermal' => 'Thermal'],
                                                'reception.background_type' => ['image' => 'Image', 'color' => 'Color'],
                                                'whatsapp.provider' => ['none' => 'None', 'meta' => 'Meta'],
                                            ][$definition['key']] ?? [];
                                        @endphp
                                        <select name="s[{{ $definition['key'] }}]">
                                            @foreach($options as $k => $v)
                                                <option value="{{ $k }}" {{ (string) $val($definition['key'], '') === (string) $k ? 'selected' : '' }}>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                        @break
                                    @default
                                        <input type="text" name="s[{{ $definition['key'] }}]" value="{{ is_scalar($val($definition['key'])) ? $val($definition['key']) : json_encode($val($definition['key'])) }}">
                                @endswitch
                                @if($definition['hint'] ?? null)<small style="display:block;color:#64748b;font-weight:400;">{{ $definition['hint'] }}</small>@endif
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="settings-save-bar">
                    <button class="primary">Save All Settings</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById('content-' + tab).classList.add('active');
            document.getElementById('tab-' + tab).classList.add('active');
        }

        const bgDropzone = document.getElementById('bg-dropzone');
        const bgInput = document.getElementById('bg-input');
        const bgPathHidden = document.getElementById('bg-path-hidden');
        const previewContainer = document.getElementById('reception-preview');

        if (bgDropzone) {
            bgDropzone.addEventListener('dragover', (e) => { e.preventDefault(); bgDropzone.style.borderColor = '#3498db'; bgDropzone.style.backgroundColor = '#f0f8ff'; });
            bgDropzone.addEventListener('dragleave', (e) => { e.preventDefault(); bgDropzone.style.borderColor = '#ccc'; bgDropzone.style.backgroundColor = 'transparent'; });
            bgDropzone.addEventListener('drop', (e) => {
                e.preventDefault(); bgDropzone.style.borderColor = '#ccc'; bgDropzone.style.backgroundColor = 'transparent';
                const files = e.dataTransfer.files;
                if (files.length > 0) { bgInput.files = files; previewImage(files[0], bgDropzone); updateImagePreview(files[0]); }
            });
            bgDropzone.addEventListener('click', () => bgInput.click());
            bgInput.addEventListener('change', (e) => { if (e.target.files.length > 0) { previewImage(e.target.files[0], bgDropzone); updateImagePreview(e.target.files[0]); } });
        }

        function removeBackground() {
            bgPathHidden.value = '';
            bgInput.value = '';
            bgDropzone.innerHTML = `<div class="upload-placeholder"><span class="upload-icon">📁</span><span class="upload-text">Click to upload or drag and drop</span><span class="upload-subtext">JPEG, PNG, JPG, GIF (Max 5MB)</span></div>`;
            bgDropzone.appendChild(bgInput);
            const bgDiv = previewContainer.querySelector('.preview-bg');
            if (bgDiv) { bgDiv.style.backgroundImage = 'none'; bgDiv.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'; }
        }

        function previewImage(file, dropzone) {
            const reader = new FileReader();
            reader.onload = (e) => {
                dropzone.innerHTML = `<div class="current-image"><img src="${e.target.result}" alt="Current Background"><button type="button" onclick="removeBackground()" class="remove-btn">Remove Image</button></div>`;
                dropzone.appendChild(bgInput);
            };
            reader.readAsDataURL(file);
        }

        function updateImagePreview(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const bgDiv = previewContainer.querySelector('.preview-bg');
                if (bgDiv) { bgDiv.style.backgroundImage = `url(${e.target.result})`; bgDiv.style.background = 'none'; }
            };
            reader.readAsDataURL(file);
        }

        function toggleBackgroundType() {
            const isImage = document.getElementById('bg-type-image').checked;
            const uploadSection = document.getElementById('bg-upload-section');
            const colorSection = document.getElementById('bg-color-section');
            const bgInput = document.getElementById('bg-input');
            if (isImage) { uploadSection.style.display = 'block'; colorSection.style.display = 'none'; bgInput.disabled = false; }
            else { uploadSection.style.display = 'none'; colorSection.style.display = 'block'; bgInput.disabled = true; }
        }

        function updateColorPreview() {
            const colorSelect = document.querySelector('input[name="s[reception.background_color]"]:checked');
            const customColorInput = document.getElementById('custom-bg-color');
            const hexInput = document.getElementById('color-hex-value');
            const customPreview = document.getElementById('custom-color-preview');
            const bgDiv = previewContainer.querySelector('.preview-bg');
            if (bgDiv && colorSelect && colorSelect.value) {
                bgDiv.style.backgroundImage = 'none'; bgDiv.style.background = colorSelect.value;
                customColorInput.value = '#667eea'; hexInput.value = '#667eea'; customPreview.style.background = '#667eea';
            }
        }

        function updateCustomColorPreview() {
            const customColorInput = document.getElementById('custom-bg-color');
            const hexInput = document.getElementById('color-hex-value');
            const customPreview = document.getElementById('custom-color-preview');
            const bgDiv = previewContainer.querySelector('.preview-bg');
            if (bgDiv && customColorInput.value) {
                bgDiv.style.backgroundImage = 'none'; bgDiv.style.background = customColorInput.value;
                document.querySelectorAll('input[name="s[reception.background_color]"]').forEach(el => el.checked = false);
                hexInput.value = customColorInput.value; customPreview.style.background = customColorInput.value;
            }
        }

        function updateColorFromHex() {
            const hexInput = document.getElementById('color-hex-value');
            const customColorInput = document.getElementById('custom-bg-color');
            const customPreview = document.getElementById('custom-color-preview');
            const bgDiv = previewContainer.querySelector('.preview-bg');
            if (hexInput.value && /^#[0-9A-Fa-f]{6}$/.test(hexInput.value)) {
                customColorInput.value = hexInput.value; customPreview.style.background = hexInput.value;
                bgDiv.style.backgroundImage = 'none'; bgDiv.style.background = hexInput.value;
                document.querySelectorAll('input[name="s[reception.background_color]"]').forEach(el => el.checked = false);
            }
        }

        const dropzone = document.getElementById('logo-dropzone');
        const fileInput = document.getElementById('logo-input');
        const logoPathHidden = document.getElementById('logo-path-hidden');

        if (dropzone) {
            dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.style.borderColor = '#3498db'; dropzone.style.backgroundColor = '#f0f8ff'; });
            dropzone.addEventListener('dragleave', (e) => { e.preventDefault(); dropzone.style.borderColor = '#ccc'; dropzone.style.backgroundColor = 'transparent'; });
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault(); dropzone.style.borderColor = '#ccc'; dropzone.style.backgroundColor = 'transparent';
                const files = e.dataTransfer.files;
                if (files.length > 0) { fileInput.files = files; previewImageLogo(files[0], dropzone); updateLogoPreview(files[0]); }
            });
            dropzone.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', (e) => { if (e.target.files.length > 0) { previewImageLogo(e.target.files[0], dropzone); updateLogoPreview(e.target.files[0]); } });
        }

        function removeLogo() {
            logoPathHidden.value = '';
            fileInput.value = '';
            dropzone.innerHTML = `<div class="upload-placeholder"><span class="upload-icon">📁</span><span class="upload-text">Click to upload or drag and drop</span><span class="upload-subtext">JPEG, PNG, JPG, GIF (Max 2MB)</span></div>`;
            dropzone.appendChild(fileInput);
            document.querySelectorAll('.preview-logo').forEach(el => el.style.display = 'none');
        }

        function previewImageLogo(file, dropzone) {
            const reader = new FileReader();
            reader.onload = (e) => {
                dropzone.innerHTML = `<div class="current-image"><img src="${e.target.result}" alt="Current Logo" style="max-height:100px;margin-bottom:10px;"><button type="button" onclick="removeLogo()" class="remove-btn">Remove Logo</button></div>`;
                dropzone.appendChild(fileInput);
            };
            reader.readAsDataURL(file);
        }

        function updateLogoPreview(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.querySelectorAll('.preview-logo').forEach(el => { el.src = e.target.result; el.style.display = 'block'; });
            };
            reader.readAsDataURL(file);
        }

        document.getElementById('logo-size-a4')?.addEventListener('input', (e) => {
            document.getElementById('logo-size-a4-value').textContent = e.target.value;
            document.querySelectorAll('#preview-a4 .preview-logo').forEach(el => { el.style.maxHeight = e.target.value + 'px'; });
        });

        document.getElementById('logo-size-thermal')?.addEventListener('input', (e) => {
            document.getElementById('logo-size-thermal-value').textContent = e.target.value;
            document.querySelectorAll('#preview-thermal .preview-logo').forEach(el => { el.style.maxHeight = e.target.value + 'px'; });
        });

        document.getElementById('company-name')?.addEventListener('input', (e) => {
            const val = e.target.value || 'Company Name';
            document.getElementById('preview-company-name-a4').textContent = val;
            document.getElementById('preview-company-name-thermal').textContent = val;
        });

        document.getElementById('address')?.addEventListener('input', (e) => {
            const val = e.target.value || '';
            document.getElementById('preview-address-a4').textContent = val;
            document.getElementById('preview-address-thermal').textContent = val;
        });

        document.getElementById('phone')?.addEventListener('input', (e) => {
            document.getElementById('preview-phone-a4').textContent = e.target.value ? 'Phone: ' + e.target.value : '';
        });

        document.getElementById('email')?.addEventListener('input', (e) => {
            document.getElementById('preview-email-a4').textContent = e.target.value ? 'Email: ' + e.target.value : '';
        });

        document.getElementById('invoice-prefix')?.addEventListener('input', (e) => {
            const val = (e.target.value || 'INV') + '-001';
            document.getElementById('preview-invoice-prefix-a4').textContent = val;
            document.getElementById('preview-invoice-prefix-thermal').textContent = val;
        });

        document.getElementById('footer-text')?.addEventListener('input', (e) => {
            const val = e.target.value || '';
            document.getElementById('preview-footer-a4').textContent = val;
            document.getElementById('preview-footer-thermal').textContent = val;
        });

        document.getElementById('terms-conditions')?.addEventListener('input', (e) => {
            document.getElementById('preview-terms-a4').textContent = e.target.value || '';
        });

        function showPreview(format) {
            document.querySelectorAll('.preview-container').forEach(el => el.style.display = 'none');
            document.getElementById('preview-' + format).style.display = 'block';
            document.getElementById('btn-a4').classList.remove('primary');
            document.getElementById('btn-thermal').classList.remove('primary');
            document.getElementById('btn-' + format).classList.add('primary');
        }

        showPreview('{{ $val('billing.default_print_format', 'a4') }}');
        toggleBackgroundType();
    </script>
@endsection
