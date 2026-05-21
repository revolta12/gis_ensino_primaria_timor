<?php
// =============================================
// Escola Detail - GIS Ensino Primaria Timor-Leste
// GOOGLE MAPS DIRECT VERSION (INSTANT)
// =============================================

require_once '../config/database.php';
require_once '../includes/functions.php';

$includeMap = true;
$db = getDB();

// Get slug from URL
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (!$slug) {
    header('Location: escola.php');
    exit();
}

// OPTIMIZED QUERY - Get school details with single query
$stmt = $db->prepare("
    SELECT e.*, k.naran_kategoria,
           (SELECT COUNT(*) FROM foto_escola WHERE escola_id = e.id) as total_foto,
           (SELECT COUNT(*) FROM avaliasaun_escola WHERE escola_id = e.id AND aprovadu = 1) as total_avaliasaun
    FROM escola e
    LEFT JOIN kategoria_escola k ON e.kategoria_id = k.id
    WHERE e.slug = ? AND e.aktivo = 1
");
$stmt->execute([$slug]);
$school = $stmt->fetch();

if (!$school) {
    header('HTTP/1.0 404 Not Found');
    include_once '../includes/header.php';
    echo '<div class="container py-5 text-center"><h2>Eskola la hetan</h2><a href="escola.php" class="btn btn-primary">Fila</a></div>';
    include_once '../includes/footer.php';
    exit();
}

// Get gallery
$stmt = $db->prepare("SELECT * FROM foto_escola WHERE escola_id = ? ORDER BY ordem ASC LIMIT 12");
$stmt->execute([$school['id']]);
$gallery = $stmt->fetchAll();

// Get facilities
$stmt = $db->prepare("
    SELECT f.* 
    FROM fasilidade_escola f
    INNER JOIN escola_fasilidade ef ON f.id = ef.fasilidade_id
    WHERE ef.escola_id = ?
");
$stmt->execute([$school['id']]);
$facilities = $stmt->fetchAll();

// Get reviews with pagination
$page = isset($_GET['review_page']) ? (int)$_GET['review_page'] : 1;
$reviewsPerPage = 10;
$offset = ($page - 1) * $reviewsPerPage;

$stmt = $db->prepare("
    SELECT * FROM avaliasaun_escola 
    WHERE escola_id = ? AND aprovadu = 1 
    ORDER BY kria_iha DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$school['id'], $reviewsPerPage, $offset]);
$reviews = $stmt->fetchAll();

// Get total reviews for pagination
$stmt = $db->prepare("SELECT COUNT(*) FROM avaliasaun_escola WHERE escola_id = ? AND aprovadu = 1");
$stmt->execute([$school['id']]);
$totalReviews = $stmt->fetchColumn();
$totalPages = ceil($totalReviews / $reviewsPerPage);

$main_photo = $school['foto_prinsipal'] ? BASE_URL . '/' . $school['foto_prinsipal'] : BASE_URL . '/assets/img/escola-placeholder.jpg';
if (!empty($gallery)) {
    $main_photo = BASE_URL . '/' . $gallery[0]['naran_fail'];
}

include_once '../includes/header.php';
$googleMapsApiKey = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';
?>

<style>
.school-gallery { position: relative; margin-bottom: 30px; }
.main-photo { width: 100%; height: 400px; object-fit: cover; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
.thumbnail-gallery { display: flex; gap: 12px; margin-top: 15px; overflow-x: auto; padding-bottom: 5px; }
.thumbnail { width: 100px; height: 75px; object-fit: cover; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; opacity: 0.6; border: 2px solid transparent; }
.thumbnail:hover, .thumbnail.active { opacity: 1; border-color: #F39C12; transform: scale(1.02); }

/* Google Map */
#schoolMap { height: 450px; border-radius: 16px; width: 100%; background: #f0f0f0; }
.map-card-wrapper {
    position: relative;
}

.map-info-card h5 {
    margin-bottom: 12px;
    font-size: 1rem;
    letter-spacing: 0.02em;
}
.map-info-card p {
    margin-bottom: 10px;
    color: #495057;
    font-size: 0.9rem;
}
.map-info-card .badge {
    font-size: 0.75rem;
    margin-right: 6px;
}
.map-info-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 10px;
}
.map-info-buttons .btn {
    flex: 1 1 140px;
}

/* Button Route */
.btn-route-main {
    background: #2C3E50;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s;
    margin-top: 15px;
    cursor: pointer;
    width: 100%;
}
.btn-route-main:hover { background: #F39C12; color: #1A252F; transform: translateY(-2px); }

/* Toast */
.toast-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #2C3E50;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    z-index: 10000;
    animation: fadeInOut 3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.toast-notification.success { background: #27AE60; }
.toast-notification.error { background: #E74C3C; }
.toast-notification.info { background: #F39C12; }
@keyframes fadeInOut {
    0% { opacity: 0; transform: translateY(20px); }
    15% { opacity: 1; transform: translateY(0); }
    85% { opacity: 1; transform: translateY(0); }
    100% { opacity: 0; transform: translateY(20px); }
}

/* Review */
.rating-input { display: inline-flex; align-items: center; gap: 5px; background: #f9f9f9; padding: 10px 15px; border-radius: 50px; border: 1px solid #eee; }
.star-rating { transition: all 0.2s ease; cursor: pointer; font-size: 28px; margin-right: 8px; }
.star-rating.fas { color: #F39C12 !important; }
.star-rating.far { color: #ddd !important; }
.review-card { background: #f8f9fa; border-radius: 16px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #F39C12; }
.pagination { display: flex; justify-content: center; gap: 10px; margin-top: 20px; }
.pagination a { padding: 8px 16px; background: #2C3E50; color: white; text-decoration: none; border-radius: 8px; }
.pagination a:hover, .pagination a.active { background: #F39C12; color: #1A252F; }

@media (max-width: 768px) { .main-photo { height: 250px; } }
</style>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Uma</a></li>
            <li class="breadcrumb-item"><a href="escola.php">Eskola</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($school['naran_escola']) ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Gallery -->
            <div class="school-gallery">
                <img src="<?= $main_photo ?>" alt="<?= htmlspecialchars($school['naran_escola']) ?>" class="main-photo" id="mainPhoto">
                <?php if (count($gallery) > 1): ?>
                <div class="thumbnail-gallery">
                    <?php foreach ($gallery as $index => $photo): ?>
                        <img src="<?= BASE_URL . '/' . $photo['naran_fail'] ?>" class="thumbnail <?= $index == 0 ? 'active' : '' ?>" onclick="changeMainPhoto(this.src)" loading="lazy">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <h2><?= htmlspecialchars($school['naran_escola']) ?></h2>
            <p class="text-muted"><i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($school['enderesu']) ?>, <?= htmlspecialchars($school['municipio']) ?></p>
            
            <div class="mb-3">
                <?= renderStars($school['avaliasaun']) ?>
                <span class="ms-2">(<?= $school['total_avaliasaun'] ?> avaliasaun)</span>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4"><div class="bg-light p-3 rounded-3 text-center"><h3 class="text-primary mb-0"><?= number_format($school['total_estudante']) ?></h3><small>Total Estudante</small></div></div>
                <div class="col-md-4"><div class="bg-light p-3 rounded-3 text-center"><h3 class="text-primary mb-0"><?= number_format($school['total_profesor']) ?></h3><small>Total Profesor</small></div></div>
                <div class="col-md-4"><div class="bg-light p-3 rounded-3 text-center"><h3 class="text-primary mb-0">Klase <?= $school['klase_hosi'] ?> - <?= $school['klase_too'] ?></h3><small>Nivel Ensinu</small></div></div>
            </div>
            
            <h5>Deskrisaun</h5>
            <p><?= nl2br(htmlspecialchars($school['deskrisaun'] ?? 'Seidauk iha deskrisaun.')) ?></p>
            
            <h5 class="mt-4">Fasilidade</h5>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <?php if ($school['iha_bee_moos']): ?><span class="badge bg-success"><i class="fas fa-water"></i> Bee Moos</span><?php else: ?><span class="badge bg-danger"><i class="fas fa-times"></i> La iha Bee</span><?php endif; ?>
                <?php if ($school['iha_eletrisidade']): ?><span class="badge bg-success"><i class="fas fa-plug"></i> Eletrisidade</span><?php endif; ?>
                <?php if ($school['iha_toilet']): ?><span class="badge bg-success"><i class="fas fa-toilet"></i> Toilet</span><?php endif; ?>
                <?php foreach ($facilities as $facility): ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($facility['naran_fasilidade']) ?></span>
                <?php endforeach; ?>
            </div>
            
            <!-- MAP SECTION - GOOGLE MAPS -->
            <h5 class="mt-4">Lokasaun Eskola</h5>
            <div class="map-card-wrapper">
                <div id="schoolMap"></div>
                <div class="map-info-card" id="mapInfoCard">
                    <h5><?= htmlspecialchars($school['naran_escola']) ?></h5>
                    <p><i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($school['enderesu']) ?></p>
                    <div class="mb-2">
                        <?php if ($school['iha_bee_moos']): ?><span class="badge bg-success"><i class="fas fa-water"></i> Bee</span><?php else: ?><span class="badge bg-danger"><i class="fas fa-times"></i> La iha Bee</span><?php endif; ?>
                        <?php if ($school['iha_eletrisidade']): ?><span class="badge bg-success"><i class="fas fa-plug"></i> Luz</span><?php endif; ?>
                        <?php if ($school['iha_toilet']): ?><span class="badge bg-success"><i class="fas fa-toilet"></i> Toilet</span><?php endif; ?>
                    </div>
                    <div class="map-info-buttons">
                        <button id="toggleSatelliteBtn" class="btn btn-outline-secondary" onclick="toggleSatelliteView()">
                            <i class="fas fa-layer-group"></i> Lihat Satelit
                        </button>
                        <button class="btn btn-secondary" onclick="openGoogleMapsWithRoute()">
                            <i class="fas fa-route"></i> Google Maps
                        </button>
                    </div>
                </div>
            </div>
            
            <button class="btn-route-main mt-3" onclick="openGoogleMapsWithRoute()">
                <i class="fas fa-route"></i> Rota husi ha'u nia fatin
            </button>
            
            <!-- REVIEW SECTION -->
            <h5 class="mt-4">Avaliasaun</h5>
            
            <div class="bg-light p-4 rounded-4 mb-4">
                <h6><i class="fas fa-edit me-2"></i> Fo Avaliasaun Foun</h6>
                <form id="reviewForm">
                    <input type="hidden" name="escola_id" value="<?= $school['id'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><input type="text" name="naran" id="reviewName" class="form-control" placeholder="Ita nia naran" required></div>
                        <div class="col-md-6"><input type="email" name="email" id="reviewEmail" class="form-control" placeholder="Email (opsional)"></div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Rating:</label>
                            <div class="rating-input mb-2">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <i class="far fa-star star-rating" data-rating="<?= $i ?>"></i>
                                <?php endfor; ?>
                                <input type="hidden" name="rating" id="ratingValue" required>
                            </div>
                        </div>
                        <div class="col-12"><textarea name="komentar" id="reviewComment" class="form-control" rows="3" placeholder="Sabe oinsá ita nia esperiénsia ho eskola ne'e?" required></textarea></div>
                        <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i> Fo Avaliasaun</button></div>
                    </div>
                </form>
            </div>
            
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="d-flex justify-content-between"><strong><i class="fas fa-user"></i> <?= htmlspecialchars($review['naran_avaliador']) ?></strong><small class="text-muted"><?= timeAgo($review['kria_iha']) ?></small></div>
                        <div class="mb-1"><?= renderStars($review['pontuasaun']) ?></div>
                        <p><?= nl2br(htmlspecialchars($review['komentariu'])) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?><a href="?slug=<?= $slug ?>&review_page=<?= $page-1 ?>">Anterior</a><?php endif; ?>
                    <span class="active"><?= $page ?> / <?= $totalPages ?></span>
                    <?php if ($page < $totalPages): ?><a href="?slug=<?= $slug ?>&review_page=<?= $page+1 ?>">Prósimu</a><?php endif; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-muted">Seidauk iha avaliasaun ba eskola ne'e. Klik iha leten atu sai primeiru!</p>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-4">
            <div class="bg-white p-4 rounded-4 shadow-sm sticky-top" style="top: 90px;">
                <h5>Informasaun Kontaktu</h5>
                <hr>
                <?php if ($school['telefone']): ?><p><i class="fas fa-phone text-primary"></i> <a href="tel:<?= $school['telefone'] ?>"><?= $school['telefone'] ?></a></p><?php endif; ?>
                <?php if ($school['email_escola']): ?><p><i class="fas fa-envelope text-primary"></i> <?= $school['email_escola'] ?></p><?php endif; ?>
                <p><i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($school['enderesu']) ?></p>
                <hr>
                <h5>Prioridade</h5>
                <?php if (!$school['iha_bee_moos']): ?>
                    <div class="alert alert-danger">⚠️ Urjente: Eskola la iha bee moos!</div>
                <?php elseif (!$school['iha_eletrisidade']): ?>
                    <div class="alert alert-warning">⚠️ Prioridade: Eskola presiza eletrisidade.</div>
                <?php else: ?>
                    <div class="alert alert-success">✅ Eskola ho kondisaun di'ak.</div>
                <?php endif; ?>
                <hr>
                <h5>Kompletu Fasilidade</h5>
                <div class="progress">
                    <?php 
                    $completion = 0;
                    if ($school['iha_bee_moos']) $completion += 33;
                    if ($school['iha_eletrisidade']) $completion += 33;
                    if ($school['iha_toilet']) $completion += 34;
                    ?>
                    <div class="progress-bar bg-success" style="width: <?= $completion ?>%"><?= $completion ?>%</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let googleMap = null;
let googleMarker = null;
let baseLayer = null;
let satelliteLayer = null;
let isSatelliteView = false;
const googleMapsApiKey = '<?= addslashes($googleMapsApiKey) ?>';

function loadGoogleMapsScript(key, onLoad, onError) {
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(key)}&libraries=places`;
    script.async = true;
    script.defer = true;
    script.onload = onLoad;
    script.onerror = onError;
    document.head.appendChild(script);
}

function initLeafletMap(errorMessage = '') {
    const lat = <?= $school['latitude'] ?>;
    const lng = <?= $school['longitude'] ?>;

    googleMap = L.map('schoolMap', {
        center: [lat, lng],
        zoom: 17,
        maxZoom: 19,
        minZoom: 13,
        zoomControl: true
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(googleMap);

    const markerColor = <?= $school['iha_bee_moos'] ?> ? '#27AE60' : '#E74C3C';
    const customIcon = L.divIcon({
        html: `<div style="background: ${markerColor}; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid #F39C12; box-shadow: 0 4px 12px rgba(0,0,0,0.2);"><i class="fas fa-school" style="color: white; font-size: 20px;"></i></div>`,
        iconSize: [44, 44],
        className: 'custom-marker-icon'
    });

    baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(googleMap);

    satelliteLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        subdomains: ['mt0','mt1','mt2','mt3'],
        maxZoom: 19,
        attribution: '© Google Satellite'
    });

    googleMarker = L.marker([lat, lng], { icon: customIcon }).addTo(googleMap);
    googleMarker.bindPopup(createPopupContent()).openPopup();

    if (errorMessage) {
        showToast(errorMessage, 'error');
    }
}

function toggleSatelliteView() {
    const btn = document.getElementById('toggleSatelliteBtn');
    if (!btn) return;
    isSatelliteView = !isSatelliteView;

    if (typeof google !== 'undefined' && google.maps && googleMap && googleMap.setMapTypeId) {
        googleMap.setMapTypeId(isSatelliteView ? google.maps.MapTypeId.SATELLITE : google.maps.MapTypeId.ROADMAP);
    } else if (window.L && googleMap instanceof L.Map) {
        if (isSatelliteView) {
            googleMap.removeLayer(baseLayer);
            satelliteLayer.addTo(googleMap);
        } else {
            googleMap.removeLayer(satelliteLayer);
            baseLayer.addTo(googleMap);
        }
    }

    btn.innerHTML = `${isSatelliteView ? '<i class="fas fa-map"></i> Lihat Jalan' : '<i class="fas fa-satellite-dish"></i> Lihat Satelit'}`;
}

// ============================================
// CHANGE MAIN PHOTO
// ============================================
function changeMainPhoto(src) {
    document.getElementById('mainPhoto').src = src;
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
        if (thumb.src === src) thumb.classList.add('active');
    });
}

// ============================================
// SHOW TOAST
// ============================================
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ============================================
// INIT GOOGLE MAPS
// ============================================
function initGoogleMaps() {
    const lat = <?= $school['latitude'] ?>;
    const lng = <?= $school['longitude'] ?>;
    
    const mapOptions = {
        center: { lat: lat, lng: lng },
        zoom: 17,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        zoomControl: true,
        streetViewControl: true,
        fullscreenControl: true
    };
    
    googleMap = new google.maps.Map(document.getElementById('schoolMap'), mapOptions);
    
    const markerColor = <?= $school['iha_bee_moos'] ?> ? 'green' : 'red';
    
    googleMarker = new google.maps.Marker({
        position: { lat: lat, lng: lng },
        map: googleMap,
        title: '<?= addslashes(htmlspecialchars($school['naran_escola'])) ?>',
        animation: google.maps.Animation.DROP,
        icon: {
            url: `https://maps.google.com/mapfiles/ms/icons/${markerColor}-dot.png`,
            scaledSize: new google.maps.Size(44, 44)
        }
    });
    
    const infoWindow = new google.maps.InfoWindow({
        content: createPopupContent()
    });
    
    googleMarker.addListener('click', () => infoWindow.open(googleMap, googleMarker));
    infoWindow.open(googleMap, googleMarker);
}

function createPopupContent() {
    return `
        <div style="min-width: 260px; max-width: 300px;">
            <img src="<?= $main_photo ?>" style="width:100%; height:120px; object-fit:cover; border-radius:8px; margin-bottom:10px;">
            <h6 style="margin:0 0 5px 0; font-weight:bold;"><?= addslashes(htmlspecialchars($school['naran_escola'])) ?></h6>
            <p style="margin:0 0 10px 0; font-size:12px; color:#666;"><i class="fas fa-map-marker-alt"></i> <?= addslashes(htmlspecialchars($school['enderesu'])) ?></p>
            <button onclick="openGoogleMapsWithRoute()" style="background:#F39C12; border:none; padding:8px 12px; border-radius:5px; color:#1A252F; cursor:pointer; width:100%; font-weight:bold;">
                <i class="fas fa-route"></i> Rota ba eskola
            </button>
        </div>
    `;
}

// ============================================
// OPEN GOOGLE MAPS DIRECTLY - INSTANT! (TANPA DELAY)
// ============================================
function openGoogleMapsWithRoute() {
    const schoolLat = <?= $school['latitude'] ?>;
    const schoolLng = <?= $school['longitude'] ?>;
    const destination = `${schoolLat},${schoolLng}`;
    const url = `https://www.google.com/maps/dir/?api=1&destination=${destination}&travelmode=driving`;
    window.open(url, '_blank');
    showToast('Loke Google Maps...', 'info');
}

// ============================================
// RATING STARS
// ============================================
let currentRating = 0;
const stars = document.querySelectorAll('.star-rating');
const ratingInput = document.getElementById('ratingValue');

function updateStarsColor(rating) {
    stars.forEach(star => {
        const starRating = parseInt(star.getAttribute('data-rating'));
        if (starRating <= rating) {
            star.classList.remove('far');
            star.classList.add('fas');
        } else {
            star.classList.remove('fas');
            star.classList.add('far');
        }
    });
}

stars.forEach(star => {
    star.addEventListener('click', function() {
        currentRating = parseInt(this.getAttribute('data-rating'));
        ratingInput.value = currentRating;
        updateStarsColor(currentRating);
    });
    star.addEventListener('mouseenter', function() {
        updateStarsColor(parseInt(this.getAttribute('data-rating')));
    });
    star.addEventListener('mouseleave', function() {
        updateStarsColor(currentRating);
    });
});

// ============================================
// SUBMIT REVIEW
// ============================================
document.getElementById('reviewForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const name = document.getElementById('reviewName').value;
    const comment = document.getElementById('reviewComment').value;
    const rating = parseInt(ratingInput.value);
    
    if (!name || !rating || !comment) {
        showToast('Favor prense naran, rating, no komentariu.', 'error');
        return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envia...';
    
    const data = {
        escola_id: <?= $school['id'] ?>,
        naran: name,
        email: document.getElementById('reviewEmail').value,
        rating: rating,
        komentar: comment
    };
    
    try {
        const response = await fetch(BASE_URL + '/api/avaliasaun.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Falha envia avaliasaun. Favor halodik tan.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Fo Avaliasaun';
    }
});

// ============================================
// INITIALIZE
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    if (googleMapsApiKey) {
        loadGoogleMapsScript(googleMapsApiKey, initGoogleMaps, function() {
            initLeafletMap('Google Maps API key la validu or la suporta. Usu fallback Leaflet.');
        });
    } else {
        initLeafletMap('Google Maps API key la konfiguradu. Usu fallback Leaflet.');
    }
});

// Make functions global
window.changeMainPhoto = changeMainPhoto;
window.openGoogleMapsWithRoute = openGoogleMapsWithRoute;
</script>

<?php include_once '../includes/footer.php'; ?>