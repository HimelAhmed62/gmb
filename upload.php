<?php 
require_once 'includes/config.php'; 
$pageTitle = 'Upload Leads';
include 'includes/header.php'; 
?>

<div class="mb-4">
    <h2 class="fw-bold text-dark mb-1">Upload Leads</h2>
    <p class="text-muted mb-0">Import your leads via CSV or enter them manually.</p>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="premium-card h-100">
            <div class="card-header-custom border-bottom pb-3">
                <h5 class="fw-bold mb-0">Bulk Import</h5>
            </div>
            <div class="card-body-custom">
                <div class="upload-zone p-5 text-center border-2 border-dashed rounded-4 mb-4" id="dropzone" style="cursor: pointer; transition: all 0.3s ease;">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i data-lucide="upload-cloud" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Click or drag CSV file here</h5>
                    <p class="text-muted small">Only CSV files are supported. Max size: 10MB</p>
                    <input type="file" id="fileInput" class="d-none" accept=".csv">
                    <button class="btn btn-primary-custom px-4" onclick="document.getElementById('fileInput').click()">Select File</button>
                    
                    <div id="uploadProgress" class="mt-4 d-none">
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                        </div>
                        <p class="small text-muted mb-0">Uploading and processing...</p>
                    </div>
                </div>
                
                <div class="p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold small text-muted mb-2">CSV Requirements:</h6>
                    <ul class="small text-muted mb-0 ps-3">
                        <li>Must include <strong>company_name</strong> column.</li>
                        <li>Must include <strong>website</strong> column.</li>
                        <li>Optional columns: email, phone, contact_name.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-5">
        <div class="premium-card h-100">
            <div class="card-header-custom border-bottom pb-3">
                <h5 class="fw-bold mb-0">Manual Entry</h5>
            </div>
            <div class="card-body-custom">
                <form id="manualEntryForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Company Name</label>
                        <input type="text" id="manualCompanyName" class="form-control form-control-custom" placeholder="e.g. Acme Corp" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">Website URL</label>
                        <input type="url" id="manualWebsite" class="form-control form-control-custom" placeholder="e.g. https://acme.com" required>
                    </div>
                    <button type="submit" id="manualSubmitBtn" class="btn btn-primary-custom w-100">Add Lead to Queue</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const progressWrapper = document.getElementById('uploadProgress');
    const progressBar = progressWrapper.querySelector('.progress-bar');
    const manualForm = document.getElementById('manualEntryForm');

    // Drag and drop events
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => dropzone.classList.add('bg-light'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => dropzone.classList.remove('bg-light'), false);
    });

    dropzone.addEventListener('drop', handleDrop, false);
    dropzone.addEventListener('click', () => fileInput.click());

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length > 0) {
            const file = files[0];
            if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
                uploadFile(file);
            } else {
                showToast('Please upload a valid CSV file.', 'danger');
            }
        }
    }

    function uploadFile(file) {
        const formData = new FormData();
        formData.append('leads_file', file);

        progressWrapper.classList.remove('d-none');
        progressBar.style.width = '0%';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'actions/lead-handler.php', true);

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressBar.style.width = percentComplete + '%';
            }
        };

        xhr.onload = function() {
            progressWrapper.classList.add('d-none');
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => window.location.href = 'leads.php', 1500);
                    } else {
                        showToast(response.message, 'danger');
                    }
                } catch (e) {
                    showToast('An error occurred during upload.', 'danger');
                }
            } else {
                showToast('Server error: ' + xhr.status, 'danger');
            }
        };

        xhr.onerror = function() {
            progressWrapper.classList.add('d-none');
            showToast('Network error occurred.', 'danger');
        };

        xhr.send(formData);
    }

    // Manual Entry handling
    manualForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('manualSubmitBtn');
        const company = document.getElementById('manualCompanyName').value;
        const website = document.getElementById('manualWebsite').value;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Adding...';

        fetch('actions/lead-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                company_name: company,
                website: website
            })
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'Add Lead to Queue';
            if (data.success) {
                showToast(data.message, 'success');
                manualForm.reset();
                setTimeout(() => window.location.href = 'leads.php', 1000);
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = 'Add Lead to Queue';
            showToast('An error occurred.', 'danger');
        });
    });
});

function showToast(message, type = 'success') {
    // Check if Lucide is available
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

<?php include 'includes/footer.php'; ?>
