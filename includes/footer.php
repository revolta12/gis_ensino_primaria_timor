<?php
// =============================================
// Public Footer - GIS Ensino Primaria Timor-Leste
// =============================================
// Footer ba website publik
?>

<!-- ============================================= -->
<!-- FOOTER -->
<!-- ============================================= -->
<footer class="footer mt-5 pt-5">
    <div class="container">
        <div class="row">
            <!-- About Section -->
            <div class="col-lg-4 mb-4">
                <h5 class="mb-3 footer-title">
                    <i class="fas fa-school text-primary"></i>
                    GIS Ensino Primaria<br>Timor-Leste
                </h5>
                <p>Sistema Informasaun Geografiku (GIS) ba ensinu primaria iha Timor-Leste. 
                   Faisilita visualizasaun no jestaun dadus eskola iha mapa interativu.</p>
                <p class="fw-bold quote">"Ita nia rai, ita nia eskola"</p>
                <p class="text-muted small">(Nossa terra, nossas escolas)</p>
                <div class="social-links mt-3">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <!-- Navigation Menu -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="mb-3 fw-bold footer-heading">Navegasaun</h6>
                <ul class="list-unstyled">
                    <li><a href="<?= BASE_URL ?>/index.php"><i class="fas fa-chevron-right me-1"></i> Uma</a></li>
                    <li><a href="<?= BASE_URL ?>/public/escola.php"><i class="fas fa-chevron-right me-1"></i> Eskola</a></li>
                    <li><a href="<?= BASE_URL ?>/public/mapa.php"><i class="fas fa-chevron-right me-1"></i> Mapa</a></li>
                    <li><a href="<?= BASE_URL ?>/public/estatistika.php"><i class="fas fa-chevron-right me-1"></i> Estatístika</a></li>
                    <li><a href="<?= BASE_URL ?>/public/kontaktu.php"><i class="fas fa-chevron-right me-1"></i> Kontaktu</a></li>
                </ul>
            </div>
            
            <!-- School Categories -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="mb-3 fw-bold footer-heading">Kategoria Eskola</h6>
                <ul class="list-unstyled">
                    <?php
                    // Get school categories from database
                    if (isset($db) && $db) {
                        $stmt = $db->query("SELECT naran_kategoria FROM kategoria_escola LIMIT 6");
                        while ($kat = $stmt->fetch()):
                    ?>
                    <li>
                        <a href="<?= BASE_URL ?>/public/escola.php?kategoria=<?= urlencode($kat['naran_kategoria']) ?>">
                            <i class="fas fa-chevron-right me-1"></i> <?= htmlspecialchars($kat['naran_kategoria']) ?>
                        </a>
                    </li>
                    <?php 
                        endwhile;
                    } else {
                        // Fallback categories if database not available
                        $fallback_categories = ['Eskola Pública', 'Eskola Privada', 'Eskola Katólika', 'Eskola Evangeliku', 'Eskola Komunitária'];
                        foreach ($fallback_categories as $kat):
                    ?>
                    <li>
                        <a href="<?= BASE_URL ?>/public/escola.php?kategoria=<?= urlencode($kat) ?>">
                            <i class="fas fa-chevron-right me-1"></i> <?= htmlspecialchars($kat) ?>
                        </a>
                    </li>
                    <?php 
                        endforeach;
                    }
                    ?>
                </ul>
            </div>
            
            <!-- Contact Information -->
            <div class="col-lg-3 mb-4">
                <h6 class="mb-3 fw-bold footer-heading">Kontaktu</h6>
                <ul class="list-unstyled contact-list">
                    <li class="mb-2">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <a href="mailto:info@ensinoprimaria.tl">info@ensinoprimaria.tl</a>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone text-primary me-2"></i>
                        <a href="tel:+6703331234">+670 333 1234</a>
                    </li>
                    <li class="mb-2">
                        <i class="fab fa-whatsapp text-primary me-2"></i>
                        <a href="https://wa.me/67077771234">+670 7777 1234</a>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <span>Díli, Timor-Leste</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <hr class="my-4 footer-divider">
        
        <!-- Copyright Section -->
        <div class="text-center py-3">
            <p class="mb-0 small">
                &copy; <?= date('Y') ?> GIS Ensino Primaria Timor-Leste. 
                Direitus hotu reservadu.
                <br class="d-md-none">
                <span class="heart">Construído ho <i class="fas fa-heart text-danger"></i> ba labarik sira iha Timor-Leste</span>
            </p>
                <p class="mt-2 small text-muted">
                <a href="<?= BASE_URL ?>/public/termos.php">Termos no Kondisaun</a> &nbsp;|&nbsp; 
                <a href="<?= BASE_URL ?>/public/privacidade.php">Polítika Privacidade</a> &nbsp;|&nbsp;
                <a href="<?= BASE_URL ?>/public/faq.php">FAQ</a>
            </p>
        </div>
    </div>
</footer>

<!-- ============================================= -->
<!-- SCRIPTS -->
<!-- ============================================= -->

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<!-- FontAwesome (if not loaded in header) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Base URL for JavaScript -->
<script>
    const BASE_URL = '<?= BASE_URL ?>';
    const SITE_NAME = 'GIS Ensino Primaria Timor-Leste';
    const SITE_DESCRIPTION = 'Sistema Informasaun Geografiku ba Ensinu Primaria iha Timor-Leste';
</script>

<!-- Custom JavaScript Files -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/js/frontend.js"></script>
<script src="<?= BASE_URL ?>/assets/js/map.js"></script>

<!-- Page-specific map initialization -->
<?php if (isset($includeMap) && $includeMap): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map if map container exists
        const mapContainer = document.getElementById('mainMap');
        if (mapContainer && typeof initMap === 'function') {
            const map = initMap('mainMap');
            if (typeof loadSchoolsToMap === 'function') {
                loadSchoolsToMap();
            }
        }
    });
</script>
<?php endif; ?>

<!-- Back to Top Button -->
<script>
    // Create back to top button if not exists
    (function() {
        if (!document.getElementById('backToTop')) {
            const backBtn = document.createElement('button');
            backBtn.id = 'backToTop';
            backBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
            backBtn.style.cssText = `
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 45px;
                height: 45px;
                background: #2C3E50;
                color: #F39C12;
                border: none;
                border-radius: 50%;
                cursor: pointer;
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 999;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                transition: all 0.3s ease;
            `;
            document.body.appendChild(backBtn);
            
            window.addEventListener('scroll', function() {
                if (window.scrollY > 300) {
                    backBtn.style.display = 'flex';
                } else {
                    backBtn.style.display = 'none';
                }
            });
            
            backBtn.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            
            backBtn.addEventListener('mouseenter', function() {
                backBtn.style.background = '#F39C12';
                backBtn.style.color = '#2C3E50';
                backBtn.style.transform = 'scale(1.1)';
            });
            
            backBtn.addEventListener('mouseleave', function() {
                backBtn.style.background = '#2C3E50';
                backBtn.style.color = '#F39C12';
                backBtn.style.transform = 'scale(1)';
            });
        }
    })();
</script>

<!-- Auto-hide alerts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
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
    });
</script>

</body>
</html>