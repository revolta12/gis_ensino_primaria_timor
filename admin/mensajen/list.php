<?php
// =============================================
// Mensajen Kontaktu - GIS Ensino Primaria Timor-Leste
// =============================================

// Set page configuration
$page_title = 'Mensajen Kontaktu';
$page_icon = 'fa-envelope';

// Include required files
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

// Check admin login
checkAdminLogin();

$db = getDB();

// =============================================
// HANDLE MARK AS READ
// =============================================
if (isset($_GET['read'])) {
    $id = (int)$_GET['read'];
    $stmt = $db->prepare("UPDATE kontaktu_mensajen SET lee_ona = 1 WHERE id = ?");
    $stmt->execute([$id]);
    setFlashMessage('success', '✅ Mensajen markadu hanesan lee ona');
    redirect('/admin/mensajen/list.php');
    exit();
}

// =============================================
// HANDLE DELETE MESSAGE
// =============================================
if (isset($_GET['delete']) && isAdmin()) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM kontaktu_mensajen WHERE id = ?");
    $stmt->execute([$id]);
    setFlashMessage('success', '🗑️ Mensajen hamos diak');
    redirect('/admin/mensajen/list.php');
    exit();
}

// =============================================
// GET MESSAGES
// =============================================

// Get unread messages (lee_ona = 0)
$stmt = $db->prepare("SELECT * FROM kontaktu_mensajen WHERE lee_ona = 0 ORDER BY kria_iha DESC");
$stmt->execute();
$result_unread = $stmt->fetchAll();

// PASTIKAN HASIL ADALAH ARRAY
$unread_messages = [];
if (is_array($result_unread) && count($result_unread) > 0) {
    $unread_messages = $result_unread;
}

// Get read messages (lee_ona = 1)
$stmt = $db->prepare("SELECT * FROM kontaktu_mensajen WHERE lee_ona = 1 ORDER BY kria_iha DESC LIMIT 50");
$stmt->execute();
$result_read = $stmt->fetchAll();

// PASTIKAN HASIL ADALAH ARRAY
$read_messages = [];
if (is_array($result_read) && count($result_read) > 0) {
    $read_messages = $result_read;
}

$total_unread = count($unread_messages);
$total_read = count($read_messages);
$total_messages = $total_unread + $total_read;

$flash = getFlashMessage();

// Include admin header
require_once '../../includes/admin-header.php';
?>

<!-- ============================================= -->
STATISTICS SUMMARY
<!-- ============================================= -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card-mini">
            <div class="stat-icon bg-primary-gradient">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $total_messages ?></h3>
                <p class="stat-text">Total Mensajen</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-mini">
            <div class="stat-icon bg-warning-gradient">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $total_unread ?></h3>
                <p class="stat-text">La'ós Lee</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-mini">
            <div class="stat-icon bg-success-gradient">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?= $total_read ?></h3>
                <p class="stat-text">Hasai Lee</p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================= -->
FLASH MESSAGE
<!-- ============================================= -->
<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] == 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-<?= $flash['type'] == 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- ============================================= -->
UNREAD MESSAGES SECTION
<!-- ============================================= -->
<div class="card-table mb-4">
    <div class="card-table-header">
        <div class="header-title">
            <i class="fas fa-envelope text-primary"></i>
            <h5>Mensajen La'ós Lee</h5>
            <?php if ($total_unread > 0): ?>
                <span class="badge-unread"><?= $total_unread ?> Foin</span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card-table-body p-0">
        <?php if ($total_unread > 0 && !empty($unread_messages)): ?>
            <div class="table-responsive">
                <table class="table-message">
                    <thead>
                        <tr>
                            <th>Remetente</th>
                            <th>Email</th>
                            <th>Asuntu</th>
                            <th>Mensajen</th>
                            <th>Data</th>
                            <th width="150">Aksaun</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unread_messages as $msg): ?>
                        <tr class="message-unread">
                            <td>
                                <div class="sender-info">
                                    <i class="fas fa-user-circle"></i>
                                    <strong><?= htmlspecialchars($msg['naran_ema']) ?></strong>
                                    <span class="badge-new">Foin</span>
                                </div>
                             </div>
                            <td><?= htmlspecialchars($msg['email_ema']) ?></div>
                            <td><?= htmlspecialchars($msg['asuntu'] ?: '-') ?></div>
                            <td><?= htmlspecialchars(substr($msg['mensajen'], 0, 60)) ?>...</div>
                            <td>
                                <div class="date-info">
                                    <?= date('d/m/Y', strtotime($msg['kria_iha'])) ?>
                                    <small><?= date('H:i', strtotime($msg['kria_iha'])) ?></small>
                                </div>
                             </div>
                            <td>
                                <div class="action-buttons">
                                    <a href="?read=<?= $msg['id'] ?>" class="btn-read" onclick="return confirm('Marka hanesan lee ona?')">
                                        <i class="fas fa-check"></i> Lee
                                    </a>
                                    <?php if (isAdmin()): ?>
                                    <a href="?delete=<?= $msg['id'] ?>" class="btn-delete" onclick="return confirm('Hamos mensajen ida ne\'e?')">
                                        <i class="fas fa-trash"></i> Hamos
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
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h5>La iha mensajen foun</h5>
                <p class="text-muted">Hotu mensajen sira hasai lee ona</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================= -->
READ MESSAGES SECTION (ARCHIVE)
<!-- ============================================= -->
<div class="card-table">
    <div class="card-table-header">
        <div class="header-title">
            <i class="fas fa-archive text-secondary"></i>
            <h5>Arkivu Mensajen</h5>
            <span class="badge-count"><?= $total_read ?> Mensajen</span>
        </div>
    </div>
    
    <div class="card-table-body p-0">
        <?php if ($total_read > 0 && !empty($read_messages)): ?>
            <div class="table-responsive">
                <table class="table-message">
                    <thead>
                        <tr>
                            <th>Remetente</th>
                            <th>Email</th>
                            <th>Asuntu</th>
                            <th>Mensajen</th>
                            <th>Data</th>
                            <?php if (isAdmin()): ?>
                            <th width="80">Aksaun</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($read_messages as $msg): ?>
                        <tr class="message-read">
                            <td><?= htmlspecialchars($msg['naran_ema']) ?></div>
                            <td><?= htmlspecialchars($msg['email_ema']) ?></div>
                            <td><?= htmlspecialchars($msg['asuntu'] ?: '-') ?></div>
                            <td><?= htmlspecialchars(substr($msg['mensajen'], 0, 50)) ?>...</div>
                            <td><?= date('d/m/Y', strtotime($msg['kria_iha'])) ?></div>
                            <?php if (isAdmin()): ?>
                            <td>
                                <a href="?delete=<?= $msg['id'] ?>" class="btn-delete" onclick="return confirm('Hamos mensajen ida ne\'e?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-archive fa-4x text-muted mb-3"></i>
                <h5>Arkivu la iha</h5>
                <p class="text-muted">La iha mensajen ne'ebé hasai lee</p>
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
    .bg-warning-gradient { background: linear-gradient(135deg, #F39C12, #E67E22); }
    .bg-success-gradient { background: linear-gradient(135deg, #27AE60, #1E8449); }
    .stat-number { font-size: 28px; font-weight: 800; margin: 0; color: #1a1a1a; }
    .stat-text { font-size: 13px; color: #7f8c8d; margin: 0; }
    .card-table { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .card-table-header { padding: 18px 24px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
    .header-title { display: flex; align-items: center; gap: 12px; }
    .badge-unread { background: #2C3E50; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-count { background: #f0f0f0; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; color: #666; }
    .table-message { width: 100%; border-collapse: collapse; }
    .table-message th { padding: 15px 16px; text-align: left; font-size: 12px; font-weight: 700; background: #f8f9fa; border-bottom: 1px solid #eee; }
    .table-message td { padding: 16px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
    .message-unread { background: #e8f4f8; }
    .sender-info { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .badge-new { background: #2C3E50; color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; }
    .date-info { font-size: 12px; color: #666; display: flex; flex-direction: column; gap: 2px; }
    .date-info small { font-size: 10px; color: #aaa; }
    .action-buttons { display: flex; gap: 8px; }
    .btn-read { background: rgba(39,174,96,0.1); color: #27AE60; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: all 0.3s ease; }
    .btn-read:hover { background: #27AE60; color: white; }
    .btn-delete { background: rgba(231,76,60,0.1); color: #E74C3C; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: all 0.3s ease; }
    .btn-delete:hover { background: #E74C3C; color: white; }
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-state i { opacity: 0.5; }
    .empty-state h5 { margin: 15px 0 5px; font-weight: 700; }
    @media (max-width: 992px) { .table-message { display: block; overflow-x: auto; } }
</style>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?>