            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Sidebar Toggle Logic
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }

        // Custom Toast Function
        function showToast(message, type = 'success') {
            const oldToast = document.querySelector('.custom-toast');
            if (oldToast) oldToast.remove();

            const toast = document.createElement('div');
            toast.className = `custom-toast ${type}`;
            toast.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}" style="width: 20px; height: 20px;"></i>
                    <span class="fw-bold small">${message}</span>
                </div>
            `;
            document.body.appendChild(toast);
            if (window.lucide) lucide.createIcons();
            
            toast.offsetHeight;
            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        <?php 
        $flash = get_flash_message();
        if ($flash): 
        ?>
        showToast("<?php echo $flash['message']; ?>", "<?php echo $flash['type']; ?>");
        <?php endif; ?>
    </script>
</body>
</html>
