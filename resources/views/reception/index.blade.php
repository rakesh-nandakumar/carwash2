@extends('layouts.reception')

@section('content')
@php
    $business = auth()->user()->business;
    $settings = $business ? $business->getBillingSettings() : [];

    $bgType = $settings['background_type'] ?? 'image';
    $bgImage = $settings['reception_background_image'] ?? '';
    $bgColor = $settings['background_color'] ?? '';
    $customBgColor = $settings['custom_background_color'] ?? '';

    if ($bgType === 'color' && $customBgColor) {
        $receptionBackground = $customBgColor;
        $receptionBackgroundType = 'color';
    } elseif ($bgType === 'color' && $bgColor) {
        $receptionBackground = $bgColor;
        $receptionBackgroundType = 'color';
    } elseif ($bgImage) {
        $receptionBackground = "url('" . asset($bgImage) . "')";
        $receptionBackgroundType = 'image';
    } else {
        $receptionBackground = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        $receptionBackgroundType = 'color';
    }
@endphp

<style>
    :root {
        --reception-background: {!! $receptionBackground !!};
    }
</style>

{{-- Toast outside reception-container so it is never trapped under modal blur --}}
<div id="toastContainer"></div>

<div class="reception-container"
    @if($receptionBackgroundType === 'image')
        style="--reception-background: {{ $receptionBackground }};"
    @endif>
    
    <!-- Navigation Menu -->
    <div class="reception-nav">
        <button class="nav-toggle" onclick="toggleNav()">☰</button>
        <div class="nav-menu" id="navMenu">
            <a href="{{ route('dashboard') }}" @if(!auth()->user()->hasPermissionTo('dashboard.access')) style="display:none" @endif>Dashboard</a>
            <a href="{{ route('jobs.index') }}" @if(!auth()->user()->hasPermissionTo('job_cards.access')) style="display:none" @endif>Job Cards</a>
            <a href="{{ route('customers.index') }}" @if(!auth()->user()->hasPermissionTo('customers.access')) style="display:none" @endif>Customers</a>
            <a href="{{ route('vehicles.index') }}" @if(!auth()->user()->hasPermissionTo('vehicles.access')) style="display:none" @endif>Vehicles</a>
            <a href="{{ route('appointments.index') }}" @if(!auth()->user()->hasPermissionTo('appointments.access')) style="display:none" @endif>Appointments</a>
            <a href="{{ route('inventory.index') }}" @if(!auth()->user()->hasPermissionTo('inventory.access')) style="display:none" @endif>Inventory</a>
            <a href="{{ route('invoices.index') }}" @if(!auth()->user()->hasPermissionTo('invoices.access')) style="display:none" @endif>Billing</a>
            <a href="{{ route('reports') }}" @if(!auth()->user()->hasPermissionTo('reports.access')) style="display:none" @endif>Reports</a>
            <a href="{{ route('users.index') }}" @if(!auth()->user()->hasPermissionTo('users.access')) style="display:none" @endif>Users</a>
            
            <a href="{{ route('logout') }}" class="logout-link">Sign Out</a>
        </div>
    </div>

    <div class="reception-header">
        <h1>Vehicle Check-in</h1>
        <p>Enter vehicle registration number to begin</p>
    </div>

    <div class="search-section">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Vehicle registration number..." autocomplete="off">
        </div>
        <div id="searchResults" class="search-results"></div>
    </div>

    <!-- Vehicle Modal -->
    <div id="vehicleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Vehicle</h3>
                <button class="modal-close" onclick="closeVehicleModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Vehicle Image</label>
                    <div class="image-upload-area" id="vehicleImageUploadArea">
                        <div class="image-preview" id="vehicleImagePreview" style="display:none;">
                            <img id="vehiclePreviewImg" src="" alt="Vehicle preview">
                            <button type="button" class="btn-remove-image" onclick="removeVehicleImage()" title="Remove image">&times;</button>
                        </div>
                        <div class="image-upload-placeholder" id="vehicleImagePlaceholder">
                            <div class="upload-icon">📷</div>
                            <p>Add vehicle photo</p>
                            <div class="upload-actions">
                                <button type="button" class="btn-upload" onclick="openLiveCamera('vehicle')">
                                    📸 Camera
                                </button>
                                <label class="btn-upload btn-upload-secondary" for="vehicleImageInput">
                                    <input type="file" id="vehicleImageInput" accept="image/*" style="display:none;">
                                    🖼️ Gallery / Files
                                </label>
                            </div>
                            <small class="upload-hint">Phone: Camera or Gallery · Desktop: choose either</small>
                        </div>
                    </div>
                </div>

                <div class="vehicle-form-grid">
                    <div class="form-group">
                        <label>Registration Number *</label>
                        <input type="text" id="modalVehicleRegistration" placeholder="Enter registration number">
                    </div>
                    <div class="form-group">
                        <label>Make</label>
                        <input type="text" id="modalVehicleMake" placeholder="e.g., Toyota">
                    </div>
                    <div class="form-group">
                        <label>Model</label>
                        <input type="text" id="modalVehicleModel" placeholder="e.g., Corolla">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <div class="searchable-dropdown" id="vehicleCategoryDropdown">
                            <input type="hidden" id="modalVehicleCategory" name="category" value="">
                            <input type="text" class="searchable-dropdown-input" id="vehicleCategoryInput" placeholder="Search or select category...">
                            <div class="searchable-dropdown-options"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Customer *</label>
                        <div class="customer-select-wrapper">
                            <div class="searchable-dropdown" id="customerDropdown">
                                <input type="hidden" id="modalVehicleCustomer" name="customer_id" value="">
                                <input type="text" class="searchable-dropdown-input" id="modalVehicleCustomerInput" placeholder="Search or select customer...">
                                <div class="searchable-dropdown-options"></div>
                            </div>
                            <button type="button" class="btn-add-customer" onclick="openCustomerModal()">+ New Customer</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="createVehicleFromModal()" class="btn-primary">Create Vehicle</button>
                <button onclick="closeVehicleModal()" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Customer Modal -->
    <div id="customerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Customer</h3>
                <button class="modal-close" onclick="closeCustomerModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" id="modalCustomerName" placeholder="Enter customer name">
                </div>
                <div class="form-group">
                    <label>Phone *</label>
                    <input
                        type="tel"
                        id="modalCustomerPhone"
                        placeholder="Enter phone number"
                        autocomplete="tel"
                    >
                </div>
                <div class="form-group">
                    <label>WhatsApp Number</label>
                    <input
                        type="tel"
                        id="modalCustomerWhatsapp"
                        placeholder="+94XXXXXXXXX"
                        autocomplete="tel"
                    >
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="modalCustomerEmail" placeholder="Enter email (optional)">
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="createCustomerFromModal()" class="btn-primary">Create Customer</button>
                <button onclick="closeCustomerModal()" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Job Card Modal -->
    <div id="jobModal" class="modal">
        <div class="modal-content job-modal-content">
            <div class="modal-header">
                <h3>Create Job Card</h3>
                <button class="modal-close" onclick="closeJobModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="vehicle-image-section">
                    <div id="vehicleImageContainer" class="vehicle-image-container">
                        <div class="vehicle-placeholder">
                            <span>Vehicle Image</span>
                        </div>
                    </div>
                    <div class="job-image-actions">
                        <button type="button" class="btn-change-image" onclick="openLiveCamera('job')">
                            📷 Camera
                        </button>
                        <label class="btn-change-image" for="jobVehicleImageInput">
                            <input type="file" id="jobVehicleImageInput" accept="image/*" style="display:none;">
                            🖼️ Gallery / Files
                        </label>
                        <button type="button" id="removeJobImageBtn" class="btn-remove-job-image" onclick="removeJobImage()" style="display:none;">Remove</button>
                    </div>
                </div>

                <div class="info-row">
                    <div class="customer-info">
                        <h3>Customer Information</h3>
                        <div id="customerDetails"></div>
                    </div>

                    <div class="vehicle-info">
                        <h3>Vehicle Information</h3>
                        <div id="vehicleDetails"></div>
                    </div>
                </div>

                <div class="services-section">
                    <h3>Select Services</h3>
                    <div id="servicesList" class="services-grid"></div>
                </div>

                <div class="products-section">
                    <h3>Products / Parts</h3>

                    <div class="product-search-wrapper">
                        <input
                            type="text"
                            id="productSearch"
                            class="form-input"
                            placeholder="Search products by name, SKU or part number..."
                            oninput="filterReceptionProducts()"
                        >
                    </div>

                    <div id="productsList" class="products-grid"></div>

                    <div id="selectedProductsList" class="selected-products-list"></div>
                </div>

                <div class="discount-section">
                    <div class="form-group">
                        <label for="receptionDiscountType">Discount</label>
                        <select id="receptionDiscountType" onchange="handleReceptionDiscountTypeChange()">
                            <option value="none">No Discount</option>
                            <option value="amount">Fixed Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>

                    <div id="receptionDiscountOptions" style="display: none;">
                        <div class="form-group">
                            <label for="receptionDiscountValue">Discount Value</label>
                            <div class="discount-input-wrapper">
                                <input
                                    type="number"
                                    id="receptionDiscountValue"
                                    min="0"
                                    step="0.01"
                                    value="0"
                                    oninput="calculateReceptionDiscount()"
                                >
                                <span id="receptionDiscountSuffix">Rs.</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="receptionDiscountApplyTo">Apply Discount To</label>
                            <select id="receptionDiscountApplyTo" onchange="calculateReceptionDiscount()">
                                <option value="total">Total</option>
                                <option value="services">Services</option>
                                <option value="parts">Products / Parts</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="reception-summary">
                    <div class="summary-row">
                        <span>Services Total</span>
                        <strong id="receptionServicesTotal">Rs. 0.00</strong>
                    </div>

                    <div class="summary-row">
                        <span>Products / Parts Total</span>
                        <strong id="receptionProductsTotal">Rs. 0.00</strong>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong id="receptionSubtotal">Rs. 0.00</strong>
                    </div>

                    <div class="summary-row">
                        <span>Discount</span>
                        <strong id="receptionDiscount">- Rs. 0.00</strong>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row total-row">
                        <span>Estimated Total</span>
                        <strong id="receptionGrandTotal">Rs. 0.00</strong>
                    </div>
                </div>

                <div class="notes-section">
                    <h3>Notes</h3>
                    <textarea id="jobNotes" rows="3" placeholder="Additional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button id="createJobBtn" class="btn-primary">Create Job Card</button>
                <button id="cancelBtn" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <!-- WhatsApp Modal -->
    <div id="whatsappModal" class="modal whatsapp-modal">
        <div class="modal-content whatsapp-modal-content">
            <div class="modal-header">
                <h3>Send WhatsApp Notification</h3>
                <button class="modal-close" onclick="closeWhatsappModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Job created successfully! Would you like to send a WhatsApp notification to the customer?</p>

                <div class="custom-notes-section">
                    <label>Additional Notes (Optional)</label>
                    <textarea id="whatsappNotes" rows="3" placeholder="Add any additional notes for the customer"></textarea>
                    <small>This will be included in the WhatsApp message</small>
                </div>

                <div class="whatsapp-section">
                    <div id="whatsappCustomerInfo">
                        <label class="whatsapp-toggle">
                            <input type="checkbox" id="sendWhatsapp" checked>
                            <span class="toggle-label" id="whatsappLabel">Send WhatsApp to customer</span>
                        </label>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="closeModalBtn">Cancel</button>
                    <button type="button" class="btn-primary" id="sendWhatsappBtn">Send WhatsApp & Open Job</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Camera Modal -->
<div id="liveCameraModal" class="modal">
    <div class="modal-content" style="max-width:640px;">
        <div class="modal-header">
            <h3>Take Photo</h3>
            <button class="modal-close" onclick="closeLiveCamera()">&times;</button>
        </div>
        <div class="modal-body" style="text-align:center;">
            <video id="liveCameraVideo" autoplay playsinline style="width:100%;max-height:360px;border-radius:12px;background:#000;"></video>
            <canvas id="liveCameraCanvas" style="display:none;"></canvas>
            <p id="liveCameraError" style="color:#fca5a5;display:none;margin-top:12px;"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" onclick="captureLivePhoto()">Capture</button>
            <button type="button" class="btn-secondary" onclick="closeLiveCamera()">Cancel</button>
        </div>
    </div>
</div>

<script>
let selectedCustomer = null;
let selectedVehicle = null;
let selectedServices = [];
let selectedProducts = [];
let searchTimeout = null;
let customerDropdown = null;
let selectedCustomerData = null;
let vehicleCategoryDropdown = null;
let selectedCategoryData = null;

// ---------- Customer WhatsApp auto-fill ----------
let receptionWhatsappManuallyEdited = false;

function formatReceptionSriLankaWhatsApp(phone) {
    phone = phone.trim();

    if (phone.startsWith('+94')) {
        return phone;
    }

    if (phone.startsWith('94')) {
        return '+' + phone;
    }

    if (phone.startsWith('0') && phone.length >= 10) {
        return '+94' + phone.substring(1);
    }

    return phone;
}

document
    .getElementById('modalCustomerWhatsapp')
    ?.addEventListener('input', function () {
        receptionWhatsappManuallyEdited = true;
    });

document
    .getElementById('modalCustomerPhone')
    ?.addEventListener('input', function () {
        // Only auto-fill if whatsapp field hasn't been manually edited and is still just "+94"
        if (!receptionWhatsappManuallyEdited && document.getElementById('modalCustomerWhatsapp').value === '+94') {
            const formattedPhone = formatReceptionSriLankaWhatsApp(this.value);
            if (formattedPhone !== this.value) {
                document.getElementById('modalCustomerWhatsapp').value = formattedPhone;
            }
        }
    });

// Cache of full service objects (id + base_price) loaded from /reception/services,
// needed because selectedServices only stores the checked ids.
let receptionServices = [];

// Discount state (Bill Summary)
let receptionDiscountType = 'none';
let receptionDiscountValue = 0;
let receptionDiscountApplyTo = 'total';

// Image state
let vehicleImageFile = null;
let vehicleImagePreviewDataUrl = null;
let jobImageFile = null;
let jobImagePreviewUrl = null;

function resolveImageUrl(image) {
    if (!image || typeof image !== 'string') {
        return null;
    }

    image = image.trim();

    // Already a complete URL
    if (
        image.startsWith('http://') ||
        image.startsWith('https://') ||
        image.startsWith('blob:') ||
        image.startsWith('data:')
    ) {
        return image;
    }

    // Laravel-generated storage URL
    return @json(asset('storage')) + '/' + image.replace(/^\/+/, '');
}

function showToast(message, type = 'success') {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        document.body.appendChild(container);
    } else if (container.parentElement !== document.body) {
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

document.getElementById('searchInput').addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();
    
    if (query.length >= 2) {
        searchTimeout = setTimeout(() => performSearch(query), 300);
    } else {
        document.getElementById('searchResults').innerHTML = '';
    }
});

document.getElementById('cancelBtn').addEventListener('click', closeJobModal);
document.getElementById('createJobBtn').addEventListener('click', createJob);

document.getElementById('vehicleImageInput')?.addEventListener('change', handleVehicleImageSelect);
document.getElementById('jobVehicleImageInput')?.addEventListener('change', handleJobImageSelect);

// ---------- Live camera ----------
let liveCameraStream = null;
let liveCameraTarget = null;

async function openLiveCamera(target) {
    liveCameraTarget = target;
    const modal = document.getElementById('liveCameraModal');
    const video = document.getElementById('liveCameraVideo');
    const errEl = document.getElementById('liveCameraError');
    errEl.style.display = 'none';
    errEl.textContent = '';
    modal.classList.add('active');

    if (liveCameraStream) {
        liveCameraStream.getTracks().forEach(t => t.stop());
        liveCameraStream = null;
    }

    try {
        liveCameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
            audio: false
        });
        video.srcObject = liveCameraStream;
        await video.play();
    } catch (err) {
        try {
            liveCameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            video.srcObject = liveCameraStream;
            await video.play();
        } catch (err2) {
            errEl.textContent = 'Camera access denied or not available. Use Gallery / Files instead.';
            errEl.style.display = 'block';
            showToast('Camera not available', 'error');
        }
    }
}

function closeLiveCamera() {
    const modal = document.getElementById('liveCameraModal');
    const video = document.getElementById('liveCameraVideo');
    if (liveCameraStream) {
        liveCameraStream.getTracks().forEach(t => t.stop());
        liveCameraStream = null;
    }
    if (video) video.srcObject = null;
    modal.classList.remove('active');
    liveCameraTarget = null;
}

function captureLivePhoto() {
    const video = document.getElementById('liveCameraVideo');
    const canvas = document.getElementById('liveCameraCanvas');
    if (!video || !video.videoWidth) {
        showToast('Camera not ready yet', 'error');
        return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    canvas.toBlob(function (blob) {
        if (!blob) {
            showToast('Could not capture photo', 'error');
            return;
        }
        const file = new File([blob], 'camera-capture.jpg', { type: 'image/jpeg' });

        if (liveCameraTarget === 'vehicle') {
            vehicleImageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                vehicleImagePreviewDataUrl = e.target.result;
                document.getElementById('vehiclePreviewImg').src = vehicleImagePreviewDataUrl;
                document.getElementById('vehicleImagePreview').style.display = 'block';
                document.getElementById('vehicleImagePlaceholder').style.display = 'none';
                showToast('Photo captured', 'success');
            };
            reader.readAsDataURL(file);
        } else if (liveCameraTarget === 'job') {
            jobImageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                jobImagePreviewUrl = e.target.result;
                const container = document.getElementById('vehicleImageContainer');
                if (container) {
                    container.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = jobImagePreviewUrl;
                    img.alt = 'Vehicle Image';
                    img.style.cssText = 'width:100%;height:auto;display:block;object-fit:cover;min-height:180px;';
                    container.appendChild(img);
                }
                const removeBtn = document.getElementById('removeJobImageBtn');
                if (removeBtn) removeBtn.style.display = 'inline-block';
                showToast('Photo captured', 'success');
            };
            reader.readAsDataURL(file);
        }

        closeLiveCamera();
    }, 'image/jpeg', 0.92);
}

// ---------- Vehicle image ----------
function handleVehicleImageSelect(event) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        showToast('Please select an image file', 'error');
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        showToast('Image must be under 5MB', 'error');
        return;
    }

    vehicleImageFile = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        vehicleImagePreviewDataUrl = e.target.result;
        document.getElementById('vehiclePreviewImg').src = vehicleImagePreviewDataUrl;
        document.getElementById('vehicleImagePreview').style.display = 'block';
        document.getElementById('vehicleImagePlaceholder').style.display = 'none';
        showToast('Photo selected', 'success');
    };
    reader.onerror = () => showToast('Could not read image', 'error');
    reader.readAsDataURL(file);
}

function removeVehicleImage() {
    vehicleImageFile = null;
    vehicleImagePreviewDataUrl = null;
    const input = document.getElementById('vehicleImageInput');
    if (input) input.value = '';
    const previewImg = document.getElementById('vehiclePreviewImg');
    if (previewImg) previewImg.src = '';
    const preview = document.getElementById('vehicleImagePreview');
    if (preview) preview.style.display = 'none';
    const placeholder = document.getElementById('vehicleImagePlaceholder');
    if (placeholder) placeholder.style.display = 'block';
}

// ---------- Job form image ----------
function handleJobImageSelect(event) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        showToast('Please select an image file', 'error');
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        showToast('Image must be under 5MB', 'error');
        return;
    }

    jobImageFile = file;

    if (jobImagePreviewUrl && jobImagePreviewUrl.startsWith('blob:')) {
        URL.revokeObjectURL(jobImagePreviewUrl);
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        jobImagePreviewUrl = e.target.result;
        const container = document.getElementById('vehicleImageContainer');
        if (container) {
            container.innerHTML = `<img src="${jobImagePreviewUrl}" alt="Vehicle Image" style="width:100%;height:auto;display:block;object-fit:cover;min-height:180px;">`;
        }
        const removeBtn = document.getElementById('removeJobImageBtn');
        if (removeBtn) removeBtn.style.display = 'inline-block';
        showToast('Photo selected', 'success');
    };
    reader.onerror = function () {
        showToast('Could not read image file', 'error');
    };
    reader.readAsDataURL(file);
}

function removeJobImage() {
    jobImageFile = null;
    if (jobImagePreviewUrl && jobImagePreviewUrl.startsWith('blob:')) {
        URL.revokeObjectURL(jobImagePreviewUrl);
    }
    jobImagePreviewUrl = null;

    const galleryInput = document.getElementById('jobVehicleImageInput');
    if (galleryInput) galleryInput.value = '';
    document.getElementById('vehicleImageContainer').innerHTML = `
        <div class="vehicle-placeholder">
            <span>Vehicle Image</span>
        </div>
    `;
    document.getElementById('removeJobImageBtn').style.display = 'none';
}

async function performSearch(query) {
    const response = await fetch('{{ route('reception.search') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ q: query })
    });

    const data = await response.json();
    displaySearchResults(data);
}

function displaySearchResults(data) {
    const resultsDiv = document.getElementById('searchResults');
    
    if (!data.found || (data.vehicles && data.vehicles.length === 0)) {
        const query = document.getElementById('searchInput').value.trim();
        resultsDiv.innerHTML = `
            <div class="result-card">
                <h4>No vehicle found</h4>
                <p>No vehicle found with registration "${query}"</p>
                <button onclick="openVehicleModal('${encodeURIComponent(query)}')" class="btn-primary">+ Add New Vehicle</button>
            </div>
        `;
        return;
    }

    let html = '';
    data.vehicles.forEach(vehicle => {
        html += `
            <div class="result-card">
                <h4>Vehicle Found</h4>
                <p><strong>Registration:</strong> ${vehicle.registration_number}</p>
                <p><strong>Make/Model:</strong> ${vehicle.make} ${vehicle.model}</p>
                <p><strong>Customer:</strong> ${vehicle.customer_name}</p>
                <button class="btn-primary" onclick='selectVehicleDirect(${JSON.stringify({
                    vehicle_id: vehicle.vehicle_id,
                    id: vehicle.vehicle_id,
                    registration_number: vehicle.registration_number,
                    make: vehicle.make,
                    model: vehicle.model,
                    category: vehicle.category,
                    customer_id: vehicle.customer_id,
                    customer_name: vehicle.customer_name,
                    customer_phone: vehicle.customer_phone,
                    customer_whatsapp_number: vehicle.customer_whatsapp_number,
                    image: vehicle.image,
                    image_url: vehicle.image_url
                })})'>Proceed to Job Creation</button>
            </div>
        `;
    });

    resultsDiv.innerHTML = html;
}

function openVehicleModal(registration = '') {
    const modal = document.getElementById('vehicleModal');
    modal.classList.add('active');
    document.getElementById('modalVehicleRegistration').value = decodeURIComponent(registration);
    removeVehicleImage();
    loadCustomersForModal();
    loadVehicleCategoriesForModal();
}

function closeVehicleModal(keepPreview = false) {
    document.getElementById('vehicleModal').classList.remove('active');
    if (!keepPreview) {
        removeVehicleImage();
    } else {
        const input = document.getElementById('vehicleImageInput');
        if (input) input.value = '';
        const preview = document.getElementById('vehicleImagePreview');
        if (preview) preview.style.display = 'none';
        const placeholder = document.getElementById('vehicleImagePlaceholder');
        if (placeholder) placeholder.style.display = 'block';
        const previewImg = document.getElementById('vehiclePreviewImg');
        if (previewImg) previewImg.src = '';
    }
}

function openCustomerModal() {
    document.getElementById('customerModal').classList.add('active');
}

function closeCustomerModal() {
    document.getElementById('customerModal').classList.remove('active');
    document.getElementById('modalCustomerName').value = '';
    document.getElementById('modalCustomerPhone').value = '';
    document.getElementById('modalCustomerWhatsapp').value = '';
    document.getElementById('modalCustomerEmail').value = '';
    receptionWhatsappManuallyEdited = false;
}

function closeJobModal() {
    document.getElementById('jobModal').classList.remove('active');
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('searchInput').value = '';
    selectedCustomer = null;
    selectedVehicle = null;
    selectedServices = [];
    selectedProducts = [];

    if (jobImagePreviewUrl && jobImagePreviewUrl.startsWith('blob:')) {
        URL.revokeObjectURL(jobImagePreviewUrl);
    }
    jobImageFile = null;
    jobImagePreviewUrl = null;
    document.getElementById('jobVehicleImageInput').value = '';
    document.getElementById('removeJobImageBtn').style.display = 'none';

    const productSearch = document.getElementById('productSearch');
    if (productSearch) productSearch.value = '';

    const selectedProductsList = document.getElementById('selectedProductsList');
    if (selectedProductsList) selectedProductsList.innerHTML = '';

    // Reset discount state so it doesn't carry over to the next job card
    receptionDiscountType = 'none';
    receptionDiscountValue = 0;
    receptionDiscountApplyTo = 'total';

    const discountType = document.getElementById('receptionDiscountType');
    if (discountType) discountType.value = 'none';

    const discountValue = document.getElementById('receptionDiscountValue');
    if (discountValue) discountValue.value = 0;

    const discountApplyTo = document.getElementById('receptionDiscountApplyTo');
    if (discountApplyTo) discountApplyTo.value = 'total';

    const discountOptions = document.getElementById('receptionDiscountOptions');
    if (discountOptions) discountOptions.style.display = 'none';

    calculateReceptionTotals();
}

async function loadCustomersForModal() {
    try {
        const response = await fetch('{{ route('customers.list') }}');
        const customers = await response.json();

        // Initialize searchable dropdown with customer data
        const dropdownContainer = document.getElementById('customerDropdown');
        if (dropdownContainer) {
            const customerData = Array.isArray(customers) ? customers.map(customer => ({
                id: customer.id,
                label: `${customer.full_name} — ${customer.whatsapp_number || customer.phone}`,
                name: customer.full_name,
                phone: customer.phone,
                whatsapp: customer.whatsapp_number || ''
            })) : [];

            customerDropdown = new SearchableDropdown(dropdownContainer, {
                data: customerData,
                onSelect: function(item) {
                    // Store selected customer data for later use
                    selectedCustomerData = item;
                }
            });
        }
    } catch (error) {
        console.error('Error loading customers:', error);
        showToast('Error loading customers', 'error');
    }
}

function loadVehicleCategoriesForModal() {
    // Initialize searchable dropdown with vehicle category data
    const categoryDropdownContainer = document.getElementById('vehicleCategoryDropdown');
    if (categoryDropdownContainer) {
        const categoryData = [
            { id: 'Small Car', label: 'Small Car' },
            { id: 'Sedan', label: 'Sedan' },
            { id: 'SUV', label: 'SUV' },
            { id: 'Luxury', label: 'Luxury' },
            { id: 'Van', label: 'Van' },
            { id: 'Pickup', label: 'Pickup' },
            { id: 'Jeep', label: 'Jeep' },
            { id: 'Three-wheeler', label: 'Three-wheeler' },
            { id: 'Motorcycle', label: 'Motorcycle' },
            { id: 'Commercial', label: 'Commercial' },
            { id: 'Bus', label: 'Bus' },
            { id: 'JCB Truck', label: 'JCB Truck' }
        ];

        vehicleCategoryDropdown = new SearchableDropdown(categoryDropdownContainer, {
            data: categoryData,
            onSelect: function(item) {
                // Store selected category data for later use
                selectedCategoryData = item;
            }
        });
    }
}

async function createVehicleFromModal() {
    const customerId = document.getElementById('modalVehicleCustomer').value;
    const registration = document.getElementById('modalVehicleRegistration').value.trim();
    const make = document.getElementById('modalVehicleMake').value.trim();
    const model = document.getElementById('modalVehicleModel').value.trim();
    const category = document.getElementById('modalVehicleCategory').value;

    if (!registration) {
        showToast('Registration number is required', 'error');
        return;
    }

    if (!customerId || isNaN(customerId)) {
        showToast('Please select a customer from the list', 'error');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('customer_id', customerId);
        formData.append('registration_number', registration);
        formData.append('make', make);
        formData.append('model', model);
        formData.append('category', category);
        if (vehicleImageFile) {
            formData.append('image', vehicleImageFile);
        }

        const response = await fetch('{{ route('vehicles.store') }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const data = await response.json();
        
        if (response.ok) {
            showToast('Vehicle created successfully!', 'success');

            const imageForJob =
                resolveImageUrl(data.image_url || data.image || null) ||
                vehicleImagePreviewDataUrl ||
                null;

            // Use selected customer data from dropdown
            const customerName = selectedCustomerData?.name || '';
            const customerPhone = selectedCustomerData?.phone || '';
            const customerWhatsapp = selectedCustomerData?.whatsapp || '';

            selectedVehicle = {
                vehicle_id: data.id,
                id: data.id,
                registration_number: data.registration_number,
                make: data.make,
                model: data.model,
                category: data.category || category,
                customer_id: data.customer_id,
                image: imageForJob
            };
            selectedCustomer = {
                id: customerId,
                full_name: customerName,
                phone: customerPhone,
                whatsapp_number: customerWhatsapp
            };

            closeVehicleModal(true);

            document.getElementById('searchResults').innerHTML = '';
            document.getElementById('searchInput').value = registration;
            showJobForm();

            vehicleImageFile = null;
            vehicleImagePreviewDataUrl = null;
        } else {
            const errorMsg = data.error || data.message || 'Error creating vehicle';
            showToast(errorMsg, 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

async function createCustomerFromModal() {
    const name = document.getElementById('modalCustomerName').value.trim();
    const phone = document.getElementById('modalCustomerPhone').value.trim();
    let whatsapp = document.getElementById('modalCustomerWhatsapp').value.trim();
    const email = document.getElementById('modalCustomerEmail').value.trim();

    if (!name || !phone) {
        showToast('Name and phone are required', 'error');
        return;
    }

    // If WhatsApp is still just "+94", format it from phone number
    if (whatsapp === '+94' || whatsapp === '') {
        whatsapp = formatReceptionSriLankaWhatsApp(phone);
    }

    try {
        const response = await fetch('{{ route('customers.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                full_name: name,
                phone: phone,
                whatsapp_number: whatsapp,
                email: email
            })
        });

        const data = await response.json();

        if (response.ok) {
            showToast('Customer created successfully!', 'success');
            closeCustomerModal();
            loadCustomersForModal().then(() => {
                // Update dropdown with new customer selected
                if (customerDropdown) {
                    customerDropdown.setValue(data.id, `${data.full_name} — ${data.whatsapp_number || data.phone}`);
                    selectedCustomerData = {
                        id: data.id,
                        label: `${data.full_name} — ${data.whatsapp_number || data.phone}`,
                        name: data.full_name,
                        phone: data.phone,
                        whatsapp: data.whatsapp_number || ''
                    };
                }
            });
        } else {
            const errorMsg = data.error || data.message || 'Error creating customer';
            showToast(errorMsg, 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

function selectVehicleDirect(vehicle) {
    selectedCustomer = {
        id: vehicle.customer_id,
        full_name: vehicle.customer_name,
        phone: vehicle.customer_phone || '',
        whatsapp_number: vehicle.customer_whatsapp_number || ''
    };

    selectedVehicle = {
        vehicle_id: vehicle.vehicle_id ?? vehicle.id,
        id: vehicle.vehicle_id ?? vehicle.id,
        registration_number: vehicle.registration_number ?? '',
        make: vehicle.make ?? '',
        model: vehicle.model ?? '',
        category: vehicle.category ?? '',
        customer_id: vehicle.customer_id,

        // Prefer Laravel's generated URL
        image: vehicle.image_url || resolveImageUrl(vehicle.image || null)
    };

    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('searchInput').value = vehicle.registration_number ?? '';

    showJobForm();
}

function selectCustomerDirect(customer) {
    selectedCustomer = { 
        id: customer.customer_id, 
        full_name: customer.name,
        phone: customer.phone || '',
        whatsapp_number: customer.whatsapp_number || ''
    };
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('searchInput').value = customer.name;
    
    if (customer.vehicles && customer.vehicles.length > 0) {
        let vehiclesHtml = customer.vehicles.map(v => 
            `<div class="vehicle-option">
                <button class="vehicle-btn" onclick="selectVehicle(${JSON.stringify(v).replace(/"/g, '&quot;')})">
                    <strong>${v.registration_number}</strong>
                    <span>${v.make} ${v.model}</span>
                </button>
            </div>`
        ).join('');
        
        document.getElementById('searchResults').innerHTML = `
            <div class="result-card">
                <h4>Select Vehicle for ${customer.name}</h4>
                <p>Found ${customer.vehicles.length} vehicle(s)</p>
                <div class="vehicles-list">
                    ${vehiclesHtml}
                </div>
                <a href="/vehicles/create?customer_id=${customer.customer_id}" class="btn-secondary">+ Add New Vehicle</a>
            </div>
        `;
    } else {
        document.getElementById('searchResults').innerHTML = `
            <div class="result-card">
                <p>No vehicles found for this customer.</p>
                <a href="/vehicles/create?customer_id=${customer.customer_id}" class="btn-primary">+ Add New Vehicle</a>
            </div>
        `;
    }
}

function selectVehicle(vehicle) {
    selectedVehicle = {
        vehicle_id: vehicle.vehicle_id ?? vehicle.id,
        id: vehicle.vehicle_id ?? vehicle.id,
        registration_number: vehicle.registration_number ?? '',
        make: vehicle.make ?? '',
        model: vehicle.model ?? '',
        category: vehicle.category ?? '',
        customer_id: vehicle.customer_id,

        // Prefer Laravel's generated URL
        image: vehicle.image_url || resolveImageUrl(vehicle.image || null)
    };

    showJobForm();
}

function showJobForm() {
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('jobModal').classList.add('active');

    jobImageFile = null;
    jobImagePreviewUrl = selectedVehicle?.image || null;

    document.getElementById('customerDetails').innerHTML = `
        <p>
            <strong>Name:</strong>
            ${selectedCustomer?.full_name ?? ''}
        </p>
    `;

    const vehicleImageContainer = document.getElementById('vehicleImageContainer');
    const imageUrl = selectedVehicle?.image || null;

    vehicleImageContainer.innerHTML = '';

    if (imageUrl) {
        const img = document.createElement('img');

        img.src = imageUrl;
        img.alt = 'Vehicle Image';

        img.style.width = '100%';
        img.style.height = '180px';
        img.style.display = 'block';
        img.style.objectFit = 'cover';
        img.style.borderRadius = '10px';

        img.onload = function () {
            const btn = document.getElementById('removeJobImageBtn');
            if (btn) {
                btn.style.display = 'inline-block';
            }
        };

        img.onerror = function () {
            console.error('Vehicle image failed:', imageUrl);

            vehicleImageContainer.innerHTML = `
                <div class="vehicle-placeholder">
                    <span>Vehicle Image</span>
                </div>
            `;

            const btn = document.getElementById('removeJobImageBtn');
            if (btn) {
                btn.style.display = 'none';
            }
        };

        vehicleImageContainer.appendChild(img);

    } else {
        vehicleImageContainer.innerHTML = `
            <div class="vehicle-placeholder">
                <span>Vehicle Image</span>
            </div>
        `;

        const btn = document.getElementById('removeJobImageBtn');
        if (btn) {
            btn.style.display = 'none';
        }
    }

    const category = selectedVehicle?.category;

    document.getElementById('vehicleDetails').innerHTML = `
        <p>
            <strong>Registration:</strong>
            ${selectedVehicle?.registration_number ?? ''}
        </p>
        <p>
            <strong>Make/Model:</strong>
            ${selectedVehicle?.make ?? ''}
            ${selectedVehicle?.model ?? ''}
        </p>
        <p>
            <strong>Category:</strong>
            ${category || 'Not specified'}
        </p>
    `;

    loadServices();
    loadProducts();
    calculateReceptionTotals();
}

async function loadServices() {
    const response = await fetch('{{ route('reception.services') }}');
    const services = await response.json();

    receptionServices = services;

    const servicesList = document.getElementById('servicesList');
    servicesList.innerHTML = services.map(service => `
        <label class="service-item">
            <input type="checkbox" value="${service.id}" data-price="${service.base_price}">
            <div class="service-info">
                <span class="service-name">${service.name}</span>
                <span class="service-price">LKR ${service.base_price.toLocaleString()}</span>
            </div>
        </label>
    `).join('');
    
    document.querySelectorAll('.service-item input').forEach(checkbox => {
        checkbox.addEventListener('change', (e) => {
            if (e.target.checked) {
                selectedServices.push(e.target.value);
            } else {
                selectedServices = selectedServices.filter(id => id !== e.target.value);
            }
            calculateReceptionTotals();
        });
    });
}

let receptionProducts = [];

async function loadProducts() {
    try {
        const response = await fetch('{{ route('reception.products') }}');

        if (!response.ok) {
            throw new Error('Failed to load products');
        }

        receptionProducts = await response.json();
        renderReceptionProducts(receptionProducts);

    } catch (error) {
        console.error('Error loading products:', error);

        document.getElementById('productsList').innerHTML = `
            <div class="empty-state">
                Unable to load products.
            </div>
        `;
    }
}

function renderReceptionProducts(products) {
    const container = document.getElementById('productsList');

    if (!products.length) {
        container.innerHTML = `
            <div class="empty-state">
                No products available.
            </div>
        `;
        return;
    }

    container.innerHTML = products.map(product => {
        const outOfStock = product.available_quantity <= 0;

        return `
            <div
                class="product-item ${outOfStock ? 'product-out-of-stock' : ''}"
                data-product-name="${(product.name || '').toLowerCase()}"
                data-product-sku="${(product.sku || '').toLowerCase()}"
                data-product-part="${(product.part_number || '').toLowerCase()}"
            >
                <div class="product-info">
                    <span class="product-name">${product.name}</span>
                    <span class="product-meta">
                        ${product.sku ? `SKU: ${product.sku}` : ''}
                        ${product.part_number ? ` · Part: ${product.part_number}` : ''}
                    </span>
                    <span class="product-stock">
                        Stock: ${product.available_quantity} ${product.unit || ''}
                    </span>
                </div>

                <div class="product-action">
                    <span class="product-price">
                        LKR ${Number(product.selling_price).toLocaleString()}
                    </span>
                    <button
                        type="button"
                        class="btn-secondary"
                        ${outOfStock ? 'disabled' : ''}
                        onclick="addReceptionProduct(${product.id})"
                    >
                        ${outOfStock ? 'Out of Stock' : 'Add'}
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function addReceptionProduct(productId) {
    const product = receptionProducts.find(p => Number(p.id) === Number(productId));
    if (!product) return;

    const existing = selectedProducts.find(p => Number(p.product_id) === Number(productId));

    if (existing) {
        if (existing.quantity + 1 > product.available_quantity) {
            showToast(`Only ${product.available_quantity} ${product.unit || ''} available`, 'error');
            return;
        }
        existing.quantity += 1;
    } else {
        selectedProducts.push({
            product_id: product.id,
            name: product.name,
            unit: product.unit,
            unit_price: Number(product.selling_price),
            available_quantity: Number(product.available_quantity),
            quantity: 1
        });
    }

    renderSelectedProducts();
    calculateReceptionTotals();
}

function renderSelectedProducts() {
    const container = document.getElementById('selectedProductsList');

    if (!selectedProducts.length) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = `
        <div class="selected-products-title">Selected Products</div>
        ${selectedProducts.map((product, index) => `
            <div class="selected-product-row">
                <div class="selected-product-info">
                    <strong>${product.name}</strong>
                    <span>LKR ${product.unit_price.toLocaleString()} / ${product.unit || 'unit'}</span>
                </div>
                <div class="selected-product-quantity">
                    <button type="button" onclick="changeReceptionProductQuantity(${index}, -1)">−</button>
                    <span>${product.quantity}</span>
                    <button type="button" onclick="changeReceptionProductQuantity(${index}, 1)">+</button>
                </div>
                <div class="selected-product-total">
                    LKR ${(product.unit_price * product.quantity).toLocaleString()}
                </div>
                <button type="button" class="remove-product-btn" onclick="removeReceptionProduct(${index})">×</button>
            </div>
        `).join('')}
    `;
}

function changeReceptionProductQuantity(index, change) {
    const product = selectedProducts[index];
    if (!product) return;

    const newQuantity = product.quantity + change;

    if (newQuantity < 1) {
        removeReceptionProduct(index);
        return;
    }

    if (newQuantity > product.available_quantity) {
        showToast(`Only ${product.available_quantity} ${product.unit || ''} available`, 'error');
        return;
    }

    product.quantity = newQuantity;
    renderSelectedProducts();
    calculateReceptionTotals();
}

function removeReceptionProduct(index) {
    selectedProducts.splice(index, 1);
    renderSelectedProducts();
    calculateReceptionTotals();
}

function filterReceptionProducts() {
    const query = (document.getElementById('productSearch')?.value || '').toLowerCase().trim();

    if (!query) {
        renderReceptionProducts(receptionProducts);
        return;
    }

    const filtered = receptionProducts.filter(product => {
        return (
            (product.name || '').toLowerCase().includes(query) ||
            (product.sku || '').toLowerCase().includes(query) ||
            (product.part_number || '').toLowerCase().includes(query) ||
            (product.barcode || '').toLowerCase().includes(query)
        );
    });

    renderReceptionProducts(filtered);
}

// ===================== Bill Summary / Discount =====================

function getReceptionServicesTotal() {
    let total = 0;

    selectedServices.forEach(serviceId => {
        const service = receptionServices.find(
            service => Number(service.id) === Number(serviceId)
        );

        if (!service) {
            return;
        }

        total += Number(service.base_price || 0);
    });

    return total;
}

function getReceptionProductsTotal() {
    let total = 0;

    selectedProducts.forEach(product => {
        total += Number(product.unit_price || 0) * Number(product.quantity || 0);
    });

    return total;
}

function calculateReceptionDiscountAmount(servicesTotal, productsTotal, subtotal) {
    let discountBase = subtotal;

    if (receptionDiscountApplyTo === 'services') {
        discountBase = servicesTotal;
    }

    if (receptionDiscountApplyTo === 'parts') {
        discountBase = productsTotal;
    }

    if (receptionDiscountType === 'amount') {
        return Math.min(receptionDiscountValue, discountBase);
    }

    if (receptionDiscountType === 'percentage') {
        const percentage = Math.min(receptionDiscountValue, 100);
        return (discountBase * percentage) / 100;
    }

    return 0;
}

function formatReceptionMoney(value) {
    return 'Rs. ' + Number(value || 0).toLocaleString('en-LK', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function calculateReceptionTotals() {
    const servicesTotal = getReceptionServicesTotal();
    const productsTotal = getReceptionProductsTotal();
    const subtotal = servicesTotal + productsTotal;

    const discountAmount = calculateReceptionDiscountAmount(
        servicesTotal,
        productsTotal,
        subtotal
    );

    const grandTotal = Math.max(0, subtotal - discountAmount);

    document.getElementById('receptionServicesTotal').textContent = formatReceptionMoney(servicesTotal);
    document.getElementById('receptionProductsTotal').textContent = formatReceptionMoney(productsTotal);
    document.getElementById('receptionSubtotal').textContent = formatReceptionMoney(subtotal);
    document.getElementById('receptionDiscount').textContent = '- ' + formatReceptionMoney(discountAmount);
    document.getElementById('receptionGrandTotal').textContent = formatReceptionMoney(grandTotal);
}

function handleReceptionDiscountTypeChange() {
    const type = document.getElementById('receptionDiscountType').value;
    const options = document.getElementById('receptionDiscountOptions');
    const valueInput = document.getElementById('receptionDiscountValue');
    const suffix = document.getElementById('receptionDiscountSuffix');

    receptionDiscountType = type;

    if (type === 'none') {
        options.style.display = 'none';
        valueInput.value = 0;
        receptionDiscountValue = 0;
        calculateReceptionTotals();
        return;
    }

    options.style.display = 'block';

    if (type === 'percentage') {
        suffix.textContent = '%';
        valueInput.max = '100';
    } else {
        suffix.textContent = 'Rs.';
        valueInput.removeAttribute('max');
    }

    calculateReceptionDiscount();
}

function calculateReceptionDiscount() {
    receptionDiscountType = document.getElementById('receptionDiscountType').value;
    receptionDiscountValue = parseFloat(document.getElementById('receptionDiscountValue').value) || 0;
    receptionDiscountApplyTo = document.getElementById('receptionDiscountApplyTo').value;

    if (receptionDiscountType === 'percentage') {
        receptionDiscountValue = Math.min(Math.max(receptionDiscountValue, 0), 100);
    }

    calculateReceptionTotals();
}

let createdJobData = null;

async function createJob() {
    // Final rule: at least one service OR one product
    if (selectedServices.length === 0 && selectedProducts.length === 0) {
        showToast('Please select at least one service or product', 'error');
        return;
    }

    const createBtn = document.getElementById('createJobBtn');
    createBtn.disabled = true;
    createBtn.textContent = 'Creating Job...';

    try {
        const formData = new FormData();
        formData.append('customer_id', selectedCustomer.id);
        formData.append('vehicle_id', selectedVehicle.vehicle_id);

        selectedServices.forEach(id => formData.append('service_ids[]', id));

        selectedProducts.forEach((product, index) => {
            formData.append(`product_items[${index}][product_id]`, product.product_id);
            formData.append(`product_items[${index}][quantity]`, product.quantity);
        });

        formData.append('notes', document.getElementById('jobNotes').value);
        formData.append('whatsapp_notes', document.getElementById('whatsappNotes').value);

        if (jobImageFile) {
            formData.append('vehicle_image', jobImageFile);
        }

        const response = await fetch('{{ route('reception.create-job') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            createdJobData = data;
            showToast(`Job ${data.job_number} created successfully!`, 'success');
            
            // Setup WhatsApp modal
            setupWhatsappModal();
            
            // Show WhatsApp modal
            document.getElementById('whatsappModal').classList.add('active');
            
            // Close job modal
            closeJobModal();
        } else {
            showToast('Error: ' + (data.message || 'Unknown error creating job'), 'error');
            createBtn.disabled = false;
            createBtn.textContent = 'Create Job Card';
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
        createBtn.disabled = false;
        createBtn.textContent = 'Create Job Card';
    }
}

function setupWhatsappModal() {
    const whatsappLabel = document.getElementById('whatsappLabel');
    const whatsappCheckbox = document.getElementById('sendWhatsapp');
    
    // Use WhatsApp number from customer creation if available, otherwise phone
    const phoneNumber = selectedCustomer.whatsapp_number || selectedCustomer.phone;
    
    if (phoneNumber) {
        whatsappLabel.textContent = `Send WhatsApp to customer (${phoneNumber})`;
        whatsappCheckbox.checked = true;
        whatsappCheckbox.disabled = false;
    } else {
        whatsappLabel.textContent = 'Send WhatsApp to customer';
        whatsappCheckbox.checked = false;
        whatsappCheckbox.disabled = true;
    }
    
    // Setup button event listeners
    const sendWhatsappBtn = document.getElementById('sendWhatsappBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    
    // Remove existing listeners to prevent duplicates
    const newSendBtn = sendWhatsappBtn.cloneNode(true);
    const newCloseBtn = closeModalBtn.cloneNode(true);
    sendWhatsappBtn.parentNode.replaceChild(newSendBtn, sendWhatsappBtn);
    closeModalBtn.parentNode.replaceChild(newCloseBtn, closeModalBtn);
    
    // Add fresh listeners
    newSendBtn.addEventListener('click', function() {
        const whatsappCheckbox = document.getElementById('sendWhatsapp');

        if (whatsappCheckbox.checked && createdJobData) {
            // Use server-generated URL if available, otherwise generate client-side
            let whatsappUrl = createdJobData.whatsapp_url;

            if (!whatsappUrl) {
                const customerPhone = selectedCustomer.whatsapp_number || selectedCustomer.phone;
                if (customerPhone) {
                    let message = `🚗 *New Job Created*\n\nDear ${selectedCustomer.full_name},\n\nJob Number: ${createdJobData.job_number}\nVehicle: ${selectedVehicle.registration_number}\nCustomer: ${selectedCustomer.full_name}\n\nYour vehicle has been checked in. Our team has documented the current condition of your vehicle upon arrival. Photos documenting our observations will be sent to you via WhatsApp following this message for your reference. We will update you on the progress and notify you once the inspection is complete.`;

                    // Add custom notes if provided
                    const customNotes = document.getElementById('whatsappNotes')?.value?.trim();
                    if (customNotes) {
                        message += `\n\n📝 *Additional Notes:*\n${customNotes}`;
                    }

                    // Format phone number for wa.me (remove + and handle Sri Lanka format)
                    let formattedPhone = customerPhone.replace(/[^0-9]/g, '');
                    if (formattedPhone.startsWith('0')) {
                        formattedPhone = '94' + formattedPhone.substring(1);
                    }

                    whatsappUrl = `https://wa.me/${formattedPhone}?text=${encodeURIComponent(message)}`;
                }
            } else {
                // If server generated URL, add custom notes to it
                const customNotes = document.getElementById('whatsappNotes')?.value?.trim();
                if (customNotes) {
                    try {
                        const url = new URL(whatsappUrl);
                        const existingMessage = url.searchParams.get('text') || '';
                        const newMessage = existingMessage + `\n\n📝 *Additional Notes:*\n${customNotes}`;
                        url.searchParams.set('text', newMessage);
                        whatsappUrl = url.toString();
                    } catch (e) {
                        console.error('Error adding notes to URL:', e);
                    }
                }
            }

            if (whatsappUrl) {
                window.open(whatsappUrl, '_blank');
            }
        }

        // Open job page
        window.location.href = createdJobData.redirect;
    });
    
    newCloseBtn.addEventListener('click', function() {
        // Just open job page without WhatsApp (Cancel button)
        if (createdJobData) {
            window.location.href = createdJobData.redirect;
        }
    });
}

function closeWhatsappModal() {
    document.getElementById('whatsappModal').classList.remove('active');
}

function toggleWhatsappCheckbox() {
    const whatsappInput = document.getElementById('customerWhatsappNumber');
    const whatsappCheckbox = document.getElementById('sendWhatsapp');

    if (whatsappInput && whatsappCheckbox) {
        if (whatsappInput.value.trim().length > 5) {
            whatsappCheckbox.disabled = false;
            whatsappCheckbox.checked = true;
        } else {
            whatsappCheckbox.disabled = true;
            whatsappCheckbox.checked = false;
        }
    }
}

// WhatsApp modal button handlers


document.getElementById('closeModalBtn').addEventListener('click', function() {
    // Just open job page without WhatsApp
    if (createdJobData) {
        window.location.href = createdJobData.redirect;
    }
});

function toggleNav() {
    document.getElementById('navMenu').classList.toggle('active');
}

document.addEventListener('click', (e) => {
    const navMenu = document.getElementById('navMenu');
    const navToggle = document.querySelector('.nav-toggle');
    if (!navMenu.contains(e.target) && !navToggle.contains(e.target)) {
        navMenu.classList.remove('active');
    }
});
</script>

<style>
/* =========================================================
   TOKENS
   ========================================================= */
:root {
    --ink: #060a14;
    --panel: rgba(255, 255, 255, 0.06);
    --panel-border: rgba(147, 197, 253, 0.22);
    --accent: #38bdf8;
    --accent-deep: #2563eb;
    --text: #f1f5f9;
    --text-dim: rgba(226, 232, 240, 0.75);
}

.reception-fullscreen {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    width: 100%;
    overflow-x: hidden;
}

.reception-container {
    margin: 0;
    padding: 40px 16px;
    min-height: 100vh;
    width: 100%;
    max-width: none;
    box-sizing: border-box;
    background: var(--reception-background);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    position: relative;
    overflow-x: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.reception-container::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
        radial-gradient(circle at 15% 20%, rgba(125, 211, 252, 0.10), transparent 35%),
        radial-gradient(circle at 85% 75%, rgba(56, 189, 248, 0.08), transparent 35%),
        linear-gradient(135deg, rgba(3, 7, 18, 0.38) 0%, rgba(8, 15, 30, 0.42) 50%, rgba(4, 10, 22, 0.48) 100%);
    z-index: 0;
    pointer-events: none;
}

.reception-container > * {
    position: relative;
    z-index: 1;
}

.reception-header {
    text-align: center;
    margin-bottom: 36px;
    padding: 28px 12px 0;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
}

.reception-header h1 {
    color: var(--text);
    margin-bottom: 10px;
    font-size: clamp(26px, 6vw, 44px);
    font-weight: 700;
    letter-spacing: -0.5px;
    line-height: 1.2;
}

.reception-header p {
    color: var(--text-dim);
    font-size: clamp(14px, 3.5vw, 18px);
    margin: 0;
    font-weight: 400;
}

.reception-nav {
    position: fixed;
    top: 16px;
    right: 16px;
    z-index: 1000;
}

.nav-toggle {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid var(--panel-border);
    color: white;
    padding: 12px 16px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 22px;
    backdrop-filter: blur(10px);
    transition: background 0.2s ease;
    min-width: 48px;
    min-height: 48px;
}

.nav-toggle:hover {
    background: rgba(255, 255, 255, 0.16);
}

.nav-menu {
    position: absolute;
    top: 56px;
    right: 0;
    background: rgba(10, 16, 32, 0.96);
    border: 1px solid var(--panel-border);
    border-radius: 12px;
    padding: 10px;
    min-width: 200px;
    max-height: 70vh;
    overflow-y: auto;
    display: none;
    backdrop-filter: blur(16px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
}

.nav-menu::-webkit-scrollbar {
    width: 4px;
}

.nav-menu::-webkit-scrollbar-track {
    background: transparent;
}

.nav-menu::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
}

.nav-menu::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

.nav-menu.active {
    display: block;
}

.nav-menu a {
    display: block;
    color: var(--text);
    text-decoration: none;
    padding: 12px 14px;
    border-radius: 7px;
    margin-bottom: 2px;
    font-size: 14px;
    transition: background 0.15s ease;
}

.nav-menu a:hover {
    background: rgba(56, 189, 248, 0.14);
}

.nav-menu a.logout-link {
    color: #f87171;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 12px;
    margin-top: 8px;
}

.nav-menu a.logout-link:hover {
    background: rgba(239, 68, 68, 0.14);
}

.search-section {
    margin-bottom: 30px;
    position: relative;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
}

.search-box {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.search-box input {
    width: 100%;
    padding: 18px 20px;
    border: 1px solid var(--panel-border);
    border-radius: 16px;
    font-size: 16px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.06));
    color: var(--text);
    backdrop-filter: blur(24px) saturate(140%);
    -webkit-backdrop-filter: blur(24px) saturate(140%);
    transition: border-color 0.25s ease, box-shadow 0.25s ease;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.12);
}

@media (max-width: 1024px) {
    .nav-toggle {
        padding: 8px 12px;
        font-size: 16px;
        min-width: 36px;
        min-height: 36px;
    }

    .nav-menu {
        min-width: 180px;
        padding: 10px;
    }

    .nav-menu a {
        padding: 12px 14px;
        font-size: 14px;
    }

    .search-box input {
        padding: 12px 14px;
        font-size: 13px;
        border-radius: 12px;
    }
}

@media (max-width: 768px) {
    .reception-header {
        padding-top: 20px;
        margin-bottom: 20px;
    }

    .reception-header h1 {
        font-size: 24px;
    }

    .reception-header p {
        font-size: 14px;
    }

    .nav-toggle {
        padding: 5px 8px;
        font-size: 12px;
        min-width: 28px;
        min-height: 28px;
    }

    .nav-menu {
        min-width: 160px;
        padding: 8px;
        max-height: 60vh;
    }

    .nav-menu a {
        padding: 10px 12px;
        font-size: 12px;
        margin-bottom: 2px;
    }

    .search-box input {
        padding: 6px 8px;
        font-size: 10px;
        border-radius: 6px;
    }
}

@media (max-width: 480px) {
    .reception-header {
        padding-top: 16px;
        margin-bottom: 16px;
    }

    .reception-header h1 {
        font-size: 20px;
    }

    .reception-header p {
        font-size: 12px;
    }

    .nav-toggle {
        padding: 4px 6px;
        font-size: 10px;
        min-width: 24px;
        min-height: 24px;
    }

    .nav-menu {
        min-width: 140px;
        padding: 6px;
        max-height: 50vh;
    }

    .nav-menu a {
        padding: 8px 10px;
        font-size: 11px;
    }

    .search-box input {
        padding: 5px 7px;
        font-size: 9px;
        border-radius: 5px;
    }
}

.search-box input::placeholder {
    color: rgba(255, 255, 255, 0.55);
}

.search-box input:focus {
    outline: none;
    border-color: rgba(125, 211, 252, 0.75);
    box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.12), 0 12px 45px rgba(14, 165, 233, 0.22);
}

.search-results {
    min-height: 100px;
}

.result-card {
    position: relative;
    padding: 20px;
    border-radius: 18px;
    border: 1px solid var(--panel-border);
    border-left: 3px solid var(--accent);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.11), rgba(96, 165, 250, 0.06));
    backdrop-filter: blur(22px) saturate(140%);
    -webkit-backdrop-filter: blur(22px) saturate(140%);
    box-shadow: 0 18px 50px rgba(0, 0, 0, 0.30), inset 0 1px 0 rgba(255, 255, 255, 0.10);
    color: var(--text);
    margin-bottom: 15px;
    animation: resultCardIn 0.3s ease both;
}

@keyframes resultCardIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

.result-card h4 {
    margin: 0 0 12px;
    color: #e0f2fe;
    font-size: 16px;
    font-weight: 600;
}

.result-card p {
    margin: 8px 0;
    color: var(--text-dim);
    font-size: 14px;
}

.result-card strong {
    color: #bae6fd;
}

#toastContainer {
    position: fixed !important;
    top: 80px;
    right: 16px;
    left: 16px;
    z-index: 99999 !important;
    pointer-events: none;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.toast {
    pointer-events: auto;
    background: white;
    padding: 14px 18px;
    margin-bottom: 10px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    border-left: 5px solid;
    max-width: 100%;
    width: 100%;
    max-width: 360px;
    transition: opacity 0.3s ease;
    animation: slideIn 0.25s ease;
    font-size: 14px;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.toast-success {
    border-left-color: #28a745;
    background: #d4edda;
    color: #155724;
}

.toast-error {
    border-left-color: #dc3545;
    background: #f8d7da;
    color: #721c24;
}

/* Image upload */
.image-upload-area {
    border: 1px dashed rgba(186, 230, 253, 0.35);
    border-radius: 14px;
    padding: 16px;
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.08), rgba(59, 130, 246, 0.04));
    text-align: center;
}

.image-upload-placeholder .upload-icon {
    font-size: 36px;
    margin-bottom: 8px;
}

.image-upload-placeholder p {
    color: var(--text);
    margin: 0 0 12px;
    font-size: 14px;
}

.upload-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-upload {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, var(--accent) 0%, var(--accent-deep) 100%);
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-upload:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
}

.btn-upload-secondary {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(186, 230, 253, 0.35);
    color: var(--text);
}

.btn-upload-secondary:hover {
    background: rgba(56, 189, 248, 0.2);
    box-shadow: none;
}

.upload-hint {
    display: block;
    margin-top: 10px;
    color: var(--text-dim);
    font-size: 12px;
}

.image-preview {
    position: relative;
    display: inline-block;
    max-width: 100%;
}

.image-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 12px;
    display: block;
    object-fit: cover;
}

.btn-remove-image {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    font-size: 18px;
    cursor: pointer;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.job-image-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.btn-change-image {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.12);
    color: var(--text);
    border: 1px solid rgba(186, 230, 253, 0.3);
    padding: 8px 14px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: background 0.2s ease;
}

.btn-change-image:hover {
    background: rgba(56, 189, 248, 0.2);
}

.btn-remove-job-image {
    background: rgba(239, 68, 68, 0.2);
    color: #fca5a5;
    border: 1px solid rgba(248, 113, 113, 0.4);
    padding: 8px 14px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
}

.btn-remove-job-image:hover {
    background: rgba(239, 68, 68, 0.35);
}

.vehicle-image-section {
    margin-bottom: 20px;
    text-align: center;
}

.vehicle-image-container {
    width: 100%;
    max-width: 360px;
    margin: 0 auto;
    border-radius: 12px;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.12), rgba(59, 130, 246, 0.07));
    border: 1px solid var(--panel-border);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

.vehicle-image-container img {
    width: 100%;
    height: auto;
    display: block;
    object-fit: cover;
}

.vehicle-placeholder {
    padding: 36px 16px;
    color: var(--accent);
    font-size: 15px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 160px;
}

.info-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 20px;
}

.customer-info, .vehicle-info, .services-section, .notes-section, .products-section {
    margin-bottom: 20px;
}

.info-row .customer-info, .info-row .vehicle-info {
    margin-bottom: 0;
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.10), rgba(59, 130, 246, 0.05));
    border: 1px solid rgba(186, 230, 253, 0.16);
    border-radius: 14px;
    padding: 16px;
}

.customer-info h3, .vehicle-info h3, .services-section h3, .notes-section h3, .products-section h3 {
    color: #e0f2fe;
    margin-bottom: 12px;
    border-bottom: 1px solid rgba(186, 230, 253, 0.16);
    padding-bottom: 8px;
    font-size: 16px;
    font-weight: 700;
}

.customer-info p, .vehicle-info p {
    margin: 8px 0;
    color: var(--text-dim);
    font-size: 14px;
    line-height: 1.5;
}

.customer-info strong, .vehicle-info strong {
    color: #bae6fd;
    font-weight: 700;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
}

.service-item {
    display: flex;
    align-items: center;
    padding: 14px;
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.10), rgba(59, 130, 246, 0.05));
    border: 1px solid rgba(186, 230, 253, 0.14);
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.2s ease, border-color 0.2s ease;
}

.service-item:hover {
    background: rgba(56, 189, 248, 0.14);
    border-color: rgba(125, 211, 252, 0.4);
}

.service-item input {
    margin-right: 12px;
    width: 20px;
    height: 20px;
    accent-color: var(--accent);
    flex-shrink: 0;
}

.service-info {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
}

.service-name {
    font-weight: 500;
    color: var(--text);
    font-size: 14px;
}

.service-price {
    color: var(--accent);
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
}

/* Products section */
.product-search-wrapper {
    margin-bottom: 12px;
}

.product-search-wrapper input {
    width: 100%;
    box-sizing: border-box;
    padding: 12px 14px;
    border: 1px solid rgba(186, 230, 253, 0.25);
    border-radius: 12px;
    font-size: 14px;
    color: var(--text);
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.12), rgba(59, 130, 246, 0.07));
    backdrop-filter: blur(14px);
}

.product-search-wrapper input::placeholder {
    color: rgba(186, 230, 253, 0.55);
}

.product-search-wrapper input:focus {
    outline: none;
    border-color: rgba(125, 211, 252, 0.75);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.12);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    max-height: 260px;
    overflow-y: auto;
    padding-right: 4px;
}

.product-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px;
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.10), rgba(59, 130, 246, 0.05));
    border: 1px solid rgba(186, 230, 253, 0.14);
    border-radius: 12px;
}

.product-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 2px;
}

.product-name {
    font-weight: 600;
    color: var(--text);
    font-size: 13px;
}

.product-meta, .product-stock {
    font-size: 11px;
    color: var(--text-dim);
}

.product-stock {
    color: rgba(125, 211, 252, 0.85);
}

.product-action {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.product-price {
    font-weight: 600;
    color: var(--accent);
    font-size: 13px;
    white-space: nowrap;
}

.product-out-of-stock {
    opacity: 0.5;
    pointer-events: none;
}

.selected-products-list {
    margin-top: 14px;
}

.selected-products-title {
    font-weight: 700;
    color: #e0f2fe;
    margin-bottom: 8px;
    font-size: 14px;
}

.selected-product-row {
    display: grid;
    grid-template-columns: 1fr auto auto auto;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.10), rgba(59, 130, 246, 0.05));
    border: 1px solid rgba(186, 230, 253, 0.16);
    border-radius: 10px;
    margin-bottom: 8px;
}

.selected-product-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.selected-product-info strong {
    color: var(--text);
    font-size: 13px;
}

.selected-product-info span {
    font-size: 11px;
    color: var(--text-dim);
}

.selected-product-quantity {
    display: flex;
    align-items: center;
    gap: 8px;
}

.selected-product-quantity button {
    width: 30px;
    height: 30px;
    border: 1px solid rgba(186, 230, 253, 0.3);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.08);
    color: var(--text);
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.selected-product-quantity span {
    min-width: 22px;
    text-align: center;
    color: var(--text);
    font-weight: 600;
}

.selected-product-total {
    font-weight: 600;
    color: var(--accent);
    white-space: nowrap;
    font-size: 13px;
}

.remove-product-btn {
    border: none;
    background: transparent;
    color: #f87171;
    font-size: 20px;
    cursor: pointer;
    line-height: 1;
    padding: 0 4px;
}

.empty-state {
    color: var(--text-dim);
    font-size: 13px;
    padding: 14px;
    text-align: center;
}

.notes-section textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 12px 14px;
    border: 1px solid rgba(186, 230, 253, 0.25);
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.5;
    color: var(--text);
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.12), rgba(59, 130, 246, 0.07));
    resize: vertical;
    font-family: inherit;
}

.notes-section textarea::placeholder {
    color: rgba(186, 230, 253, 0.55);
}

.notes-section textarea:focus {
    outline: none;
    border-color: rgba(125, 211, 252, 0.75);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.12);
}

/* ===================== DISCOUNT + BILL SUMMARY ===================== */

.discount-section {
    margin-bottom: 20px;
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.10), rgba(59, 130, 246, 0.05));
    border: 1px solid rgba(186, 230, 253, 0.16);
    border-radius: 14px;
    padding: 16px;
}

.discount-section .form-group:last-child {
    margin-bottom: 0;
}

.discount-input-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.discount-input-wrapper input {
    flex: 1;
}

.discount-input-wrapper span {
    flex-shrink: 0;
    min-width: 36px;
    text-align: center;
    padding: 12px 10px;
    border: 1px solid rgba(186, 230, 253, 0.25);
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.18), rgba(59, 130, 246, 0.10));
    color: #bae6fd;
    font-weight: 600;
    font-size: 13px;
}

.reception-summary {
    margin-bottom: 20px;
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.10), rgba(59, 130, 246, 0.05));
    border: 1px solid rgba(186, 230, 253, 0.16);
    border-radius: 14px;
    padding: 16px 18px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    font-size: 14px;
    color: var(--text-dim);
}

.summary-row strong {
    color: #e0f2fe;
    font-weight: 600;
}

.summary-divider {
    height: 1px;
    margin: 8px 0;
    background: rgba(186, 230, 253, 0.16);
}

.summary-row.total-row {
    padding-top: 4px;
    font-size: 16px;
}

.summary-row.total-row span {
    color: #e0f2fe;
    font-weight: 700;
}

.summary-row.total-row strong {
    color: #7dd3fc;
    font-size: 18px;
    font-weight: 800;
}

#createJobBtn {
    flex: 1;
}

.btn-primary {
    background: linear-gradient(135deg, var(--accent) 0%, var(--accent-deep) 100%);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.35);
}

.whatsapp-section {
    margin: 16px 0;
    padding: 12px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
}

.whatsapp-toggle {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
}

.whatsapp-toggle input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #22c55e;
}

.whatsapp-toggle .toggle-label {
    font-size: 14px;
    color: #1f2937;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

/* WhatsApp Modal specific styling to match job status modal */
.whatsapp-modal .modal-content {
    background: #9ca3af;
    border-radius: 16px;
    padding: 24px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.whatsapp-modal .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.whatsapp-modal .modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: #1f2937;
}

.whatsapp-modal .modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #9ca3af;
}

.whatsapp-modal .modal-close:hover {
    color: #1f2937;
}

.whatsapp-modal .modal-body p {
    color: #1f2937;
    font-size: 14px;
    margin-bottom: 16px;
    line-height: 1.5;
}

.whatsapp-modal .custom-notes-section {
    margin: 16px 0;
}

.whatsapp-modal .custom-notes-section label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #1f2937;
}

.whatsapp-modal .custom-notes-section textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    resize: vertical;
    background: white;
    color: #1f2937;
}

.whatsapp-modal .custom-notes-section small {
    display: block;
    margin-top: 6px;
    font-size: 12px;
    color: #4b5563;
}

.whatsapp-modal .modal-actions .btn-primary {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
}

.whatsapp-modal .modal-actions .btn-primary:hover {
    background: #2563eb;
}

.whatsapp-modal .modal-actions .btn-secondary {
    background: white;
    color: #1f2937;
    border: 1px solid #d1d5db;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
}

.whatsapp-modal .modal-actions .btn-secondary:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(37, 99, 235, 0.5);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.10);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 12px 20px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: background 0.2s ease, border-color 0.2s ease;
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.18);
    border-color: rgba(255, 255, 255, 0.5);
}

.modal {
    display: none;
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at center, rgba(14, 165, 233, 0.08), transparent 45%), rgba(2, 6, 23, 0.72);
    backdrop-filter: blur(14px) saturate(120%);
    -webkit-backdrop-filter: blur(14px) saturate(120%);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    padding: 12px;
}

.modal.active {
    display: flex;
}

.modal-content {
    position: relative;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.07));
    border: 1px solid var(--panel-border);
    border-radius: 18px;
    width: 100%;
    max-width: 600px;
    max-height: 92vh;
    overflow-y: auto;
    backdrop-filter: blur(30px) saturate(145%);
    -webkit-backdrop-filter: blur(30px) saturate(145%);
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.55);
    animation: modalGlassIn 0.3s cubic-bezier(.2,.8,.2,1);
}

.job-modal-content {
    max-width: 960px;
}

@keyframes modalGlassIn {
    from { opacity: 0; transform: translateY(20px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 18px;
    border-bottom: 1px solid rgba(186, 230, 253, 0.16);
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.10), rgba(59, 130, 246, 0.05));
    position: sticky;
    top: 0;
    z-index: 5;
}

.modal-header h3 {
    margin: 0;
    color: #e0f2fe;
    font-size: 18px;
    font-weight: 700;
}

.modal-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(186, 230, 253, 0.18);
    border-radius: 9px;
    font-size: 22px;
    cursor: pointer;
    color: rgba(224, 242, 254, 0.75);
    line-height: 1;
}

.modal-close:hover {
    color: #ffffff;
    background: rgba(239, 68, 68, 0.18);
}

.modal-body {
    padding: 16px;
}

.modal-footer {
    display: flex;
    gap: 10px;
    padding: 16px 18px;
    border-top: 1px solid rgba(186, 230, 253, 0.14);
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.07), rgba(59, 130, 246, 0.04));
    position: sticky;
    bottom: 0;
}

.form-group {
    margin-bottom: 14px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #bae6fd;
    font-size: 13px;
}

.form-group input,
.form-group select {
    width: 100%;
    box-sizing: border-box;
    padding: 12px 14px;
    border: 1px solid rgba(186, 230, 253, 0.25);
    border-radius: 12px;
    font-size: 14px;
    color: var(--text);
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.12), rgba(59, 130, 246, 0.07));
}

.form-group input::placeholder {
    color: rgba(186, 230, 253, 0.55);
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: rgba(125, 211, 252, 0.75);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.12);
}

.form-group select option {
    background-color: #0b1224;
    color: var(--text);
}

.customer-select-wrapper {
    display: flex;
    gap: 10px;
    align-items: center;
}

.customer-select-wrapper .searchable-dropdown {
    flex: 1;
}

/* Ensure customer dropdown input matches category dropdown height */
#modalVehicleCustomerInput {
    height: 46px !important;
}

.btn-add-customer {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.75), rgba(37, 99, 235, 0.75));
    color: #ffffff;
    border: 1px solid rgba(186, 230, 253, 0.35);
    padding: 11px 14px;
    border-radius: 11px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    height: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    flex-shrink: 0;
}

.btn-add-customer:hover {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.85), rgba(37, 99, 235, 0.85));
    transform: translateY(-1px);
}

/* ===================== RESPONSIVE ===================== */

@media (max-width: 1024px) {
    .job-modal-content {
        max-width: 90%;
    }
}

@media (max-width: 768px) {
    .reception-container {
        padding: 24px 12px;
    }

    .info-row {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .products-grid {
        grid-template-columns: 1fr;
        max-height: 220px;
    }

    .selected-product-row {
        grid-template-columns: 1fr auto;
        gap: 8px;
    }

    .selected-product-total {
        grid-column: 1;
    }

    .services-grid {
        grid-template-columns: 1fr;
    }

    .modal-content {
        max-width: 100%;
        border-radius: 16px;
    }

    .job-modal-content {
        max-width: 100%;
    }

    .modal-footer {
        flex-direction: column;
    }

    .modal-footer button {
        width: 100%;
    }

    .customer-select-wrapper {
        flex-direction: column;
        align-items: stretch;
    }

    .btn-add-customer {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .reception-header {
        padding-top: 20px;
        margin-bottom: 24px;
    }

    .search-box input {
        padding: 15px 16px;
        font-size: 15px;
        border-radius: 14px;
    }

    .result-card {
        padding: 16px;
    }

    .vehicle-image-container {
        max-width: 100%;
    }

    .vehicle-placeholder {
        min-height: 120px;
        padding: 24px 12px;
        font-size: 13px;
    }

    .modal-header h3 {
        font-size: 16px;
    }

    .modal-body {
        padding: 14px;
    }

    .product-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .product-action {
        width: 100%;
        justify-content: space-between;
    }

    .btn-primary,
    .btn-secondary {
        padding: 12px 16px;
        font-size: 14px;
    }

    .nav-toggle {
        padding: 10px 12px;
        font-size: 20px;
    }
}

@media (max-width: 360px) {
    .reception-container {
        padding: 16px 10px;
    }

    .selected-product-quantity button {
        width: 28px;
        height: 28px;
    }
}

@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* ===================== VEHICLE FORM GRID LAYOUT ===================== */

.vehicle-form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

@media (min-width: 768px) {
    .vehicle-form-grid {
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    /* Registration and Make on same line (short fields) */
    .vehicle-form-grid .form-group:nth-child(1),
    .vehicle-form-grid .form-group:nth-child(2) {
        grid-column: span 1;
    }

    /* Model, Category, and Customer span full width */
    .vehicle-form-grid .form-group:nth-child(3),
    .vehicle-form-grid .form-group:nth-child(4),
    .vehicle-form-grid .form-group:nth-child(5) {
        grid-column: span 2;
    }
}

/* Mobile responsive adjustments */
@media (max-width: 767px) {
    .vehicle-form-grid {
        gap: 12px;
    }
}

/* ===================== SEARCHABLE DROPDOWN CUSTOM STYLES ===================== */

/* Override searchable dropdown styles for dark theme - applies to both category and customer dropdowns */
.searchable-dropdown-input {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(186, 230, 253, 0.25) !important;
    color: #bae6fd !important;
    height: 46px !important;
    padding: 12px 14px !important;
    box-sizing: border-box !important;
}

.searchable-dropdown-input::placeholder {
    color: rgba(186, 230, 253, 0.55) !important;
}

.searchable-dropdown-input:focus {
    border-color: rgba(125, 211, 252, 0.75) !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.12) !important;
}

.searchable-dropdown.open .searchable-dropdown-input {
    border-color: rgba(125, 211, 252, 0.75) !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.12) !important;
}

.searchable-dropdown-options {
    background: rgba(30, 41, 59, 0.95) !important;
    border: 1px solid rgba(186, 230, 253, 0.25) !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2) !important;
    backdrop-filter: blur(10px) !important;
    max-height: 200px !important;
    scroll-behavior: smooth !important;
}

.searchable-dropdown-option {
    color: #bae6fd !important;
    border-bottom: 1px solid rgba(186, 230, 253, 0.1) !important;
    min-height: 40px !important;
}

.searchable-dropdown-option:hover {
    background: rgba(56, 189, 248, 0.15) !important;
    color: #e0f2fe !important;
}

.searchable-dropdown-option.selected {
    background: rgba(56, 189, 248, 0.25) !important;
    color: #e0f2fe !important;
}

.searchable-dropdown-option:hover::after {
    color: #38bdf8 !important;
}

.searchable-dropdown-option.selected::after {
    color: #38bdf8 !important;
}

.searchable-dropdown-no-results {
    color: rgba(186, 230, 253, 0.55) !important;
}

/* Enhanced scrollbar for dark theme */
.searchable-dropdown-options::-webkit-scrollbar {
    width: 12px !important;
}

.searchable-dropdown-options::-webkit-scrollbar-track {
    background: rgba(30, 41, 59, 0.5) !important;
    border-radius: 6px !important;
}

.searchable-dropdown-options::-webkit-scrollbar-thumb {
    background: rgba(56, 189, 248, 0.6) !important;
    border-radius: 6px !important;
    border: 2px solid rgba(30, 41, 59, 0.5) !important;
}

.searchable-dropdown-options::-webkit-scrollbar-thumb:hover {
    background: rgba(56, 189, 248, 0.8) !important;
}

/* Firefox scrollbar for dark theme */
.searchable-dropdown-options {
    scrollbar-width: auto !important;
    scrollbar-color: rgba(56, 189, 248, 0.6) rgba(30, 41, 59, 0.5) !important;
}
</style>
@endsection