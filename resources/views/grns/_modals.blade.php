<!-- Confirm Modal -->
<div id="confirmModal" class="modal" style="display: none;">
    <div class="modal-content modal-confirm">
        <div class="modal-icon modal-icon-success">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <div class="modal-header">
            <h2>Confirm GRN</h2>
            <button class="close-btn" onclick="closeConfirmModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to confirm this GRN?</p>
            <p class="modal-subtext">This will update inventory and supplier ledger.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="secondary" onclick="window.grnCloseConfirm()">Cancel</button>
            <button type="button" class="primary" onclick="window.grnExecuteConfirm()">Confirm GRN</button>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-content modal-delete">
        <div class="modal-icon modal-icon-danger">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <div class="modal-header">
            <h2>Delete GRN</h2>
            <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this GRN?</p>
            <p class="modal-subtext">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="secondary" onclick="window.grnCloseDelete()">Cancel</button>
            <button type="button" class="danger" onclick="window.grnExecuteDelete()">Delete GRN</button>
        </div>
    </div>
</div>
