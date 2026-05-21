<?php
// =============================================
// Moderasaun Avaliasaun - GIS Ensino Primaria Timor-Leste
// =============================================

// Set page configuration
$page_title = 'Moderasaun Avaliasaun Escola';
$page_icon = 'fa-star';

// Include required files
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

// Check admin login
checkAdminLogin();

$db = getDB();

// =============================================
// APROVA / REJEITA AVALIASAUN
// =============================================
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $stmt = $db->prepare("UPDATE avaliasaun_escola SET aprovadu = 1 WHERE id = ?");
    $stmt->execute([$id]);
    
    // Update rating escola
    $stmt = $db->prepare("SELECT escola_id FROM avaliasaun_escola WHERE id = ?");
    $stmt->execute([$id]);
    $review = $stmt->fetch();
    if ($review) {
        updateRatingEscola($review['escola_id'], $db);
    }
    
    setFlashMessage('success', ' Avaliasaun konsege aprova ona');
    redirect('/admin/avaliasaun/list.php');
    exit();
}

if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $stmt = $db->prepare("DELETE FROM avaliasaun_escola WHERE id = ?");
    $stmt->execute([$id]);
    
    setFlashMessage('success', '🗑️ Avaliasaun konsege hamos ona');
    redirect('/admin/avaliasaun/list.php');
    exit();
}

// =============================================
// HETAN DADUS AVALIASAUN
// =============================================

// Hetan avaliasaun ne'ebé seidauk aprova (aprovadu = 0)
$stmt = $db->prepare("
    SELECT a.*, e.naran_escola, e.slug
    FROM avaliasaun_escola a
    JOIN  escola e ON a.escola_id = e.id
    WHERE a.aprovadu = 0
    ORDER BY a.kria_iha DESC
");
$stmt->execute();
$pending_reviews = $stmt->fetchAll();

// Hetan avaliasaun ne'ebé ona aprova (aprovadu = 1)
$stmt = $db->prepare("
    SELECT a.*, e.naran_escola, e.slug
    FROM avaliasaun_escola a
    JOIN escola e ON a.escola_id = e.id
    WHERE a.aprovadu = 1
    ORDER BY a.kria_iha DESC
    LIMIT 50
");
$stmt->execute();
$approved_reviews = $stmt->fetchAll();

// Konta total
$total_pending = count($pending_reviews);
$total_approved = count($approved_reviews);

// Konta escola ne'ebé simu avaliasaun
$escola_ids = [];
foreach ($approved_reviews as $review) {
    if (isset($review['escola_id'])) {
        $escola_ids[] = $review['escola_id'];
    }
}
$total_escolas = count(array_unique($escola_ids));

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
            <div class="stat-icon bg-warning-gradient">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $total_pending ?></h3>
                <p class="stat-text">Hein Aprovasuan</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-mini">
            <div class="stat-icon bg-success-gradient">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $total_approved ?></h3>
                <p class="stat-text">Avaliasaun Aprova</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-mini">
            <div class="stat-icon bg-primary-gradient">
                <i class="fas fa-school"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $total_escolas ?></h3>
                <p class="stat-text">Escola Simu Avaliasaun</p>
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
AVALIASAUN NE'EBÉ SEIDAUK APROVA
<!-- ============================================= -->
<div class="card-table mb-4">
    <div class="card-table-header">
        <div class="header-title">
            <i class="fas fa-clock text-warning"></i>
            <h5>Hein Aprovasuan</h5>
            <?php if ($total_pending > 0): ?>
                <span class="badge-pending"><?= $total_pending ?> Pending</span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card-table-body p-0">
        <?php if ($total_pending > 0): ?>
            <div class="table-responsive">
                <table class="table-review">
                    <thead>
                        <tr>
                            <th>Escola</th>
                            <th>Avaliador</th>
                            <th>Pontuasaun</th>
                            <th>Komentariu</th>
                            <th>Data</th>
                            <th width="180" class="text-end">Asaun</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_reviews as $review): ?>
                        <tr class="review-pending">
                            <td>
                                <div class="escola-cell">
                                    <div class="escola-icon">
                                        <i class="fas fa-school"></i>
                                    </div>
                                    <a href="<?= BASE_URL ?>/escola/<?= $review['slug'] ?>" target="_blank" class="escola-link">
                                        <?= htmlspecialchars($review['naran_escola']) ?>
                                    </a>
                                </div>
                             </div>
                            <td>
                                <div class="visitor-cell">
                                    <div class="visitor-name">
                                        <i class="fas fa-user-circle"></i>
                                        <?= htmlspecialchars($review['naran_avaliador']) ?>
                                    </div>
                                    <div class="visitor-email">
                                        <?= htmlspecialchars($review['email_avaliador'] ?: '-') ?>
                                    </div>
                                </div>
                             </div>
                            <td>
                                <div class="rating-cell">
                                    <?= renderStars($review['pontuasaun']) ?>
                                    <span class="rating-value">(<?= $review['pontuasaun'] ?>/5)</span>
                                </div>
                             </div>
                            <td>
                                <div class="comment-cell">
                                    "<?= htmlspecialchars(substr($review['komentariu'], 0, 80)) ?>"
                                </div>
                             </div>
                            <td>
                                <div class="date-cell">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('d/m/Y', strtotime($review['kria_iha'])) ?>
                                    <span class="time"><?= date('H:i', strtotime($review['kria_iha'])) ?></span>
                                </div>
                             </div>
                            <td class="text-end">
                                <div class="action-buttons">
                                    <a href="?approve=<?= $review['id'] ?>" class="btn-approve" onclick="return confirm('Aprova avaliasaun ne\'e?')">
                                        <i class="fas fa-check"></i> Aprova
                                    </a>
                                    <a href="?reject=<?= $review['id'] ?>" class="btn-reject" onclick="return confirm('Hamos avaliasaun ne\'e?')">
                                        <i class="fas fa-trash"></i> Hamos
                                    </a>
                                </div>
                             </div>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h5>La iha avaliasaun ne'ebé hein aprovasuan</h5>
                <p class="text-muted">Avaliasaun hotu ona prosesu</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================= -->
AVALIASAUN NE'EBÉ ONA APROVA
<!-- ============================================= -->
<div class="card-table">
    <div class="card-table-header">
        <div class="header-title">
            <i class="fas fa-check-circle text-success"></i>
            <h5>Avaliasaun Aprova</h5>
            <span class="badge-count"><?= $total_approved ?> Avaliasaun</span>
        </div>
    </div>
    
    <div class="card-table-body p-0">
        <?php if ($total_approved > 0): ?>
            <div class="table-responsive">
                <table class="table-review">
                    <thead>
                        <tr>
                            <th>Escola</th>
                            <th>Avaliador</th>
                            <th>Pontuasaun</th>
                            <th>Komentariu</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approved_reviews as $review): ?>
                        <tr class="review-approved">
                            <td>
                                <div class="escola-cell">
                                    <div class="escola-icon">
                                        <i class="fas fa-school"></i>
                                    </div>
                                    <a href="<?= BASE_URL ?>/escola/<?= $review['slug'] ?>" target="_blank" class="escola-link">
                                        <?= htmlspecialchars($review['naran_escola']) ?>
                                    </a>
                                </div>
                             </div>
                            <td>
                                <div class="visitor-cell">
                                    <div class="visitor-name">
                                        <i class="fas fa-user-circle"></i>
                                        <?= htmlspecialchars($review['naran_avaliador']) ?>
                                    </div>
                                </div>
                             </div>
                            <td>
                                <div class="rating-cell">
                                    <?= renderStars($review['pontuasaun']) ?>
                                </div>
                             </div>
                            <td>
                                <div class="comment-cell">
                                    "<?= htmlspecialchars(substr($review['komentariu'], 0, 60)) ?>"
                                </div>
                             </div>
                            <td>
                                <div class="date-cell">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('d/m/Y', strtotime($review['kria_iha'])) ?>
                                </div>
                             </div>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-star fa-4x text-muted mb-3"></i>
                <h5>Seidauk iha avaliasaun ne'ebé aprova</h5>
                <p class="text-muted">Aprova avaliasaun ne'ebé tama uluk</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
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
        box-shadow: 0 8px 25px rgba(30, 60, 140, 0.1);
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
    
    .bg-warning-gradient { background: linear-gradient(135deg, #F39C12, #E67E22); }
    .bg-success-gradient { background: linear-gradient(135deg, #27AE60, #1E8449); }
    .bg-primary-gradient { background: linear-gradient(135deg, #2C3E50, #1A252F); }
    
    .stat-number {
        font-size: 28px;
        font-weight: 800;
        margin: 0;
        color: #1a1a1a;
    }
    
    .stat-text {
        font-size: 13px;
        color: #7f8c8d;
        margin: 0;
    }
    
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
    }
    
    .header-title h5 {
        margin: 0;
        font-weight: 700;
    }
    
    .badge-pending {
        background: #F39C12;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .badge-count {
        background: #f0f0f0;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        color: #666;
    }
    
    .table-review {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-review th {
        padding: 15px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        background: #f8f9fa;
        border-bottom: 1px solid #eee;
    }
    
    .table-review td {
        padding: 16px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    
    .table-review tbody tr:hover {
        background: #fafafa;
    }
    
    .review-pending {
        background: #fff9e6;
    }
    
    .review-pending:hover {
        background: #fff3cc;
    }
    
    .escola-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .escola-icon {
        width: 32px;
        height: 32px;
        background: rgba(44, 62, 80, 0.1);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2C3E50;
    }
    
    .escola-link {
        color: #1a1a1a;
        text-decoration: none;
        font-weight: 600;
        transition: 0.3s;
    }
    
    .escola-link:hover {
        color: #2C3E50;
    }
    
    .visitor-cell {
        display: flex;
        flex-direction: column;
    }
    
    .visitor-name {
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .visitor-name i {
        color: #2C3E50;
        margin-right: 5px;
    }
    
    .visitor-email {
        font-size: 11px;
        color: #999;
    }
    
    .rating-cell {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .rating-value {
        font-size: 11px;
        font-weight: 600;
        color: #F39C12;
    }
    
    .comment-cell {
        font-size: 12px;
        color: #555;
        font-style: italic;
        max-width: 250px;
    }
    
    .date-cell {
        font-size: 12px;
        color: #666;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .date-cell i {
        margin-right: 5px;
        color: #999;
    }
    
    .date-cell .time {
        font-size: 10px;
        color: #aaa;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }
    
    .btn-approve {
        background: #27AE60;
        color: white;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-approve:hover {
        background: #1E8449;
        transform: translateY(-2px);
        color: white;
    }
    
    .btn-reject {
        background: #E74C3C;
        color: white;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-reject:hover {
        background: #C0392B;
        transform: translateY(-2px);
        color: white;
    }
    
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
    
    @media (max-width: 992px) {
        .table-review {
            display: block;
            overflow-x: auto;
        }
        .comment-cell {
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
        .action-buttons {
            justify-content: center;
        }
    }
</style>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?>