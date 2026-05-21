<?php
// =============================================
// Lista Eskola - GIS Ensino Primaria Timor-Leste
// =============================================

require_once '../config/database.php';
require_once '../includes/functions.php';

// =============================================
// HELPER FUNCTION
// =============================================
function buildQueryString($params) {
    $page = $params['page'] ?? null;
    unset($params['page']);
    $query = http_build_query($params);
    if ($page) {
        $query .= ($query ? '&' : '') . 'page=' . $page;
    }
    return $query;
}

$db = getDB();

// Get filter parameters
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$kategoria = isset($_GET['kategoria']) ? sanitize($_GET['kategoria']) : '';
$municipio = isset($_GET['municipio']) ? sanitize($_GET['municipio']) : '';
$min_rating = isset($_GET['min_rating']) ? (int)$_GET['min_rating'] : 0;
$filter_water = isset($_GET['water']) ? (int)$_GET['water'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build WHERE clause
$where = ["e.aktivo = 1"];
$params = [];

if ($search) {
    $where[] = "(e.naran_escola LIKE ? OR e.enderesu LIKE ? OR e.suku LIKE ? OR e.municipio LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($kategoria) {
    $where[] = "k.naran_kategoria = ?";
    $params[] = $kategoria;
}

if ($municipio) {
    $where[] = "e.municipio = ?";
    $params[] = $municipio;
}

if ($min_rating > 0) {
    $where[] = "e.avaliasaun >= ?";
    $params[] = $min_rating;
}

if ($filter_water == 1) {
    $where[] = "e.iha_bee_moos = 1";
} elseif ($filter_water == 2) {
    $where[] = "e.iha_bee_moos = 0";
}

$where_sql = "WHERE " . implode(" AND ", $where);

// Build ORDER BY
switch ($sort) {
    case 'students_desc':
        $order_sql = "ORDER BY e.total_estudante DESC";
        break;
    case 'students_asc':
        $order_sql = "ORDER BY e.total_estudante ASC";
        break;
    case 'rating':
        $order_sql = "ORDER BY e.avaliasaun DESC, e.total_avaliasaun DESC";
        break;
    case 'name_asc':
        $order_sql = "ORDER BY e.naran_escola ASC";
        break;
    default:
        $order_sql = "ORDER BY e.kria_iha DESC";
}

// Get total count
$count_sql = "
    SELECT COUNT(*) as total 
    FROM escola e
    LEFT JOIN kategoria_escola k ON e.kategoria_id = k.id
    $where_sql
";
$stmt = $db->prepare($count_sql);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$total_pages = ceil($total / $per_page);

// Get schools
$sql = "
    SELECT e.*, k.naran_kategoria,
           (SELECT naran_fail FROM foto_escola WHERE escola_id = e.id ORDER BY ordem LIMIT 1) as foto_thumbnail
    FROM escola e
    LEFT JOIN kategoria_escola k ON e.kategoria_id = k.id
    $where_sql
    $order_sql
    LIMIT $offset, $per_page
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$schools = $stmt->fetchAll();

// Get all municipios for filter
$municipios = $db->query("SELECT DISTINCT municipio FROM escola WHERE municipio IS NOT NULL AND municipio != '' AND aktivo = 1 ORDER BY municipio")->fetchAll();

// Get categories for filter
$cats = $db->query("SELECT DISTINCT naran_kategoria FROM kategoria_escola ORDER BY naran_kategoria")->fetchAll();

// Get statistics
$total_schools = $db->query("SELECT COUNT(*) FROM escola WHERE aktivo = 1")->fetchColumn();
$total_students = $db->query("SELECT SUM(total_estudante) FROM escola WHERE aktivo = 1")->fetchColumn() ?: 0;
$schools_water = $db->query("SELECT COUNT(*) FROM escola WHERE iha_bee_moos = 1 AND aktivo = 1")->fetchColumn();

$includeMap = false;
include_once '../includes/header.php';
?>

<style>
    /* Filter Sidebar */
    .filter-sidebar {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        position: sticky;
        top: 90px;
    }
    
    .filter-title {
        font-weight: 700;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #F39C12;
        display: inline-block;
    }
    
    .school-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
    }
    
    .school-list-view {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .school-list-item {
        display: flex;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }
    
    .school-list-item:hover {
        transform: translateX(5px);
    }
    
    .school-list-item .school-img {
        width: 250px;
        height: 180px;
        object-fit: cover;
    }
    
    .school-list-item .school-info {
        flex: 1;
        padding: 15px;
    }
    
    .view-toggle {
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 8px;
        transition: 0.3s;
    }
    
    .view-toggle.active {
        background: #2C3E50;
        color: white;
    }
    
    /* School Card */
    .school-card {
        position: relative;
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .school-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(44, 62, 80, 0.15);
    }
    
    .school-badge-featured {
        position: absolute;
        top: 15px;
        left: 15px;
        background: #F39C12;
        color: #1A252F;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        z-index: 2;
    }
    
    .school-badge-water {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        z-index: 2;
    }
    
    .school-badge-water.no-water {
        background: #E74C3C;
    }
    
    .school-badge-water.has-water {
        background: #27AE60;
    }
    
    .school-card-img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }
    
    .school-stats {
        display: flex;
        gap: 15px;
        font-size: 0.7rem;
        color: #666;
        margin: 8px 0;
    }
    
    .school-facilities {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
        margin: 8px 0;
    }
    
    .school-price {
        font-weight: 700;
        color: #2C3E50;
        font-size: 1.1rem;
    }
    
    .pagination {
        margin-top: 30px;
    }
    
    @media (max-width: 768px) {
        .school-grid {
            grid-template-columns: 1fr;
        }
        .school-list-item {
            flex-direction: column;
        }
        .school-list-item .school-img {
            width: 100%;
            height: 200px;
        }
        .filter-sidebar {
            margin-bottom: 20px;
            position: static;
        }
    }
</style>

<div class="container py-4">
    <div class="row">
        <!-- Sidebar Filter -->
        <div class="col-lg-3 mb-4">
            <div class="filter-sidebar">
                <h5 class="filter-title"><i class="fas fa-filter me-2"></i> Filter Eskola</h5>
                
                <form method="GET" id="filterForm">
                    <?php if ($search): ?>
                        <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
                    <?php endif; ?>
                    
                    <!-- Search -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Buka Eskola</label>
                        <input type="text" name="q" class="form-control" placeholder="Naran eskola ka lokasaun..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    
                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kategoria</label>
                        <select name="kategoria" class="form-select" onchange="this.form.submit()">
                            <option value="">Kategoria hotu</option>
                            <?php foreach ($cats as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['naran_kategoria']) ?>" <?= $kategoria == $cat['naran_kategoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['naran_kategoria']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Municipio -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Municipio</label>
                        <select name="municipio" class="form-select" onchange="this.form.submit()">
                            <option value="">Municipio hotu</option>
                            <?php foreach ($municipios as $m): ?>
                                <option value="<?= htmlspecialchars($m['municipio']) ?>" <?= $municipio == $m['municipio'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['municipio']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Water Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Bee Moos</label>
                        <select name="water" class="form-select" onchange="this.form.submit()">
                            <option value="0">Hotu</option>
                            <option value="1" <?= $filter_water == 1 ? 'selected' : '' ?>>Iha Bee Moos</option>
                            <option value="2" <?= $filter_water == 2 ? 'selected' : '' ?>>La Iha Bee Moos</option>
                        </select>
                    </div>
                    
                    <!-- Rating -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rating Minimal</label>
                        <select name="min_rating" class="form-select" onchange="this.form.submit()">
                            <option value="0">Rating hotu</option>
                            <option value="4" <?= $min_rating == 4 ? 'selected' : '' ?>>4+ Bintang</option>
                            <option value="3" <?= $min_rating == 3 ? 'selected' : '' ?>>3+ Bintang</option>
                            <option value="2" <?= $min_rating == 2 ? 'selected' : '' ?>>2+ Bintang</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-search"></i> Aplica Filter
                    </button>
                    <a href="escola.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-undo-alt"></i> Reset
                    </a>
                </form>
                
                <!-- Quick Stats -->
                <hr class="my-3">
                <div class="small text-muted">
                    <div><i class="fas fa-school"></i> Total: <?= number_format($total_schools) ?> eskola</div>
                    <div><i class="fas fa-users"></i> Estudante: <?= number_format($total_students) ?></div>
                    <div><i class="fas fa-water"></i> Iha bee: <?= number_format($schools_water) ?> eskola</div>
                </div>
            </div>
        </div>
        
        <!-- School List -->
        <div class="col-lg-9">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-school text-primary me-2"></i>
                        <?php if ($search): ?>
                            Rezultadu ba: "<?= htmlspecialchars($search) ?>"
                        <?php else: ?>
                            Lista Eskola Primaria iha Timor-Leste
                        <?php endif; ?>
                    </h4>
                    <p class="text-muted small">Hato'o <?= count($schools) ?> husi <?= $total ?> eskola</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="text-muted small">Sort:</span>
                    <select name="sort" class="form-select form-select-sm w-auto" onchange="updateSort(this.value)">
                        <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Foun Liuliu</option>
                        <option value="students_desc" <?= $sort == 'students_desc' ? 'selected' : '' ?>>Estudante Barak</option>
                        <option value="students_asc" <?= $sort == 'students_asc' ? 'selected' : '' ?>>Estudante Ki'ik</option>
                        <option value="rating" <?= $sort == 'rating' ? 'selected' : '' ?>>Rating Aas</option>
                        <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Naran A-Z</option>
                    </select>
                    
                    <!-- View Toggle -->
                    <div class="btn-group btn-group-sm ms-2">
                        <button type="button" class="view-toggle btn btn-outline-secondary active" data-view="grid" onclick="setView('grid')">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="view-toggle btn btn-outline-secondary" data-view="list" onclick="setView('list')">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Grid View -->
            <div id="gridView" class="school-grid">
                <?php if (count($schools) > 0): ?>
                    <?php foreach ($schools as $school): ?>
                        <div class="school-card">
                            <?php if ($school['destakadu']): ?>
                                <div class="school-badge-featured"><i class="fas fa-star"></i> Destakadu</div>
                            <?php endif; ?>
                            <div class="school-badge-water <?= $school['iha_bee_moos'] ? 'has-water' : 'no-water' ?>">
                                <?php if ($school['iha_bee_moos']): ?>
                                    <i class="fas fa-water"></i> Iha Bee
                                <?php else: ?>
                                    <i class="fas fa-times"></i> La iha Bee
                                <?php endif; ?>
                            </div>
                            <?php
                            $foto = $school['foto_thumbnail'] ? BASE_URL . '/' . $school['foto_thumbnail'] : BASE_URL . '/assets/img/escola-placeholder.jpg';
                            ?>
                            <img src="<?= $foto ?>" class="school-card-img" alt="<?= htmlspecialchars($school['naran_escola']) ?>" loading="lazy">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-secondary"><?= htmlspecialchars($school['naran_kategoria'] ?? 'Eskola') ?></span>
                                    <?php if ($school['sistema_ensinu']): ?>
                                        <small class="text-muted">
                                            <?php 
                                            $langs = explode(',', $school['sistema_ensinu']);
                                            echo '<i class="fas fa-language"></i> ' . count($langs);
                                            ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($school['naran_escola']) ?></h5>
                                <div class="mb-2"><?= renderStars($school['avaliasaun']) ?> <small>(<?= $school['total_avaliasaun'] ?> avaliasaun)</small></div>
                                <p class="card-text small text-muted mb-2">
                                    <i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($school['suku'] ?: $school['postu_administrativu'] ?: $school['municipio']) ?>
                                </p>
                                <div class="school-stats">
                                    <span><i class="fas fa-users"></i> <?= number_format($school['total_estudante']) ?></span>
                                    <span><i class="fas fa-chalkboard-user"></i> <?= number_format($school['total_profesor']) ?></span>
                                </div>
                                <div class="school-facilities">
                                    <?php if ($school['iha_bee_moos']): ?>
                                        <span class="badge bg-success"><i class="fas fa-water"></i> Bee</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fas fa-times"></i> Bee</span>
                                    <?php endif; ?>
                                    <?php if ($school['iha_eletrisidade']): ?>
                                        <span class="badge bg-success"><i class="fas fa-plug"></i> Luz</span>
                                    <?php endif; ?>
                                    <?php if ($school['iha_toilet']): ?>
                                        <span class="badge bg-success"><i class="fas fa-toilet"></i> Toilet</span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <a href="<?= BASE_URL ?>/escola/<?= $school['slug'] ?>" class="btn btn-sm btn-primary">Detallu <i class="fas fa-arrow-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-school fa-4x text-muted mb-3"></i>
                        <h5>La iha eskola ne'ebé kombina ho filter</h5>
                        <p class="text-muted">Favor troka filter ka reset atu hare lista kompletu</p>
                        <a href="escola.php" class="btn btn-primary">Reset Filter</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- List View (hidden by default) -->
            <div id="listView" class="school-list-view" style="display: none;">
                <?php foreach ($schools as $school): ?>
                    <div class="school-list-item">
                        <?php
                        $foto = $school['foto_thumbnail'] ? BASE_URL . '/' . $school['foto_thumbnail'] : BASE_URL . '/assets/img/escola-placeholder.jpg';
                        ?>
                        <img src="<?= $foto ?>" class="school-img" alt="<?= htmlspecialchars($school['naran_escola']) ?>" loading="lazy">
                        <div class="school-info">
                            <div class="d-flex justify-content-between">
                                <h5 class="mb-1"><?= htmlspecialchars($school['naran_escola']) ?></h5>
                                <span class="badge bg-secondary"><?= htmlspecialchars($school['naran_kategoria'] ?? 'Eskola') ?></span>
                            </div>
                            <div class="mb-2"><?= renderStars($school['avaliasaun']) ?> (<?= $school['total_avaliasaun'] ?> avaliasaun)</div>
                            <p class="small text-muted mb-2"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($school['enderesu']) ?></p>
                            <div class="d-flex gap-3 mb-2">
                                <span><i class="fas fa-users"></i> <?= number_format($school['total_estudante']) ?> estudante</span>
                                <span><i class="fas fa-chalkboard-user"></i> <?= number_format($school['total_profesor']) ?> professor</span>
                            </div>
                            <div class="d-flex gap-2 mb-2">
                                <?php if ($school['iha_bee_moos']): ?>
                                    <span class="badge bg-success"><i class="fas fa-water"></i> Bee</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="fas fa-times"></i> La iha bee</span>
                                <?php endif; ?>
                                <?php if ($school['iha_eletrisidade']): ?>
                                    <span class="badge bg-success"><i class="fas fa-plug"></i> Luz</span>
                                <?php endif; ?>
                                <?php if ($school['iha_toilet']): ?>
                                    <span class="badge bg-success"><i class="fas fa-toilet"></i> Toilet</span>
                                <?php endif; ?>
                            </div>
                            <p class="small"><?= htmlspecialchars(substr($school['deskrisaun'] ?? '', 0, 150)) ?>...</p>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <a href="<?= BASE_URL ?>/escola/<?= $school['slug'] ?>" class="btn btn-sm btn-primary">Detallu <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= buildQueryString(array_merge($_GET, ['page' => $page - 1])) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= buildQueryString(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= buildQueryString(array_merge($_GET, ['page' => $page + 1])) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function updateSort(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', value);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }
    
    function setView(view) {
        const gridView = document.getElementById('gridView');
        const listView = document.getElementById('listView');
        const buttons = document.querySelectorAll('.view-toggle');
        
        if (view === 'grid') {
            gridView.style.display = 'grid';
            listView.style.display = 'none';
            buttons.forEach(btn => {
                if (btn.getAttribute('data-view') === 'grid') {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            localStorage.setItem('schoolView', 'grid');
        } else {
            gridView.style.display = 'none';
            listView.style.display = 'flex';
            buttons.forEach(btn => {
                if (btn.getAttribute('data-view') === 'list') {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            localStorage.setItem('schoolView', 'list');
        }
    }
    
    // Load saved view preference
    const savedView = localStorage.getItem('schoolView');
    if (savedView === 'list') {
        setView('list');
    }
</script>

<?php include_once '../includes/footer.php'; ?>