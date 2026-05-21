<?php
// =============================================
// Admin Footer - GIS Ensino Primaria Timor-Leste
// =============================================
// Footer ba admin panel ho JavaScript functions
?>

    </div> <!-- Close .main-content -->
    </div> <!-- Close .admin-wrapper -->

    <!-- ============================================= -->
    <!-- SCRIPTS -->
    <!-- ============================================= -->
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- FontAwesome (if not loaded in header) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Admin Custom JavaScript -->
    <script src="../assets/js/admin.js"></script>
    
    <script>
        // =============================================
        // TOGGLE SIDEBAR ON MOBILE
        // =============================================
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('open');
            }
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.menu-toggle');
            
            if (window.innerWidth <= 768) {
                if (sidebar && toggle && !sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
        
        // Auto close sidebar after clicking menu on mobile
        document.querySelectorAll('.sidebar-menu a, .sidebar-nav a').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    const sidebar = document.getElementById('sidebar');
                    if (sidebar) sidebar.classList.remove('open');
                }
            });
        });
        
        // =============================================
        // AUTO HIDE ALERTS
        // =============================================
        document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert && alert.parentNode) alert.remove();
                    }, 500);
                }
            }, 5000);
        });
        
        // =============================================
        // CONFIRM DELETE FUNCTION (Tetun version)
        // =============================================
        function confirmDelete(message, itemType = 'dadus') {
            let defaultMessage = `Ita boot hakarak hamos ${itemType} ne'e? Dadus ne'ebé hamos sei labele restaura.`;
            const msg = message || defaultMessage;
            return confirm(msg);
        }
        
        // =============================================
        // TOAST NOTIFICATION (Tetun version)
        // =============================================
        function showToast(message, type = 'success') {
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.style.position = 'fixed';
                toastContainer.style.bottom = '20px';
                toastContainer.style.right = '20px';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }
            
            // Determine icon and title based on type
            let icon = 'check-circle';
            let title = 'Susesu!';
            let bgClass = 'bg-success';
            
            if (type === 'error' || type === 'danger') {
                icon = 'exclamation-circle';
                title = 'Error!';
                bgClass = 'bg-danger';
            } else if (type === 'info') {
                icon = 'info-circle';
                title = 'Informasaun';
                bgClass = 'bg-info';
            } else if (type === 'warning') {
                icon = 'exclamation-triangle';
                title = 'Atenasaun';
                bgClass = 'bg-warning';
            }
            
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white ${bgClass} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.style.marginTop = '10px';
            toast.style.minWidth = '280px';
            toast.style.borderRadius = '10px';
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${icon} me-2"></i>
                        <strong>${title}</strong>
                        <div class="mt-1 small">${message}</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            // Initialize Bootstrap toast
            const bsToast = new bootstrap.Toast(toast, { 
                autohide: true, 
                delay: 4000 
            });
            bsToast.show();
            
            // Remove toast from DOM after hidden
            toast.addEventListener('hidden.bs.toast', () => {
                if (toast && toast.parentNode) toast.remove();
            });
        }
        
        // =============================================
        // SHORTCUT FUNCTIONS FOR SPECIFIC DELETES
        // =============================================
        function confirmDeleteEscola() {
            return confirmDelete('', 'escola');
        }
        
        function confirmDeleteKategoria() {
            return confirmDelete('', 'kategoria');
        }
        
        function confirmDeleteFasilidade() {
            return confirmDelete('', 'fasilidade');
        }
        
        function confirmDeleteAvaliasaun() {
            return confirmDelete('Ita boot hakarak hamos avaliasaun ne\'e?', 'avaliasaun');
        }
        
        function confirmDeleteMensajen() {
            return confirmDelete('Ita boot hakarak hamos mensajen ne\'e?', 'mensajen');
        }
        
        // =============================================
        // FORM VALIDATION HELPER
        // =============================================
        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (!form) return true;
            
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Email validation
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                const emailValue = field.value.trim();
                if (emailValue && !isValidEmail(emailValue)) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            return isValid;
        }
        
        // Email validation helper
        function isValidEmail(email) {
            const re = /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/;
            return re.test(email);
        }
        
        // =============================================
        // ACTIVE MENU HIGHLIGHT
        // =============================================
        function highlightActiveMenu() {
            const currentPath = window.location.pathname;
            const menuLinks = document.querySelectorAll('.sidebar-menu a, .sidebar-nav a');
            
            menuLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && currentPath.includes(href) && href !== '#' && href !== '') {
                    link.classList.add('active');
                    // Expand parent if inside submenu
                    const parentLi = link.closest('.nav-item');
                    if (parentLi && parentLi.classList.contains('has-dropdown')) {
                        parentLi.classList.add('open');
                    }
                } else if (link) {
                    link.classList.remove('active');
                }
            });
        }
        
        // Run on page load
        document.addEventListener('DOMContentLoaded', function() {
            highlightActiveMenu();
            
            // Initialize tooltips if Bootstrap is available
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
            
            // Remove is-invalid class on input
            const formInputs = document.querySelectorAll('input, select, textarea');
            formInputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                });
            });
        });
        
        // =============================================
        // DROPDOWN TOGGLE FOR SIDEBAR (if any)
        // =============================================
        document.querySelectorAll('.sidebar-nav .nav-item.has-dropdown > a').forEach(dropdownToggle => {
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                const parentLi = this.closest('.nav-item');
                if (parentLi) {
                    parentLi.classList.toggle('open');
                }
            });
        });
    </script>
</body>
</html>