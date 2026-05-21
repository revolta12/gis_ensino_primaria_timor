<?php
// =============================================
// Lista Fasilidade Escola - GIS Ensino Primaria Timor-Leste
// =============================================

// Set page configuration
$page_title = 'Lista Fasilidade Escola';
$page_icon = 'fa-school';

// Include required files
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

// Check admin login
checkAdminLogin();

$db = getDB();

// =============================================
// HAMOOS FASILIDADE
// =============================================
if (isset($_GET['delete']) && isAdmin()) {
    $id = (int)$_GET['delete'];
    
    // Check if facility is used by any escola
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM escola_fasilidade WHERE fasilidade_id = ?");
    $stmt->execute([$id]);
    $used_count = $stmt->fetch()['total'];
    
    if ($used_count > 0) {
        setFlashMessage('danger', "⚠️ Fasilidade labele hamoos tanba uza ona husi escola $used_count.");
    } else {
        $stmt = $db->prepare("DELETE FROM fasilidade_escola WHERE id = ?");
        $stmt->execute([$id]);
        setFlashMessage('success', '✅ Fasilidade escola hamoos ho susesu!');
    }
    redirect('/admin/fasilidade/list.php');
    exit();
}

// =============================================
// HETAN DADUS FASILIDADE HOTU
// =============================================
$fasilidades = $db->query("SELECT * FROM fasilidade_escola ORDER BY naran_fasilidade")->fetchAll();
$total_facilities = count($fasilidades);

// Count used facilities
$stmt = $db->query("SELECT COUNT(DISTINCT fasilidade_id) as total FROM escola_fasilidade");
$used_facilities = $stmt->fetch()['total'];

// Count total escolas
$stmt = $db->query("SELECT COUNT(*) as total FROM escola WHERE aktivo = 1");
$total_escolas = $stmt->fetch()['total'];

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
                <i class="fas fa-school"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $total_facilities ?></h3>
                <p class="stat-text">Total Fasilidade Escola</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-mini">
            <div class="stat-icon bg-success-gradient">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $used_facilities ?></h3>
                <p class="stat-text">Fasilidade Uza Ona</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-mini">
            <div class="stat-icon bg-warning-gradient">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $total_escolas ?></h3>
                <p class="stat-text">Escola Rejistu</p>
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
LISTA FASILIDADE ESCOLA
<!-- ============================================= -->
<div class="card-table">
    <div class="card-table-header">
        <div class="header-title">
            <i class="fas fa-list"></i>
            <h5>Lista Fasilidade Escola</h5>
            <span class="badge-count"><?= $total_facilities ?> Fasilidade</span>
        </div>
        <a href="create.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Aumenta Fasilidade
        </a>
    </div>
    
    <div class="card-table-body p-0">
        <?php if (count($fasilidades) > 0): ?>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th width="60">ID</th>
                            <th>Fasilidade</th>
                            <th width="150">Ikon</th>
                            <th width="130">Uza iha</th>
                            <th width="120" class="text-end">Asaun</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fasilidades as $f): 
                            // Count how many escolas use this facility
                            $stmt = $db->prepare("SELECT COUNT(*) as total FROM escola_fasilidade WHERE fasilidade_id = ?");
                            $stmt->execute([$f['id']]);
                            $used_count = $stmt->fetch()['total'];
                        ?>
                        <tr>
                            <td><span class="badge-id">#<?= $f['id'] ?></span></div>
                            <td>
                                <div class="facility-cell">
                                    <div class="facility-icon">
                                        <i class="fas fa-<?= $f['ikonu'] ?? 'school' ?>"></i>
                                    </div>
                                    <div>
                                        <div class="facility-name"><?= htmlspecialchars($f['naran_fasilidade']) ?></div>
                                        <div class="facility-slug"><?= strtolower(str_replace(' ', '-', $f['naran_fasilidade'])) ?></div>
                                    </div>
                                </div>
                             </div>
                            <td>
                                <span class="badge-icon">
                                    <i class="fas fa-<?= $f['ikonu'] ?? 'tag' ?>"></i>
                                    <?= htmlspecialchars($f['ikonu'] ?? 'school') ?>
                                </span>
                             </div>
                            <td>
                                <?php if ($used_count > 0): ?>
                                    <span class="used-badge has-escola">
                                        <i class="fas fa-school"></i> <?= $used_count ?> Escola
                                    </span>
                                <?php else: ?>
                                    <span class="used-badge no-escola">
                                        <i class="fas fa-box"></i> Seidauk Uza
                                    </span>
                                <?php endif; ?>
                             </div>
                            <td class="text-end">
                                <div class="action-buttons">
                                    <a href="edit.php?id=<?= $f['id'] ?>" class="btn-action btn-edit" title="Edita">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (isAdmin()): ?>
                                        <?php if ($used_count > 0): ?>
                                            <button class="btn-action btn-delete disabled" disabled title="Labele hamoos, tanba uza ona iha escola">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <a href="?delete=<?= $f['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Ita boot hakarak hamoos fasilidade <?= addslashes($f['naran_fasilidade']) ?>?')" title="Hamoos">
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
                <i class="fas fa-school fa-4x text-muted mb-3"></i>
                <h5>Seidauk iha data fasilidade escola</h5>
                <p class="text-muted">Favor halo klik iha botão iha leten atu aumenta fasilidade foun</p>
                <a href="create.php" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-2"></i> Aumenta Fasilidade Primeiru
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================= -->
EZEMPLU FASILIDADE ESKOLA POPULÁR
<!-- ============================================= -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card-table">
            <div class="card-table-header">
                <div class="header-title">
                    <i class="fas fa-fire text-warning"></i>
                    <h5>Fasilidade Eskola Ne'ebé Komun</h5>
                </div>
            </div>
            <div class="card-table-body">
                <div class="popular-facilities">
                    <span class="popular-badge"><i class="fas fa-water"></i> Bee Moos</span>
                    <span class="popular-badge"><i class="fas fa-plug"></i> Eletrisidade</span>
                    <span class="popular-badge"><i class="fas fa-toilet"></i> Toilet</span>
                    <span class="popular-badge"><i class="fas fa-book"></i> Biblioteka</span>
                    <span class="popular-badge"><i class="fas fa-chalkboard"></i> Ladrilhosaun</span>
                    <span class="popular-badge"><i class="fas fa-futbol"></i> Area Joga</span>
                    <span class="popular-badge"><i class="fas fa-computer"></i> Kompastru</span>
                    <span class="popular-badge"><i class="fas fa-road"></i> Akses Estrada</span>
                    <span class="popular-badge"><i class="fas fa-utensils"></i> Kantina</span>
                    <span class="popular-badge"><i class="fas fa-wheelchair"></i> Rampa</span>
                    <span class="popular-badge"><i class="fas fa-hand-holding-heart"></i> Merenda Eskolar</span>
                    <span class="popular-badge"><i class="fas fa-tree"></i> Jardim</span>
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
    
    /* Facility Cell */
    .facility-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .facility-icon {
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
    
    .facility-name {
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 2px;
    }
    
    .facility-slug {
        font-size: 11px;
        color: #999;
        font-family: monospace;
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
    
    /* Popular Facilities */
    .popular-facilities {
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
    }
    
    @media (max-width: 768px) {
        .card-table-header {
            flex-direction: column;
            text-align: center;
        }
        .header-title {
            justify-content: center;
        }
        .facility-cell {
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