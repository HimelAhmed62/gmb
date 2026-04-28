<?php 
require_once 'includes/config.php'; 
$pageTitle = 'ChatGPT API Configuration';
include 'includes/header.php'; 
?>

<div class="mb-4">
    <a href="settings.php" class="text-decoration-none text-muted small fw-bold mb-3 d-inline-flex align-items-center gap-1">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i> Back to Settings
    </a>
    <h2 class="fw-bold text-dark mb-1">OpenAI ChatGPT API</h2>
    <p class="text-muted mb-0">Configure OpenAI models for precise website analysis and content generation.</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="premium-card">
            <div class="card-header-custom d-flex justify-content-between align-items-center border-bottom pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 shadow-sm p-2 rounded-3" style="color: #10a37f;"><i data-lucide="brain" style="width: 24px; height: 24px;"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">ChatGPT Configuration</h5>
                        <span id="statusBadge" class="badge <?php echo $_SESSION['chatgpt_connected'] ? 'badge-soft-success' : 'badge-soft-danger'; ?> rounded-pill mt-1">
                            Status: <?php echo $_SESSION['chatgpt_connected'] ? 'Connected' : 'Not Connected'; ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-4">Integrate GPT-4 or GPT-3.5 Turbo for high-precision auditing and personalized communication.</p>
                
                <form action="actions/api-handler.php" method="POST">
                    <input type="hidden" name="api" value="chatgpt">
                    <input type="hidden" name="action" value="connect">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">OpenAI API Key</label>
                        <div class="input-group">
                            <input type="password" name="api_key" class="form-control form-control-custom font-monospace" value="<?php echo $_SESSION['chatgpt_connected'] ? 'sk-proj-xxxxxxxxxxxxxxxxxxxxxxx' : ''; ?>" placeholder="sk-...">
                            <button class="btn btn-outline-custom border-start-0 text-primary" type="button" onclick="const p = this.previousElementSibling; p.type = p.type === 'password' ? 'text' : 'password';"><i data-lucide="eye" style="width: 18px; height: 18px;"></i></button>
                        </div>
                        <div class="form-text small">Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank" class="text-decoration-none fw-bold">OpenAI Dashboard</a>.</div>
                    </div>
                    
                    <div class="mb-4" id="modelSelectionWrapper">
                        <label class="form-label fw-bold small text-muted">Model Selection</label>
                        <select name="model" class="form-select form-control-custom fw-medium" disabled>
                            <option value="">Test API to load available models...</option>
                        </select>
                        <div id="modelLoadStatus" class="form-text extra-small text-muted mt-1"><i data-lucide="info" style="width: 12px; height: 12px;"></i> Models are fetched dynamically from your OpenAI account.</div>
                    </div>

                    <div class="d-flex gap-2 pt-2 border-top">
                        <button type="button" id="testChatBtn" class="btn btn-outline-custom">Test API</button>
                        <button type="submit" class="btn btn-primary-custom flex-grow-1">Save & Connect</button>
                        <?php if ($_SESSION['chatgpt_connected']): ?>
                        <a href="actions/api-handler.php?action=disconnect&api=chatgpt" class="btn btn-outline-danger">Disconnect</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="premium-card">
            <div class="card-header-custom border-bottom pb-2">
                <h6 class="fw-bold mb-0">Usage Limits</h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-0">OpenAI usage is billed based on your account's credit balance. Ensure your billing is active at <a href="https://platform.openai.com/billing" target="_blank" class="text-decoration-none">OpenAI Billing</a>.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('testChatBtn').addEventListener('click', function() {
    const apiKeyInput = document.querySelector('input[name="api_key"]');
    const apiKey = apiKeyInput.value.trim();
    
    if (!apiKey) {
        showToast('Please enter an API key.', 'warning');
        return;
    }

    const originalText = this.innerHTML;
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Testing...';

    const formData = new FormData();
    formData.append('api', 'chatgpt');
    formData.append('action', 'verify');
    formData.append('api_key', apiKey);

    fetch('actions/api-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        this.disabled = false;
        this.innerHTML = originalText;
        
        const badge = document.getElementById('statusBadge');
        const modelSelect = document.querySelector('select[name="model"]');

        if (result.success) {
            showToast(result.message, 'success');
            badge.innerText = 'Status: Connected';
            badge.className = 'badge badge-soft-success rounded-pill mt-1';

            // Populate Models
            if (result.models && result.models.length > 0) {
                modelSelect.innerHTML = '';
                modelSelect.disabled = false;
                result.models.forEach(m => {
                    const option = document.createElement('option');
                    option.value = m;
                    option.innerText = m;
                    if (m === 'gpt-4o' || m === 'gpt-4-turbo') option.selected = true;
                    modelSelect.appendChild(option);
                });
                document.getElementById('modelLoadStatus').innerHTML = `<i data-lucide="check" class="text-success" style="width: 12px; height: 12px;"></i> ${result.models.length} models loaded successfully.`;
                showToast('Available models loaded from API.', 'info');
                lucide.createIcons();
            }
        } else {
            showToast(result.message, 'danger');
            badge.innerText = 'Status: Error / Not Connected';
            badge.className = 'badge badge-soft-danger rounded-pill mt-1';
        }
    })
    .catch(error => {
        this.disabled = false;
        this.innerHTML = originalText;
        showToast('Connection failed. Please check your internet or API key.', 'danger');
    });
});

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-custom show bg-white border border-${type} p-3 rounded-3 shadow-lg d-flex align-items-center gap-3`;
    toast.style.position = 'fixed'; toast.style.bottom = '24px'; toast.style.right = '24px'; toast.style.zIndex = '9999';
    toast.innerHTML = `<p class="mb-0 fw-bold small text-dark">${message}</p>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}
</script>

<?php include 'includes/footer.php'; ?>
