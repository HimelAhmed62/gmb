<?php 
require_once 'includes/config.php'; 
$pageTitle = 'Leads Table';
include 'includes/header.php'; 

$leads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC")->fetchAll();
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <div class="d-flex align-items-center gap-3 mb-1">
            <h2 class="fw-bold text-dark mb-0">Leads Table</h2>
            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold" style="font-size: 0.9rem;">
                <?php echo count($leads); ?> Total Leads
            </span>
        </div>
        <p class="text-muted mb-0">Manage and filter your website audit results.</p>
    </div>
    <div class="d-flex gap-2">
        <button id="filterRecent" class="btn btn-outline-custom d-flex align-items-center gap-2">
            <i data-lucide="clock" style="width: 18px; height: 18px;"></i> Recently Uploaded
        </button>
        <a href="upload.php" class="btn btn-primary-custom d-flex align-items-center gap-2">
            <i data-lucide="plus" style="width: 18px; height: 18px;"></i> Add New Leads
        </a>
    </div>
</div>

<div class="premium-card">
    <div class="card-header-custom border-bottom">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 border-light"><i data-lucide="search" style="width: 16px; height: 16px;"></i></span>
                    <input type="text" id="leadSearch" class="form-control form-control-custom border-start-0" placeholder="Search leads...">
                </div>
            </div>
            <div class="col-md-3">
                <select id="statusFilter" class="form-select form-control-custom">
                    <option value="all">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Qualified">Qualified</option>
                    <option value="Failed">Failed</option>
                </select>
            </div>
            <div class="col-md-5 d-flex justify-content-md-end gap-2">
                <button id="deleteSelected" class="btn btn-danger-custom d-none align-items-center gap-2">
                    <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i> Delete Selected
                </button>
                <button class="btn btn-outline-custom d-flex align-items-center gap-2">
                    <i data-lucide="download" style="width: 16px; height: 16px;"></i> Export
                </button>
            </div>
        </div>
    </div>
    <div class="card-body-custom p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="leadsTable">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 ps-4"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                        <th class="text-uppercase text-muted small fw-bold py-3">Company</th>
                        <th class="text-uppercase text-muted small fw-bold py-3">Contact Info</th>
                        <th class="text-uppercase text-muted small fw-bold py-3">Score</th>
                        <th class="text-uppercase text-muted small fw-bold py-3">Status</th>
                        <th class="text-uppercase text-muted small fw-bold py-3 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leads)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i data-lucide="database" class="mb-3" style="width: 48px; height: 48px; opacity: 0.2;"></i>
                                <p>No leads found. <a href="upload.php">Upload some leads</a> to get started.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($leads as $lead): ?>
                        <tr class="lead-row" data-created="<?php echo strtotime($lead['created_at']); ?>" data-status="<?php echo htmlspecialchars($lead['status']); ?>">
                            <td class="ps-4"><input type="checkbox" class="form-check-input lead-checkbox" value="<?php echo $lead['id']; ?>"></td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($lead['company_name']); ?></div>
                                <a href="<?php echo htmlspecialchars($lead['website']); ?>" target="_blank" class="text-decoration-none small d-flex align-items-center gap-1 text-muted">
                                    <?php 
                                    $displayUrl = preg_replace('/^https?:\/\/(www\.)?/', '', $lead['website']);
                                    echo htmlspecialchars(strlen($displayUrl) > 30 ? substr($displayUrl, 0, 27) . '...' : $displayUrl); 
                                    ?> 
                                    <i data-lucide="external-link" style="width: 10px; height: 10px;"></i>
                                </a>
                            </td>
                            <td>
                                <?php if (!empty($lead['email'])): ?>
                                <div class="small text-dark d-flex align-items-center gap-1"><i data-lucide="mail" style="width: 12px; height: 12px;"></i> <?php echo htmlspecialchars($lead['email']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($lead['phone'])): ?>
                                <div class="extra-small text-muted d-flex align-items-center gap-1"><i data-lucide="phone" style="width: 12px; height: 12px;"></i> <?php echo htmlspecialchars($lead['phone']); ?></div>
                                <?php endif; ?>
                                <?php if (empty($lead['email']) && empty($lead['phone'])): ?>
                                <span class="text-muted small italic">Not provided</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2" style="width: 100px;">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar <?php echo $lead['score'] > 70 ? 'bg-success' : ($lead['score'] > 40 ? 'bg-warning' : 'bg-danger'); ?>" style="width: <?php echo $lead['score']; ?>%"></div>
                                    </div>
                                    <span class="fw-bold small"><?php echo $lead['score']; ?></span>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $statusClass = 'primary';
                                if ($lead['status'] === 'Qualified') $statusClass = 'success';
                                if ($lead['status'] === 'Failed') $statusClass = 'danger';
                                if ($lead['status'] === 'Pending') $statusClass = 'warning';
                                ?>
                                <span class="badge badge-soft-<?php echo $statusClass; ?> rounded-pill"><?php echo htmlspecialchars($lead['status']); ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <button class="btn btn-light btn-sm p-2 view-details" data-lead='<?php echo json_encode($lead); ?>' title="View Details"><i data-lucide="eye" style="width: 16px; height: 16px;"></i></button>
                                    <button class="btn btn-light btn-sm p-2 text-primary prepare-lead-btn" data-id="<?php echo $lead['id']; ?>" title="Prepare Lead Data"><i data-lucide="cpu" style="width: 16px; height: 16px;"></i></button>
                                    <button class="btn btn-light btn-sm p-2 text-danger individual-delete" data-id="<?php echo $lead['id']; ?>" title="Delete Lead"><i data-lucide="trash-2" style="width: 16px; height: 16px;"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center py-3 px-4">
        <span class="text-muted small">Showing <?php echo count($leads); ?> leads</span>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
            </ul>
        </nav>
    </div>
</div>

<!-- Email Outreach Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i data-lucide="mail" style="width: 20px; height: 20px;"></i>
                    </div>
                    <h5 class="fw-bold mb-0">Email Outreach: <span id="emailCompany">Company</span></h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Recipient Email</label>
                    <input type="email" id="outreachEmail" class="form-control form-control-custom" placeholder="recipient@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Subject</label>
                    <input type="text" id="emailSubject" class="form-control form-control-custom" value="Website Audit for [Company Name]">
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Message Draft (AI Optimized)</label>
                    <textarea id="emailBody" class="form-control form-control-custom" rows="8"></textarea>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <p class="text-muted extra-small mb-0"><i data-lucide="info" style="width: 12px; height: 12px;"></i> Using connected Gmail account: <strong>growth@stripe.com</strong></p>
                    <button id="sendEmailBtn" class="btn btn-primary-custom px-5">Send Email Now</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Outreach Modal -->
<div class="modal fade" id="whatsappModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success p-2 rounded-3">
                        <i data-lucide="message-circle" style="width: 20px; height: 20px;"></i>
                    </div>
                    <h5 class="fw-bold mb-0">WhatsApp Message</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3 text-start">
                    <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Phone Number</label>
                    <input type="text" id="outreachPhone" class="form-control form-control-custom" placeholder="+1234567890">
                </div>
                <div class="mb-4 text-start">
                    <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Message Draft</label>
                    <textarea id="whatsappBody" class="form-control form-control-custom" rows="4"></textarea>
                </div>
                <button id="sendWhatsappBtn" class="btn btn-success w-100 rounded-3 py-2 fw-bold d-flex align-items-center justify-content-center gap-2">
                    <i data-lucide="external-link" style="width: 18px; height: 18px;"></i> Open WhatsApp Web
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                    <i data-lucide="alert-triangle" style="width: 32px; height: 32px;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Confirm Deletion</h5>
                <p class="text-muted small mb-4">Are you sure you want to permanently delete <span id="deleteCountText">this lead</span>? This action cannot be undone.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger-custom px-4 rounded-3">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lead Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="fw-bold" id="detailsTitle">Lead Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="detailsContent">
                <!-- Content will be injected here -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const leadSearch = document.getElementById('leadSearch');
    const statusFilter = document.getElementById('statusFilter');
    const selectAll = document.getElementById('selectAll');
    const deleteSelectedBtn = document.getElementById('deleteSelected');
    const rows = document.querySelectorAll('#leadsTable tbody tr.lead-row');

    function applyFilters() {
        const searchTerm = leadSearch.value.toLowerCase();
        const statusValue = statusFilter.value;
        const recentFilterActive = document.getElementById('filterRecent').classList.contains('active');
        const now = Math.floor(Date.now() / 1000);
        const oneDay = 24 * 60 * 60;

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const status = row.getAttribute('data-status');
            const created = parseInt(row.getAttribute('data-created'));

            const matchesSearch = text.includes(searchTerm);
            const matchesStatus = (statusValue === 'all' || status === statusValue);
            const matchesRecent = (!recentFilterActive || (now - created < oneDay));

            row.style.display = (matchesSearch && matchesStatus && matchesRecent) ? '' : 'none';
        });
        updateSelectionUI();
    }

    leadSearch.addEventListener('keyup', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    
    // Recent Filter Override
    document.getElementById('filterRecent').addEventListener('click', function() {
        this.classList.toggle('active');
        this.classList.toggle('btn-primary-custom');
        this.classList.toggle('btn-outline-custom');
        applyFilters();
    });

    // Select All Logic
    selectAll.addEventListener('change', function() {
        const visibleCheckboxes = document.querySelectorAll('#leadsTable tbody tr.lead-row:not([style*="display: none"]) .lead-checkbox');
        visibleCheckboxes.forEach(cb => cb.checked = selectAll.checked);
        updateSelectionUI();
    });

    function updateSelectionUI() {
        const checkedCount = document.querySelectorAll('.lead-checkbox:checked').length;
        if (checkedCount > 0) {
            deleteSelectedBtn.classList.remove('d-none');
            deleteSelectedBtn.classList.add('d-flex');
            deleteSelectedBtn.innerHTML = `<i data-lucide="trash-2" style="width: 16px; height: 16px;"></i> Delete Selected (${checkedCount})`;
        } else {
            deleteSelectedBtn.classList.add('d-none');
            deleteSelectedBtn.classList.remove('d-flex');
        }
        lucide.createIcons();
    }

    document.querySelectorAll('.lead-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectionUI);
    });

    // Selection & Delete Modal Logic
    const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    let idsToDelete = [];

    // Individual Delete Click
    document.querySelectorAll('.individual-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            idsToDelete = [this.getAttribute('data-id')];
            document.getElementById('deleteCountText').innerText = "this lead";
            deleteConfirmModal.show();
        });
    });

    // Bulk Delete Click
    deleteSelectedBtn.addEventListener('click', function() {
        idsToDelete = Array.from(document.querySelectorAll('.lead-checkbox:checked')).map(cb => cb.value);
        if (idsToDelete.length === 0) return;
        document.getElementById('deleteCountText').innerText = `${idsToDelete.length} selected leads`;
        deleteConfirmModal.show();
    });

    // Actual Delete Execution
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Deleting...';

        fetch('actions/delete-lead.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: idsToDelete })
        })
        .then(async response => {
            const text = await response.text();
            try { return JSON.parse(text); } catch (e) { throw new Error('Invalid server response'); }
        })
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message, 'danger');
                btn.disabled = false;
                btn.innerText = 'Yes, Delete';
            }
        })
        .catch(err => {
            showToast('Error: ' + err.message, 'danger');
            btn.disabled = false;
            btn.innerText = 'Yes, Delete';
        });
    });

    // Prepare Lead Button Logic
    document.querySelectorAll('.prepare-lead-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const originalContent = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch('actions/update-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, status: 'Preparing' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Lead moved to preparation queue...', 'primary');
                    setTimeout(() => {
                        window.location.href = `prepare.php?id=${id}`;
                    }, 500);
                } else {
                    this.disabled = false;
                    this.innerHTML = originalContent;
                    showToast('Error updating status.', 'danger');
                }
            })
            .catch(() => {
                this.disabled = false;
                this.innerHTML = originalContent;
                showToast('Server error.', 'danger');
            });
        });
    });

    // Details Modal logic... (rest of the modal code)
    const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
    document.querySelectorAll('.view-details').forEach(btn => {
        btn.addEventListener('click', function() {
            const lead = JSON.parse(this.getAttribute('data-lead'));
            document.getElementById('detailsTitle').innerText = lead.company_name;
            
            let html = `
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted extra-small fw-bold mb-3 tracking-wider">Core Information</h6>
                        <div class="mb-2"><span class="text-muted small">Website:</span> <a href="${lead.website}" target="_blank" class="small fw-bold">${lead.website}</a></div>
                        <div class="mb-2"><span class="text-muted small">Email:</span> <span class="small fw-bold">${lead.email || 'N/A'}</span></div>
                        <div class="mb-2"><span class="text-muted small">Phone:</span> <span class="small fw-bold">${lead.phone || 'N/A'}</span></div>
                        <div class="mb-2"><span class="text-muted small">Contact:</span> <span class="small fw-bold">${lead.contact_name || 'N/A'}</span></div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted extra-small fw-bold mb-3 tracking-wider">Audit Status</h6>
                        <div class="mb-2"><span class="text-muted small">Status:</span> <span class="badge badge-soft-primary">${lead.status}</span></div>
                        <div class="mb-2"><span class="text-muted small">AI Score:</span> <span class="fw-bold text-primary">${lead.score}/100</span></div>
                        <div class="mb-2"><span class="text-muted small">Imported:</span> <span class="small fw-bold">${lead.created_at}</span></div>
                    </div>
                    <div class="col-12 mt-4">
                        <h6 class="text-uppercase text-muted extra-small fw-bold mb-3 tracking-wider">Raw CSV Data (All Details)</h6>
                        <div class="bg-light p-3 rounded-4 border overflow-auto" style="max-height: 200px;">
                            <table class="table table-sm table-borderless mb-0 small">
                                <tbody>
                                    ${Object.entries(lead.metadata || {}).map(([key, val]) => `
                                        <tr>
                                            <td class="text-muted" style="width: 150px;">${key}</td>
                                            <td class="fw-bold">${val}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('detailsContent').innerHTML = html;
            detailsModal.show();
        });
    });

    lucide.createIcons();
});

function showToast(message, type = 'success') {
    const icon = type === 'success' ? 'check-circle' : 'alert-circle';
    const toast = document.createElement('div');
    toast.className = `toast-custom show bg-white border border-${type} p-3 rounded-3 shadow-lg d-flex align-items-center gap-3`;
    toast.style.position = 'fixed';
    toast.style.bottom = '24px';
    toast.style.right = '24px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.innerHTML = `
        <div class="bg-${type} bg-opacity-10 text-${type} rounded-circle p-2">
            <i data-lucide="${icon}" style="width: 20px; height: 20px;"></i>
        </div>
        <div class="flex-grow-1">
            <p class="mb-0 fw-bold small text-dark">${message}</p>
        </div>
        <button type="button" class="btn-close small" onclick="this.parentElement.remove()"></button>
    `;
    document.body.appendChild(toast);
    lucide.createIcons();
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        toast.style.transition = 'all 0.5s ease';
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}
</script>

<?php include 'includes/footer.php'; ?>
