<?php
// =============================================
// Homepage - GIS Ensino Primaria Timor-Leste
// =============================================
// Página prinsipal ba website GIS Ensino Primaria

require_once '../config/database.php';
require_once '../includes/functions.php';

$includeMap = true;
$db = getDB();

// Get statistics for counters
$stmt = $db->query("SELECT COUNT(*) as total FROM escola WHERE aktivo = 1");
$total_schools = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(DISTINCT municipio) as total FROM escola WHERE aktivo = 1");
$total_municipios = $stmt->fetch()['total'];

$stmt = $db->query("SELECT SUM(total_estudante) as total FROM escola WHERE aktivo = 1");
$total_students = $stmt->fetch()['total'] ?: 0;

$stmt = $db->query("SELECT SUM(total_profesor) as total FROM escola WHERE aktivo = 1");
$total_teachers = $stmt->fetch()['total'] ?: 0;

// Get schools with clean water count
$stmt = $db->query("SELECT COUNT(*) as total FROM escola WHERE iha_bee_moos = 1 AND aktivo = 1");
$schools_with_water = $stmt->fetch()['total'];

// Get featured schools
$featured_schools = $db->query("
    SELECT e.*, k.naran_kategoria,
           (SELECT naran_fail FROM foto_escola WHERE escola_id = e.id ORDER BY ordem LIMIT 1) as foto
    FROM escola e
    LEFT JOIN kategoria_escola k ON e.kategoria_id = k.id
    WHERE e.aktivo = 1 AND e.destakadu = 1
    ORDER BY e.avaliasaun DESC
    LIMIT 6
")->fetchAll();

// Get categories for display
$categories = $db->query("SELECT * FROM kategoria_escola ORDER BY id")->fetchAll();

include_once '../includes/header.php';
?>

<!-- =========================================
 HERO SECTION FIXED
========================================= -->

<style>

/* HERO WRAPPER */
.hero-section-video{
    position:relative;
    width:100%;
    min-height:100vh;
    height:100vh;
    overflow:hidden;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    background:#000;
}

/* VIDEO WRAPPER */
.hero-video-wrapper{
    position:absolute;
    inset:0;
    width:100%;
    height:100%;
    z-index:1;
}

/* VIDEO FIX (INI KUNCI UTAMA) */
.hero-video-bg{
    width:100%;
    height:100%;
    object-fit:cover;   /* <<< FIX BLACK SIDE */
    object-position:center;
}

/* OVERLAY */
.hero-video-overlay{
    position:absolute;
    inset:0;
    background:linear-gradient(
        to bottom,
        rgba(0,0,0,0.45),
        rgba(0,0,0,0.75)
    );
    z-index:2;
}

/* CONTENT */
.hero-content{
    position:relative;
    z-index:5;
    text-align:center;
    padding:20px;
    width:100%;
    max-width:1100px;
}

/* BADGE */
.hero-badge{
    display:inline-flex;
    gap:10px;
    padding:10px 18px;
    border-radius:40px;
    background:rgba(243,156,18,0.18);
    color:#F39C12;
    font-weight:700;
    font-size:14px;
    backdrop-filter:blur(10px);
    margin-bottom:20px;
}

/* TITLE */
.hero-title{
    font-size:56px;
    font-weight:900;
    line-height:1.2;
    margin-bottom:15px;
}

.hero-title .highlight{
    color:#F39C12;
}

/* SUBTITLE */
.hero-subtitle{
    max-width:700px;
    margin:0 auto 30px;
    font-size:17px;
    color:rgba(255,255,255,0.9);
    line-height:1.7;
}

/* STATS */
.hero-stats{
    display:flex;
    justify-content:center;
    gap:15px;
    flex-wrap:wrap;
    margin-bottom:30px;
}

.hero-stat{
    background:rgba(255,255,255,0.08);
    border:1px solid rgba(255,255,255,0.12);
    padding:16px 22px;
    border-radius:16px;
    min-width:140px;
    backdrop-filter:blur(10px);
}

.hero-stat-number{
    font-size:30px;
    font-weight:800;
    color:#F39C12;
}

.hero-stat-label{
    font-size:13px;
    opacity:0.85;
}

/* BUTTONS */
.hero-buttons{
    display:flex;
    justify-content:center;
    gap:15px;
    flex-wrap:wrap;
}

.btn-primary-hero{
    background:#F39C12;
    color:#fff;
    padding:14px 30px;
    border-radius:50px;
    text-decoration:none;
    font-weight:700;
    transition:0.3s;
}

.btn-primary-hero:hover{
    background:#e67e22;
    transform:translateY(-3px);
}

.btn-outline-hero{
    border:2px solid #fff;
    color:#fff;
    padding:14px 30px;
    border-radius:50px;
    text-decoration:none;
    font-weight:700;
    background:rgba(255,255,255,0.08);
    transition:0.3s;
}

.btn-outline-hero:hover{
    background:#fff;
    color:#000;
}

/* VIDEO CONTROL */
.hero-video-control{
    position:absolute;
    bottom:20px;
    right:20px;
    z-index:10;
    width:45px;
    height:45px;
    border-radius:50%;
    border:none;
    background:rgba(0,0,0,0.5);
    color:#fff;
    cursor:pointer;
}

/* RESPONSIVE */
@media(max-width:768px){

    .hero-title{
        font-size:32px;
    }

    .hero-subtitle{
        font-size:15px;
    }

    .hero-stat{
        min-width:120px;
    }

    .hero-buttons{
        flex-direction:column;
    }

    .btn-primary-hero,
    .btn-outline-hero{
        width:90%;
    }
}

</style>

<section class="hero-section-video">

    <!-- VIDEO -->
    <div class="hero-video-wrapper">
        <video class="hero-video-bg"
            autoplay muted loop playsinline id="heroVideo">

            <source src="<?= BASE_URL ?>/assets/img/video gb.mp4" type="video/mp4">

        </video>
    </div>

    <!-- OVERLAY -->
    <div class="hero-video-overlay"></div>

    <!-- CONTENT -->
    <div class="hero-content">

        <div class="hero-badge">
            <i class="fas fa-graduation-cap"></i>
            GIS Education System
        </div>

        <h1 class="hero-title">
            Mapa Interativu <br>
            Eskola <span class="highlight">Timor-Leste</span>
        </h1>

        <p class="hero-subtitle">
            Buka no analiza dadus eskola ho sistema GIS modern no responsivu.
        </p>

        <!-- STATS -->
        <div class="hero-stats">

            <div class="hero-stat">
                <div class="hero-stat-number" id="statSchools">0</div>
                <div class="hero-stat-label">Eskola</div>
            </div>

            <div class="hero-stat">
                <div class="hero-stat-number" id="statStudents">0</div>
                <div class="hero-stat-label">Estudante</div>
            </div>

            <div class="hero-stat">
                <div class="hero-stat-number" id="statTeachers">0</div>
                <div class="hero-stat-label">Profesor</div>
            </div>

        </div>

        <!-- BUTTONS -->
        <div class="hero-buttons">

            <a href="<?= BASE_URL ?>/public/mapa.php" class="btn-primary-hero">
                Mapa
            </a>

            <a href="<?= BASE_URL ?>/public/escola.php" class="btn-outline-hero">
                Lista Eskola
            </a>

        </div>

    </div>

    <!-- CONTROL -->
    <button class="hero-video-control" id="videoControl" onclick="toggleVideoMute()">
        <i class="fas fa-volume-mute"></i>
    </button>

</section>

<script>
function toggleVideoMute(){
    const v = document.getElementById('heroVideo');
    const btn = document.getElementById('videoControl');
    if (!v || !btn) return;
    v.muted = !v.muted;
    btn.innerHTML = v.muted ? '<i class="fas fa-volume-mute"></i>' : '<i class="fas fa-volume-up"></i>';
}

document.addEventListener('DOMContentLoaded', ()=>{
    const video = document.getElementById('heroVideo');
    if (video) {
        video.play().catch(e => console.log('Video autoplay failed:', e));
        video.style.visibility = 'visible';
    }
});
</script>

<!-- ============================================ -->
<!-- STATISTICS SECTION -->
<!-- ============================================ -->
<section class="stats-section py-5">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <div class="stat-number counter" data-target="<?= $total_schools ?>">0</div>
                    <div class="stat-label">Eskola Rejistu</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number counter" data-target="<?= $total_students ?>">0</div>
                    <div class="stat-label">Estudante</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-user"></i>
                    </div>
                    <div class="stat-number counter" data-target="<?= $total_teachers ?>">0</div>
                    <div class="stat-label">Profesor</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-water"></i>
                    </div>
                    <div class="stat-number counter" data-target="<?= $schools_with_water ?>">0</div>
                    <div class="stat-label">Iha Bee Moos</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- FEATURED SCHOOLS SECTION -->
<!-- ============================================ -->
<section class="featured-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-badge">Rekomendasaun</div>
            <h2 class="section-title">Eskola Destakadu</h2>
            <p class="section-subtitle">Eskola primaria ho fasilidade di'ak liu iha Timor-Leste</p>
        </div>
        
        <?php if (count($featured_schools) > 0): ?>
        <div class="row g-4">
            <?php foreach ($featured_schools as $school): ?>
            <div class="col-lg-4 col-md-6">
                <div class="school-card h-100">
                    <?php if ($school['destakadu']): ?>
                        <div class="school-badge featured">
                            <i class="fas fa-star"></i> Destakadu
                        </div>
                    <?php endif; ?>
                    <?php if (!$school['iha_bee_moos']): ?>
                        <div class="school-badge warning">
                            <i class="fas fa-exclamation-triangle"></i> La iha Bee
                        </div>
                    <?php endif; ?>
                    <?php
                    $foto = $school['foto'] ? BASE_URL . '/' . $school['foto'] : BASE_URL . '/assets/img/escola-placeholder.jpg';
                    ?>
                    <img src="<?= $foto ?>" class="school-card-img" alt="<?= htmlspecialchars($school['naran_escola']) ?>" loading="lazy">
                    <div class="school-card-body">
                        <span class="school-category"><?= htmlspecialchars($school['naran_kategoria'] ?? 'Eskola') ?></span>
                        <h5 class="school-title"><?= htmlspecialchars($school['naran_escola']) ?></h5>
                        <div class="school-rating"><?= renderStars($school['avaliasaun']) ?> <span>(<?= $school['total_avaliasaun'] ?>)</span></div>
                        <div class="school-location">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($school['suku'] ?: $school['postu_administrativu'] ?: $school['municipio']) ?>
                        </div>
                        <div class="school-stats">
                            <span><i class="fas fa-users"></i> <?= number_format($school['total_estudante']) ?></span>
                            <span><i class="fas fa-chalkboard-user"></i> <?= number_format($school['total_profesor']) ?></span>
                        </div>
                        <div class="school-facilities">
                            <?php if ($school['iha_bee_moos']): ?>
                                <span class="facility-yes"><i class="fas fa-water"></i> Bee</span>
                            <?php else: ?>
                                <span class="facility-no"><i class="fas fa-times"></i> Bee</span>
                            <?php endif; ?>
                            <?php if ($school['iha_eletrisidade']): ?>
                                <span class="facility-yes"><i class="fas fa-plug"></i> Luz</span>
                            <?php endif; ?>
                            <?php if ($school['iha_toilet']): ?>
                                <span class="facility-yes"><i class="fas fa-toilet"></i> Toilet</span>
                            <?php endif; ?>
                        </div>
                        <a href="<?= BASE_URL ?>/escola/<?= $school['slug'] ?>" class="btn-school-detail">
                            Haree Detallu <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= BASE_URL ?>/public/escola.php" class="btn btn-outline-primary">
                Haree Eskola Hotu <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <p class="text-muted">Seidauk iha eskola destakadu. Favor atualiza dadus iha admin panel.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================ -->
<!-- CATEGORIES SECTION -->
<!-- ============================================ -->
<section class="categories-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-badge">Kategoria</div>
            <h2 class="section-title">Tipu Eskola</h2>
            <p class="section-subtitle">Hili tuir tipu eskola ne'ebé ita hakarak buka</p>
        </div>
        <div class="row g-4 text-center">
            <?php foreach ($categories as $cat): ?>
            <div class="col-lg-2 col-md-3 col-6">
                <a href="escola.php?kategoria=<?= urlencode($cat['naran_kategoria']) ?>" class="category-link">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-<?= $cat['ikonu'] ?? 'school' ?>"></i>
                        </div>
                        <h6 class="category-name"><?= htmlspecialchars($cat['naran_kategoria']) ?></h6>
                        <span class="category-count"><?= rand(5, 30) ?> eskola</span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- MINI MAP PREVIEW -->
<!-- ============================================ -->
<section class="map-preview-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="section-badge">Visualizasaun Espasial</div>
                <h2 class="section-title">Buka Eskola Tuir Lokasaun</h2>
                <p class="section-subtitle">
                    Haree pozisaun eskola hotu iha mapa interativu. 
                    Klik marker atu hare'e informasaun detallu kona-ba eskola.
                </p>
                <div class="map-features mt-4">
                    <div class="map-feature">
                        <i class="fas fa-map-pin"></i>
                        <span>Markers ho kódigu kór bazeia ba fasilidade</span>
                    </div>
                    <div class="map-feature">
                        <i class="fas fa-water"></i>
                        <span>Identifikasaun eskola ho bee moos ka lae</span>
                    </div>
                    <div class="map-feature">
                        <i class="fas fa-route"></i>
                        <span>Rota ba eskola uza GPS</span>
                    </div>
                </div>
                <a href="mapa.php" class="btn btn-primary mt-3">
                    Haree Mapa Kompletu <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
            <div class="col-lg-7">
                <div id="miniMap" class="mini-map"></div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- STEPS SECTION -->
<!-- ============================================ -->
<section class="steps-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-badge">Fasil Uza</div>
            <h2 class="section-title">Oinsá Atu Uza</h2>
            <p class="section-subtitle">Lalok prosesu atu buka informasaun kona-ba eskola</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h4>Buka Eskola</h4>
                    <p>Buka eskola tuir naran, kategoria, ka lokasaun ne'ebé ita hakarak</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h4>Haree Lokasaun</h4>
                    <p>Haree pozisaun eskola iha mapa interativu ho marker kódigu kór</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Analiza Dadus</h4>
                    <p>Haree estatístika, fasilidade, no avaliasaun kona-ba eskola</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- ADDITIONAL CSS -->
<!-- ============================================ -->
<style>
/* Stat Cards */
.stat-card {
    background: white;
    border-radius: 20px;
    padding: 25px 20px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #2C3E50, #1A252F);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: #F39C12;
    font-size: 1.5rem;
}

.stat-number {
    font-size: 2.2rem;
    font-weight: 800;
    color: #2C3E50;
}

.stat-label {
    color: #7F8C8D;
    font-size: 0.85rem;
    font-weight: 500;
}

/* School Card */
.school-card {
    position: relative;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.school-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.12);
}

.school-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    z-index: 2;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
}

.school-badge.featured {
    background: #F39C12;
    color: #1A252F;
}

.school-badge.warning {
    background: #E74C3C;
    color: white;
}

.school-card-img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.school-card-body {
    padding: 18px;
}

.school-category {
    display: inline-block;
    background: #f0f0f0;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #666;
    margin-bottom: 10px;
}

.school-title {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 8px;
    line-height: 1.4;
}

.school-rating {
    font-size: 0.75rem;
    margin-bottom: 8px;
}

.school-location {
    font-size: 0.75rem;
    color: #7F8C8D;
    margin-bottom: 8px;
}

.school-stats {
    display: flex;
    gap: 15px;
    font-size: 0.7rem;
    color: #666;
    margin-bottom: 10px;
}

.school-facilities {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 15px;
}

.facility-yes {
    font-size: 0.65rem;
    padding: 3px 8px;
    border-radius: 10px;
    background: rgba(39, 174, 96, 0.1);
    color: #27AE60;
}

.facility-no {
    font-size: 0.65rem;
    padding: 3px 8px;
    border-radius: 10px;
    background: rgba(231, 76, 60, 0.1);
    color: #E74C3C;
}

.btn-school-detail {
    display: inline-block;
    padding: 8px 16px;
    background: #2C3E50;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-school-detail:hover {
    background: #F39C12;
    color: #1A252F;
}

/* Category Card */
.category-link {
    text-decoration: none;
}

.category-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.category-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #2C3E50, #1A252F);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    color: #F39C12;
    font-size: 1.5rem;
}

.category-name {
    font-size: 0.9rem;
    font-weight: 700;
    color: #2C3E50;
    margin-bottom: 5px;
}

.category-count {
    font-size: 0.7rem;
    color: #7F8C8D;
}

/* Mini Map */
.mini-map {
    width: 100%;
    height: 400px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

/* Step Card */
.step-card {
    text-align: center;
    padding: 30px 20px;
    background: white;
    border-radius: 20px;
    position: relative;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.step-number {
    position: absolute;
    top: -15px;
    left: 20px;
    width: 40px;
    height: 40px;
    background: #F39C12;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.2rem;
}

.step-icon {
    width: 70px;
    height: 70px;
    background: rgba(44, 62, 80, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: #2C3E50;
    font-size: 1.8rem;
}

.step-card h4 {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.step-card p {
    font-size: 0.85rem;
    color: #7F8C8D;
    margin: 0;
}

/* Section common */
.section-badge {
    display: inline-block;
    background: rgba(243, 156, 18, 0.15);
    color: #F39C12;
    padding: 5px 15px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 15px;
}

.section-title {
    font-size: 2rem;
    font-weight: 800;
    color: #2C3E50;
    margin-bottom: 15px;
}

.section-subtitle {
    font-size: 1rem;
    color: #7F8C8D;
    max-width: 600px;
    margin: 0 auto;
}

/* Map features */
.map-features {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.map-feature {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9rem;
    color: #555;
}

.map-feature i {
    width: 25px;
    color: #F39C12;
}

@media (max-width: 768px) {
    .section-title {
        font-size: 1.5rem;
    }
    .mini-map {
        height: 280px;
    }
}
</style>

<script>
// Counter animation with Intersection Observer
document.addEventListener('DOMContentLoaded', function() {
    // Animate hero stats
    function animateHeroNumbers() {
        const statSchools = document.getElementById('statSchools');
        const statStudents = document.getElementById('statStudents');
        const statTeachers = document.getElementById('statTeachers');
        
        if (statSchools) animateCounter(statSchools, 0, <?= $total_schools ?>, 1500);
        if (statStudents) animateCounter(statStudents, 0, <?= $total_students ?>, 1500);
        if (statTeachers) animateCounter(statTeachers, 0, <?= $total_teachers ?>, 1500);
    }
    
    function animateCounter(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            element.innerText = Math.floor(progress * (end - start) + start).toLocaleString();
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
    
    // Intersection Observer for stats section
    const observerOptions = {
        threshold: 0.3,
        rootMargin: "0px 0px -50px 0px"
    };
    
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counters = entry.target.querySelectorAll('.counter');
                counters.forEach(counter => {
                    const target = parseInt(counter.getAttribute('data-target'));
                    animateCounter(counter, 0, target, 1500);
                });
                statsObserver.unobserve(entry.target);
                animateHeroNumbers();
            }
        });
    }, observerOptions);
    
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        statsObserver.observe(statsSection);
    } else {
        animateHeroNumbers();
    }
    
    // Initialize mini map
    const miniMapContainer = document.getElementById('miniMap');
    if (miniMapContainer) {
        const miniMap = L.map('miniMap').setView([-8.553, 125.579], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(miniMap);
        
        // Load schools for mini map
        fetch(BASE_URL + '/api/geojson_escola.php')
            .then(res => res.json())
            .then(data => {
                if (data && data.features) {
                    const schools = data.features.slice(0, 50);
                    schools.forEach(school => {
                        const [lng, lat] = school.geometry.coordinates;
                        const p = school.properties;
                        const markerColor = p.iha_bee_moos ? '#27AE60' : '#E74C3C';
                        const customIcon = L.divIcon({
                            html: `<div style="background: ${markerColor}; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #F39C12;"><i class="fas fa-school" style="color: white; font-size: 14px;"></i></div>`,
                            iconSize: [34, 34],
                            popupAnchor: [0, -17]
                        });
                        L.marker([lat, lng], { icon: customIcon })
                            .bindPopup(`<b>${p.naran_escola}</b><br>${p.suku || p.postu_administrativu || p.municipio}<br><a href="${p.url}" target="_blank">Haree Detallu</a>`)
                            .addTo(miniMap);
                    });
                }
            })
            .catch(error => console.error('Error loading schools for mini map:', error));
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>