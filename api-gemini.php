<?php 
require_once 'includes/config.php'; 

$leadsFile = 'data/leads.json';
$processedCount = 0;
if (file_exists($leadsFile)) {
    $leads = json_decode(file_get_contents($leadsFile), true) ?? [];
    foreach ($leads as $lead) {
        if (isset($lead['status']) && !in_array($lead['status'], ['Pending', 'Preparing'])) {
            $processedCount++;
        }
    }
}

// Simulated Token Calculation: ~1,500 tokens per audit
$tokensPerLead = 1500;
$totalTokensUsed = $processedCount * $tokensPerLead;
$monthlyLimit = 5000000; // 5M Free tier limit
$usagePercent = ($totalTokensUsed / $monthlyLimit) * 100;

$pageTitle = 'Gemini API Configuration';
include 'includes/header.php'; 
?>

<div class="mb-4">
    <a href="settings.php" class="text-decoration-none text-muted small fw-bold mb-3 d-inline-flex align-items-center gap-1">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i> Back to Settings
    </a>
    <h2 class="fw-bold text-dark mb-1">Google Gemini API</h2>
    <p class="text-muted mb-0">Configure your AI model settings for auditing and content generation.</p>
</div>

<?php if (!($_SESSION['gemini_connected'] ?? false)): ?>
<div class="alert alert-info border-0 shadow-sm rounded-4 p-4 mb-4 d-flex align-items-center gap-4">
    <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
        <i data-lucide="info" style="width: 24px; height: 24px;"></i>
    </div>
    <div>
        <h6 class="fw-bold mb-1">Localhost Connection Issues?</h6>
        <p class="text-muted small mb-3">If you are having trouble connecting the real API on localhost due to SSL or Network errors, you can enable <strong>Demo Mode</strong> to test the platform features with simulated AI data.</p>
        <a href="actions/api-handler.php?action=demo&api=gemini" class="btn btn-info btn-sm text-white fw-bold px-4 rounded-pill">Enable Demo Mode</a>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="premium-card">
            <div class="card-header-custom d-flex justify-content-between align-items-center border-bottom pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 shadow-sm p-2 rounded-3" style="color: #4285F4;"><i data-lucide="sparkles" style="width: 24px; height: 24px;"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">Gemini Configuration</h5>
                        <span id="statusBadge" class="badge <?php echo ($_SESSION['gemini_connected'] ?? false) ? 'badge-soft-success' : 'badge-soft-danger'; ?> rounded-pill mt-1">
                            Status: <?php echo ($_SESSION['gemini_connected'] ?? false) ? 'Connected' : 'Not Connected'; ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-4">Required for AI website auditing, sentiment analysis, and generating personalized outreach emails.</p>
                
                <form action="actions/api-handler.php" method="POST">
                    <input type="hidden" name="api" value="gemini">
                    <input type="hidden" name="action" value="connect">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Gemini API Key</label>
                        <div class="input-group">
                            <input type="password" name="api_key" class="form-control form-control-custom font-monospace" value="<?php echo ($_SESSION['gemini_connected'] ?? false) ? 'AIzaSyB-xxxxxxxxxxxxxxxxxxxxxxx' : ''; ?>" placeholder="Enter your API key">
                            <button class="btn btn-outline-custom border-start-0 text-primary" type="button" onclick="const p = this.previousElementSibling; p.type = p.type === 'password' ? 'text' : 'password';"><i data-lucide="eye" style="width: 18px; height: 18px;"></i></button>
                        </div>
                        <div class="form-text small">Get your API key from <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-decoration-none fw-bold">Google AI Studio</a>.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">AI Model Selection</label>
                        <select name="model" class="form-select form-control-custom fw-medium" <?php echo !($_SESSION['gemini_connected'] ?? false) ? 'disabled' : ''; ?>>
                            <?php if ($_SESSION['gemini_model'] ?? false): ?>
                                <option value="<?php echo $_SESSION['gemini_model']; ?>" selected><?php echo $_SESSION['gemini_model']; ?></option>
                            <?php else: ?>
                                <option value="">Test Connection to load models...</option>
                            <?php endif; ?>
                        </select>
                        <div class="form-text extra-small text-muted mt-1"><i data-lucide="info" style="width: 12px; height: 12px;"></i> Models are fetched dynamically from Google AI Studio.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">Research Instructions (Custom Prompt)</label>
                        <textarea name="research_instructions" class="form-control form-control-custom small" rows="4" placeholder="Example: Analyze the website's SEO, loading speed, and mobile responsiveness. Provide specific suggestions..."><?php echo $_SESSION['gemini_prompt'] ?? ''; ?></textarea>
                        <div class="form-text extra-small text-muted mt-1"><i data-lucide="info" style="width: 12px; height: 12px;"></i> This prompt will be used to guide the AI during the website audit.</div>
                    </div>

                    <div class="d-flex gap-2 pt-2 border-top">
                        <button type="button" id="testConnectionBtn" class="btn btn-outline-custom">Test Connection</button>
                        <button type="submit" class="btn btn-primary-custom flex-grow-1">Save & Connect</button>
                        <?php if ($_SESSION['gemini_connected'] ?? false): ?>
                        <a href="actions/api-handler.php?action=disconnect&api=gemini" class="btn btn-outline-danger">Disconnect</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="premium-card">
            <div class="card-header-custom border-bottom pb-2">
                <h6 class="fw-bold mb-0">API Usage</h6>
            </div>
            <?php if ($_SESSION['gemini_connected'] ?? false): ?>
            <div class="card-body-custom" id="usageInfo">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-bold small text-dark">Tokens Used</span>
                        <span class="text-muted small"><?php echo number_format($totalTokensUsed / 1000000, 2); ?>M / <?php echo number_format($monthlyLimit / 1000000, 0); ?>M</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: <?php echo max(5, $usagePercent); ?>%"></div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <span class="text-muted extra-small">Total Audits</span>
                        <span class="fw-bold small text-primary"><?php echo $processedCount; ?> Completed</span>
                    </div>
                    <p class="text-muted extra-small mt-2 mb-0">Based on processed leads in your database.</p>
                </div>
            </div>
            <?php else: ?>
            <div class="card-body-custom" id="noUsageInfo">
                <p class="text-muted small text-center py-4">API not connected.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Live Chat Test -->
        <div class="premium-card mt-4" id="chatTestCard" style="display: <?php echo ($_SESSION['gemini_connected'] ?? false) ? 'block' : 'none'; ?>;">
            <div class="card-header-custom border-bottom pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-2 text-primary"><i data-lucide="messages-square" style="width: 18px; height: 18px;"></i></div>
                    <h6 class="fw-bold mb-0">Live Test Chat</h6>
                </div>
            </div>
            <div class="card-body-custom">
                <div id="chatWindow" class="bg-light rounded-3 p-3 mb-3 font-monospace small text-muted" style="height: 180px; overflow-y: auto; border: 1px inset rgba(0,0,0,0.05);">
                    <div class="mb-2">System: Gemini ready. Send a message to test.</div>
                </div>
                <div class="input-group">
                    <input type="text" id="chatInput" class="form-control form-control-custom" placeholder="Say hi...">
                    <button class="btn btn-primary-custom" type="button" id="sendChatBtn">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById('testConnectionBtn').addEventListener('click', function() {
    const apiKeyInput = document.querySelector('input[name="api_key"]');
    const apiKey = apiKeyInput.value.trim();
    
    if (!apiKey) {
        showToast('Please enter your Gemini API key.', 'warning');
        return;
    }

    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Testing...';

    const formData = new URLSearchParams();
    formData.append('api', 'gemini');
    formData.append('action', 'verify');
    formData.append('api_key', apiKey);

    fetch('actions/api-handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
    })
    .then(async response => {
        const text = await response.text();
        try { return JSON.parse(text); } catch (e) { throw new Error('Invalid server response'); }
    })
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (data.success) {
            showToast('Success! Gemini models loaded.', 'success');
            
            // Populate models dynamically
            const modelSelect = document.querySelector('select[name="model"]');
            if (data.models && data.models.length > 0) {
                modelSelect.innerHTML = '';
                data.models.forEach(m => {
                    const option = document.createElement('option');
                    option.value = m;
                    option.innerText = m;
                    if (m === 'gemini-1.5-flash') option.selected = true;
                    modelSelect.appendChild(option);
                });
            }
            
            document.getElementById('chatTestCard').style.display = 'block';
        } else {
            showToast('Failed: ' + data.message, 'danger');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        showToast('Connection error: ' + err.message, 'danger');
    });
});

// Gemini Chat Test Logic
document.getElementById('sendChatBtn').addEventListener('click', function() {
    const chatInput = document.getElementById('chatInput');
    const chatWindow = document.getElementById('chatWindow');
    const apiKey = document.querySelector('input[name="api_key"]').value.trim();
    const model = document.querySelector('select[name="model"]').value || 'gemini-1.5-flash';
    const msg = chatInput.value.trim();

    if (!msg) return;

    chatWindow.innerHTML += `<div class="mb-2 text-dark"><strong>You:</strong> ${msg}</div>`;
    chatInput.value = '';
    chatWindow.scrollTop = chatWindow.scrollHeight;

    const originalBtn = this.innerHTML;
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    const formData = new URLSearchParams();
    formData.append('api', 'gemini');
    formData.append('action', 'chat');
    formData.append('api_key', apiKey);
    formData.append('model', model);
    formData.append('message', msg);

    fetch('actions/api-handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
    })
    .then(async response => {
        const text = await response.text();
        try { return JSON.parse(text); } catch(e) { throw new Error(text); }
    })
    .then(result => {
        this.disabled = false;
        this.innerHTML = originalBtn;
        if (result.success) {
            chatWindow.innerHTML += `<div class="mb-2 text-primary"><strong>Gemini:</strong> ${result.response}</div>`;
        } else {
            chatWindow.innerHTML += `<div class="mb-2 text-danger"><strong>Error:</strong> ${result.message}</div>`;
        }
        chatWindow.scrollTop = chatWindow.scrollHeight;
    })
    .catch(error => {
        this.disabled = false;
        this.innerHTML = originalBtn;
        chatWindow.innerHTML += `<div class="mb-2 text-danger"><strong>System Error:</strong> ${error.message}</div>`;
        chatWindow.scrollTop = chatWindow.scrollHeight;
    });
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
    if (window.lucide) lucide.createIcons();
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        toast.style.transition = 'all 0.5s ease';
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}
</script>
