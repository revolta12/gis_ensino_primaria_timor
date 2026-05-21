/**
 * =============================================
 * Admin JavaScript - GIS Ensino Primaria Timor-Leste
 * =============================================
 * 
 * Fungsi sira ba dashboard admin:
 * - Sidebar toggle (mobile)
 * - Toast notification
 * - Confirm delete dialog (Tetun)
 * - Form validation
 * - Auto-hide alerts
 */

// Toggle sidebar on mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
    return false;
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    
    if (window.innerWidth <= 768) {
        if (sidebar && !sidebar.contains(event.target) && menuToggle && !menuToggle.contains(event.target)) {
            sidebar.classList.remove('open');
        }
    }
});

/**
 * Confirm delete function
 * @param {string} message - Custom message (optional)
 * @param {string} itemType - Type of item being deleted (escola, kategoria, etc)
 * @returns {boolean}
 */
function confirmDelete(message, itemType = 'dadus') {
    let defaultMessage = `Ita boot hakarak hamos ${itemType} ne'e? Dadus ne'ebé hamos sei labele restaura.`;
    const msg = message || defaultMessage;
    return confirm(msg);
}

// Shortcut for common delete confirmations
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

/**
 * Show toast notification
 * @param {string} message - Message to display
 * @param {string} type - 'success' or 'danger'
 */
function showToast(message, type = 'success') {
    // Create toast container if not exists
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
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.style.marginTop = '10px';
    toast.style.minWidth = '250px';
    toast.style.borderRadius = '10px';
    
    const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
    const title = type === 'success' ? 'Susesu!' : 'Error!';
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${icon} me-2"></i>
                <strong>${title}</strong><br>
                <small>${message}</small>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 4000 });
    bsToast.show();
    
    // Remove toast after hidden
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

/**
 * Form validation helper
 * @param {string} formId - ID of the form element
 * @returns {boolean}
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        const fieldValue = field.value.trim();
        if (!fieldValue) {
            field.classList.add('is-invalid');
            isValid = false;
            
            // Add error message if not exists
            let errorDiv = field.parentNode.querySelector('.invalid-feedback');
            if (!errorDiv && field.parentNode) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Favor prense field ne\'e.';
                field.parentNode.appendChild(errorDiv);
            }
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    // Special validation for email fields
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        const emailValue = field.value.trim();
        if (emailValue && !isValidEmail(emailValue)) {
            field.classList.add('is-invalid');
            let errorDiv = field.parentNode.querySelector('.invalid-feedback');
            if (!errorDiv && field.parentNode) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Email format la validu. Uza ezemplu: naran@domain.com';
                field.parentNode.appendChild(errorDiv);
            }
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validate email format
 * @param {string} email - Email to validate
 * @returns {boolean}
 */
function isValidEmail(email) {
    const re = /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/;
    return re.test(email);
}

/**
 * Format number with thousand separator
 * @param {number} num - Number to format
 * @returns {string}
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

/**
 * Preview image before upload
 * @param {HTMLInputElement} input - File input element
 * @param {string} previewId - ID of preview container
 */
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview) return;
    
    preview.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        Array.from(input.files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '80px';
                    img.style.height = '80px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '8px';
                    img.style.margin = '4px';
                    img.style.border = '2px solid #2C3E50';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

/**
 * Copy to clipboard helper
 * @param {string} text - Text to copy
 * @param {string} successMessage - Success message (optional)
 */
function copyToClipboard(text, successMessage = 'Kopia ba clipboard!') {
    navigator.clipboard.writeText(text).then(() => {
        showToast(successMessage, 'success');
    }).catch(() => {
        showToast('Falha kopia ba clipboard.', 'danger');
    });
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide regular alerts
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert && alert.parentNode) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => {
                    if (alert.parentNode) alert.remove();
                }, 500);
            }
        }, 5000);
    });
    
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Auto-remove invalid feedback on input
    const formInputs = document.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
});