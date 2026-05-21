<?php
// =============================================
// Lista Escola - GIS Ensino Primaria Timor-Leste
// =============================================

// Set page configuration
$page_title = 'Lista Escola';
$page_icon = 'fa-school';

// Include required files
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

// Check admin login
checkAdminLogin();

// Include admin header
require_once '../../includes/admin-header.php';

$db = getDB();

// =============================================
// HAMOOS ESCOLA
// =============================================
if (isset($_GET['delete']) && isAdmin()) {
    $id = (int)$_GET['delete'];
    
    $db->prepare("DELETE FROM escola_fasilidade WHERE escola_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM foto_escola WHERE escola_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM avaliasaun_escola WHERE escola_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM escola WHERE id = ?")->execute([$id]);
    
    setFlashMessage('success', '✅ Escola hamoos ho susesu!');
    redirect('/admin/escola/list.php');
    exit();
}

// =============================================
// TROKA STATUS AKTIVU / DESTAKADU
// =============================================
if (isset($_GET['toggle_aktif'])) {
    $id = (int)$_GET['toggle_aktif'];
    $db->prepare("UPDATE escola SET aktivo = NOT aktivo WHERE id = ?")->execute([$id]);
    setFlashMessage('success', 'Status escola troka ona!');
    redirect('/admin/escola/list.php');
    exit();
}

if (isset($_GET['toggle_destakadu'])) {
    $id = (int)$_GET['toggle_destakadu'];
    $db->prepare("UPDATE escola SET destakadu = NOT destakadu WHERE id = ?")->execute([$id]);
    setFlashMessage('success', 'Status destakadu troka ona!');
    redirect('/admin/escola/list.php');
    exit();
}

// =============================================
// HETAN DADUS ESCOLA
// =============================================
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$bee_filter = isset($_GET['bee_moos']) ? (int)$_GET['bee_moos'] : 0;

$where = ["1=1"];
$params = [];

if ($search) {
    $where[] = "(e.naran_escola LIKE ? OR e.enderesu LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($kategori_filter > 0) {
    $where[] = "e.kategoria_id = ?";
    $params[] = $kategori_filter;
}

if ($bee_filter == 1) {
    $where[] = "e.iha_bee_moos = 1";
} elseif ($bee_filter == 2) {
    $where[] = "e.iha_bee_moos = 0";
}

$where_sql = "WHERE " . implode(" AND ", $where);

$sql = "
    SELECT e.*, k.naran_kategoria 
    FROM escola e 
    LEFT JOIN kategoria_escola k ON e.kategoria_id = k.id 
    $where_sql
    ORDER BY e.kria_iha DESC
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$escolas = $stmt->fetchAll();

// Get categories for filter
$categories = $db->query("SELECT id, naran_kategoria FROM kategoria_escola ORDER BY naran_kategoria")->fetchAll();

$total_escolas = count($escolas);
$active_escolas = count(array_filter($escolas, fn($e) => $e['aktivo'] == 1));
$featured_escolas = count(array_filter($escolas, fn($e) => $e['destakadu'] == 1));
$escolas_iha_bee = count(array_filter($escolas, fn($e) => $e['iha_bee_moos'] == 1));

$flash = getFlashMessage();
?>

<!-- ============================================= -->
ESTATÍSTIKA
<!-- ============================================= -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card-mini">
            <div class="stat-icon bg-primary-gradient">
                <i class="fas fa-school"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $total_escolas ?></h3>
                <p class="stat-text">Total Escola</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-mini">
            <div class="stat-icon bg-success-gradient">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $active_escolas ?></h3>
                <p class="stat-text">Escola Aktivo</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-mini">
            <div class="stat-icon bg-warning-gradient">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $featured_escolas ?></h3>
                <p class="stat-text">Escola Destakadu</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-mini">
            <div class="stat-icon bg-info-gradient">
                <i class="fas fa-water"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $escolas_iha_bee ?></h3>
                <p class="stat-text">Iha Bee Moos</p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================= -->
MENSAGEM FLASH
<!-- ============================================= -->
<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] == 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-<?= $flash['type'] == 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- ============================================= -->
FILTRU & BUKA
<!-- ============================================= -->
<div class="card-filter mb-4">
    <div class="card-filter-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Buka naran escola ka enderesu..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="kategori" class="form-select">
                    <option value="0">Kategoria Hotu</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $kategori_filter == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['naran_kategoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="bee_moos" class="form-select">
                    <option value="0">Bee Moos: Hotu</option>
                    <option value="1" <?= $bee_filter == 1 ? 'selected' : '' ?>>Iha Bee Moos</option>
                    <option value="2" <?= $bee_filter == 2 ? 'selected' : '' ?>>La Iha Bee Moos</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i> Filtru
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================= -->
LISTA ESCOLA
<!-- ============================================= -->
<div class="card-table">
    <div class="card-table-header">
        <div class="header-title">
            <i class="fas fa-list"></i>
            <h5>Lista Escola</h5>
            <span class="badge-count"><?= $total_escolas ?> Escola</span>
        </div>
        <a href="create.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Aumenta Escola
        </a>
    </div>
    
    <div class="card-table-body p-0">
        <?php if (count($escolas) > 0): ?>
            <div class="table-responsive">
                <table class="table-escola">
                    <thead>
                        <tr>
                            <th>Escola</th>
                            <th>Kategoria</th>
                            <th>Lokasaun</th>
                            <th>Estudante</th>
                            <th>Profesor</th>
                            <th>Bee Moos</th>
                            <th>Status</th>
                            <th>Destakadu</th>
                            <th width="120">Asaun</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($escolas as $escola): ?>
                        <tr>
                            <td>
                                <div class="escola-cell">
                                    <?php if ($escola['foto_prinsipal']): ?>
                                        <img src="<?= BASE_URL . '/' . $escola['foto_prinsipal'] ?>" class="escola-img" alt="">
                                    <?php else: ?>
                                        <div class="escola-img-placeholder">
                                            <i class="fas fa-school"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="escola-info">
                                        <div class="escola-name"><?= htmlspecialchars($escola['naran_escola']) ?></div>
                                        <div class="escola-address"><?= htmlspecialchars(substr($escola['enderesu'] ?? '', 0, 50)) ?></div>
                                    </div>
                                </div>
                             </div>
                            <td>
                                <span class="badge-category">
                                    <?= htmlspecialchars($escola['naran_kategoria'] ?? '-') ?>
                                </span>
                             </div>
                            <td>
                                <div class="location-cell">
                                    <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                    <?= htmlspecialchars($escola['suku'] ?? '-') ?>
                                </div>
                             </div>
                            <td>
                                <div class="student-cell">
                                    <span class="student-number"><?= number_format($escola['total_estudante']) ?></span>
                                    <span class="student-label">alunu</span>
                                </div>
                             </div>
                            <td>
                                <div class="teacher-cell">
                                    <span class="teacher-number"><?= number_format($escola['total_profesor']) ?></span>
                                    <span class="teacher-label">profesor</span>
                                </div>
                             </div>
                            <td>
                                <?php if ($escola['iha_bee_moos']): ?>
                                    <span class="facility-badge facility-yes">
                                        <i class="fas fa-check-circle"></i> Iha
                                    </span>
                                <?php else: ?>
                                    <span class="facility-badge facility-no">
                                        <i class="fas fa-times-circle"></i> La iha
                                    </span>
                                <?php endif; ?>
                             </div>
                            <td>
                                <?php if ($escola['aktivo']): ?>
                                    <span class="status-badge status-active">
                                        <i class="fas fa-circle"></i> Aktivo
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-inactive">
                                        <i class="fas fa-circle"></i> Inaktivo
                                    </span>
                                <?php endif; ?>
                             </div>
                            <td>
                                <div class="text-center">
                                    <a href="?toggle_destakadu=<?= $escola['id'] ?>" class="btn-featured <?= $escola['destakadu'] ? 'active' : '' ?>" onclick="return confirm('Troka status destakadu?')">
                                        <i class="fas fa-star"></i>
                                    </a>
                                </div>
                             </div>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit.php?id=<?= $escola['id'] ?>" class="btn-action btn-edit" title="Edita">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?toggle_aktif=<?= $escola['id'] ?>" class="btn-action btn-toggle" onclick="return confirm('Troka status ativu/inativu?')" title="Troka Status">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <?php if (isAdmin()): ?>
                                        <a href="?delete=<?= $escola['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Ita boot hakarak hamos escola ne\'e? Dadus hotu sei lakon!')" title="Hamos">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                             </div>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-school fa-4x text-muted mb-3"></i>
                <h5>Seidauk iha data escola</h5>
                <p class="text-muted">Favor aumenta escola foun liu husi botão iha leten</p>
                <a href="create.php" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-2"></i> Aumenta Escola Primeiru
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Statistics Card Mini */
    .stat-card-mini {
        background: white;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .stat-card-mini:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(44, 62, 80, 0.1);
    }
    
    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }
    
    .bg-primary-gradient { background: linear-gradient(135deg, #2C3E50, #1A252F); }
    .bg-success-gradient { background: linear-gradient(135deg, #27AE60, #1E8449); }
    .bg-warning-gradient { background: linear-gradient(135deg, #F39C12, #E67E22); }
    .bg-info-gradient { background: linear-gradient(135deg, #3498DB, #2980B9); }
    
    .stat-number {
        font-size: 28px;
        font-weight: 800;
        margin: 0;
        color: #1a1a1a;
        line-height: 1.2;
    }
    
    .stat-text {
        font-size: 13px;
        color: #7f8c8d;
        margin: 0;
    }
    
    /* Filter Card */
    .card-filter {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .card-filter-body {
        padding: 20px;
    }
    
    .search-box {
        position: relative;
    }
    
    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        z-index: 1;
    }
    
    .search-box input {
        padding-left: 40px;
    }
    
    /* Table Card */
    .card-table {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .card-table-header {
        padding: 18px 24px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .header-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .header-title i {
        font-size: 20px;
        color: #2C3E50;
    }
    
    .header-title h5 {
        margin: 0;
        font-weight: 700;
    }
    
    .badge-count {
        background: #f0f0f0;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: #666;
    }
    
    /* Table Styles */
    .table-escola {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-escola th {
        padding: 15px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #7f8c8d;
        background: #f8f9fa;
        border-bottom: 1px solid #eee;
    }
    
    .table-escola td {
        padding: 16px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    
    .table-escola tbody tr:hover {
        background: #fafafa;
    }
    
    /* Escola Cell */
    .escola-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .escola-img {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        object-fit: cover;
    }
    
    .escola-img-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
    }
    
    .escola-info {
        flex: 1;
    }
    
    .escola-name {
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 4px;
    }
    
    .escola-address {
        font-size: 12px;
        color: #999;
    }
    
    /* Badge Category */
    .badge-category {
        background: #f0f0f0;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    
    /* Location Cell */
    .location-cell {
        font-size: 13px;
        color: #555;
    }
    
    /* Student & Teacher Cells */
    .student-cell, .teacher-cell {
        display: flex;
        flex-direction: column;
    }
    
    .student-number, .teacher-number {
        font-weight: 700;
        font-size: 16px;
        color: #2C3E50;
    }
    
    .student-label, .teacher-label {
        font-size: 10px;
        color: #999;
    }
    
    /* Facility Badge */
    .facility-badge {
        font-size: 12px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .facility-yes {
        background: rgba(39, 174, 96, 0.1);
        color: #27AE60;
    }
    
    .facility-no {
        background: rgba(231, 76, 60, 0.1);
        color: #E74C3C;
    }
    
    /* Status Badge */
    .status-badge {
        font-size: 12px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .status-badge i {
        font-size: 8px;
    }
    
    .status-active {
        background: rgba(39, 174, 96, 0.1);
        color: #27AE60;
    }
    
    .status-inactive {
        background: rgba(149, 165, 166, 0.1);
        color: #95A5A6;
    }
    
    /* Button Featured */
    .btn-featured {
        background: #f0f0f0;
        color: #ccc;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .btn-featured.active {
        background: #F39C12;
        color: white;
    }
    
    .btn-featured:hover {
        background: #F39C12;
        color: white;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .btn-action {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .btn-edit {
        background: rgba(52, 152, 219, 0.1);
        color: #3498DB;
    }
    
    .btn-edit:hover {
        background: #3498DB;
        color: white;
    }
    
    .btn-toggle {
        background: rgba(149, 165, 166, 0.1);
        color: #95A5A6;
    }
    
    .btn-toggle:hover {
        background: #95A5A6;
        color: white;
    }
    
    .btn-delete {
        background: rgba(231, 76, 60, 0.1);
        color: #E74C3C;
    }
    
    .btn-delete:hover {
        background: #E74C3C;
        color: white;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    
    .empty-state i {
        opacity: 0.5;
    }
    
    .empty-state h5 {
        margin: 15px 0 5px;
        font-weight: 700;
    }
    
    /* Form Controls */
    .form-control, .form-select {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        padding: 10px 15px;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #2C3E50;
        box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .table-escola {
            display: block;
            overflow-x: auto;
        }
        .action-buttons {
            flex-wrap: wrap;
        }
        .stat-card-mini {
            padding: 15px;
        }
        .stat-number {
            font-size: 22px;
        }
        .stat-icon {
            width: 45px;
            height: 45px;
            font-size: 20px;
        }
    }
    
    @media (max-width: 768px) {
        .card-table-header {
            flex-direction: column;
            text-align: center;
        }
        .header-title {
            justify-content: center;
        }
        .escola-cell {
            min-width: 200px;
        }
    }
</style>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?>