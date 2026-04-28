<?php 
require_once 'includes/config.php'; 

$leadsFile = 'data/leads.json';
$leads = [];
if (file_exists($leadsFile)) {
    $leads = json_decode(file_get_contents($leadsFile), true) ?? [];
}

// Filter leads that have been contacted
$contactedLeads = array_filter($leads, function($l) {
    return isset($l['status']) && $l['status'] === 'Contacted';
});

// Sort by ID or simulated date (since we don't have a real timestamp yet, we'll use a simulated one)
usort($contactedLeads, function($a, $b) {
    return strcmp($b['id'], $a['id']); // Reverse order
});

$pageTitle = 'Outreach Logs';
include 'includes/header.php'; 
?>

<div class="mb-4">
    <h2 class="fw-bold text-dark mb-1">Outreach Logs</h2>
    <p class="text-muted mb-0">Track all communication sent through the platform.</p>
</div>

<div class="premium-card">
    <div class="card-body-custom p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light bg-opacity-50">
                    <tr>
                        <th class="py-3 ps-4 text-uppercase text-muted small fw-bold">Date & Time</th>
                        <th class="text-uppercase text-muted small fw-bold py-3">Lead</th>
                        <th class="text-uppercase text-muted small fw-bold py-3">Channel</th>
                        <th class="text-uppercase text-muted small fw-bold py-3">Status</th>
                        <th class="text-uppercase text-muted small fw-bold py-3 text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contactedLeads)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted small">No outreach logs found yet. Start contacting leads from the <a href="prepare.php">Data Prepare</a> page.</div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($contactedLeads as $lead): ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark"><?php echo date('M d, Y'); ?></div>
                                <div class="text-muted extra-small"><?php echo date('h:i A'); ?></div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($lead['company_name']); ?></div>
                                <div class="text-muted extra-small"><?php echo htmlspecialchars($lead['email'] ?: $lead['phone']); ?></div>
                            </td>
                            <td>
                                <span class="d-flex align-items-center gap-2 small fw-bold">
                                    <?php if ($lead['email']): ?>
                                        <i data-lucide="mail" class="text-danger" style="width: 14px;"></i> Email
                                    <?php else: ?>
                                        <i data-lucide="message-circle" class="text-success" style="width: 14px;"></i> WhatsApp
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td><span class="badge badge-soft-success rounded-pill">Delivered</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-outline-custom btn-sm view-log-btn" data-lead='<?php echo json_encode($lead); ?>'>View Content</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Content Modal -->
<div class="modal fade" id="viewLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i data-lucide="eye" style="width: 20px; height: 20px;"></i>
                    </div>
                    <h5 class="fw-bold mb-0">Communication Content</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-4 bg-light rounded-4 border mb-3">
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <div>
                            <span class="text-muted extra-small text-uppercase fw-bold tracking-wider d-block">Recipient</span>
                            <span id="logRecipient" class="fw-bold text-dark">Company Name</span>
                        </div>
                        <div class="text-end">
                            <span class="text-muted extra-small text-uppercase fw-bold tracking-wider d-block">Channel</span>
                            <span id="logChannel" class="badge badge-soft-primary rounded-pill">Email</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-muted extra-small text-uppercase fw-bold tracking-wider d-block mb-2">Message Body</span>
                        <div id="logBody" class="small text-dark lh-base" style="white-space: pre-wrap;">
                            Message content goes here...
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-light px-4 rounded-3 fw-bold" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewLogModal = new bootstrap.Modal(document.getElementById('viewLogModal'));

    document.querySelectorAll('.view-log-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const lead = JSON.parse(this.getAttribute('data-lead'));
            
            document.getElementById('logRecipient').innerText = lead.company_name + (lead.email ? ` (${lead.email})` : '');
            document.getElementById('logChannel').innerText = lead.email ? 'Email' : 'WhatsApp';
            
            // Reconstruct the message draft for viewing
            const draft = lead.email 
                ? `Hi ${lead.contact_name || 'there'},\n\nI just finished an audit of your website (${lead.website}) and found some critical optimization opportunities that could help you scale.\n\nWould you be open to a quick 5-minute chat to discuss the results?\n\nBest regards,\nAuditAI Team`
                : `Hi! This is regarding the website audit for ${lead.company_name}. I have some interesting insights to share. Let me know if we can connect!`;
            
            document.getElementById('logBody').innerText = draft;
            
            viewLogModal.show();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
