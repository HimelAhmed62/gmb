<?php 
require_once 'includes/config.php'; 
$pageTitle = 'Manual Audit Settings';
include 'includes/header.php'; 

// Load script if not in session
if (!isset($_SESSION['manual_audit_script'])) {
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'manual_audit_script'");
    $script = $stmt->fetchColumn();
    $_SESSION['manual_audit_script'] = $script ? $script : '';
}
?>

<div class="mb-4">
    <a href="settings.php" class="text-decoration-none text-muted mb-3 d-inline-block"><i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Back to Settings</a>
    <div class="d-flex align-items-center gap-3">
        <div class="bg-warning bg-opacity-10 p-3 rounded-4">
            <i data-lucide="code-2" class="text-warning" style="width: 32px; height: 32px;"></i>
        </div>
        <div>
            <h3 class="fw-bold text-dark mb-0">Manual Audit Engine</h3>
            <p class="text-muted mb-0">Define your own custom JavaScript logic for local, unlimited audits.</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="premium-card">
            <div class="card-header-custom border-bottom pb-3">
                <h5 class="fw-bold mb-1">Custom JS Script</h5>
                <p class="text-muted small mb-0">This script runs directly in the browser and processes the raw HTML of the target website.</p>
            </div>
            <div class="card-body-custom">
                <form id="manualForm">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-dark">Function Code <span class="text-danger">*</span></label>
                        <textarea id="manualScript" class="form-control form-control-custom font-monospace small bg-light" rows="18" placeholder="function audit(html, url) { ... }"><?php echo htmlspecialchars($_SESSION['manual_audit_script']); ?></textarea>
                        <div class="form-text extra-small mt-2 text-muted">
                            <i data-lucide="info" style="width: 14px; height: 14px; margin-top: -2px;"></i> 
                            The function must be named <code>audit(html, url)</code> and return a JSON object with: <code>performance</code>, <code>seo</code>, <code>accessibility</code>, <code>analysis</code>, and <code>status</code> ('Qualified' or 'Failed').
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom px-5 py-2 fw-bold w-100" id="saveBtn">Save Script</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="premium-card bg-light bg-opacity-50 border-0 mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i data-lucide="help-circle" class="text-primary"></i> How it works
                </h6>
                <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-3">
                    <li class="d-flex align-items-start gap-2">
                        <i data-lucide="check-circle-2" class="text-success mt-1" style="width: 16px;"></i>
                        <span>When you click "Manual Audit", the system fetches the website's HTML source code.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i data-lucide="check-circle-2" class="text-success mt-1" style="width: 16px;"></i>
                        <span>The HTML and URL are passed to this script in your browser.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i data-lucide="check-circle-2" class="text-success mt-1" style="width: 16px;"></i>
                        <span>Your custom logic extracts data (emails, phones, SEO tags) and calculates scores.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i data-lucide="check-circle-2" class="text-success mt-1" style="width: 16px;"></i>
                        <span>The returned data is automatically saved to your MySQL database.</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="premium-card">
            <div class="card-body p-4 text-center">
                <div class="mb-3">
                    <i data-lucide="shield-check" class="text-success" style="width: 48px; height: 48px;"></i>
                </div>
                <h6 class="fw-bold mb-2">100% Free & Unlimited</h6>
                <p class="text-muted small mb-0">Unlike AI models, this script runs locally on your machine and consumes zero API credits. You can audit thousands of sites for free.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('manualForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('saveBtn');
    const originalText = btn.innerHTML;
    const scriptContent = document.getElementById('manualScript').value;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';

    // We can use the existing settings-handler.php to save this
    fetch('actions/settings-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ manual_audit_script: scriptContent })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i data-lucide="check-circle" class="me-2"></i> Saved Successfully';
            btn.classList.remove('btn-primary-custom');
            btn.classList.add('btn-success');
            lucide.createIcons();
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.add('btn-primary-custom');
                btn.classList.remove('btn-success');
                btn.disabled = false;
            }, 2000);
        } else {
            alert('Failed to save settings: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        alert('An error occurred.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});
</script>

<?php include 'includes/footer.php'; ?>
