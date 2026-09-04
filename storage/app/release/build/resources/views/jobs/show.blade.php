@extends('layouts.app')

@section('content')
<div class="job-detail-container">
    <!-- Header Section -->
    <div class="job-header">
        <div class="job-info">
            <h1 class="job-number">{{ $job->job_number }}</h1>
            <div class="job-meta">
                <span class="vehicle-reg">{{ $job->vehicle->registration_number }}</span>
                <span class="separator">·</span>
                <span class="customer-name">{{ $job->customer->full_name }}</span>
            </div>
        </div>
        <div class="status-badge status-{{ $job->status->getColor() }}">
            {{ $job->status->getLabel() }}
        </div>
    </div>

    <!-- Status Workflow -->
    <div class="status-workflow">
        <h3>Job Progress</h3>
        <div class="workflow-steps">
            @php
                $workflowSteps = [
                    'checked_in' => 'Checked In',
                    'inspection_completed' => 'Inspection',
                    'approved' => 'Approved',
                    'waiting_for_parts' => 'Parts',
                    'in_service' => 'In Service',
                    'quality_check' => 'Quality Check',
                    'ready_for_payment' => 'Ready',
                    'paid' => 'Paid',
                    'delivered' => 'Delivered'
                ];
                $currentIndex = array_search($job->status->value, array_keys($workflowSteps));
            @endphp
            @foreach($workflowSteps as $status => $label)
                @php
                    $stepIndex = array_search($status, array_keys($workflowSteps));
                    $isCompleted = $stepIndex < $currentIndex;
                    $isCurrent = $stepIndex === $currentIndex;
                    $isFuture = $stepIndex > $currentIndex;
                    $canTransition = $job->status->canTransitionTo(\App\Enums\JobStatus::from($status));
                @endphp
                <div class="workflow-step {{ $isCompleted ? 'completed' : '' }} {{ $isCurrent ? 'current' : '' }} {{ $isFuture ? 'future' : '' }}">
                    @if($canTransition && !$isCurrent)
                        <button onclick="changeStatus('{{ $status }}')" class="step-action" title="Change to {{ $label }}">
                    @endif
                    <div class="step-indicator">
                        @if($isCompleted)
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        @else
                            <div class="step-number">{{ $stepIndex + 1 }}</div>
                        @endif
                    </div>
                    <span class="step-label">{{ $label }}</span>
                    @if($canTransition && !$isCurrent)
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-buttons">
            @if($job->status->value === \App\Enums\JobStatus::CHECKED_IN->value)
                <button onclick="changeStatus('inspection_pending')" class="action-btn action-yellow">Start Inspection</button>
                <button onclick="changeStatus('in_service')" class="action-btn action-blue">Start Service</button>
                <button onclick="changeStatus('cancelled')" class="action-btn action-red">Cancel Job</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
            @elseif($job->status->value === \App\Enums\JobStatus::INSPECTION_PENDING->value)
                <button onclick="changeStatus('inspection_completed')" class="action-btn action-purple">Complete Inspection</button>
                <button onclick="changeStatus('cancelled')" class="action-btn action-red">Cancel Job</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
            @elseif($job->status->value === \App\Enums\JobStatus::INSPECTION_COMPLETED->value)
                <button onclick="changeStatus('customer_approval_pending')" class="action-btn action-yellow">Request Approval</button>
                <button onclick="changeStatus('approved')" class="action-btn action-blue">Approve</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
            @elseif($job->status->value === \App\Enums\JobStatus::CUSTOMER_APPROVAL_PENDING->value)
                <button onclick="changeStatus('approved')" class="action-btn action-blue">Approve</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
                <button onclick="changeStatus('cancelled')" class="action-btn action-red">Cancel Job</button>
            @elseif($job->status->value === \App\Enums\JobStatus::APPROVED->value)
                <button onclick="changeStatus('waiting_for_parts')" class="action-btn action-orange">Wait for Parts</button>
                <button onclick="changeStatus('in_service')" class="action-btn action-blue">Start Service</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
            @elseif($job->status->value === \App\Enums\JobStatus::WAITING_FOR_PARTS->value)
                <button onclick="changeStatus('in_service')" class="action-btn action-blue">Start Service</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
                <button onclick="changeStatus('cancelled')" class="action-btn action-red">Cancel Job</button>
            @elseif($job->status->value === \App\Enums\JobStatus::IN_SERVICE->value)
                <button onclick="changeStatus('quality_check')" class="action-btn action-purple">Quality Check</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
            @elseif($job->status->value === \App\Enums\JobStatus::QUALITY_CHECK->value)
                <button onclick="changeStatus('ready_for_payment')" class="action-btn action-cyan">Ready for Payment</button>
                <button onclick="changeStatus('in_service')" class="action-btn action-blue">Back to Service</button>
            @elseif($job->status->value === \App\Enums\JobStatus::READY_FOR_PAYMENT->value)
                <div class="info-note">Job is ready for payment. Cashier will handle payment and delivery.</div>
            @elseif($job->status->value === \App\Enums\JobStatus::ON_HOLD->value)
                <button onclick="changeStatus('checked_in')" class="action-btn action-blue">Resume (Checked In)</button>
                <button onclick="changeStatus('inspection_completed')" class="action-btn action-purple">Resume (Inspection)</button>
                <button onclick="changeStatus('approved')" class="action-btn action-blue">Resume (Approved)</button>
                <button onclick="changeStatus('in_service')" class="action-btn action-blue">Resume (In Service)</button>
                <button onclick="changeStatus('waiting_for_parts')" class="action-btn action-orange">Resume (Wait Parts)</button>
                <button onclick="changeStatus('cancelled')" class="action-btn action-red">Cancel Job</button>
            @endif
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Vehicle & Customer Info -->
        <div class="info-panel">
            <h3>Vehicle & Customer</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Customer</label>
                    <a href="{{ route('customers.show',$job->customer) }}" class="info-value">{{ $job->customer->full_name }}</a>
                </div>
                <div class="info-item">
                    <label>Vehicle</label>
                    <a href="{{ route('vehicles.show',$job->vehicle) }}" class="info-value">{{ $job->vehicle->registration_number }}</a>
                </div>
                <div class="info-item">
                    <label>Make/Model</label>
                    <span class="info-value">{{ $job->vehicle->make }} {{ $job->vehicle->model }}</span>
                </div>
                <div class="info-item">
                    <label>Category</label>
                    <span class="info-value">{{ $job->vehicle->category ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <label>Priority</label>
                    <span class="info-value priority-{{ $job->priority }}">{{ ucfirst($job->priority) }}</span>
                </div>
                <div class="info-item">
                    <label>Checked In</label>
                    <span class="info-value">{{ $job->checked_in_at ? $job->checked_in_at->format('M d, Y H:i') : '—' }}</span>
                </div>
            </div>
        </div>

        <!-- Services -->
        <div class="info-panel">
            <h3>Services</h3>
            <div class="services-list">
                @forelse($job->services as $service)
                    <div class="service-item {{ $service->approval_status === 'approved' ? 'service-applied' : 'service-pending' }}">
                        <div class="service-info">
                            <span class="service-name">{{ $service->name_snapshot }}</span>
                            <span class="service-status status-{{ $service->approval_status === 'approved' ? 'green' : ($service->approval_status === 'pending' ? 'yellow' : 'red') }}">
                                {{ $service->approval_status === 'approved' ? 'Applied' : ucfirst($service->approval_status) }}
                            </span>
                        </div>
                        <div class="service-actions">
                            <span class="service-price">Rs. {{ number_format($service->unit_price,2) }}</span>
                            @if($service->approval_status === 'pending')
                                <form class="inline-form" method="post" action="{{ route('jobs.services.apply', [$job, $service]) }}">
                                    @csrf
                                    <button type="submit" class="confirm-btn">Confirm Apply</button>
                                </form>
                            @endif
                            <button type="button" class="close-btn" onclick="showCloseModal('service', {{ $service->id }}, '{{ $service->name_snapshot }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">No services added</div>
                @endforelse
            </div>
            <div class="add-service-form">
                <h4>Add Service</h4>
                @php
                    $addedServiceIds = $job->services->pluck('service_id')->toArray();
                    $availableServices = $services->whereNotIn('id', $addedServiceIds);
                @endphp
                <form class="inline-form" method="post" action="{{ route('jobs.services.add', $job) }}">
                    @csrf
                    <select name="service_id" class="responsive-select" required>
                        <option value="">Select Service</option>
                        @forelse($availableServices as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} — Rs. {{ number_format($s->base_price,2) }}</option>
                        @empty
                            <option value="" disabled>All services already added</option>
                        @endforelse
                    </select>
                    <button type="submit">Add</button>
                </form>
            </div>
        </div>

        <!-- Parts -->
        <div class="info-panel">
            <h3>Parts Used</h3>
            <div class="parts-list">
                @forelse($job->parts as $part)
                    <div class="part-item {{ $part->applied ? 'part-applied' : 'part-pending' }}">
                        <div class="part-info">
                            <span class="part-name">{{ $part->product->name }}</span>
                            <span class="part-quantity">{{ $part->quantity }} {{ $part->product->unit ?? 'unit' }}</span>
                            <span class="part-status {{ $part->applied ? 'status-green' : 'status-yellow' }}">
                                {{ $part->applied ? 'Applied' : 'Pending' }}
                            </span>
                        </div>
                        <div class="part-actions">
                            <span class="part-price">Rs. {{ number_format($part->unit_price * $part->quantity,2) }}</span>
                            @if(!$part->applied)
                                <form class="inline-form" method="post" action="{{ route('jobs.parts.apply', [$job, $part]) }}">
                                    @csrf
                                    <button type="submit" class="confirm-btn">Confirm Apply</button>
                                </form>
                            @endif
                            <button type="button" class="close-btn" onclick="showCloseModal('part', {{ $part->id }}, '{{ $part->product->name }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">No parts consumed</div>
                @endforelse
            </div>
            <div class="add-part-form">
                <h4>Consume Part</h4>
                @php
                    $pendingProductIds = $job->parts->where('applied', false)->pluck('product_id')->toArray();
                    $availableProducts = $products->whereNotIn('id', $pendingProductIds);
                @endphp
                <form class="inline-form" method="post" action="{{ route('jobs.consume-part',$job) }}">
                    @csrf
                    <select name="product_id" id="productSelect" class="responsive-select" onchange="updateSellingPrice(); updateStockInfo()">
                        <option value="">Select Product</option>
                        @forelse($availableProducts as $p)
                            @php
                                $inventory = \App\Models\Inventory::where('product_id', $p->id)->where('branch_id', $job->branch_id)->first();
                                $availableStock = $inventory ? $inventory->quantity : 0;
                            @endphp
                            <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-stock="{{ $availableStock }}">{{ $p->name }} (Stock: {{ $availableStock }})</option>
                        @empty
                            <option value="" disabled>All products already pending</option>
                        @endforelse
                    </select>
                    <input name="quantity" type="number" step=".001" value="1" min="0.001" placeholder="Qty">
                    <input name="unit_price" type="number" step=".01" placeholder="Price" id="unitPriceInput">
                    <button type="submit">Add</button>
                </form>
                <div id="stockWarning" class="stock-warning"></div>
            </div>
        </div>

        <!-- Notes -->
        <div class="info-panel">
            <h3>Notes</h3>
            <div class="notes-content">
                {{ $job->notes ?: 'No notes added' }}
            </div>
        </div>

        <!-- Back button -->
        <div class="back-button-cell">
            <a href="javascript:history.back()" class="btn-back">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back
            </a>
        </div>
    </div>

    @if($job->invoice)
        <div class="invoice-link">
            <a href="{{ route('invoices.show',$job->invoice) }}" class="btn-primary">View Invoice</a>
        </div>
    @endif
</div>

<!-- Status Change Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Change Job Status</h3>
            <button class="modal-close" onclick="closeStatusModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to change the status to <strong id="newStatusLabel"></strong>?</p>
            
            <div class="custom-notes-section">
                <label>Additional Notes (Optional)</label>
                <textarea id="customNotes" rows="3" placeholder="Add any additional notes for the customer"></textarea>
                <small>This will be included in the WhatsApp message</small>
            </div>

            <div class="whatsapp-section">
                @if($job->customer->whatsapp_number)
                    <label class="whatsapp-toggle">
                        <input type="checkbox" id="sendWhatsapp">
                        <span class="toggle-label">Send WhatsApp update to customer ({{ $job->customer->whatsapp_number }})</span>
                    </label>
                @else
                    <div class="whatsapp-number-input">
                        <label>Customer doesn't have WhatsApp number. Add one to send update:</label>
                        <input type="text" id="customerWhatsappNumber" value="+94" placeholder="+94XXXXXXXXX" oninput="toggleWhatsappCheckbox()">
                        <label class="whatsapp-toggle" style="margin-top: 8px;">
                            <input type="checkbox" id="sendWhatsapp" disabled>
                            <span class="toggle-label">Send WhatsApp update to customer</span>
                        </label>
                    </div>
                @endif
            </div>

            <form id="statusForm" method="post" action="{{ route('jobs.status',$job) }}">
                @csrf
                <input type="hidden" name="status" id="statusInput">
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeStatusModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Confirm Change</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close Confirmation Modal -->
<div id="closeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Close Item</h3>
            <button class="modal-close" onclick="closeCloseModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p id="closeModalMessage"></p>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeCloseModal()">Cancel</button>
                <button type="button" class="btn-primary" onclick="confirmClose()">OK</button>
            </div>
        </div>
    </div>
</div>

<style>
/* ========== DESKTOP ========== */
.job-detail-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px;
}

.job-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    padding: 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    color: white;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
    animation: fadeInDown 0.5s ease;
}

.job-info h1 {
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 700;
}

.job-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    opacity: 0.9;
}

.status-badge {
    padding: 10px 20px;
    border-radius: 24px;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    animation: pulseBadge 2s infinite;
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-12px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes pulseBadge {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.03); }
}

.status-blue { background: #3b82f6; color: white; }
.status-green { background: #10b981; color: white; }
.status-yellow { background: #f59e0b; color: white; }
.status-orange { background: #f97316; color: white; }
.status-purple { background: #8b5cf6; color: white; }
.status-red { background: #ef4444; color: white; }
.status-cyan { background: #06b6d4; color: white; }

.status-workflow {
    background: white;
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.status-workflow h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
    color: #1f2937;
}

.workflow-steps {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
}

.workflow-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 100px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.workflow-step .step-action {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.step-indicator {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s ease;
}

.workflow-step.completed .step-indicator {
    background: #10b981;
    color: white;
}

.workflow-step.current .step-indicator {
    background: #3b82f6;
    color: white;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.25);
}

.workflow-step.future .step-indicator {
    background: #e5e7eb;
    color: #9ca3af;
}

.step-number {
    color: inherit;
    font-weight: 700;
}

.step-label {
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    color: #4b5563;
}

.workflow-step.completed .step-label { color: #10b981; }
.workflow-step.current .step-label { color: #3b82f6; font-weight: 600; }

.quick-actions {
    background: white;
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.quick-actions h3 {
    margin: 0 0 16px 0;
    font-size: 18px;
    color: #1f2937;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.action-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: white;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.action-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.action-yellow { background: linear-gradient(135deg, #f59e0b, #d97706); }
.action-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
.action-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.action-cyan { background: linear-gradient(135deg, #06b6d4, #0891b2); }
.action-red { background: linear-gradient(135deg, #ef4444, #dc2626); }

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.info-panel {
    background: white;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.info-panel h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
    color: #1f2937;
    border-bottom: 2px solid #f3f4f6;
    padding-bottom: 12px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-item label {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
}

.info-value {
    font-size: 15px;
    color: #1f2937;
    font-weight: 500;
}

.info-value a {
    color: #3b82f6;
    text-decoration: none;
}

.priority-high { color: #ef4444; }
.priority-medium { color: #f59e0b; }
.priority-low { color: #10b981; }

.service-item, .part-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f9fafb;
    border-radius: 8px;
    margin-bottom: 8px;
}

.service-pending { background: #fffbeb; border-left: 3px solid #f59e0b; }
.service-applied { background: #f0fdf4; border-left: 3px solid #10b981; }
.part-pending { background: #fffbeb; border-left: 3px solid #f59e0b; }
.part-applied { background: #f0fdf4; border-left: 3px solid #10b981; }

.service-actions, .part-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.service-info, .part-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.service-name, .part-name {
    font-weight: 600;
    color: #1f2937;
}

.service-status, .part-status {
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 600;
}

.service-status.status-green, .part-status.status-green { background: #d1fae5; color: #065f46; }
.service-status.status-yellow, .part-status.status-yellow { background: #fef3c7; color: #92400e; }
.service-status.status-red { background: #fee2e2; color: #991b1b; }

.confirm-btn {
    padding: 6px 12px;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
}

.close-btn {
    padding: 6px 8px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.service-price, .part-price {
    font-weight: 600;
    color: #3b82f6;
}

.part-quantity {
    font-size: 13px;
    color: #6b7280;
}

.empty-state {
    text-align: center;
    padding: 24px;
    color: #9ca3af;
    font-style: italic;
}

.add-service-form, .add-part-form {
    padding-top: 16px;
    border-top: 2px solid #f3f4f6;
}

.add-service-form h4, .add-part-form h4 {
    margin: 0 0 12px 0;
    font-size: 14px;
    color: #6b7280;
}

.inline-form {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.inline-form select,
.inline-form input {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
}

.inline-form select {
    flex: 1;
    min-width: 150px;
}

.inline-form input {
    width: 80px;
}

.inline-form button {
    padding: 8px 16px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
}

.responsive-select {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.stock-warning {
    margin-top: 8px;
    padding: 8px 12px;
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 6px;
    color: #dc2626;
    font-size: 13px;
    display: none;
}

.notes-content {
    color: #4b5563;
    line-height: 1.6;
    white-space: pre-wrap;
}

/* Back button cell - aligns bottom with Notes card */
.back-button-cell {
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
    min-height: 0;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 22px;
    background: #e5e7eb;
    color: #374151;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-back:hover {
    background: #d1d5db;
    color: #111827;
}

.invoice-link {
    text-align: center;
    margin-top: 16px;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
}

.btn-secondary {
    padding: 10px 20px;
    background: #e5e7eb;
    color: #374151;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 24px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #9ca3af;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
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

.custom-notes-section {
    margin: 20px 0;
}

.custom-notes-section label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 14px;
}

.custom-notes-section textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    min-height: 80px;
    box-sizing: border-box;
}

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
    .job-detail-container {
        padding: 16px 12px;
    }

    .job-header {
        flex-direction: column;
        gap: 14px;
        text-align: center;
        padding: 18px 16px;
        margin-bottom: 20px;
    }

    .job-info h1 {
        font-size: 20px;
    }

    .job-meta {
        font-size: 13px;
        justify-content: center;
        flex-wrap: wrap;
        gap: 6px;
    }

    .status-badge {
        font-size: 12px;
        padding: 7px 14px;
    }

    .status-workflow {
        padding: 16px;
        margin-bottom: 16px;
    }

    .status-workflow h3 {
        font-size: 16px;
        margin-bottom: 14px;
    }

    .workflow-steps {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        overflow: visible;
    }

    .workflow-step {
        min-width: 0;
    }

    .step-indicator {
        width: 34px;
        height: 34px;
        font-size: 13px;
    }

    .step-label {
        font-size: 11px;
    }

    .quick-actions {
        padding: 16px;
        margin-bottom: 16px;
    }

    .quick-actions h3 {
        font-size: 16px;
        margin-bottom: 12px;
    }

    .action-buttons {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .action-btn {
        width: 100%;
        padding: 11px 8px;
        font-size: 13px;
        text-align: center;
    }

    .content-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .info-panel {
        padding: 16px;
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }

    .info-panel h3 {
        font-size: 16px;
        margin-bottom: 14px;
    }

    .info-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .info-item label {
        font-size: 11px;
    }

    .info-value {
        font-size: 13.5px;
        word-break: break-word;
    }

    .service-item,
    .part-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        padding: 12px;
    }

    .service-actions,
    .part-actions {
        width: 100%;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }

    .inline-form {
        flex-direction: column;
        width: 100%;
    }

    .inline-form select,
    .inline-form input,
    .responsive-select {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        min-width: 0 !important;
        font-size: 13px !important;
    }

    .inline-form button {
        width: 100%;
    }

    select option {
        font-size: 13px;
    }

    .notes-content {
        font-size: 14px;
    }

    .back-button-cell {
        justify-content: stretch;
    }

    .btn-back {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .workflow-steps {
        grid-template-columns: repeat(2, 1fr);
    }

    .job-info h1 {
        font-size: 18px;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .action-btn {
        font-size: 12.5px;
        padding: 10px 6px;
    }

    .inline-form select,
    .responsive-select {
        font-size: 12.5px !important;
    }

    select option {
        font-size: 12.5px;
    }
}
</style>

<script>
function changeStatus(status) {
    const statusLabels = {
        'checked_in': 'Checked In',
        'inspection_pending': 'Inspection Pending',
        'inspection_completed': 'Inspection Completed',
        'customer_approval_pending': 'Waiting Approval',
        'approved': 'Approved',
        'waiting_for_parts': 'Waiting for Parts',
        'in_service': 'In Service',
        'quality_check': 'Quality Check',
        'ready_for_payment': 'Ready for Payment',
        'paid': 'Paid',
        'delivered': 'Delivered',
        'cancelled': 'Cancelled',
        'on_hold': 'On Hold'
    };

    document.getElementById('newStatusLabel').textContent = statusLabels[status] || status;
    document.getElementById('statusInput').value = status;
    document.getElementById('statusModal').classList.add('active');
    
    const whatsappInput = document.getElementById('customerWhatsappNumber');
    if (whatsappInput) {
        whatsappInput.value = '+94';
        document.getElementById('sendWhatsapp').disabled = true;
        document.getElementById('sendWhatsapp').checked = false;
    }
    
    const notesInput = document.getElementById('customNotes');
    if (notesInput) notesInput.value = '';
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

function closeStatusModal() {
    document.getElementById('statusModal').classList.remove('active');
}

let currentCloseType = '';
let currentCloseId = '';

function showCloseModal(type, id, name) {
    currentCloseType = type;
    currentCloseId = id;
    document.getElementById('closeModalMessage').textContent = `Are you sure you want to close this ${type}: ${name}?`;
    document.getElementById('closeModal').classList.add('active');
}

function closeCloseModal() {
    document.getElementById('closeModal').classList.remove('active');
}

function confirmClose() {
    const jobId = '{{ $job->id }}';
    let url = currentCloseType === 'service' 
        ? `/jobs/${jobId}/services/${currentCloseId}/remove`
        : `/jobs/${jobId}/parts/${currentCloseId}/remove`;

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeCloseModal();
            window.location.reload();
        } else {
            alert(data.message || 'Error');
        }
    });
}

document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('send_whatsapp', document.getElementById('sendWhatsapp')?.checked ? '1' : '0');
    
    const whatsappInput = document.getElementById('customerWhatsappNumber');
    if (whatsappInput && whatsappInput.value.trim() && whatsappInput.value.trim() !== '+94') {
        formData.append('customer_whatsapp_number', whatsappInput.value.trim());
    }
    
    const notesInput = document.getElementById('customNotes');
    if (notesInput && notesInput.value.trim()) {
        formData.append('custom_notes', notesInput.value.trim());
    }
    
    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.whatsapp_url) window.open(data.whatsapp_url, '_blank');
            setTimeout(() => window.location.reload(), 800);
        } else {
            alert(data.message || 'Error');
        }
    });
});

function updateSellingPrice() {
    const select = document.getElementById('productSelect');
    const option = select.options[select.selectedIndex];
    const priceInput = document.getElementById('unitPriceInput');
    priceInput.value = option.value ? option.getAttribute('data-price') : '';
}

function updateStockInfo() {
    const select = document.getElementById('productSelect');
    const option = select.options[select.selectedIndex];
    const warning = document.getElementById('stockWarning');
    const qty = parseFloat(document.querySelector('input[name="quantity"]').value) || 1;

    if (option.value) {
        const stock = parseFloat(option.getAttribute('data-stock'));
        if (stock === 0) {
            warning.textContent = '⚠️ This product is out of stock!';
            warning.style.display = 'block';
        } else if (stock < qty) {
            warning.textContent = `⚠️ Insufficient stock. Available: ${stock}`;
            warning.style.display = 'block';
        } else {
            warning.style.display = 'none';
        }
    } else {
        warning.style.display = 'none';
    }
}

document.querySelector('input[name="quantity"]')?.addEventListener('input', updateStockInfo);

document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});
</script>
@endsection