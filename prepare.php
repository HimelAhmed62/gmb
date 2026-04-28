<?php 
require_once 'includes/config.php'; 

// Fetch leads from Database
try {
    $pendingStmt = $pdo->query("SELECT * FROM leads WHERE status = 'Preparing' ORDER BY id DESC");
    $pendingLeads = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);

    $readyStmt = $pdo->query("SELECT * FROM leads WHERE status IN ('Ready', 'Qualified', 'Failed') ORDER BY id DESC");
    $preparedLeads = $readyStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pendingLeads = [];
    $preparedLeads = [];
}

$targetId = $_GET['id'] ?? '';

$pageTitle = 'Data Preparation';
include 'includes/header.php'; 
?>

<div class="mb-4">
    <h2 class="fw-bold text-dark mb-1">Data Preparation</h2>
    <p class="text-muted mb-0">Audit pending leads and manage outreach for prepared data.</p>
</div>

<div class="row g-4">
    <!-- Analysis Section -->
    <div class="col-lg-12">
        <div class="premium-card mb-4">
            <div class="card-header-custom border-bottom pb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0">AI Processing Unit</h5>
                        <p class="text-muted small mb-0"><?php echo count($pendingLeads); ?> leads waiting for analysis.</p>
                    </div>
                </div>
            </div>
            <div class="card-body-custom">
                <div id="processingArea" class="py-4 text-center d-none">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <h5 class="fw-bold">AI Analyzing Websites...</h5>
                    <div class="progress mt-4 mb-2 mx-auto" style="height: 10px; max-width: 600px;">
                        <div id="processingProgress" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%"></div>
                    </div>
                    <p id="progressStatus" class="extra-small text-muted fw-bold">Starting analysis...</p>
                </div>

                <div id="setupArea" class="row align-items-center">
                    <div class="col-md-7">
                        <div class="p-4 bg-light rounded-4 border border-dashed">
                            <h6 class="fw-bold mb-2">Audit Engine Configuration</h6>
                            <div class="d-flex gap-3 mt-3">
                                <?php $firstChecked = false; ?>
                                <?php if (!isset($_SESSION['engine_gemini']) || $_SESSION['engine_gemini']): ?>
                                <div class="form-check custom-radio-card">
                                    <input class="form-check-input d-none" type="radio" name="auditEngine" id="engineGemini" value="gemini" <?php echo !$firstChecked ? 'checked' : ''; $firstChecked = true; ?>>
                                    <label class="form-check-label p-3 rounded-4 border w-100 cursor-pointer" for="engineGemini">
                                        <div class="fw-bold small mb-1"><i data-lucide="sparkles" class="text-primary" style="width: 14px;"></i> Gemini</div>
                                        <div class="extra-small text-muted">Advanced reasoning</div>
                                    </label>
                                </div>
                                <?php endif; ?>

                                <?php if (!isset($_SESSION['engine_chatgpt']) || $_SESSION['engine_chatgpt']): ?>
                                <div class="form-check custom-radio-card">
                                    <input class="form-check-input d-none" type="radio" name="auditEngine" id="engineChatGPT" value="chatgpt" <?php echo !$firstChecked ? 'checked' : ''; $firstChecked = true; ?>>
                                    <label class="form-check-label p-3 rounded-4 border w-100 cursor-pointer" for="engineChatGPT">
                                        <div class="fw-bold small mb-1"><i data-lucide="brain" class="text-success" style="width: 14px;"></i> ChatGPT</div>
                                        <div class="extra-small text-muted">Precise analysis</div>
                                    </label>
                                </div>
                                <?php endif; ?>

                                <?php if (!isset($_SESSION['engine_manual']) || $_SESSION['engine_manual']): ?>
                                <div class="form-check custom-radio-card">
                                    <input class="form-check-input d-none" type="radio" name="auditEngine" id="engineManual" value="manual" <?php echo !$firstChecked ? 'checked' : ''; $firstChecked = true; ?>>
                                    <label class="form-check-label p-3 rounded-4 border w-100 cursor-pointer" for="engineManual">
                                        <div class="fw-bold small mb-1"><i data-lucide="code" class="text-warning" style="width: 14px;"></i> Manual JS</div>
                                        <div class="extra-small text-muted">Fast local audit</div>
                                    </label>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex flex-column gap-2">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted small">Leads</span>
                                <input type="number" id="processCount" class="form-control form-control-custom text-center fw-bold" value="<?php echo $targetId ? '1' : '10'; ?>" min="1" max="<?php echo count($pendingLeads); ?>">
                            </div>
                            <button id="startProcessBtn" class="btn btn-primary-custom w-100 py-3 fw-bold" <?php echo count($pendingLeads) === 0 ? 'disabled' : ''; ?>>
                                <i data-lucide="zap" style="width: 18px; height: 18px;"></i> Run Selected Audit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prepared Leads List -->
    <div class="col-lg-12">
        <div class="premium-card">
            <div class="card-header-custom border-bottom pb-3">
                <h5 class="fw-bold mb-0">Prepared Leads & Outreach</h5>
                <p class="text-muted small mb-0">Leads in this list have been audited and are ready for communication.</p>
            </div>
            <div class="card-body-custom p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="ps-4 py-3 text-muted small fw-bold text-uppercase">Company</th>
                                <th class="py-3 text-muted small fw-bold text-uppercase">Score</th>
                                <th class="py-3 text-muted small fw-bold text-uppercase">Contact Info</th>
                                <th class="py-3 text-muted small fw-bold text-uppercase">Status</th>
                                <th class="pe-4 py-3 text-end text-muted small fw-bold text-uppercase">Outreach</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($preparedLeads)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted small">No prepared leads yet. Start processing from the unit above.</div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($preparedLeads as $lead): 
                                    $badgeClass = 'badge-soft-primary';
                                    if ($lead['status'] === 'Qualified') $badgeClass = 'badge-soft-success';
                                    if ($lead['status'] === 'Failed') $badgeClass = 'badge-soft-danger';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($lead['company_name']); ?></div>
                                        <div class="text-muted extra-small"><?php echo htmlspecialchars($lead['website']); ?></div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 4px; width: 60px;">
                                                <div class="progress-bar bg-primary" style="width: <?php echo $lead['score']; ?>%"></div>
                                            </div>
                                            <span class="fw-bold small"><?php echo $lead['score']; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-dark fw-medium"><?php echo htmlspecialchars($lead['email'] ?: 'N/A'); ?></div>
                                        <div class="extra-small text-muted"><?php echo htmlspecialchars($lead['phone'] ?: 'N/A'); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $badgeClass; ?> rounded-pill"><?php echo $lead['status']; ?></span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="details.php?id=<?php echo $lead['id']; ?>" class="btn btn-outline-custom btn-sm"><i data-lucide="eye" style="width: 14px; height: 14px;"></i> Details</a>
                                            <button class="btn btn-primary-custom btn-sm outreach-email-btn" data-lead='<?php echo json_encode($lead); ?>'><i data-lucide="mail" style="width: 14px; height: 14px;"></i> Mail</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailModal = new bootstrap.Modal(document.getElementById('emailModal'));
    const whatsappModal = new bootstrap.Modal(document.getElementById('whatsappModal'));

    // Handle all clicks in the document
    document.addEventListener('click', function(e) {
        // 1. Start Analysis Button
        if (e.target && e.target.id === 'startProcessBtn') {
            const count = parseInt(document.getElementById('processCount').value);
            const engine = document.querySelector('input[name="auditEngine"]:checked').value;
            if (isNaN(count) || count < 1) return;

            document.getElementById('setupArea').classList.add('d-none');
            document.getElementById('processingArea').classList.remove('d-none');

            // 1. Get lead IDs to process
            const pendingLeads = <?php echo json_encode(array_slice($pendingLeads, 0, 100)); ?>;
            const processList = pendingLeads.slice(0, count);
            
            let processed = 0;
            const runNext = async () => {
                if (processed >= processList.length) {
                    showToast(`Success: ${processed} leads audited.`, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                    return;
                }

                const lead = processList[processed];
                document.getElementById('progressStatus').innerText = `Auditing: ${lead.company_name} (${processed + 1}/${processList.length})`;
                
                try {
                    // Call the real audit handler
                    const res = await fetch('actions/audit-handler.php', {
                        method: 'POST',
                        body: new URLSearchParams({ lead_id: lead.id, ai: engine })
                    });
                    const data = await res.json();
                } catch (err) {
                    console.error("Audit failed for lead:", lead.id, err);
                }

                processed++;
                const percent = (processed / processList.length) * 100;
                document.getElementById('processingProgress').style.width = percent + '%';
                
                runNext(); // Process next lead
            };

            runNext();
        }

        // 2. Outreach Email Button
        const emailBtn = e.target.closest('.outreach-email-btn');
        if (emailBtn) {
            const lead = JSON.parse(emailBtn.getAttribute('data-lead'));
            
            document.getElementById('emailCompany').innerText = lead.company_name;
            document.getElementById('outreachEmail').value = lead.email || '';
            document.getElementById('emailSubject').value = `Professional Audit: ${lead.company_name}`;
            document.getElementById('emailBody').value = `Hi ${lead.contact_name || 'there'},\n\nI just finished an audit of your website (${lead.website}) and found some critical optimization opportunities that could help you scale.\n\nWould you be open to a quick 5-minute chat to discuss the results?\n\nBest regards,\nAuditAI Team`;
            
            // Set the ID on the "Send" button in the modal
            document.getElementById('sendEmailBtn').setAttribute('data-target-id', lead.id);
            emailModal.show();
        }

        // 3. Outreach WhatsApp Button
        const waBtn = e.target.closest('.outreach-whatsapp-btn');
        if (waBtn) {
            const lead = JSON.parse(waBtn.getAttribute('data-lead'));
            
            document.getElementById('outreachPhone').value = lead.phone || '';
            document.getElementById('whatsappBody').value = `Hi! This is regarding the website audit for ${lead.company_name}. I have some interesting insights to share. Let me know if we can connect!`;
            
            // Set the ID on the "Open" button in the modal
            document.getElementById('sendWhatsappBtn').setAttribute('data-target-id', lead.id);
            whatsappModal.show();
        }

        // 4. Send Email Execution
        if (e.target && e.target.id === 'sendEmailBtn') {
            const btn = e.target;
            const id = btn.getAttribute('data-target-id');
            if (!id) return;

            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sending...';
            
            fetch('actions/update-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, status: 'Contacted' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Email sent successfully!', 'success');
                    setTimeout(() => {
                        emailModal.hide();
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Failed to update status');
                }
            })
            .catch(err => {
                showToast('Error: ' + err.message, 'danger');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }

        // 5. WhatsApp Web Execution
        if (e.target && e.target.id === 'sendWhatsappBtn') {
            const btn = e.target;
            const id = btn.getAttribute('data-target-id');
            const phone = document.getElementById('outreachPhone').value.replace(/[^0-9]/g, '');
            const message = encodeURIComponent(document.getElementById('whatsappBody').value);
            
            if (!phone) {
                showToast('Please provide a phone number.', 'danger');
                return;
            }

            // Update status to Contacted
            fetch('actions/update-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, status: 'Contacted' })
            })
            .then(() => {
                window.open(`https://wa.me/${phone}?text=${message}`, '_blank');
                whatsappModal.hide();
                showToast('WhatsApp Web opened!', 'success');
                setTimeout(() => window.location.reload(), 1500);
            });
        }
    });
});

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-custom show bg-white border border-${type} p-3 rounded-3 shadow-lg d-flex align-items-center gap-3`;
    toast.style.position = 'fixed'; 
    toast.style.bottom = '24px'; 
    toast.style.right = '24px'; 
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.innerHTML = `<p class="mb-0 fw-bold small text-dark">${message}</p>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}
</script>

<?php include 'includes/footer.php'; ?>
