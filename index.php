<?php 
require_once 'includes/config.php'; 
$pageTitle = 'Dashboard';
include 'includes/header.php'; 

// Fetch Live Statistics from MySQL
try {
    // Total Leads
    $totalLeads = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
    
    // Processed Leads (status not pending)
    $processedLeads = $pdo->query("SELECT COUNT(*) FROM leads WHERE status != 'Pending'")->fetchColumn();
    
    // Qualified Leads
    $qualifiedLeads = $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'Qualified'")->fetchColumn();
    
    // Recent Leads (Last 5)
    $recentLeads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5")->fetchAll();
    
} catch (Exception $e) {
    $totalLeads = 0;
    $processedLeads = 0;
    $qualifiedLeads = 0;
    $recentLeads = [];
}
?>

<div class="mb-4">
    <h2 class="fw-bold text-dark mb-1">Dashboard</h2>
    <p class="text-muted mb-0">Welcome back, here's what's happening with your leads today.</p>
</div>

<!-- Stats Grid -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="premium-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted fw-medium mb-1 small">Total Leads</p>
                    <h3 class="fw-bold text-dark mb-0"><?php echo number_format($totalLeads); ?></h3>
                </div>
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i data-lucide="users"></i>
                </div>
            </div>
            <div class="mt-3 d-flex align-items-center gap-2">
                <span class="badge badge-soft-success rounded-pill px-2 py-1"><i data-lucide="trending-up" style="width: 12px; height: 12px;"></i> 100%</span>
                <span class="text-muted small fw-medium">lifetime</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="premium-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted fw-medium mb-1 small">Processed</p>
                    <h3 class="fw-bold text-dark mb-0"><?php echo number_format($processedLeads); ?></h3>
                </div>
                <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i data-lucide="check-circle-2"></i>
                </div>
            </div>
            <div class="mt-3 d-flex align-items-center gap-2">
                <span class="badge badge-soft-success rounded-pill px-2 py-1"><i data-lucide="trending-up" style="width: 12px; height: 12px;"></i> <?php echo $totalLeads > 0 ? round(($processedLeads / $totalLeads) * 100) : 0; ?>%</span>
                <span class="text-muted small fw-medium">completion</span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="premium-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted fw-medium mb-1 small">Qualified</p>
                    <h3 class="fw-bold text-dark mb-0"><?php echo number_format($qualifiedLeads); ?></h3>
                </div>
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i data-lucide="alert-circle"></i>
                </div>
            </div>
            <div class="mt-3 d-flex align-items-center gap-2">
                <span class="badge badge-soft-success rounded-pill px-2 py-1"><i data-lucide="trending-up" style="width: 12px; height: 12px;"></i> <?php echo $processedLeads > 0 ? round(($qualifiedLeads / $processedLeads) * 100) : 0; ?>%</span>
                <span class="text-muted small fw-medium">conversion</span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="premium-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted fw-medium mb-1 small">Emails Sent</p>
                    <h3 class="fw-bold text-dark mb-0">0</h3>
                </div>
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i data-lucide="mail"></i>
                </div>
            </div>
            <div class="mt-3 d-flex align-items-center gap-2">
                <span class="badge badge-soft-danger rounded-pill px-2 py-1"><i data-lucide="trending-down" style="width: 12px; height: 12px;"></i> 0%</span>
                <span class="text-muted small fw-medium">vs last month</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Leads Table -->
    <div class="col-lg-8">
        <div class="premium-card h-100">
            <div class="card-header-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1">Recent Leads</h5>
                        <p class="text-muted small mb-0">The latest companies added to your pipeline.</p>
                    </div>
                    <a href="upload.php" class="btn btn-primary-custom btn-sm">Add Leads</a>
                </div>
            </div>
            <div class="card-body-custom p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-uppercase text-muted small fw-bold py-3 ps-4">Company</th>
                                <th class="text-uppercase text-muted small fw-bold py-3">Score</th>
                                <th class="text-uppercase text-muted small fw-bold py-3">Status</th>
                                <th class="text-uppercase text-muted small fw-bold py-3 text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentLeads)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">No recent leads found.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($recentLeads as $lead): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($lead['company_name']); ?></div>
                                        <a href="<?php echo htmlspecialchars($lead['website']); ?>" target="_blank" class="text-decoration-none small d-flex align-items-center gap-1">
                                            <?php 
                                            $displayUrl = preg_replace('/^https?:\/\/(www\.)?/', '', $lead['website']);
                                            echo htmlspecialchars(strlen($displayUrl) > 25 ? substr($displayUrl, 0, 22) . '...' : $displayUrl); 
                                            ?> 
                                            <i data-lucide="external-link" style="width: 12px; height: 12px;"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
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
                                        <a href="details.php?id=<?php echo $lead['id']; ?>" class="btn btn-outline-custom btn-sm">Details <i data-lucide="arrow-right" class="ms-1" style="width: 14px; height: 14px;"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 text-center py-3">
                <a href="leads.php" class="text-decoration-none fw-bold small">View all leads</a>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="col-lg-4">
        <div class="d-flex flex-column gap-4">
            <div class="premium-card">
                <div class="card-header-custom">
                    <h5 class="fw-bold mb-0">System Status</h5>
                </div>
                <div class="card-body-custom">
                    <!-- Gmail API Status -->
                    <?php if ($_SESSION['gmail_connected'] ?? false): ?>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-grow spinner-grow-sm text-success" role="status"><span class="visually-hidden">Loading...</span></div>
                            <span class="fw-bold text-success small">Gmail API Connected</span>
                        </div>
                        <i data-lucide="check-circle-2" class="text-success"></i>
                    </div>
                    <?php else: ?>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-grow spinner-grow-sm text-danger" role="status"><span class="visually-hidden">Loading...</span></div>
                            <span class="fw-bold text-danger small">Gmail API Not Connected</span>
                        </div>
                        <i data-lucide="x-circle" class="text-danger"></i>
                    </div>
                    <?php endif; ?>

                    <!-- WhatsApp API Status -->
                    <?php if ($_SESSION['whatsapp_connected'] ?? false): ?>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-grow spinner-grow-sm text-success" role="status"><span class="visually-hidden">Loading...</span></div>
                            <span class="fw-bold text-success small">WhatsApp API Active</span>
                        </div>
                        <i data-lucide="check-circle-2" class="text-success"></i>
                    </div>
                    <?php else: ?>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-grow spinner-grow-sm text-danger" role="status"><span class="visually-hidden">Loading...</span></div>
                            <span class="fw-bold text-danger small">WhatsApp API Not Connected</span>
                        </div>
                        <i data-lucide="x-circle" class="text-danger"></i>
                    </div>
                    <?php endif; ?>

                    <!-- Gemini API Status -->
                    <?php if ($_SESSION['gemini_connected'] ?? false): ?>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-grow spinner-grow-sm text-success" role="status"><span class="visually-hidden">Loading...</span></div>
                            <span class="fw-bold text-success small">Gemini API Connected</span>
                        </div>
                        <i data-lucide="check-circle-2" class="text-success"></i>
                    </div>
                    <?php else: ?>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-grow spinner-grow-sm text-danger" role="status"><span class="visually-hidden">Loading...</span></div>
                            <span class="fw-bold text-danger small">Gemini API Not Connected</span>
                        </div>
                        <i data-lucide="x-circle" class="text-danger"></i>
                    </div>
                    <?php endif; ?>

                    <!-- ChatGPT API Status -->
                    <?php if ($_SESSION['chatgpt_connected'] ?? false): ?>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-grow spinner-grow-sm text-success" role="status"><span class="visually-hidden">Loading...</span></div>
                            <span class="fw-bold text-success small">ChatGPT API Active</span>
                        </div>
                        <i data-lucide="check-circle-2" class="text-success"></i>
                    </div>
                    <?php else: ?>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-grow spinner-grow-sm text-danger" role="status"><span class="visually-hidden">Loading...</span></div>
                            <span class="fw-bold text-danger small">ChatGPT Not Connected</span>
                        </div>
                        <i data-lucide="x-circle" class="text-danger"></i>
                    </div>
                    <?php endif; ?>

                    <div class="p-3 bg-light rounded-3 border">
                        <p class="text-uppercase text-muted small fw-bold mb-2 tracking-wider">Daily Limit</p>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold small">0 / <?php echo number_format($_SESSION['daily_limit'] ?? 500); ?></span>
                            <span class="text-muted small">0%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
