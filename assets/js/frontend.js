/**
 * =============================================
 * Frontend JavaScript - GIS Ensino Primaria Timor-Leste
 * =============================================
 * 
 * Fungsi sira ba frontend (website publik):
 * - Navbar scroll effect
 * - Counter animation ba estatistika
 * - Smooth scroll ba anchor links
 * - Form validation
 * - Toast notification
 */

// =============================================
// NAVBAR SCROLL EFFECT
// =============================================
window.addEventListener('scroll', function() {
    const navbar = document.getElementById('mainNav');
    if (navbar) {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
});

// =============================================
// COUNTER ANIMATION FOR STATS
// =============================================
function animateCounter(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        // Use easeOutCubic for smoother animation
        const easeProgress = 1 - Math.pow(1 - progress, 3);
        element.innerText = Math.floor(easeProgress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Trigger counters when in viewport
const observerOptions = {
    threshold: 0.3,
    rootMargin: "0px 0px -50px 0px"
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const counters = entry.target.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                if (!isNaN(target)) {
                    animateCounter(counter, 0, target, 1500);
                }
            });
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe stats section
const statsSection = document.querySelector('.stats-section');
if (statsSection) {
    observer.observe(statsSection);
}

// Also observe hero stats if exists
const heroStats = document.querySelector('.hero-stats');
if (heroStats) {
    observer.observe(heroStats);
}

// =============================================
// SMOOTH SCROLL FOR ANCHOR LINKS
// =============================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        // Skip if href is just "#" or empty
        if (href === '#' || href === '') return;
        
        const target = document.querySelector(href);
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// =============================================
// BACK TO TOP BUTTON
// =============================================
function initBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    if (!backToTopBtn) return;
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTopBtn.classList.add('visible');
        } else {
            backToTopBtn.classList.remove('visible');
        }
    });
    
    backToTopBtn.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

initBackToTop();

// =============================================
// FORM VALIDATION HELPER
// =============================================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        // Clear previous error
        input.classList.remove('is-invalid');
        
        // Check if empty
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        }
        
        // Email validation if input type is email
        if (input.type === 'email' && input.value.trim()) {
            if (!isValidEmail(input.value.trim())) {
                input.classList.add('is-invalid');
                isValid = false;
            }
        }
        
        // Phone number validation (optional)
        if (input.type === 'tel' && input.value.trim()) {
            if (!isValidPhone(input.value.trim())) {
                input.classList.add('is-invalid');
                isValid = false;
            }
        }
    });
    
    return isValid;
}

// Email validation
function isValidEmail(email) {
    const re = /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/;
    return re.test(email);
}

// Phone number validation (Timor-Leste format)
function isValidPhone(phone) {
    // Accept: +670XXXXXXXX, 77XXXXXX, 78XXXXXX
    const re = /^(\+670|0)?[0-9]{7,12}$/;
    return re.test(phone);
}

// =============================================
// TOAST NOTIFICATION
// =============================================
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
    
    // Determine icon and title
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
    
    // Create toast element
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
    
    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 4000 });
    bsToast.show();
    
    // Remove toast after hidden
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

// =============================================
// FILTER SCHOOLS (for list page)
// =============================================
function filterSchools() {
    const filterType = document.getElementById('filterType')?.value;
    const searchInput = document.getElementById('searchInput')?.value.toLowerCase();
    const schoolCards = document.querySelectorAll('.school-card');
    
    if (!schoolCards.length) return;
    
    schoolCards.forEach(card => {
        let show = true;
        
        // Filter by type
        if (filterType && filterType !== 'all') {
            const cardType = card.getAttribute('data-kategoria');
            if (cardType !== filterType) show = false;
        }
        
        // Filter by search
        if (searchInput && show) {
            const schoolName = card.getAttribute('data-naran')?.toLowerCase() || '';
            const schoolAddress = card.getAttribute('data-enderesu')?.toLowerCase() || '';
            if (!schoolName.includes(searchInput) && !schoolAddress.includes(searchInput)) {
                show = false;
            }
        }
        
        card.style.display = show ? 'block' : 'none';
    });
    
    // Show/hide no results message
    const visibleCards = document.querySelectorAll('.school-card[style="display: block"], .school-card:not([style*="display: none"])');
    const noResults = document.getElementById('noResults');
    if (noResults) {
        noResults.style.display = visibleCards.length === 0 ? 'block' : 'none';
    }
}

// =============================================
// LAZY LOAD IMAGES
// =============================================
function initLazyLoad() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.getAttribute('data-src');
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

initLazyLoad();

// =============================================
// AUTO-HIDE ALERTS
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide regular alerts after 5 seconds
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
    
    // Remove is-invalid class on input
    const formInputs = document.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
});

// =============================================
// MOBILE MENU TOGGLE
// =============================================
function toggleMobileMenu() {
    const mobileMenu = document.querySelector('.mobile-menu');
    if (mobileMenu) {
        mobileMenu.classList.toggle('active');
    }
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const mobileMenu = document.querySelector('.mobile-menu');
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    
    if (window.innerWidth <= 768 && mobileMenu && mobileMenu.classList.contains('active')) {
        if (!mobileMenu.contains(event.target) && menuToggle && !menuToggle.contains(event.target)) {
            mobileMenu.classList.remove('active');
        }
    }
});