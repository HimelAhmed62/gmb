<?php 
require_once 'includes/config.php'; 
$pageTitle = 'WhatsApp API Configuration';
include 'includes/header.php'; 
?>

<div class="mb-4">
    <a href="settings.php" class="text-decoration-none text-muted small fw-bold mb-3 d-inline-flex align-items-center gap-1">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i> Back to Settings
    </a>
    <h2 class="fw-bold text-dark mb-1">WhatsApp Business Cloud API</h2>
    <p class="text-muted mb-0">Configure your WhatsApp integration to send automated messages.</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="premium-card">
            <div class="card-header-custom border-bottom pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 shadow-sm p-2 rounded-3 text-success"><i data-lucide="message-circle" style="width: 24px; height: 24px;"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">WhatsApp Configuration</h5>
                        <span id="statusBadge" class="badge <?php echo ($_SESSION['whatsapp_connected'] ?? false) ? 'badge-soft-success' : 'badge-soft-danger'; ?> rounded-pill mt-1">
                            Status: <?php echo ($_SESSION['whatsapp_connected'] ?? false) ? 'Active' : 'Disconnected'; ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-4">Required to send automated template messages and replies directly to leads via WhatsApp.</p>
                
                <form action="actions/api-handler.php" method="POST">
                    <input type="hidden" name="api" value="whatsapp">
                    <input type="hidden" name="action" value="connect">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">System User Access Token (Permanent)</label>
                        <input type="password" name="access_token" class="form-control form-control-custom font-monospace" value="<?php echo ($_SESSION['whatsapp_connected'] ?? false) ? 'EAABxxxxxxxxxxxxxxxxxxxxxxx' : ''; ?>" placeholder="Enter Access Token">
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Phone Number ID</label>
                            <input type="text" name="phone_id" class="form-control form-control-custom font-monospace" value="<?php echo ($_SESSION['whatsapp_connected'] ?? false) ? '123456789012345' : ''; ?>" placeholder="e.g. 123456789012345">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">WABA ID</label>
                            <input type="text" name="waba_id" class="form-control form-control-custom font-monospace" value="<?php echo ($_SESSION['whatsapp_connected'] ?? false) ? '987654321098765' : ''; ?>" placeholder="WhatsApp Business Account ID">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">Webhook Verify Token</label>
                        <input type="text" name="verify_token" class="form-control form-control-custom" value="audit_ai_secure_token_2026">
                    </div>

                    <div class="d-flex gap-2 pt-2 border-top">
                        <button type="submit" class="btn btn-primary-custom flex-grow-1">Save Configuration</button>
                        <?php if ($_SESSION['whatsapp_connected'] ?? false): ?>
                        <a href="actions/api-handler.php?action=disconnect&api=whatsapp" class="btn btn-outline-danger">Disconnect Account</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="premium-card">
            <div class="card-header-custom border-bottom pb-2">
                <h6 class="fw-bold mb-0">Platform Info</h6>
            </div>
            <?php if ($_SESSION['whatsapp_connected'] ?? false): ?>
            <div class="card-body-custom" id="platformInfo">
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <p class="text-uppercase text-muted small fw-bold mb-1" style="font-size: 10px;">Message Templates</p>
                    <h4 class="fw-bold text-dark mb-0">12</h4>
                    <p class="text-muted small mb-0 mt-1">Verified templates ready to use.</p>
                </div>
                <div class="p-3 bg-light rounded-3 border">
                    <p class="text-uppercase text-muted small fw-bold mb-1" style="font-size: 10px;">Quality Rating</p>
                    <h4 class="fw-bold text-success mb-0">High</h4>
                    <p class="text-muted small mb-0 mt-1">Status: Green</p>
                </div>
            </div>
            <?php else: ?>
            <div class="card-body-custom" id="noPlatformInfo">
                <p class="text-muted small text-center py-4">API not configured.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
