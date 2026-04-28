<?php
$currentPath = $_SERVER['PHP_SELF'];
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i data-lucide="layout-dashboard"></i>
        </div>
        <span class="fs-5 fw-bold text-dark tracking-tight">AuditAI</span>
    </div>
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-link-custom <?php echo (basename($currentPath) == 'index.php' || basename($currentPath) == '') ? 'active' : ''; ?>">
            <i data-lucide="layout-dashboard"></i> Dashboard
        </a>
        <a href="upload.php" class="nav-link-custom <?php echo (basename($currentPath) == 'upload.php') ? 'active' : ''; ?>">
            <i data-lucide="upload-cloud"></i> Upload Leads
        </a>
        <a href="leads.php" class="nav-link-custom <?php echo (basename($currentPath) == 'leads.php' || basename($currentPath) == 'details.php') ? 'active' : ''; ?>">
            <i data-lucide="users"></i> Leads Table
        </a>
        <a href="prepare.php" class="nav-link-custom <?php echo (basename($currentPath) == 'prepare.php') ? 'active' : ''; ?>">
            <i data-lucide="cpu"></i> Data Prepare
        </a>
        <a href="logs.php" class="nav-link-custom <?php echo (basename($currentPath) == 'logs.php') ? 'active' : ''; ?>">
            <i data-lucide="history"></i> Outreach Logs
        </a>
        <a href="settings.php" class="nav-link-custom <?php echo (strpos(basename($currentPath), 'settings.php') !== false || strpos(basename($currentPath), 'api-') !== false) ? 'active' : ''; ?>">
            <i data-lucide="settings"></i> Settings
        </a>
    </nav>
    <div class="p-3 border-top border-light mt-auto">
        <div class="d-flex align-items-center p-2 bg-light rounded-3">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 12px;">
                HA
            </div>
            <div class="ms-2 overflow-hidden">
                <p class="mb-0 text-dark fw-bold" style="font-size: 13px;">Himel Ahmed</p>
                <p class="mb-0 text-muted" style="font-size: 11px;">Pro Plan</p>
            </div>
        </div>
    </div>
</aside>
