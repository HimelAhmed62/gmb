<?php 
require_once 'includes/config.php'; 
$pageTitle = 'Settings';
include 'includes/header.php'; 

$settings = [
    'daily_limit' => 500,
    'delay_between_messages' => 45,
    'email_notifications' => true,
    'whatsapp_notifications' => true,
    'browser_notifications' => true,
    'low_credit_alert' => true
];

// Load settings from database
try {
    $dbSettings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($dbSettings as $key => $val) {
        // Convert to appropriate types
        if (in_array($key, ['email_notifications', 'whatsapp_notifications', 'browser_notifications', 'low_credit_alert'])) {
            $settings[$key] = (bool)$val;
        } else {
            $settings[$key] = $val;
        }
    }
} catch (Exception $e) {
    // Fallback to defaults
}
?>

<div class="mb-4">
    <h2 class="fw-bold text-dark mb-1">Settings</h2>
    <p class="text-muted mb-0">Configure your API connections and outreach limits.</p>
</div>

<div class="row g-4">
    <div class="col-lg-3">
        <div class="d-flex flex-column gap-2" id="settingsTabs">
            <button class="settings-nav-btn active" data-tab="api">
                <i data-lucide="key" style="width: 18px; height: 18px;"></i> API Connections
            </button>
            <button class="settings-nav-btn" data-tab="limits">
                <i data-lucide="settings" style="width: 18px; height: 18px;"></i> Platform Limits
            </button>
            <button class="settings-nav-btn" data-tab="notifications">
                <i data-lucide="bell" style="width: 18px; height: 18px;"></i> Notifications
            </button>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="tab-content-wrapper">
            <!-- API Connections Tab -->
            <div class="settings-tab-pane active" id="tab-api">
                <div class="premium-card">
                    <div class="card-header-custom">
                        <h5 class="fw-bold mb-1">API Connections</h5>
                        <p class="text-muted small mb-0">Manage your outreach channel connections.</p>
                    </div>
                    <div class="card-body-custom d-flex flex-column gap-3">
                        <!-- Gmail -->
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-white shadow-sm p-2 rounded-3 text-danger"><i data-lucide="mail"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Gmail / Google Workspace</h6>
                                    <?php if ($_SESSION['gmail_connected'] ?? false): ?>
                                    <p class="mb-0 small text-success fw-bold d-flex align-items-center gap-1"><i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i> Connected as growth@stripe.com</p>
                                    <?php else: ?>
                                    <p class="mb-0 small text-muted">Not connected</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="api-gmail.php" class="btn btn-outline-custom btn-sm"><?php echo ($_SESSION['gmail_connected'] ?? false) ? 'Manage API' : 'Connect'; ?></a>
                        </div>

                        <!-- WhatsApp -->
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-white shadow-sm p-2 rounded-3 text-success"><i data-lucide="message-circle"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">WhatsApp Business API</h6>
                                    <?php if ($_SESSION['whatsapp_connected'] ?? false): ?>
                                    <p class="mb-0 small text-success fw-bold d-flex align-items-center gap-1"><i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i> Active</p>
                                    <?php else: ?>
                                    <p class="mb-0 small text-muted">Not active</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="api-whatsapp.php" class="btn btn-outline-custom btn-sm"><?php echo ($_SESSION['whatsapp_connected'] ?? false) ? 'Manage API' : 'Connect'; ?></a>
                        </div>

                        <!-- Gemini -->
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-white shadow-sm p-2 rounded-3" style="color: #4285F4;"><i data-lucide="sparkles"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Google Gemini API</h6>
                                    <?php if ($_SESSION['gemini_connected'] ?? false): ?>
                                    <p class="mb-0 small text-success fw-bold d-flex align-items-center gap-1"><i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i> Active</p>
                                    <?php else: ?>
                                    <p class="mb-0 small text-muted">Used for AI website auditing and email generation.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="api-gemini.php" class="btn <?php echo ($_SESSION['gemini_connected'] ?? false) ? 'btn-outline-custom' : 'btn-primary-custom'; ?> btn-sm px-3"><?php echo ($_SESSION['gemini_connected'] ?? false) ? 'Manage API' : 'Connect'; ?></a>
                        </div>

                        <!-- ChatGPT -->
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-white shadow-sm p-2 rounded-3" style="color: #10a37f;"><i data-lucide="brain"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">OpenAI ChatGPT API</h6>
                                    <?php if ($_SESSION['chatgpt_connected'] ?? false): ?>
                                    <p class="mb-0 small text-success fw-bold d-flex align-items-center gap-1"><i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i> Active</p>
                                    <?php else: ?>
                                    <p class="mb-0 small text-muted">High-precision analysis and personalized content.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="api-chatgpt.php" class="btn <?php echo ($_SESSION['chatgpt_connected'] ?? false) ? 'btn-outline-custom' : 'btn-primary-custom'; ?> btn-sm px-3"><?php echo ($_SESSION['chatgpt_connected'] ?? false) ? 'Manage API' : 'Connect'; ?></a>
                        </div>
                        
                        <!-- Manual JS Engine -->
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-white shadow-sm p-2 rounded-3 text-warning"><i data-lucide="code-2"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Manual JS Engine</h6>
                                    <p class="mb-0 small text-success fw-bold d-flex align-items-center gap-1"><i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i> Active (Unlimited & Free)</p>
                                </div>
                            </div>
                            <a href="api-manual.php" class="btn btn-outline-custom btn-sm px-3">Manage Script</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Platform Limits Tab -->
            <div class="settings-tab-pane d-none" id="tab-limits">
                <div class="premium-card">
                    <div class="card-header-custom">
                        <h5 class="fw-bold mb-1">Platform Limits</h5>
                        <p class="text-muted small mb-0">Set daily limits and anti-spam delays.</p>
                    </div>
                    <div class="card-body-custom">
                        <form id="limitsForm">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Daily Sending Limit</label>
                                    <div class="input-group">
                                        <input type="number" name="daily_limit" class="form-control form-control-custom fw-bold" value="<?php echo htmlspecialchars($settings['daily_limit']); ?>">
                                        <span class="input-group-text bg-light border-light text-muted small fw-bold" style="border-radius: 0 12px 12px 0;">emails/day</span>
                                    </div>
                                    <p class="text-muted small mt-2 mb-0 d-flex align-items-center gap-1"><i data-lucide="alert-circle" style="width: 14px; height: 14px;"></i> Recommended: < 500 for Gmail</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Delay Between Messages</label>
                                    <div class="input-group">
                                        <input type="number" name="delay_between_messages" class="form-control form-control-custom fw-bold" value="<?php echo htmlspecialchars($settings['delay_between_messages']); ?>">
                                        <span class="input-group-text bg-light border-light text-muted small fw-bold" style="border-radius: 0 12px 12px 0;">seconds</span>
                                    </div>
                                    <p class="text-muted small mt-2 mb-0 d-flex align-items-center gap-1"><i data-lucide="clock" style="width: 14px; height: 14px;"></i> Higher delay reduces spam flags</p>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary-custom px-4">Save Platform Limits</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Notifications Tab -->
            <div class="settings-tab-pane d-none" id="tab-notifications">
                <div class="premium-card">
                    <div class="card-header-custom">
                        <h5 class="fw-bold mb-1">Notifications Settings</h5>
                        <p class="text-muted small mb-0">Choose how you want to be alerted.</p>
                    </div>
                    <div class="card-body-custom">
                        <form id="notificationsForm">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex align-items-center justify-content-between p-3 border rounded-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle"><i data-lucide="mail" style="width: 20px; height: 20px;"></i></div>
                                        <div>
                                            <h6 class="fw-bold mb-0">Email Notifications</h6>
                                            <p class="text-muted small mb-0">Get daily reports and critical alerts via email.</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="email_notifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between p-3 border rounded-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-success bg-opacity-10 text-success p-2 rounded-circle"><i data-lucide="message-circle" style="width: 20px; height: 20px;"></i></div>
                                        <div>
                                            <h6 class="fw-bold mb-0">WhatsApp Alerts</h6>
                                            <p class="text-muted small mb-0">Receive instant updates on your connected WhatsApp.</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="whatsapp_notifications" <?php echo $settings['whatsapp_notifications'] ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between p-3 border rounded-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-info bg-opacity-10 text-info p-2 rounded-circle"><i data-lucide="monitor" style="width: 20px; height: 20px;"></i></div>
                                        <div>
                                            <h6 class="fw-bold mb-0">Browser Notifications</h6>
                                            <p class="text-muted small mb-0">Show real-time alerts in your web browser.</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="browser_notifications" <?php echo $settings['browser_notifications'] ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between p-3 border rounded-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-circle"><i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i></div>
                                        <div>
                                            <h6 class="fw-bold mb-0">Low Credit Alerts</h6>
                                            <p class="text-muted small mb-0">Notify me when API credits are running low.</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="low_credit_alert" <?php echo $settings['low_credit_alert'] ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary-custom px-4">Save Notification Preferences</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.settings-nav-btn');
    const panes = document.querySelectorAll('.settings-tab-pane');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabId = tab.getAttribute('data-tab');
            
            // Update active button
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Show corresponding pane
            panes.forEach(pane => {
                if (pane.id === `tab-${tabId}`) {
                    pane.classList.remove('d-none');
                    pane.classList.add('active');
                } else {
                    pane.classList.add('d-none');
                    pane.classList.remove('active');
                }
            });
        });
    });

    // Forms handling
    const handleFormSubmit = (e) => {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);
        const data = {};
        
        // Handle checkboxes/switches specifically
        form.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            data[checkbox.name] = checkbox.checked;
        });
        
        // Handle other inputs and textareas
        form.querySelectorAll('input:not([type="checkbox"]), textarea').forEach(input => {
            data[input.name] = input.value;
        });

        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';

        fetch('actions/settings-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            if (result.success) {
                showToast(result.message, 'success');
            } else {
                showToast(result.message, 'danger');
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            showToast('An error occurred while saving.', 'danger');
        });
    };

    document.getElementById('limitsForm').addEventListener('submit', handleFormSubmit);
    document.getElementById('notificationsForm').addEventListener('submit', handleFormSubmit);
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

<style>
.settings-nav-btn {
    text-align: left;
    padding: 12px 16px;
    border-radius: 12px;
    border: none;
    background: transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.2s ease;
}

.settings-nav-btn:hover {
    background: rgba(var(--bs-primary-rgb), 0.05);
    color: var(--bs-primary);
}

.settings-nav-btn.active {
    background: var(--bs-primary);
    color: white;
}

.settings-tab-pane {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<?php include 'includes/footer.php'; ?>
