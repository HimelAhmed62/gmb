<?php 
require_once 'includes/config.php'; 

$id = $_GET['id'] ?? '';
$lead = null;

if ($id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
        $stmt->execute([$id]);
        $lead = $stmt->fetch();
        
        if ($lead) {
            // Decode metadata if it's a string
            if (is_string($lead['metadata'])) {
                $lead['metadata'] = json_decode($lead['metadata'], true) ?? [];
            }
            if (is_string($lead['scores'])) {
                $lead['scores'] = json_decode($lead['scores'], true) ?? [];
            }
        }
    } catch (Exception $e) {
        $lead = null;
    }
}

if (!$lead) {
    header('Location: leads.php');
    exit;
}

$pageTitle = 'Analysis: ' . $lead['company_name'];
include 'includes/header.php'; 

// Simulated audit scores if not present
$performance = $lead['scores']['performance'] ?? rand(70, 99);
$seo = $lead['scores']['seo'] ?? rand(60, 95);
$accessibility = $lead['scores']['accessibility'] ?? rand(80, 100);
?>

<div class="mb-4">
    <a href="leads.php" class="text-decoration-none text-muted small fw-bold mb-3 d-inline-flex align-items-center gap-1">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i> Back to Leads
    </a>
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($lead['company_name']); ?></h2>
            <p class="text-muted mb-0 d-flex align-items-center gap-2">
                <?php echo htmlspecialchars($lead['website']); ?> 
                <?php 
                $statusClass = 'primary';
                if ($lead['status'] === 'Qualified') $statusClass = 'success';
                if ($lead['status'] === 'Failed') $statusClass = 'danger';
                if ($lead['status'] === 'Pending') $statusClass = 'warning';
                ?>
                <span class="badge badge-soft-<?php echo $statusClass; ?> rounded-pill"><?php echo htmlspecialchars($lead['status']); ?></span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-custom d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#auditModal">
                <i data-lucide="refresh-cw" style="width: 18px; height: 18px;"></i> Re-Audit
            </button>
            <button class="btn btn-primary-custom d-flex align-items-center gap-2">
                <i data-lucide="send" style="width: 18px; height: 18px;"></i> Start Outreach
            </button>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Audit Results Card -->
        <div class="premium-card mb-4">
            <div class="card-header-custom border-bottom">
                <h5 class="fw-bold mb-0">Website Audit Results</h5>
            </div>
            <div class="card-body-custom">
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center">
                            <p class="text-muted small mb-1 fw-bold">Performance</p>
                            <h3 class="fw-bold <?php echo $performance > 80 ? 'text-success' : 'text-warning'; ?>"><?php echo $performance; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center">
                            <p class="text-muted small mb-1 fw-bold">SEO Score</p>
                            <h3 class="fw-bold <?php echo $seo > 80 ? 'text-success' : 'text-warning'; ?>"><?php echo $seo; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center">
                            <p class="text-muted small mb-1 fw-bold">Accessibility</p>
                            <h3 class="fw-bold <?php echo $accessibility > 80 ? 'text-success' : 'text-warning'; ?>"><?php echo $accessibility; ?></h3>
                        </div>
                    </div>
                </div>
                
                <h6 class="fw-bold mb-3">AI Research & Analysis</h6>
                <div class="p-4 bg-primary bg-opacity-5 border border-primary border-opacity-10 rounded-4">
                    <?php if (isset($lead['metadata']['ai_analysis'])): ?>
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary"><i data-lucide="microscope" style="width: 20px; height: 20px;"></i></div>
                            <div class="text-dark small lh-base"><?php echo nl2br(htmlspecialchars($lead['metadata']['ai_analysis'])); ?></div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i data-lucide="search" class="text-muted mb-2" style="width: 32px; height: 32px;"></i>
                            <p class="text-muted small mb-0">No live research data available. Click <strong>Re-Audit</strong> to start analyzing this website with AI.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Metadata Card -->
        <div class="premium-card">
            <div class="card-header-custom border-bottom">
                <h5 class="fw-bold mb-0">Complete Lead Metadata</h5>
            </div>
            <div class="card-body-custom p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 small">
                        <tbody class="border-top-0">
                            <?php foreach (($lead['metadata'] ?? []) as $key => $value): ?>
                            <tr>
                                <td class="ps-4 py-2 text-muted fw-bold" style="width: 200px;"><?php echo ucwords(str_replace('_', ' ', $key)); ?></td>
                                <td class="py-2 fw-bold text-dark"><?php echo htmlspecialchars($value); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="premium-card mb-4">
            <div class="card-header-custom border-bottom">
                <h5 class="fw-bold mb-0">Company Info</h5>
            </div>
            <div class="card-body-custom">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                    <li class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                            <i data-lucide="map-pin" style="width: 18px; height: 18px;"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted extra-small fw-bold text-uppercase">Location</p>
                            <p class="mb-0 fw-bold small text-dark"><?php echo htmlspecialchars($lead['metadata']['business_location'] ?? 'Not specified'); ?></p>
                        </div>
                    </li>
                    <li class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 text-success p-2 rounded-3">
                            <i data-lucide="phone" style="width: 18px; height: 18px;"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted extra-small fw-bold text-uppercase">Phone</p>
                            <p class="mb-0 fw-bold small text-dark"><?php echo htmlspecialchars($lead['phone'] ?: ($lead['metadata']['phone_number'] ?? 'N/A')); ?></p>
                        </div>
                    </li>
                    <li class="d-flex align-items-center gap-3">
                        <div class="bg-danger bg-opacity-10 text-danger p-2 rounded-3">
                            <i data-lucide="mail" style="width: 18px; height: 18px;"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted extra-small fw-bold text-uppercase">Email</p>
                            <p class="mb-0 fw-bold small text-dark"><?php echo htmlspecialchars($lead['email'] ?: 'N/A'); ?></p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="premium-card">
            <div class="card-header-custom border-bottom">
                <h5 class="fw-bold mb-0">Outreach Actions</h5>
            </div>
            <div class="card-body-custom">
                <div class="d-flex flex-column gap-2">
                    <button class="btn btn-primary-custom w-100 d-flex align-items-center justify-content-center gap-2">
                        <i data-lucide="mail" style="width: 18px; height: 18px;"></i> Send Email
                    </button>
                    <button class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2 rounded-3 py-2 fw-bold shadow-sm">
                        <i data-lucide="message-circle" style="width: 18px; height: 18px;"></i> Send WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Re-Audit Modal -->
<div class="modal fade" id="auditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom p-4">
                <h5 class="modal-title fw-bold">Select Audit Engine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-4">Choose which AI model you want to use for this website research. The audit will use the instructions defined in your API settings.</p>
                
                <div class="row g-3">
                    <div class="col-4">
                        <label class="w-100 cursor-pointer">
                            <input type="radio" name="audit_ai" value="chatgpt" class="btn-check" checked <?php echo !($_SESSION['chatgpt_connected'] ?? false) ? 'disabled' : ''; ?>>
                            <div class="btn btn-outline-custom w-100 p-3 rounded-4 d-flex flex-column align-items-center gap-2">
                                <i data-lucide="brain" class="text-success" style="width: 28px; height: 28px;"></i>
                                <span class="fw-bold small">ChatGPT</span>
                                <span class="extra-small <?php echo ($_SESSION['chatgpt_connected'] ?? false) ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($_SESSION['chatgpt_connected'] ?? false) ? 'Ready' : 'Off'; ?>
                                </span>
                            </div>
                        </label>
                    </div>
                    <div class="col-4">
                        <label class="w-100 cursor-pointer">
                            <input type="radio" name="audit_ai" value="gemini" class="btn-check" <?php echo !($_SESSION['gemini_connected'] ?? false) ? 'disabled' : ''; ?>>
                            <div class="btn btn-outline-custom w-100 p-3 rounded-4 d-flex flex-column align-items-center gap-2">
                                <i data-lucide="sparkles" class="text-primary" style="width: 28px; height: 28px;"></i>
                                <span class="fw-bold small">Gemini</span>
                                <span class="extra-small <?php echo ($_SESSION['gemini_connected'] ?? false) ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($_SESSION['gemini_connected'] ?? false) ? 'Ready' : 'Off'; ?>
                                </span>
                            </div>
                        </label>
                    </div>
                    <div class="col-4">
                        <label class="w-100 cursor-pointer">
                            <input type="radio" name="audit_ai" value="manual" class="btn-check">
                            <div class="btn btn-outline-custom w-100 p-3 rounded-4 d-flex flex-column align-items-center gap-2">
                                <i data-lucide="code-2" class="text-warning" style="width: 28px; height: 28px;"></i>
                                <span class="fw-bold small">Manual</span>
                                <span class="extra-small text-warning">Unlimited</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 p-4 pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="startAuditBtn" class="btn btn-primary-custom px-4 flex-grow-1">Start Research</button>
            </div>
        </div>
    </div>
</div>

<script>
// Load the custom script from the database
const customAuditScript = <?php echo json_encode($_SESSION['manual_audit_script'] ?? "function audit(html, url) { return { performance: 80, seo: 80, accessibility: 80, analysis: 'No custom script defined.', status: 'Qualified' }; }"); ?>;

document.getElementById('startAuditBtn').addEventListener('click', async function() {
    const ai = document.querySelector('input[name="audit_ai"]:checked').value;
    const leadId = '<?php echo $lead['id']; ?>';
    const btn = this;
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';

    try {
        if (ai === 'manual') {
            // 1. Fetch HTML
            const fetchRes = await fetch('actions/audit-handler.php', {
                method: 'POST',
                body: new URLSearchParams({ lead_id: leadId, ai: 'manual_fetch' })
            });
            const fetchData = await fetchRes.json();
            
            if (!fetchData.success) throw new Error(fetchData.message);

            // 2. Execute User Script
            let auditResult;
            try {
                // Pass both html and the target URL to the script
                const userFunc = new Function('html', 'url', `${customAuditScript}\n return typeof audit === 'function' ? audit(html, url) : { performance: 50, seo: 50, accessibility: 50, analysis: 'Invalid script structure. Please define an audit(html, url) function.', status: 'Failed' };`);
                auditResult = userFunc(fetchData.html, fetchData.url);
            } catch (e) {
                throw new Error("Script Error: " + e.message);
            }

            // 3. Save Results
            const saveRes = await fetch('actions/audit-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 
                    lead_id: leadId, 
                    ai: 'manual_save', 
                    results: JSON.stringify(auditResult) 
                })
            });
            
            const saveText = await saveRes.text();
            let saveData;
            try { 
                saveData = JSON.parse(saveText); 
            } catch(e) { 
                console.error("Save Error Response:", saveText);
                alert("Server Error: " + saveText);
                throw new Error("Invalid response from server during save."); 
            }

            if (!saveData.success) throw new Error(saveData.message || "Unknown save error");
            
            location.reload();
        } else {
            // Standard AI Audit
            const formData = new FormData();
            formData.append('lead_id', leadId);
            formData.append('ai', ai);

            const response = await fetch('actions/audit-handler.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                location.reload();
            } else {
                throw new Error(result.message);
            }
        }
    } catch (error) {
        alert('Error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>

<?php include 'includes/footer.php'; ?>
