<?php 
require_once 'includes/config.php'; 
$pageTitle = 'Gmail API Configuration';
include 'includes/header.php'; 
?>

<div class="mb-4">
    <a href="settings.php" class="text-decoration-none text-muted small fw-bold mb-3 d-inline-flex align-items-center gap-1">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i> Back to Settings
    </a>
    <h2 class="fw-bold text-dark mb-1">Gmail / Google Workspace</h2>
    <p class="text-muted mb-0">Configure your Google OAuth credentials to send outreach emails.</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="premium-card">
            <div class="card-header-custom border-bottom pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-danger bg-opacity-10 shadow-sm p-2 rounded-3 text-danger"><i data-lucide="mail" style="width: 24px; height: 24px;"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">Google OAuth Configuration</h5>
                        <span id="statusBadge" class="badge <?php echo $_SESSION['gmail_connected'] ? 'badge-soft-success' : 'badge-soft-danger'; ?> rounded-pill mt-1">
                            Status: <?php echo $_SESSION['gmail_connected'] ? 'Connected' : 'Disconnected'; ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-4">Set up Google Cloud OAuth credentials to allow the platform to send emails on your behalf securely.</p>
                
                <form action="actions/api-handler.php" method="POST">
                    <input type="hidden" name="api" value="gmail">
                    <input type="hidden" name="action" value="connect">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Google Client ID</label>
                        <input type="text" name="client_id" class="form-control form-control-custom font-monospace" value="<?php echo $_SESSION['gmail_connected'] ? '123456-abcde.apps.googleusercontent.com' : ''; ?>" placeholder="e.g. 123456-abcde.apps.googleusercontent.com">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Google Client Secret</label>
                        <input type="password" name="client_secret" class="form-control form-control-custom font-monospace" value="<?php echo $_SESSION['gmail_connected'] ? 'GOCSPX-xxxxxxxxxxxx' : ''; ?>" placeholder="Enter Client Secret">
                    </div>

                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold small text-dark mb-1">OAuth Redirect URI</label>
                        <p class="text-muted small mb-2">Copy and paste this into your Google Cloud Console.</p>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-custom font-monospace text-muted bg-white" value="https://app.auditai.com/oauth/google/callback" readonly id="redirectUri">
                            <button class="btn btn-outline-secondary bg-white" type="button" onclick="navigator.clipboard.writeText(document.getElementById('redirectUri').value); showToast('Copied to clipboard!', 'success');"><i data-lucide="copy" style="width: 16px; height: 16px;"></i></button>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-2 border-top">
                        <button type="submit" class="btn btn-primary-custom flex-grow-1">Save & Connect</button>
                        <?php if ($_SESSION['gmail_connected']): ?>
                        <a href="actions/api-handler.php?action=disconnect&api=gmail" class="btn btn-outline-danger">Disconnect Account</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="premium-card mb-4">
            <div class="card-header-custom border-bottom pb-2">
                <h6 class="fw-bold mb-0">Account Info</h6>
            </div>
            <div class="card-body-custom">
                <?php if ($_SESSION['gmail_connected']): ?>
                <div id="accountInfo">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle bg-light d-flex justify-content-center align-items-center fw-bold text-dark" style="width: 48px; height: 48px;">G</div>
                        <div>
                            <p class="mb-0 text-uppercase text-muted small fw-bold" style="font-size: 10px;">Connected Account</p>
                            <p id="accountEmail" class="fw-bold text-dark mb-0">growth@stripe.com</p>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold small text-dark">Daily Sending Quota</span>
                            <span class="text-muted small">456 / 500</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-danger" style="width: 91%"></div>
                        </div>
                        <p class="text-muted small mt-2 mb-0">91% of your daily limit reached.</p>
                    </div>
                </div>
                <?php else: ?>
                <div id="noAccountInfo" class="text-center py-4">
                    <p class="text-muted small">No account connected.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
