<?php
// =============================================
// Lista Kategoria Escola - GIS Ensino Primaria Timor-Leste
// =============================================

// Set page configuration
$page_title = 'Lista Kategoria Escola';
$page_icon = 'fa-tags';

// Include required files
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

// Check admin login
checkAdminLogin();

$db = getDB();

// =============================================
// HAMOOS KATEGORIA
// =============================================
if (isset($_GET['delete']) && isAdmin()) {
    $id = (int)$_GET['delete'];
    
    // Check if category has escolas
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM escola WHERE kategoria_id = ?");
    $stmt->execute([$id]);
    $escola_count = $stmt->fetch()['total'];
    
    if ($escola_count > 0) {
        setFlashMessage('danger', "⚠️ Kategoria labele hamoos tanba iha escola $escola_count ne'ebé uza.");
    } else {
        $stmt = $db->prepare("DELETE FROM kategoria_escola WHERE id = ?");
        $stmt->execute([$id]);
        setFlashMessage('success', '✅ Kategoria escola hamoos ho susesu!');
    }
    redirect('/admin/kategoria/list.php');
    exit();
}

// =============================================
// HETAN DADUS KATEGORIA HOTU
// =============================================
$categories = $db->query("SELECT * FROM kategoria_escola ORDER BY id")->fetchAll();
$total_categories = count($categories);

// Count used categories (have escolas)
$stmt = $db->query("SELECT COUNT(DISTINCT kategoria_id) as total FROM escola WHERE aktivo = 1");
$used_categories = $stmt->fetch()['total'];

$flash = getFlashMessage();

// Include admin header
require_once '../../includes/admin-header.php';
?>

<!-- ============================================= -->
ESTATÍSTIKA
<!-- ============================================= -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card-mini">
            <div class="stat-icon bg-primary-gradient">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $total_categories ?></h3>
                <p class="stat-text">Total Kategoria Escola</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-mini">
            <div class="stat-icon bg-success-gradient">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $used_categories ?></h3>
                <p class="stat-text">Kategoria Uza Ona</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-mini">
            <div class="stat-icon bg-warning-gradient">
                <i class="fas fa-school"></i>
            </div>
            <div class="stat-info">
                <?php
                $stmt = $db->query("SELECT COUNT(*) as total FROM escola WHERE aktivo = 1");
                $total_escolas = $stmt->fetch()['total'];
                ?>
                <h3 class="stat-number"><?= $total_escolas ?></h3>
                <p class="stat-text">Total Escola Rejistu</p>
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
LISTA KATEGORIA ESCOLA
<!-- ============================================= -->
<div class="card-table">
    <div class="card-table-header">
        <div class="header-title">
            <i class="fas fa-list"></i>
            <h5>Lista Kategoria Escola</h5>
            <span class="badge-count"><?= $total_categories ?> Kategoria</span>
        </div>
        <a href="create.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Aumenta Kategoria
        </a>
    </div>
    
    <div class="card-table-body p-0">
        <?php if ($total_categories > 0): ?>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th width="60">ID</th>
                            <th>Kategoria</th>
                            <th>Deskrisaun</th>
                            <th width="150">Ikon</th>
                            <th width="100">Uza iha</th>
                            <th width="120" class="text-end">Asaun</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): 
                            // Count escolas in this category
                            $stmt = $db->prepare("SELECT COUNT(*) as total FROM escola WHERE kategoria_id = ?");
                            $stmt->execute([$cat['id']]);
                            $escola_count = $stmt->fetch()['total'];
                        ?>
                        <tr>
                            <td><span class="badge-id">#<?= $cat['id'] ?></span></td>
                            <td>
                                <div class="category-cell">
                                    <div class="category-icon">
                                        <i class="fas fa-<?= $cat['ikonu'] ?? 'school' ?>"></i>
                                    </div>
                                    <div>
                                        <div class="category-name"><?= htmlspecialchars($cat['naran_kategoria']) ?></div>
                                        <div class="category-slug"><?= strtolower(str_replace(' ', '-', $cat['naran_kategoria'])) ?></div>
                                    </div>
                                </div>
                             </div>
                            <td>
                                <div class="category-desc">
                                    <?= htmlspecialchars(substr($cat['deskrisaun'] ?? '-', 0, 60)) ?>
                                    <?php if (strlen($cat['deskrisaun'] ?? '') > 60): ?>...<?php endif; ?>
                                </div>
                             </div>
                            <td>
                                <span class="badge-icon">
                                    <i class="fas fa-<?= $cat['ikonu'] ?? 'school' ?>"></i>
                                    <?= htmlspecialchars($cat['ikonu'] ?? 'school') ?>
                                </span>
                             </div>
                            <td>
                                <?php if ($escola_count > 0): ?>
                                    <span class="used-badge has-escola">
                                        <i class="fas fa-school"></i> <?= $escola_count ?> Escola
                                    </span>
                                <?php else: ?>
                                    <span class="used-badge no-escola">
                                        <i class="fas fa-box"></i> Seidauk Uza
                                    </span>
                                <?php endif; ?>
                             </div>
                            <td class="text-end">
                                <div class="action-buttons">
                                    <a href="edit.php?id=<?= $cat['id'] ?>" class="btn-action btn-edit" title="Edita">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (isAdmin()): ?>
                                        <?php if ($escola_count > 0): ?>
                                            <button class="btn-action btn-delete disabled" disabled title="Labele hamoos, tanba iha escola ne'ebé uza">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <a href="?delete=<?= $cat['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Ita boot hakarak hamoos kategoria <?= addslashes($cat['naran_kategoria']) ?>?')" title="Hamoos">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
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
                <i class="fas fa-tags fa-4x text-muted mb-3"></i>
                <h5>Seidauk iha data kategoria escola</h5>
                <p class="text-muted">Favor aumenta kategoria foun liu husi botão iha leten</p>
                <a href="create.php" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-2"></i> Aumenta Kategoria Primeiru
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================= -->
EZEMPLU KATEGORIA ESKOLA POPULÁR
<!-- ============================================= -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card-table">
            <div class="card-table-header">
                <div class="header-title">
                    <i class="fas fa-fire text-warning"></i>
                    <h5>Kategoria Eskola Komun</h5>
                </div>
            </div>
            <div class="card-table-body">
                <div class="popular-categories">
                    <span class="popular-badge"><i class="fas fa-landmark"></i> Eskola Pública</span>
                    <span class="popular-badge"><i class="fas fa-building"></i> Eskola Privada</span>
                    <span class="popular-badge"><i class="fas fa-church"></i> Eskola Katólika</span>
                    <span class="popular-badge"><i class="fas fa-cross"></i> Eskola Evangeliku</span>
                    <span class="popular-badge"><i class="fas fa-users"></i> Eskola Komunitária</span>
                    <span class="popular-badge"><i class="fas fa-wheelchair"></i> Eskola Inkluziva</span>
                    <span class="popular-badge"><i class="fas fa-school"></i> Eskola Modelu</span>
                    <span class="popular-badge"><i class="fas fa-chalkboard"></i> Eskola Rural</span>
                    <span class="popular-badge"><i class="fas fa-city"></i> Eskola Urbanu</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Statistics Card */
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
    
    /* Card Table */
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
    
    /* Table Custom */
    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-custom th {
        padding: 15px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #7f8c8d;
        background: #f8f9fa;
        border-bottom: 1px solid #eee;
    }
    
    .table-custom td {
        padding: 16px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    
    .table-custom tbody tr:hover {
        background: #fafafa;
    }
    
    /* Badge ID */
    .badge-id {
        background: #f0f0f0;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        color: #666;
        font-family: monospace;
    }
    
    /* Category Cell */
    .category-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .category-icon {
        width: 40px;
        height: 40px;
        background: rgba(44, 62, 80, 0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2C3E50;
        font-size: 18px;
    }
    
    .category-name {
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 2px;
    }
    
    .category-slug {
        font-size: 11px;
        color: #999;
        font-family: monospace;
    }
    
    /* Category Description */
    .category-desc {
        font-size: 13px;
        color: #555;
        max-width: 250px;
    }
    
    /* Badge Icon */
    .badge-icon {
        background: #f8f9fa;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: #2C3E50;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    /* Used Badge */
    .used-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .used-badge.has-escola {
        background: rgba(39, 174, 96, 0.1);
        color: #27AE60;
    }
    
    .used-badge.no-escola {
        background: #f0f0f0;
        color: #999;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
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
        transform: translateY(-2px);
    }
    
    .btn-delete {
        background: rgba(231, 76, 60, 0.1);
        color: #E74C3C;
    }
    
    .btn-delete:not(.disabled):hover {
        background: #E74C3C;
        color: white;
        transform: translateY(-2px);
    }
    
    .btn-delete.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Popular Categories */
    .popular-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .popular-badge {
        background: #f8f9fa;
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 500;
        color: #333;
        transition: all 0.3s ease;
        cursor: default;
    }
    
    .popular-badge i {
        color: #2C3E50;
        margin-right: 8px;
        width: 20px;
    }
    
    .popular-badge:hover {
        background: rgba(44, 62, 80, 0.1);
        transform: translateY(-2px);
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
    
    /* Responsive */
    @media (max-width: 992px) {
        .table-custom {
            display: block;
            overflow-x: auto;
        }
        .action-buttons {
            flex-wrap: wrap;
        }
        .category-desc {
            max-width: 180px;
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
        .category-cell {
            min-width: 180px;
        }
        .popular-badge {
            font-size: 11px;
            padding: 6px 12px;
        }
    }
</style>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?>