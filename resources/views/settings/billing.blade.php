@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Settings</h1>
        <p>Configure your billing and reception settings</p>
    </div>
</div>

<div class="settings-tabs">
    <button class="tab-btn active" onclick="showTab('reception')" id="tab-reception">Reception Settings</button>
    <button class="tab-btn" onclick="showTab('billing')" id="tab-billing">Billing Settings</button>
</div>

<!-- Reception Settings Tab -->
<div class="tab-content active" id="content-reception">
    <div class="panel">
        <form method="post" action="{{ route('settings.reception.update') }}" enctype="multipart/form-data" id="reception-form">
            @csrf
        
            <h2>🎨 Background Appearance</h2>
            <div class="bg-type-selector">
                <label class="bg-type-option">
                    <input type="radio" name="background_type" value="image" {{ ($settings['background_type'] ?? 'image') === 'image' ? 'checked' : '' }} id="bg-type-image" onchange="toggleBackgroundType()">
                    <span class="bg-type-card">
                        <span class="bg-type-icon">🖼️</span>
                        <span class="bg-type-label">Use Image</span>
                        <span class="bg-type-desc">Upload a custom background image</span>
                    </span>
                </label>
                <label class="bg-type-option">
                    <input type="radio" name="background_type" value="color" {{ ($settings['background_type'] ?? 'image') === 'color' ? 'checked' : '' }} id="bg-type-color" onchange="toggleBackgroundType()">
                    <span class="bg-type-card">
                        <span class="bg-type-icon">🎨</span>
                        <span class="bg-type-label">Use Color</span>
                        <span class="bg-type-desc">Choose a solid or gradient color</span>
                    </span>
                </label>
            </div>

            <div class="bg-upload-section" id="bg-upload-section" {{ ($settings['background_type'] ?? 'image') === 'color' ? 'style="display:none;"' : '' }}>
                <h3>Background Image</h3>
                <div class="upload-zone" id="bg-dropzone">
                    @if($settings['reception_background_image'] ?? null)
                        <div class="current-image">
                            <img src="{{ asset($settings['reception_background_image']) }}" alt="Current Background">
                            <button type="button" onclick="removeBackground()" class="remove-btn">Remove Image</button>
                        </div>
                    @else
                        <div class="upload-placeholder">
                            <span class="upload-icon">📁</span>
                            <span class="upload-text">Click to upload or drag and drop</span>
                            <span class="upload-subtext">JPEG, PNG, JPG, GIF (Max 5MB)</span>
                        </div>
                    @endif
                    <input type="file" name="reception_background" id="bg-input" accept="image/*" style="display: none;">
                </div>
                <input type="hidden" name="reception_background_image" value="{{ $settings['reception_background_image'] ?? '' }}" id="bg-path-hidden">
            </div>

            <div class="bg-color-section" id="bg-color-section" {{ ($settings['background_type'] ?? 'image') === 'image' ? 'style="display:none;"' : '' }}>
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
                        <input type="radio" name="background_color" value="{{ $color }}" {{ ($settings['background_color'] ?? '') === $color ? 'checked' : '' }} onchange="updateColorPreview()">
                        <div class="color-swatch" style="background: {{ $color }};">
                            <span class="color-check">✓</span>
                        </div>
                        <span class="color-name">{{ $name }}</span>
                    </label>
                    @endforeach
                </div>
                <div class="custom-color-section">
                    <label>Custom Color</label>
                    <div class="color-picker-wrapper">
                        <div class="color-preview-box" id="custom-color-preview" style="background: {{ $settings['custom_background_color'] ?? '#667eea' }};"></div>
                        <input type="color" name="custom_background_color" value="{{ $settings['custom_background_color'] ?? '#667eea' }}" id="custom-bg-color" onchange="updateCustomColorPreview()">
                        <input type="text" class="color-hex-input" value="{{ $settings['custom_background_color'] ?? '#667eea' }}" id="color-hex-value" oninput="updateColorFromHex()">
                    </div>
                </div>
            </div>

            <h2>👁️ Live Preview</h2>
            <div class="preview-wrapper">
                <div id="reception-preview" class="reception-preview-full">
                    @if(($settings['background_type'] ?? 'image') === 'color' && ($settings['background_color'] ?? ''))
                        <div class="preview-bg" style="background: {{ $settings['background_color'] }};"></div>
                    @elseif(($settings['background_type'] ?? 'image') === 'color' && ($settings['custom_background_color'] ?? ''))
                        <div class="preview-bg" style="background: {{ $settings['custom_background_color'] }};"></div>
                    @elseif($settings['reception_background_image'] ?? null)
                        <div class="preview-bg" style="background-image: url('{{ asset($settings['reception_background_image']) }}');"></div>
                    @else
                        <div class="preview-bg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                    @endif
                    <div class="preview-content">
                        <div class="reception-nav">
                            <button class="nav-toggle">☰</button>
                            <div class="nav-menu">
                                <a href="#">Dashboard</a>
                                <a href="#">Job Cards</a>
                                <a href="#">Customers</a>
                                <a href="#">Settings</a>
                            </div>
                        </div>
                        <div class="reception-header">
                            <h1>Vehicle Check-in</h1>
                            <p>Enter vehicle registration number to begin</p>
                        </div>
                        <div class="search-section">
                            <div class="search-box">
                                <span class="search-placeholder">Vehicle registration number...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button class="primary">Save Reception Settings</button>
            </div>
        </form>
    </div>
</div>

<!-- Billing Settings Tab -->
<div class="tab-content" id="content-billing">
    <div class="billing-grid">
        <div class="panel">
            <form method="post" action="{{ route('settings.billing.update') }}" enctype="multipart/form-data" id="billing-form">
                @csrf
                
                <h2>🏢 Company Information</h2>
                <div class="grid2">
                    <label>
                        Company Name
                        <input type="text" name="company_name" value="{{ $settings['company_name'] }}" required id="company-name">
                    </label>
                    <label>
                        Tax ID
                        <input type="text" name="tax_id" value="{{ $settings['tax_id'] }}" id="tax-id">
                    </label>
                </div>
                <label>
                    Address
                    <textarea name="address" rows="2" id="address">{{ $settings['address'] }}</textarea>
                </label>
                <div class="grid3">
                    <label>
                        Phone
                        <input type="text" name="phone" value="{{ $settings['phone'] }}" id="phone">
                    </label>
                    <label>
                        Email
                        <input type="email" name="email" value="{{ $settings['email'] }}" id="email">
                    </label>
                    <label>
                        Website
                        <input type="url" name="website" value="{{ $settings['website'] }}" id="website">
                    </label>
                </div>

                <h2>📄 Invoice & Receipt Settings</h2>
                <div class="grid2">
                    <label>
                        Invoice Prefix
                        <input type="text" name="invoice_prefix" value="{{ $settings['invoice_prefix'] }}" required id="invoice-prefix">
                    </label>
                    <label>
                        Receipt Prefix
                        <input type="text" name="receipt_prefix" value="{{ $settings['receipt_prefix'] }}" required id="receipt-prefix">
                    </label>
                </div>

                <h2>🖨️ Print Format</h2>
                <div class="grid2">
                    <label>
                        Default Format
                        <select name="default_format" id="default-format">
                            <option value="a4" {{ $settings['default_format'] === 'a4' ? 'selected' : '' }}>A4 (Professional)</option>
                            <option value="thermal" {{ $settings['default_format'] === 'thermal' ? 'selected' : '' }}>80mm Thermal Receipt</option>
                        </select>
                    </label>
                    <div class="toggle-group">
                        <div class="toggle-item">
                            <span class="toggle-label">A4 Printing</span>
                            <label class="switch">
                                <input type="checkbox" name="a4_enabled" {{ $settings['a4_enabled'] ? 'checked' : '' }} id="a4-enabled">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="toggle-item">
                            <span class="toggle-label">Thermal Printing</span>
                            <label class="switch">
                                <input type="checkbox" name="thermal_enabled" {{ $settings['thermal_enabled'] ? 'checked' : '' }} id="thermal-enabled">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <h2>📝 Document Content</h2>
                <label>
                    Footer Text
                    <textarea name="footer_text" rows="2" id="footer-text">{{ $settings['footer_text'] }}</textarea>
                </label>
                <label>
                    Terms & Conditions
                    <textarea name="terms_conditions" rows="4" id="terms-conditions">{{ $settings['terms_conditions'] }}</textarea>
                </label>

                <h2>🖼️ Logo</h2>
                <div class="upload-zone" id="logo-dropzone">
                    @if($settings['logo_path'])
                        <div class="current-image">
                            <img src="{{ asset($settings['logo_path']) }}" alt="Current Logo" id="current-logo">
                            <button type="button" onclick="removeLogo()" class="remove-btn">Remove Logo</button>
                        </div>
                    @else
                        <div class="upload-placeholder">
                            <span class="upload-icon">📁</span>
                            <span class="upload-text">Click to upload or drag and drop</span>
                            <span class="upload-subtext">JPEG, PNG, JPG, GIF (Max 2MB)</span>
                        </div>
                    @endif
                    <input type="file" name="logo" id="logo-input" accept="image/*" style="display: none;">
                </div>
                <input type="hidden" name="logo_path" value="{{ $settings['logo_path'] }}" id="logo-path-hidden">
                
                <label class="range-label">
                    Logo Size (A4 Invoice)
                    <input type="range" name="logo_size_a4" min="30" max="150" value="{{ $settings['logo_size_a4'] ?? 60 }}" id="logo-size-a4">
                    <small>Current: <span id="logo-size-a4-value">{{ $settings['logo_size_a4'] ?? 60 }}</span>px</small>
                </label>
                
                <label class="range-label">
                    Logo Size (Thermal Receipt)
                    <input type="range" name="logo_size_thermal" min="20" max="80" value="{{ $settings['logo_size_thermal'] ?? 40 }}" id="logo-size-thermal">
                    <small>Current: <span id="logo-size-thermal-value">{{ $settings['logo_size_thermal'] ?? 40 }}</span>px</small>
                </label>

                <div class="form-actions">
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

            <!-- A4 Preview -->
            <div id="preview-a4" class="preview-container" style="display: {{ $settings['default_format'] === 'a4' ? 'block' : 'none' }};">
                <div class="invoice-preview a4-preview">
                    <div class="invoice-header">
                        <div class="invoice-left">
                            @if($settings['logo_path'])
                                <img src="{{ asset($settings['logo_path']) }}" alt="Logo" class="preview-logo" style="max-height: {{ $settings['logo_size_a4'] ?? 60 }}px;">
                            @else
                                <img src="" alt="Logo" class="preview-logo" style="max-height: {{ $settings['logo_size_a4'] ?? 60 }}px; display: none;">
                            @endif
                            <h1 id="preview-company-name-a4">{{ $settings['company_name'] }}</h1>
                            <p id="preview-address-a4">{{ $settings['address'] }}</p>
                            <p id="preview-phone-a4">@if($settings['phone'])Phone: {{ $settings['phone'] }}@endif</p>
                            <p id="preview-email-a4">@if($settings['email'])Email: {{ $settings['email'] }}@endif</p>
                            @if($settings['tax_id'])<p>Tax ID: {{ $settings['tax_id'] }}</p>@endif
                        </div>
                        <div class="invoice-right">
                            <h2>INVOICE</h2>
                            <p><strong>Invoice #:</strong> <span id="preview-invoice-prefix-a4">{{ $settings['invoice_prefix'] }}-001</span></p>
                            <p><strong>Date:</strong> {{ now()->format('d M Y') }}</p>
                            <p><strong>Due Date:</strong> {{ now()->addDays(30)->format('d M Y') }}</p>
                            <p><strong>Status:</strong> <span class="status-paid">Paid</span></p>
                        </div>
                    </div>
                    
                    <div class="invoice-parties">
                        <div>
                            <h3>BILL TO</h3>
                            <p><strong>John Doe</strong></p>
                            <p>Phone: 0771234567</p>
                        </div>
                        <div>
                            <h3>VEHICLE DETAILS</h3>
                            <p><strong>ABC-1234</strong></p>
                            <p>Toyota Corolla - White</p>
                        </div>
                    </div>

                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th><span class="th-full">Description</span><span class="th-short">Item</span></th>
                                <th>Qty</th>
                                <th><span class="th-full">Unit Price</span><span class="th-short">Price</span></th>
                                <th>Tax</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Full Car Wash Service</td>
                                <td>1</td>
                                <td>500.00</td>
                                <td>50.00</td>
                                <td>550.00</td>
                            </tr>
                            <tr>
                                <td>Interior Detailing</td>
                                <td>1</td>
                                <td>300.00</td>
                                <td>30.00</td>
                                <td>330.00</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="invoice-totals">
                        <div class="total-row"><span>Subtotal:</span><span>800.00</span></div>
                        <div class="total-row"><span>Tax:</span><span>80.00</span></div>
                        <div class="total-row grand"><span>Total Due:</span><span>880.00</span></div>
                        <div class="total-row"><span>Amount Received:</span><span>880.00</span></div>
                    </div>

                    @if($settings['terms_conditions'])
                        <div class="invoice-terms">
                            <strong>Terms & Conditions:</strong>
                            <p id="preview-terms-a4">{{ $settings['terms_conditions'] }}</p>
                        </div>
                    @endif

                    <div class="invoice-payment">
                        <strong>Payment Information:</strong>
                        <p>We accept Cash, Card, and Bank Transfer</p>
                        @if($settings['phone'])<p>For inquiries: {{ $settings['phone'] }}</p>@endif
                    </div>

                    <div class="invoice-footer">
                        <p id="preview-footer-a4">{{ $settings['footer_text'] }}</p>
                        <p>Generated on {{ now()->format('d M Y H:i') }} · {{ $settings['company_name'] }}</p>
                        <p class="powered">Powered by Vellix Global - 0773208478</p>
                    </div>
                </div>
            </div>

            <!-- Thermal Preview -->
            <div id="preview-thermal" class="preview-container" style="display: {{ $settings['default_format'] === 'thermal' ? 'block' : 'none' }};">
                <div class="invoice-preview thermal-preview">
                    <div class="thermal-header">
                        @if($settings['logo_path'])
                            <img src="{{ asset($settings['logo_path']) }}" alt="Logo" class="preview-logo" style="max-height: {{ $settings['logo_size_thermal'] ?? 40 }}px;">
                        @else
                            <img src="" alt="Logo" class="preview-logo" style="max-height: {{ $settings['logo_size_thermal'] ?? 40 }}px; display: none;">
                        @endif
                        <div class="thermal-company" id="preview-company-name-thermal">{{ $settings['company_name'] }}</div>
                        <div id="preview-address-thermal">{{ $settings['address'] }}</div>
                        @if($settings['phone'])<div>Tel: {{ $settings['phone'] }}</div>@endif
                        @if($settings['tax_id'])<div>Tax ID: {{ $settings['tax_id'] }}</div>@endif
                    </div>
                    <div class="thermal-divider"></div>
                    <div class="thermal-meta">
                        <div class="thermal-invoice" id="preview-invoice-prefix-thermal">{{ $settings['invoice_prefix'] }}-001</div>
                        <div>{{ now()->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="thermal-info">
                        <div><strong>Customer:</strong> John Doe</div>
                        <div><strong>Vehicle:</strong> ABC-1234</div>
                    </div>
                    <div class="thermal-divider"></div>
                    <table class="thermal-table">
                        <tr>
                            <td>Full Car Wash Service</td>
                            <td>1</td>
                            <td>500</td>
                        </tr>
                        <tr>
                            <td>Interior Detailing</td>
                            <td>1</td>
                            <td>300</td>
                        </tr>
                    </table>
                    <div class="thermal-divider"></div>
                    <div class="thermal-totals">
                        <div>Subtotal: 800</div>
                        <div>Tax: 80</div>
                        <div class="grand">TOTAL: Rs. 880</div>
                        <div>Received: Rs. 880</div>
                        <div>Return: Rs. 0</div>
                    </div>
                    <div class="thermal-divider"></div>
                    <div class="thermal-paid">PAID</div>
                    <div class="thermal-divider"></div>
                    <div class="thermal-footer">
                        <div id="preview-footer-thermal">{{ $settings['footer_text'] }}</div>
                        <div>{{ now()->format('d/m/Y H:i') }}</div>
                        <div>Thank you for your business!</div>
                        <div class="powered">Powered by Vellix Global - 0773208478</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab switching
    function showTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('content-' + tab).classList.add('active');
        document.getElementById('tab-' + tab).classList.add('active');
    }

    // Reception settings scripts
    const bgDropzone = document.getElementById('bg-dropzone');
    const bgInput = document.getElementById('bg-input');
    const bgPathHidden = document.getElementById('bg-path-hidden');
    const previewContainer = document.getElementById('reception-preview');

    if (bgDropzone) {
        bgDropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            bgDropzone.style.borderColor = '#3498db';
            bgDropzone.style.backgroundColor = '#f0f8ff';
        });

        bgDropzone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            bgDropzone.style.borderColor = '#ccc';
            bgDropzone.style.backgroundColor = 'transparent';
        });

        bgDropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            bgDropzone.style.borderColor = '#ccc';
            bgDropzone.style.backgroundColor = 'transparent';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                bgInput.files = files;
                previewImage(files[0], bgDropzone);
                updateImagePreview(files[0]);
            }
        });

        bgDropzone.addEventListener('click', () => {
            bgInput.click();
        });

        bgInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                previewImage(e.target.files[0], bgDropzone);
                updateImagePreview(e.target.files[0]);
            }
        });
    }

    function removeBackground() {
        bgPathHidden.value = '';
        bgInput.value = '';
        const img = bgDropzone.querySelector('img');
        if (img) img.remove();
        const removeBtn = bgDropzone.querySelector('button');
        if (removeBtn) removeBtn.remove();
        
        bgDropzone.innerHTML = `
            <div class="upload-placeholder">
                <span class="upload-icon">📁</span>
                <span class="upload-text">Click to upload or drag and drop</span>
                <span class="upload-subtext">JPEG, PNG, JPG, GIF (Max 5MB)</span>
            </div>
        `;
        bgDropzone.appendChild(bgInput);
        
        const bgDiv = previewContainer.querySelector('.preview-bg');
        if (bgDiv) {
            bgDiv.style.backgroundImage = 'none';
            bgDiv.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        }
    }

    function previewImage(file, dropzone) {
        const reader = new FileReader();
        reader.onload = (e) => {
            dropzone.innerHTML = `
                <div class="current-image">
                    <img src="${e.target.result}" alt="Current Background">
                    <button type="button" onclick="removeBackground()" class="remove-btn">Remove Image</button>
                </div>
            `;
            dropzone.appendChild(bgInput);
        };
        reader.readAsDataURL(file);
    }

    function updateImagePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const bgDiv = previewContainer.querySelector('.preview-bg');
            if (bgDiv) {
                bgDiv.style.backgroundImage = `url(${e.target.result})`;
                bgDiv.style.background = 'none';
            }
        };
        reader.readAsDataURL(file);
    }

    function toggleBackgroundType() {
        const isImage = document.getElementById('bg-type-image').checked;
        const uploadSection = document.getElementById('bg-upload-section');
        const colorSection = document.getElementById('bg-color-section');
        const bgInput = document.getElementById('bg-input');

        if (isImage) {
            uploadSection.style.display = 'block';
            colorSection.style.display = 'none';
            bgInput.disabled = false;
        } else {
            uploadSection.style.display = 'none';
            colorSection.style.display = 'block';
            bgInput.disabled = true;
        }
    }

    function updateColorPreview() {
        const colorSelect = document.querySelector('input[name="background_color"]:checked');
        const customColorInput = document.getElementById('custom-bg-color');
        const hexInput = document.getElementById('color-hex-value');
        const customPreview = document.getElementById('custom-color-preview');
        const bgDiv = previewContainer.querySelector('.preview-bg');
        
        if (bgDiv && colorSelect && colorSelect.value) {
            bgDiv.style.backgroundImage = 'none';
            bgDiv.style.background = colorSelect.value;
            customColorInput.value = '#667eea';
            hexInput.value = '#667eea';
            customPreview.style.background = '#667eea';
        }
    }

    function updateCustomColorPreview() {
        const customColorInput = document.getElementById('custom-bg-color');
        const hexInput = document.getElementById('color-hex-value');
        const customPreview = document.getElementById('custom-color-preview');
        const bgDiv = previewContainer.querySelector('.preview-bg');
        
        if (bgDiv && customColorInput.value) {
            bgDiv.style.backgroundImage = 'none';
            bgDiv.style.background = customColorInput.value;
            document.querySelectorAll('input[name="background_color"]').forEach(el => el.checked = false);
            hexInput.value = customColorInput.value;
            customPreview.style.background = customColorInput.value;
        }
    }

    function updateColorFromHex() {
        const hexInput = document.getElementById('color-hex-value');
        const customColorInput = document.getElementById('custom-bg-color');
        const customPreview = document.getElementById('custom-color-preview');
        const bgDiv = previewContainer.querySelector('.preview-bg');
        
        if (hexInput.value && /^#[0-9A-Fa-f]{6}$/.test(hexInput.value)) {
            customColorInput.value = hexInput.value;
            customPreview.style.background = hexInput.value;
            bgDiv.style.backgroundImage = 'none';
            bgDiv.style.background = hexInput.value;
            document.querySelectorAll('input[name="background_color"]').forEach(el => el.checked = false);
        }
    }

    // Logo dropzone
    const dropzone = document.getElementById('logo-dropzone');
    const fileInput = document.getElementById('logo-input');
    const logoPathHidden = document.getElementById('logo-path-hidden');

    if (dropzone) {
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = '#3498db';
            dropzone.style.backgroundColor = '#f0f8ff';
        });

        dropzone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = '#ccc';
            dropzone.style.backgroundColor = 'transparent';
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = '#ccc';
            dropzone.style.backgroundColor = 'transparent';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                previewImageLogo(files[0], dropzone);
                updateLogoPreview(files[0]);
            }
        });

        dropzone.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                previewImageLogo(e.target.files[0], dropzone);
                updateLogoPreview(e.target.files[0]);
            }
        });
    }

    function removeLogo() {
        logoPathHidden.value = '';
        fileInput.value = '';
        const img = dropzone.querySelector('img');
        if (img) img.remove();
        const removeBtn = dropzone.querySelector('button');
        if (removeBtn) removeBtn.remove();
        
        dropzone.innerHTML = `
            <div class="upload-placeholder">
                <span class="upload-icon">📁</span>
                <span class="upload-text">Click to upload or drag and drop</span>
                <span class="upload-subtext">JPEG, PNG, JPG, GIF (Max 2MB)</span>
            </div>
        `;
        dropzone.appendChild(fileInput);
        
        document.querySelectorAll('.preview-logo').forEach(el => el.style.display = 'none');
    }

    function previewImageLogo(file, dropzone) {
        const reader = new FileReader();
        reader.onload = (e) => {
            dropzone.innerHTML = `
                <div class="current-image">
                    <img src="${e.target.result}" alt="Current Logo" style="max-height:100px;margin-bottom:10px;">
                    <button type="button" onclick="removeLogo()" class="remove-btn">Remove Logo</button>
                </div>
            `;
            dropzone.appendChild(fileInput);
        };
        reader.readAsDataURL(file);
    }

    function updateLogoPreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            document.querySelectorAll('.preview-logo').forEach(el => {
                el.src = e.target.result;
                el.style.display = 'block';
            });
        };
        reader.readAsDataURL(file);
    }

    // Billing live preview updates
    document.getElementById('logo-size-a4')?.addEventListener('input', (e) => {
        document.getElementById('logo-size-a4-value').textContent = e.target.value;
        document.querySelectorAll('#preview-a4 .preview-logo').forEach(el => {
            el.style.maxHeight = e.target.value + 'px';
        });
    });
    
    document.getElementById('logo-size-thermal')?.addEventListener('input', (e) => {
        document.getElementById('logo-size-thermal-value').textContent = e.target.value;
        document.querySelectorAll('#preview-thermal .preview-logo').forEach(el => {
            el.style.maxHeight = e.target.value + 'px';
        });
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
    
    // Initialize
    showPreview('{{ $settings['default_format'] }}');
    toggleBackgroundType();
</script>

<style>
/* ========== BASE ========== */
.settings-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 11px 18px;
    border: none;
    background: #f1f3f5;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #555;
    transition: all 0.25s ease;
    flex: 1;
    min-width: 140px;
    text-align: center;
}

.tab-btn:hover {
    background: #e9ecef;
}

.tab-btn.active {
    background: #4a90e2;
    color: white;
    box-shadow: 0 2px 8px rgba(74, 144, 226, 0.3);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.panel {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    margin-bottom: 20px;
    overflow-x: hidden;
}

.panel h2 {
    font-size: 17px;
    margin: 0 0 18px 0;
    color: #2c3e50;
    font-weight: 600;
}

.panel h3 {
    font-size: 15px;
    margin: 0 0 12px 0;
    color: #34495e;
}

/* ========== BACKGROUND TYPE ========== */
.bg-type-selector {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}

.bg-type-option {
    cursor: pointer;
}

.bg-type-option input {
    display: none;
}

.bg-type-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 18px 12px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    transition: all 0.25s ease;
    background: #fafbfc;
    height: 100%;
}

.bg-type-option input:checked + .bg-type-card {
    border-color: #4a90e2;
    background: #f0f7ff;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
}

.bg-type-card:hover {
    border-color: #4a90e2;
}

.bg-type-icon {
    font-size: 28px;
    margin-bottom: 8px;
}

.bg-type-label {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
    font-size: 14px;
}

.bg-type-desc {
    font-size: 12px;
    color: #6b7280;
    text-align: center;
    line-height: 1.3;
}

/* ========== UPLOAD ZONE ========== */
.upload-zone {
    border: 2px dashed #d1d5db;
    padding: 24px 16px;
    text-align: center;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.25s ease;
    background: #f9fafb;
}

.upload-zone:hover {
    border-color: #4a90e2;
    background: #f0f7ff;
}

.current-image {
    position: relative;
}

.current-image img {
    max-height: 130px;
    border-radius: 8px;
    margin-bottom: 10px;
    max-width: 100%;
    object-fit: contain;
}

.remove-btn {
    background: #ef4444;
    color: white;
    border: none;
    padding: 7px 14px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: background 0.2s;
}

.remove-btn:hover {
    background: #dc2626;
}

.upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.upload-icon {
    font-size: 36px;
    opacity: 0.7;
}

.upload-text {
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.upload-subtext {
    font-size: 12px;
    color: #9ca3af;
}

/* ========== COLOR PRESETS ========== */
.color-presets {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}

.color-option {
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.color-option input {
    display: none;
}

.color-swatch {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    border: 2px solid transparent;
    transition: all 0.25s ease;
    position: relative;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}

.color-option input:checked + .color-swatch {
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.25);
    transform: scale(1.05);
}

.color-check {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 22px;
    font-weight: bold;
    opacity: 0;
    transition: opacity 0.2s;
    text-shadow: 0 1px 3px rgba(0,0,0,0.4);
}

.color-option input:checked + .color-swatch .color-check {
    opacity: 1;
}

.color-name {
    font-size: 11px;
    font-weight: 500;
    color: #6b7280;
    text-align: center;
    line-height: 1.2;
}

.custom-color-section {
    margin-top: 16px;
    padding: 18px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.color-picker-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.color-preview-box {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    border: 2px solid #e5e7eb;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.color-picker-wrapper input[type="color"] {
    width: 48px;
    height: 48px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    padding: 0;
    flex-shrink: 0;
}

.color-hex-input {
    flex: 1;
    min-width: 110px;
    padding: 11px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-family: ui-monospace, monospace;
    font-size: 14px;
    color: #1f2937;
    transition: border-color 0.2s;
}

.color-hex-input:focus {
    outline: none;
    border-color: #4a90e2;
}

/* ========== RECEPTION PREVIEW ========== */
.preview-wrapper {
    margin-top: 16px;
}

.reception-preview-full {
    height: 400px;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.preview-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
}

.preview-content {
    position: relative;
    z-index: 1;
    height: 100%;
    display: flex;
    flex-direction: column;
    color: white;
}

.preview-content .reception-nav {
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 14px;
}

.preview-content .nav-toggle {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    font-size: 18px;
    width: 38px;
    height: 38px;
    border-radius: 8px;
    cursor: pointer;
    backdrop-filter: blur(8px);
}

.preview-content .nav-menu {
    display: flex;
    gap: 18px;
}

.preview-content .nav-menu a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    opacity: 0.9;
    font-size: 13px;
}

.preview-content .reception-header {
    text-align: center;
    padding: 32px 16px 20px;
    flex-shrink: 0;
}

.preview-content .reception-header h1 {
    font-size: 26px;
    margin: 0 0 8px 0;
    font-weight: 700;
    text-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.preview-content .reception-header p {
    font-size: 14px;
    margin: 0;
    opacity: 0.9;
}

.preview-content .search-section {
    padding: 0 16px;
    max-width: 480px;
    margin: 0 auto;
    width: 100%;
    margin-bottom: 20px;
    box-sizing: border-box;
}

.preview-content .search-box {
    background: rgba(255,255,255,0.18);
    border-radius: 12px;
    padding: 14px 18px;
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    min-height: 24px;
}

/* Decorative placeholder text (not a real input) so it can wrap to
   two lines on narrow screens instead of being clipped/overlapping. */
.preview-content .search-placeholder {
    display: block;
    width: 100%;
    color: rgba(255,255,255,0.75);
    font-size: 15px;
    line-height: 1.35;
    white-space: normal;
    overflow-wrap: break-word;
    word-break: break-word;
}

/* ========== BILLING LAYOUT ========== */
.billing-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    align-items: start;
}

.preview-panel {
    position: sticky;
    top: 20px;
}

.preview-toggle {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}

.preview-toggle button {
    flex: 1;
}

/* ========== FORM ELEMENTS ========== */
.grid2, .grid3 {
    display: grid;
    gap: 14px;
}

.grid2 {
    grid-template-columns: repeat(2, 1fr);
}

.grid3 {
    grid-template-columns: repeat(3, 1fr);
}

label {
    display: block;
    margin-bottom: 14px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
}

label input,
label textarea,
label select {
    width: 100%;
    padding: 11px 13px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
    margin-top: 5px;
    background: white;
    color: #1f2937;
}

label input:focus,
label textarea:focus,
label select:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
}

label textarea {
    resize: vertical;
    min-height: 70px;
}

.range-label {
    margin-top: 16px;
}

.range-label input[type="range"] {
    width: 100%;
    margin: 8px 0 4px;
}

.range-label small {
    font-size: 12px;
    color: #6b7280;
}

.toggle-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding-top: 6px;
}

.toggle-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.toggle-label {
    font-size: 13px;
    font-weight: 500;
    color: #374151;
}

.switch {
    position: relative;
    display: inline-block;
    width: 46px;
    height: 26px;
    flex-shrink: 0;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #d1d5db;
    transition: .3s;
    border-radius: 26px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

input:checked + .slider {
    background-color: #4a90e2;
}

input:checked + .slider:before {
    transform: translateX(20px);
}

/* ========== BUTTONS ========== */
.form-actions {
    margin-top: 32px;
    padding-top: 8px;
}

.primary, .secondary {
    padding: 12px 22px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.primary {
    background: #4a90e2;
    color: white;
    box-shadow: 0 2px 8px rgba(74, 144, 226, 0.3);
}

.primary:hover {
    background: #3a7bc8;
    transform: translateY(-1px);
}

.secondary {
    background: #f1f3f5;
    color: #4b5563;
}

.secondary:hover {
    background: #e5e7eb;
}

.secondary.primary {
    background: #4a90e2;
    color: white;
}

/* ========== INVOICE PREVIEWS ========== */
.preview-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.invoice-preview {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-family: Arial, sans-serif;
    color: #333;
}

/* A4 */
.a4-preview {
    padding: 22px;
    max-width: 600px;
    width: 100%;
    box-sizing: border-box;
    margin: 0 auto;
    font-size: 12px;
}

.invoice-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin-bottom: 20px;
    border-bottom: 2px solid #333;
    padding-bottom: 15px;
    gap: 16px;
}

.invoice-left {
    flex: 1;
}

.invoice-left h1 {
    font-size: 20px;
    margin: 8px 0 6px;
    color: #2c3e50;
}

.invoice-left p {
    margin: 3px 0;
    color: #666;
    font-size: 11px;
}

.invoice-right {
    text-align: right;
}

.invoice-right h2 {
    font-size: 18px;
    margin: 0 0 10px;
    color: #e74c3c;
}

.invoice-right p {
    margin: 4px 0;
    font-size: 11px;
}

.status-paid {
    color: #16a34a;
    font-weight: bold;
}

.invoice-parties {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 16px;
}

.invoice-parties h3 {
    font-size: 13px;
    margin: 0 0 8px;
    color: #2c3e50;
    border-bottom: 1px solid #ddd;
    padding-bottom: 5px;
}

.invoice-parties p {
    margin: 4px 0;
    color: #666;
    font-size: 11px;
}

.invoice-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
    margin-bottom: 20px;
    font-size: 11px;
}

.invoice-table th,
.invoice-table td {
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.invoice-table th {
    white-space: nowrap;
}

.th-short {
    display: none;
}

.invoice-table th {
    background: #f8f9fa;
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
    font-weight: bold;
    color: #2c3e50;
}

.invoice-table td {
    border: 1px solid #ddd;
    padding: 8px;
}

.invoice-table th:nth-child(1),
.invoice-table td:nth-child(1) {
    width: 28%;
}

.invoice-table th:nth-child(2),
.invoice-table td:nth-child(2) {
    width: 10%;
    text-align: center;
}

.invoice-table th:nth-child(3),
.invoice-table td:nth-child(3),
.invoice-table th:nth-child(4),
.invoice-table td:nth-child(4),
.invoice-table th:nth-child(5),
.invoice-table td:nth-child(5) {
    width: 20.67%;
    text-align: right;
}

.invoice-totals {
    width: 250px;
    margin-left: auto;
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px solid #eee;
    font-size: 11px;
}

.total-row.grand {
    border-top: 2px solid #333;
    border-bottom: none;
    margin-top: 6px;
    padding-top: 10px;
    font-size: 13px;
    font-weight: bold;
    color: #2c3e50;
}

.invoice-terms {
    margin-top: 16px;
    padding: 12px;
    background: #f8f9fa;
    border-left: 3px solid #e74c3c;
    font-size: 10px;
}

.invoice-terms p {
    margin: 4px 0 0;
}

.invoice-payment {
    margin-top: 14px;
    padding: 12px;
    background: #f0f8ff;
    border-left: 3px solid #3498db;
    font-size: 10px;
}

.invoice-payment p {
    margin: 4px 0 0;
}

.invoice-footer {
    margin-top: 22px;
    padding-top: 14px;
    border-top: 1px solid #ddd;
    text-align: center;
    color: #666;
    font-size: 10px;
}

.invoice-footer p {
    margin: 4px 0;
}

.powered {
    font-weight: bold;
    margin-top: 10px !important;
}

/* Thermal */
.thermal-preview {
    padding: 12px;
    max-width: 280px;
    margin: 0 auto;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    font-weight: bold;
}

.thermal-header {
    text-align: center;
    margin-bottom: 8px;
}

.thermal-header img {
    margin-bottom: 4px;
}

.thermal-company {
    font-size: 16px;
    font-weight: 900;
    margin: 4px 0;
}

.thermal-header div {
    font-size: 11px;
    margin: 2px 0;
}

.thermal-divider {
    border-top: 2px dashed #000;
    margin: 8px 0;
}

.thermal-meta {
    text-align: center;
    margin-bottom: 8px;
}

.thermal-invoice {
    font-size: 14px;
    font-weight: 900;
}

.thermal-info {
    margin-bottom: 6px;
    font-size: 11px;
}

.thermal-info div {
    margin-bottom: 4px;
}

.thermal-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
    font-size: 11px;
}

.thermal-table td {
    padding: 2px 0;
}

.thermal-table td:nth-child(2) {
    text-align: center;
}

.thermal-table td:nth-child(3) {
    text-align: right;
}

.thermal-totals {
    text-align: right;
    font-size: 11px;
}

.thermal-totals div {
    margin-bottom: 3px;
}

.thermal-totals .grand {
    font-size: 13px;
    font-weight: 900;
    border-top: 2px solid #000;
    padding-top: 4px;
    margin-top: 4px;
}

.thermal-paid {
    text-align: center;
    margin: 8px 0;
    font-size: 14px;
    font-weight: 900;
}

.thermal-footer {
    text-align: center;
    margin-top: 8px;
    font-size: 10px;
}

.thermal-footer div {
    margin: 2px 0;
}

/* ========== RESPONSIVE ========== */

/* Large tablets / small desktops */
@media (max-width: 1100px) {
    .billing-grid {
        grid-template-columns: 1fr;
    }
    
    .preview-panel {
        position: static;
    }
}

/* Tablets */
@media (max-width: 768px) {
    .settings-tabs {
        flex-direction: column;
        gap: 6px;
    }
    
    .tab-btn {
        min-width: auto;
        width: 100%;
        padding: 12px 14px;
        font-size: 14px;
    }
    
    .panel {
        padding: 18px 16px;
        border-radius: 10px;
    }
    
    .panel h2 {
        font-size: 16px;
        margin-bottom: 14px;
    }
    
    .bg-type-selector {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .bg-type-card {
        padding: 14px;
        flex-direction: row;
        gap: 14px;
        text-align: left;
    }
    
    .bg-type-icon {
        font-size: 24px;
        margin-bottom: 0;
    }
    
    .bg-type-label {
        font-size: 14px;
        margin-bottom: 2px;
    }
    
    .bg-type-desc {
        font-size: 12px;
        text-align: left;
    }
    
    .color-presets {
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }
    
    .color-swatch {
        width: 46px;
        height: 46px;
    }
    
    .color-name {
        font-size: 10px;
    }
    
    .color-picker-wrapper {
        flex-direction: column;
        align-items: stretch;
    }
    
    .color-preview-box,
    .color-picker-wrapper input[type="color"] {
        width: 100%;
        height: 48px;
    }
    
    .grid2, .grid3 {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .reception-preview-full {
        height: 340px;
    }
    
    .preview-content .reception-header {
        padding: 24px 16px 16px;
    }
    
    .preview-content .reception-header h1 {
        font-size: 22px;
    }
    
    .preview-content .nav-menu {
        display: none;
    }
    
    .preview-content .search-section {
        padding: 0 14px;
        margin-bottom: 16px;
    }
    
    .preview-content .search-placeholder {
        font-size: 14px;
    }
    
    .upload-zone {
        padding: 18px 12px;
    }
    
    .current-image img {
        max-height: 100px;
    }
    
    /* Scale down previews on tablet */
    .a4-preview {
        padding: 16px 14px;
        font-size: 11px;
    }

    .invoice-header {
        flex-wrap: wrap;
        gap: 12px;
    }

    .invoice-parties {
        flex-wrap: wrap;
        gap: 14px;
    }

    .invoice-table {
        font-size: 8px;
    }

    .invoice-table th,
    .invoice-table td {
        padding: 5px 3px;
        line-height: 1.2;
    }

    .th-full {
        display: none;
    }

    .th-short {
        display: inline;
    }
    
    .preview-toggle {
        flex-direction: column;
    }
    
    .preview-toggle button {
        width: 100%;
    }
    
    .form-actions .primary {
        width: 100%;
    }
}

/* Mobile phones */
@media (max-width: 480px) {
    .page-head h1 {
        font-size: 22px;
    }
    
    .page-head p {
        font-size: 13px;
    }
    
    .panel {
        padding: 16px 12px;
    }
    
    .panel h2 {
        font-size: 15px;
    }
    
    .color-presets {
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }
    
    .color-swatch {
        width: 42px;
        height: 42px;
        border-radius: 10px;
    }
    
    .color-name {
        font-size: 9px;
    }
    
    .reception-preview-full {
        height: 320px;
        border-radius: 10px;
    }
    
    .preview-content .reception-header {
        padding: 20px 12px 14px;
    }
    
    .preview-content .reception-header h1 {
        font-size: 18px;
    }
    
    .preview-content .reception-header p {
        font-size: 12px;
    }
    
    .preview-content .search-section {
        padding: 0 12px;
        margin-bottom: 14px;
    }
    
    .preview-content .search-box {
        padding: 12px 14px;
    }
    
    .preview-content .search-placeholder {
        font-size: 13px;
        line-height: 1.3;
    }
    
    .preview-content .nav-toggle {
        width: 34px;
        height: 34px;
        font-size: 16px;
    }
    
    .upload-icon {
        font-size: 30px;
    }
    
    .upload-text {
        font-size: 13px;
    }
    
    .upload-subtext {
        font-size: 11px;
    }
    
    .a4-preview {
        padding: 14px 12px;
        font-size: 10px;
    }

    .invoice-header {
        flex-direction: column;
        gap: 10px;
        margin-bottom: 14px;
        padding-bottom: 12px;
    }

    .invoice-right {
        text-align: left;
    }

    .invoice-left h1 {
        font-size: 16px;
        margin: 6px 0 4px;
    }

    .invoice-right h2 {
        font-size: 15px;
    }

    .invoice-parties {
        flex-direction: column;
        gap: 12px;
        margin-bottom: 14px;
    }

    .invoice-table {
        font-size: 6px;
    }

    .invoice-table th,
    .invoice-table td {
        padding: 4px 2px;
        line-height: 1.2;
    }

    .th-full {
        display: none;
    }

    .th-short {
        display: inline;
    }

    .invoice-totals {
        width: 100%;
    }

    .thermal-preview {
        max-width: 100%;
    }
    
    .primary, .secondary {
        padding: 13px 16px;
        font-size: 14px;
        width: 100%;
    }
    
    label {
        font-size: 12px;
    }
    
    label input,
    label textarea,
    label select {
        padding: 12px;
        font-size: 15px; /* better for mobile input */
    }
}

/* Very small phones */
@media (max-width: 360px) {
    .color-presets {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .color-swatch {
        width: 48px;
        height: 48px;
    }
    
    .reception-preview-full {
        height: 300px;
    }
    
    .preview-content .reception-header h1 {
        font-size: 16px;
    }
    
    .preview-content .search-placeholder {
        font-size: 12px;
    }
    
    .a4-preview {
        padding: 12px 10px;
        font-size: 9px;
    }

    .invoice-left h1 {
        font-size: 14px;
    }

    .invoice-table {
        font-size: 5.5px;
    }

    .invoice-table th,
    .invoice-table td {
        padding: 3px 2px;
    }
}
</style>
@endsection