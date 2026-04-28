<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - AuditAI' : 'AuditAI'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="<?php echo (!empty($_SESSION['dark_theme'])) ? 'dark-theme' : ''; ?>">
    <div id="app-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-wrapper">
            <header class="top-header">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light d-lg-none p-2 border-0" id="sidebarToggle">
                        <i data-lucide="menu"></i>
                    </button>
                    <div class="search-wrapper d-none d-md-block">
                        <i data-lucide="search" class="search-icon"></i>
                        <input type="text" id="quickSearchInput" class="form-control form-control-custom" placeholder="Search" autocomplete="off">
                        <div id="searchResults" class="search-results-dropdown d-none shadow-lg border rounded-4 bg-white mt-2">
                            <!-- Results will be injected here -->
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light position-relative p-2 border-0 rounded-3" id="notificationBtn">
                        <i data-lucide="bell" style="width: 20px; height: 20px;"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    </button>

                    <div class="vr mx-1"></div>

                    <div class="dropdown">
                        <button class="btn btn-link text-decoration-none text-dark fw-medium d-flex align-items-center gap-2 p-1 border-0" data-bs-toggle="dropdown" aria-expanded="false">
                            Support <i data-lucide="chevron-down" style="width: 16px; height: 16px;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-3 overflow-hidden">
                            <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="#"><i data-lucide="help-circle" style="width: 16px; height: 16px;"></i> Documentation</a></li>
                            <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="#"><i data-lucide="message-square" style="width: 16px; height: 16px;"></i> Live Chat</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger" href="#"><i data-lucide="life-buoy" style="width: 16px; height: 16px;"></i> Open Ticket</a></li>
                        </ul>
                    </div>
                </div>
            </header>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('quickSearchInput');
                const searchResults = document.getElementById('searchResults');
                const notificationBtn = document.getElementById('notificationBtn');
                const notificationPanel = document.getElementById('notificationPanel');
                const closeNotifications = document.getElementById('closeNotifications');
                const notificationOverlay = document.getElementById('notificationOverlay');
                let debounceTimer;

                // Notification Panel Toggle
                notificationBtn.addEventListener('click', () => {
                    notificationPanel.classList.add('show');
                    notificationOverlay.classList.add('show');
                });

                const hideNotifications = () => {
                    notificationPanel.classList.remove('show');
                    notificationOverlay.classList.remove('show');
                };

                closeNotifications.addEventListener('click', hideNotifications);
                notificationOverlay.addEventListener('click', hideNotifications);

                // Search logic...
                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    clearTimeout(debounceTimer);
                    
                    if (query.length < 1) {
                        searchResults.classList.add('d-none');
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        fetch(`actions/search-handler.php?q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.length > 0) {
                                    searchResults.innerHTML = data.map(item => `
                                        <a href="${item.url}" class="search-result-item d-flex align-items-center gap-3 p-3 text-decoration-none border-bottom">
                                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                                <i data-lucide="${item.icon}" style="width: 18px; height: 18px;"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0 fw-bold text-dark small">${item.title}</p>
                                                <span class="text-muted extra-small">${item.category}</span>
                                            </div>
                                        </a>
                                    `).join('');
                                    searchResults.classList.remove('d-none');
                                    lucide.createIcons();
                                } else {
                                    searchResults.innerHTML = '<div class="p-4 text-center text-muted small">No options found.</div>';
                                    searchResults.classList.remove('d-none');
                                }
                            });
                    }, 300);
                });

                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                        searchResults.classList.add('d-none');
                    }
                });

                // Initialize icons
                lucide.createIcons();
            });
            </script>
            
            <!-- Notification Side Panel -->
            <div class="notification-overlay" id="notificationOverlay"></div>
            <div class="notification-panel shadow-lg" id="notificationPanel">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white sticky-top">
                    <div>
                        <h5 class="fw-bold mb-1">Notifications</h5>
                        <p class="text-muted small mb-0">Stay updated with your activities.</p>
                    </div>
                    <button class="btn btn-light rounded-circle p-2" id="closeNotifications">
                        <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                    </button>
                </div>
                <div class="p-4">
                    <div class="d-flex flex-column gap-3">
                        <!-- Notification Item -->
                        <div class="p-3 border rounded-4 bg-light bg-opacity-50 hover-shadow transition-all">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-success bg-opacity-10 text-success p-2 rounded-3">
                                    <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between mb-1">
                                        <h6 class="fw-bold mb-0 small">Website Audit Completed</h6>
                                        <span class="text-muted extra-small">2m ago</span>
                                    </div>
                                    <p class="text-muted small mb-0">The audit for <strong>stripe.com</strong> has been completed successfully.</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 border rounded-4 bg-light bg-opacity-50 hover-shadow transition-all">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-3">
                                    <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between mb-1">
                                        <h6 class="fw-bold mb-0 small">Low Credit Alert</h6>
                                        <span class="text-muted extra-small">1h ago</span>
                                    </div>
                                    <p class="text-muted small mb-0">Your Gemini API credits are running low. Please top up soon.</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 border rounded-4 bg-light bg-opacity-50 hover-shadow transition-all">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                    <i data-lucide="mail" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between mb-1">
                                        <h6 class="fw-bold mb-0 small">New Campaign Started</h6>
                                        <span class="text-muted extra-small">3h ago</span>
                                    </div>
                                    <p class="text-muted small mb-0">Outreach campaign "SaaS Founders" has been initiated.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <button class="btn btn-link text-decoration-none small fw-bold">Mark all as read</button>
                    </div>
                </div>
            </div>
            
            <main class="content-area">
