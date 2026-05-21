<?php
// =============================================
// Dashboard Administrador - GIS Ensino Primaria Timor-Leste
// =============================================

// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if logged in - MANUAL CHECK
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'Dashboard';
$page_icon  = 'fa-chart-line';

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Already checked manually, so this is safe
// checkAdminLogin(); // Commented - already checked above

require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

// ---- Estatistika ----
$total_escolas    = $db->query("SELECT COUNT(*) FROM escola WHERE aktivo = 1")->fetchColumn();
$featured_escolas = $db->query("SELECT COUNT(*) FROM escola WHERE destakadu = 1 AND aktivo = 1")->fetchColumn();
$avg_rating      = round($db->query("SELECT AVG(pontuasaun) FROM avaliasaun_escola WHERE aprovadu = 1")->fetchColumn() ?? 0, 1);
$total_reviews   = $db->query("SELECT COUNT(*) FROM avaliasaun_escola WHERE aprovadu = 1")->fetchColumn();
$pending_reviews = $db->query("SELECT COUNT(*) FROM avaliasaun_escola WHERE aprovadu = 0")->fetchColumn();
$unread_messages = $db->query("SELECT COUNT(*) FROM kontaktu_mensajen WHERE lee_ona = 0")->fetchColumn();

// Escola ho bee moos
$escolas_iha_bee = $db->query("SELECT COUNT(*) FROM escola WHERE iha_bee_moos = 1 AND aktivo = 1")->fetchColumn();

// ---- Estatistika kategoria ba chart ----
$cat_rows = $db->query("
    SELECT k.naran_kategoria, COUNT(e.id) as total
    FROM kategoria_escola k
    LEFT JOIN escola e ON k.id = e.kategoria_id AND e.aktivo = 1
    GROUP BY k.id
    ORDER BY total DESC
")->fetchAll();

// ---- Escola foun sira ----
$recent_escolas = $db->query("
    SELECT e.*, k.naran_kategoria
    FROM escola e
    LEFT JOIN kategoria_escola k ON e.kategoria_id = k.id
    WHERE e.aktivo = 1
    ORDER BY e.kria_iha DESC
    LIMIT 5
")->fetchAll();

// ---- Avaliasaun foun sira ----
$recent_reviews = $db->query("
    SELECT a.*, e.naran_escola, e.slug
    FROM avaliasaun_escola a
    JOIN escola e ON a.escola_id = e.id
    WHERE a.aprovadu = 1
    ORDER BY a.kria_iha DESC
    LIMIT 5
")->fetchAll();

// ---- Prepara dadus ba chart ----
$cat_labels = json_encode(array_column($cat_rows, 'naran_kategoria'));
$cat_totals = json_encode(array_column($cat_rows, 'total'));
?>

<!-- ===================== PAGE HEADER ===================== -->
<div class="db-header">
    <div>
        <div class="db-date" id="db-date"></div>
        <h1 class="db-title">Benvindu, Admin</h1>
        <p class="db-sub">GIS Ensino Primaria Timor-Leste — sumáriu ohin loron</p>
    </div>
</div>

<!-- ===================== KARTU ESTATÍSTIKA ===================== -->
<div class="db-stats">
    <div class="db-stat">
        <div class="db-stat-label">Total escola ativu</div>
        <div class="db-stat-num"><?= number_format($total_escolas) ?></div>
        <div class="db-stat-trend up"><i class="fas fa-trending-up fa-xs"></i> Di'ak hela</div>
    </div>
    <div class="db-stat">
        <div class="db-stat-label">Escola destakadu</div>
        <div class="db-stat-num"><?= number_format($featured_escolas) ?></div>
        <div class="db-stat-trend muted"><i class="fas fa-star fa-xs"></i> Destakadu</div>
    </div>
    <div class="db-stat">
        <div class="db-stat-label">Rating média</div>
        <div class="db-stat-num"><?= number_format($avg_rating, 1) ?></div>
        <div class="db-stat-trend up"><i class="fas fa-star fa-xs"></i> husi 5.0</div>
    </div>
    <div class="db-stat">
        <div class="db-stat-label">Avaliasaun hein</div>
        <div class="db-stat-num"><?= number_format($pending_reviews) ?></div>
        <?php if ($pending_reviews > 0): ?>
            <div class="db-stat-trend red"><i class="fas fa-clock fa-xs"></i> Presija modera</div>
        <?php else: ?>
            <div class="db-stat-trend up"><i class="fas fa-check fa-xs"></i> Hotu ona</div>
        <?php endif; ?>
    </div>
</div>

<!-- ===================== GRID PRINSIPÁL ===================== -->
<div class="db-grid-main">

    <!-- Chart -->
    <div class="db-card">
        <div class="db-card-head">
            <div class="db-card-head-left">
                <i class="fas fa-chart-bar"></i> Escola tuir kategoria
            </div>
            <span class="db-badge"><?= date('F Y') ?></span>
        </div>
        <div class="db-card-body">
            <div class="db-chart-wrap">
                <canvas id="catChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Asaun Lais -->
    <div class="db-card">
        <div class="db-card-head">
            <div class="db-card-head-left">
                <i class="fas fa-bolt"></i> Asaun lais
            </div>
        </div>
        <div class="db-card-body">
            <div class="db-actions">
                <a href="escola/create.php" class="db-action">
                    <div class="db-action-icon red"><i class="fas fa-plus"></i></div>
                    <div class="db-action-text">
                        <strong>Tamba escola</strong>
                        <small>Escola foun ba sistema</small>
                    </div>
                    <i class="fas fa-chevron-right db-action-arrow"></i>
                </a>
                <a href="escola/list.php" class="db-action">
                    <div class="db-action-icon blue"><i class="fas fa-list"></i></div>
                    <div class="db-action-text">
                        <strong>Jere escola</strong>
                        <small>Edita, hamoos, atualiza</small>
                    </div>
                    <i class="fas fa-chevron-right db-action-arrow"></i>
                </a>
                <a href="kategoria/list.php" class="db-action">
                    <div class="db-action-icon green"><i class="fas fa-tags"></i></div>
                    <div class="db-action-text">
                        <strong>Kategoria</strong>
                        <small>Tamba / edita kategoria</small>
                    </div>
                    <i class="fas fa-chevron-right db-action-arrow"></i>
                </a>
                <a href="avaliasaun/list.php" class="db-action">
                    <div class="db-action-icon amber"><i class="fas fa-star"></i></div>
                    <div class="db-action-text">
                        <strong>Modera avaliasaun</strong>
                        <small><?= $pending_reviews ?> avaliasaun hein</small>
                    </div>
                    <i class="fas fa-chevron-right db-action-arrow"></i>
                </a>
                <a href="mensajen/list.php" class="db-action">
                    <div class="db-action-icon purple"><i class="fas fa-envelope"></i></div>
                    <div class="db-action-text">
                        <strong>Mensajen kontaktu</strong>
                        <small><?= $unread_messages ?> mensajen seidauk lee</small>
                    </div>
                    <i class="fas fa-chevron-right db-action-arrow"></i>
                </a>
            </div>
        </div>
    </div>

</div>

<!-- ===================== GRID OKOS ===================== -->
<div class="db-grid-bottom">

    <!-- Escola Foun Sira -->
    <div class="db-card">
        <div class="db-card-head">
            <div class="db-card-head-left">
                <i class="fas fa-clock"></i> Escola foun sira
            </div>
            <a href="escola/list.php" class="db-link">Haree hotu <i class="fas fa-arrow-right fa-xs"></i></a>
        </div>
        <div class="db-table-wrap">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>Escola</th>
                        <th>Kategoria</th>
                        <th>Estudante</th>
                        <th>Bee Moos</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_escolas as $e): ?>
                    <tr>
                        <td>
                            <div class="db-escola-cell">
                                <div class="db-escola-thumb">
                                    <?php if ($e['foto_prinsipal']): ?>
                                        <img src="<?= BASE_URL . '/' . htmlspecialchars($e['foto_prinsipal']) ?>" alt="">
                                    <?php else: ?>
                                        <i class="fas fa-school"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="db-escola-name"><?= htmlspecialchars($e['naran_escola']) ?></div>
                                    <div class="db-escola-loc"><?= htmlspecialchars($e['suku'] ?? $e['postu_administrativu'] ?? 'Díli') ?></div>
                                </div>
                            </div>
                         </div>
                        <td><span class="db-pill"><?= htmlspecialchars($e['naran_kategoria'] ?? '—') ?></span></div>
                        <td class="db-count"><?= number_format($e['total_estudante']) ?> aluno</div>
                        <td>
                            <?php if ($e['iha_bee_moos']): ?>
                                <span class="db-facility yes"><i class="fas fa-check-circle fa-xs"></i> Iha</span>
                            <?php else: ?>
                                <span class="db-facility no"><i class="fas fa-times-circle fa-xs"></i> La iha</span>
                            <?php endif; ?>
                         </div>
                        <td>
                            <?php if ($e['aktivo']): ?>
                                <span class="db-status active"><i class="fas fa-circle fa-xs"></i> Ativu</span>
                            <?php else: ?>
                                <span class="db-status inactive"><i class="fas fa-circle fa-xs"></i> Inativu</span>
                            <?php endif; ?>
                         </div>
                        <td>
                            <a href="escola/edit.php?id=<?= $e['id'] ?>" class="db-edit-btn" title="Edita escola">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                         </div>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Avaliasaun Foun Sira -->
    <div class="db-card">
        <div class="db-card-head">
            <div class="db-card-head-left">
                <i class="fas fa-comment-dots"></i> Avaliasaun foun sira
            </div>
            <a href="avaliasaun/list.php" class="db-link">Modera <i class="fas fa-arrow-right fa-xs"></i></a>
        </div>
        <div class="db-reviews">
            <?php foreach ($recent_reviews as $r): ?>
            <div class="db-review-item">
                <div class="db-review-top">
                    <div class="db-reviewer">
                        <div class="db-reviewer-avatar"><i class="fas fa-user"></i></div>
                        <strong><?= htmlspecialchars($r['naran_avaliador']) ?></strong>
                    </div>
                    <div class="db-review-stars">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <i class="fas fa-star <?= $s <= $r['pontuasaun'] ? 'filled' : 'empty' ?>"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="db-review-escola"><?= htmlspecialchars($r['naran_escola']) ?></div>
                <div class="db-review-comment">"<?= htmlspecialchars(mb_substr($r['komentariu'], 0, 90)) ?>..."</div>
                <div class="db-review-time"><i class="far fa-clock"></i> <?= timeAgo($r['kria_iha']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<style>
.db-header { margin-bottom: 22px; }
.db-date { font-size: 12px; color: #999; margin-bottom: 4px; }
.db-title { font-size: 20px; font-weight: 600; color: #1a1a1a; margin: 0; }
.db-sub { font-size: 13px; color: #888; margin: 3px 0 0; }

.db-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px; }
.db-stat { background: #fff; border-radius: 14px; padding: 16px; border: 1px solid #ebebeb; box-shadow: 0 1px 4px rgba(0,0,0,.02); }
.db-stat-label { font-size: 12px; color: #888; margin-bottom: 6px; }
.db-stat-num { font-size: 28px; font-weight: 700; color: #1a1a1a; line-height: 1; }
.db-stat-trend { font-size: 11px; margin-top: 6px; display: flex; align-items: center; gap: 4px; font-weight: 500; }
.db-stat-trend.up { color: #27AE60; }
.db-stat-trend.red { color: #E74C3C; }
.db-stat-trend.muted { color: #aaa; }

.db-card { background: #fff; border: 1px solid #ebebeb; border-radius: 16px; overflow: hidden; }
.db-card-head { display: flex; align-items: center; justify-content: space-between; padding: 13px 18px; border-bottom: 1px solid #f0f0f0; }
.db-card-head-left { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #1a1a1a; }
.db-card-head-left i { color: #2C3E50; font-size: 14px; }
.db-card-body { padding: 16px; }
.db-badge { font-size: 11px; color: #888; background: #f0f0f0; padding: 3px 10px; border-radius: 20px; }
.db-link { font-size: 12px; color: #2C3E50; font-weight: 600; text-decoration: none; }
.db-link:hover { color: #1A252F; }

.db-grid-main { display: grid; grid-template-columns: 1fr 280px; gap: 12px; margin-bottom: 12px; }
.db-chart-wrap { position: relative; width: 100%; height: 240px; }

.db-actions { display: flex; flex-direction: column; gap: 6px; }
.db-action { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 12px; border: 1px solid #f0f0f0; text-decoration: none; transition: background .15s; }
.db-action:hover { background: #fafafa; border-color: #e0e0e0; }
.db-action-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
.db-action-icon.red    { background: #FCEBEB; color: #E74C3C; }
.db-action-icon.blue   { background: #E6F1FB; color: #2C3E50; }
.db-action-icon.green  { background: #EAF3DE; color: #27AE60; }
.db-action-icon.amber  { background: #FAEEDA; color: #E67E22; }
.db-action-icon.purple { background: #F3F0FE; color: #7F77DD; }
.db-action-text { flex: 1; }
.db-action-text strong { display: block; font-size: 12.5px; color: #1a1a1a; }
.db-action-text small { font-size: 11px; color: #888; }
.db-action-arrow { color: #ccc; font-size: 11px; }

.db-grid-bottom { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.db-table-wrap { overflow-x: auto; }
.db-table { width: 100%; border-collapse: collapse; }
.db-table th { padding: 10px 18px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #aaa; background: #fafafa; border-bottom: 1px solid #f0f0f0; }
.db-table td { padding: 12px 18px; border-bottom: 1px solid #f4f4f4; vertical-align: middle; font-size: 13px; }
.db-escola-cell { display: flex; align-items: center; gap: 10px; }
.db-escola-thumb { width: 36px; height: 36px; border-radius: 8px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 14px; color: #ccc; flex-shrink: 0; overflow: hidden; }
.db-escola-thumb img { width: 100%; height: 100%; object-fit: cover; }
.db-escola-name { font-size: 13px; font-weight: 600; color: #1a1a1a; }
.db-escola-loc { font-size: 11px; color: #999; margin-top: 1px; }
.db-pill { background: #f0f0f0; font-size: 11px; padding: 3px 10px; border-radius: 6px; color: #555; white-space: nowrap; }
.db-count { font-weight: 600; color: #2C3E50; white-space: nowrap; }
.db-facility { font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
.db-facility.yes { background: rgba(39,174,96,.1); color: #27AE60; }
.db-facility.no { background: rgba(231,76,60,.1); color: #E74C3C; }
.db-status { font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
.db-status.active { background: rgba(39,174,96,.1); color: #27AE60; }
.db-status.inactive { background: rgba(150,150,150,.1); color: #999; }
.db-status i { font-size: 7px; }
.db-edit-btn { width: 30px; height: 30px; border-radius: 7px; background: #f4f4f4; display: inline-flex; align-items: center; justify-content: center; color: #2C3E50; font-size: 12px; text-decoration: none; transition: .2s; }
.db-edit-btn:hover { background: #2C3E50; color: #fff; }

.db-reviews { display: flex; flex-direction: column; }
.db-review-item { padding: 12px 18px; border-top: 1px solid #f4f4f4; }
.db-review-item:first-child { border-top: none; }
.db-review-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
.db-reviewer { display: flex; align-items: center; gap: 8px; }
.db-reviewer-avatar { width: 28px; height: 28px; border-radius: 50%; background: #E8F0F8; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #2C3E50; }
.db-reviewer strong { font-size: 13px; color: #1a1a1a; }
.db-review-stars { display: flex; gap: 2px; font-size: 11px; }
.db-review-stars .filled { color: #F39C12; }
.db-review-stars .empty { color: #ddd; }
.db-review-escola { font-size: 12px; color: #888; margin-bottom: 4px; }
.db-review-comment { font-size: 12px; color: #555; font-style: italic; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
.db-review-time { font-size: 11px; color: #bbb; margin-top: 5px; display: flex; align-items: center; gap: 4px; }

@media (max-width: 1024px) {
    .db-stats { grid-template-columns: repeat(2, 1fr); }
    .db-grid-main { grid-template-columns: 1fr; }
    .db-grid-bottom { grid-template-columns: 1fr; }
}
@media (max-width: 576px) {
    .db-stats { grid-template-columns: 1fr 1fr; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var days   = ['Domingu','Segunda','Tersa','Kuarta','Kinta','Sesta','Sabadu'];
    var months = ['Janeiru','Fevereiru','Marsu','Abril','Maiu','Juñu','Jullu','Agostu','Setembru','Outubru','Novembru','Dezembru'];
    var now    = new Date();
    var el = document.getElementById('db-date');
    if (el) el.textContent = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();

    var canvas = document.getElementById('catChart');
    if (canvas && typeof Chart !== 'undefined') {
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: <?= $cat_labels ?>,
                datasets: [{
                    label: 'Total escola',
                    data: <?= $cat_totals ?>,
                    backgroundColor: 'rgba(44,62,80,.8)',
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, font: { size: 11 }, color: '#aaa' },
                        grid: { color: 'rgba(0,0,0,.05)' },
                        border: { display: false }
                    },
                    x: {
                        ticks: { font: { size: 11 }, color: '#aaa' },
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    }
}());
</script>

<?php require_once '../includes/admin-footer.php'; ?>