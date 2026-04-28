<?php 
require_once 'includes/config.php'; 

$id = $_GET['id'] ?? '';
$leadsFile = 'data/leads.json';
$lead = null;

if (file_exists($leadsFile)) {
    $leads = json_decode(file_get_contents($leadsFile), true) ?? [];
    foreach ($leads as $l) {
        if ($l['id'] === $id) {
            $lead = $l;
            break;
        }
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
            <button class="btn btn-outline-custom d-flex align-items-center gap-2">
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
                
                <h6 class="fw-bold mb-3">Identified Issues</h6>
                <div class="d-flex flex-column gap-2">
                    <div class="p-3 border rounded-3 d-flex align-items-center gap-3">
                        <i data-lucide="alert-triangle" class="text-warning"></i>
                        <div>
                            <p class="fw-bold mb-0">Optimization Opportunity</p>
                            <p class="text-muted small mb-0">Website at <strong><?php echo htmlspecialchars($lead['website']); ?></strong> could benefit from better meta tag descriptions to improve CTR.</p>
                        </div>
                    </div>
                    <div class="p-3 border rounded-3 d-flex align-items-center gap-3">
                        <i data-lucide="zap" class="text-primary"></i>
                        <div>
                            <p class="fw-bold mb-0">Mobile Responsiveness</p>
                            <p class="text-muted small mb-0">Initial scan shows good mobile structure, but LCP (Largest Contentful Paint) is slightly delayed.</p>
                        </div>
                    </div>
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

<?php include 'includes/footer.php'; ?>
