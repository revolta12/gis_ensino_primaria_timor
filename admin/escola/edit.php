<?php
// =============================================
// Edita Escola - GIS Ensino Primaria Timor-Leste
// =============================================

// Set page configuration
$page_title = 'Edita Escola';
$page_icon = 'fa-edit';

// Include required files
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
require_once '../../includes/csrf.php';

// Check admin login
checkAdminLogin();

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('/admin/escola/list.php');
    exit();
}

// Get escola data
$stmt = $db->prepare("SELECT * FROM escola WHERE id = ?");
$stmt->execute([$id]);
$escola = $stmt->fetch();

if (!$escola) {
    setFlashMessage('danger', 'Escola la hetan.');
    redirect('/admin/escola/list.php');
    exit();
}

// Get escola facilities
$stmt = $db->prepare("SELECT fasilidade_id FROM escola_fasilidade WHERE escola_id = ?");
$stmt->execute([$id]);
$escola_facilities = array_column($stmt->fetchAll(), 'fasilidade_id');

// Get categories
$categories = $db->query("SELECT id, naran_kategoria FROM kategoria_escola ORDER BY naran_kategoria")->fetchAll();

// Get all facilities
$all_facilities = $db->query("SELECT id, naran_fasilidade, ikonu FROM fasilidade_escola ORDER BY naran_fasilidade")->fetchAll();

// Get gallery photos
$stmt = $db->prepare("SELECT * FROM foto_escola WHERE escola_id = ? ORDER BY ordem ASC");
$stmt->execute([$id]);
$gallery_photos = $stmt->fetchAll();

$languages = ['Tetun', 'Portugues', 'Ingles', 'Bahasa Indonesia'];
$escola_languages = explode(',', $escola['sistema_ensinu'] ?? '');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token seguransa la validu.';
    } else {
        $naran_escola = sanitize($_POST['naran_escola'] ?? '');
        $kategoria_id = (int)($_POST['kategoria_id'] ?? 0);
        $enderesu = sanitize($_POST['enderesu'] ?? '');
        $suku = sanitize($_POST['suku'] ?? '');
        $postu_administrativu = sanitize($_POST['postu_administrativu'] ?? '');
        $municipio = sanitize($_POST['municipio'] ?? 'Díli');
        $latitude = (float)($_POST['latitude'] ?? 0);
        $longitude = (float)($_POST['longitude'] ?? 0);
        $telefone = sanitize($_POST['telefone'] ?? '');
        $email_escola = sanitize($_POST['email_escola'] ?? '');
        $website = sanitize($_POST['website'] ?? '');
        $total_estudante = (int)($_POST['total_estudante'] ?? 0);
        $total_estudante_feto = (int)($_POST['total_estudante_feto'] ?? 0);
        $total_estudante_mane = (int)($_POST['total_estudante_mane'] ?? 0);
        $total_profesor = (int)($_POST['total_profesor'] ?? 0);
        $total_profesor_feto = (int)($_POST['total_profesor_feto'] ?? 0);
        $total_profesor_mane = (int)($_POST['total_profesor_mane'] ?? 0);
        $klase_hosi = (int)($_POST['klase_hosi'] ?? 1);
        $klase_too = (int)($_POST['klase_too'] ?? 6);
        $sistema_ensinu = isset($_POST['sistema_ensinu']) ? implode(',', $_POST['sistema_ensinu']) : '';
        $iha_bee_moos = isset($_POST['iha_bee_moos']) ? 1 : 0;
        $iha_eletrisidade = isset($_POST['iha_eletrisidade']) ? 1 : 0;
        $iha_toilet = isset($_POST['iha_toilet']) ? 1 : 0;
        $destakadu = isset($_POST['destakadu']) ? 1 : 0;
        $aktivo = isset($_POST['aktivo']) ? 1 : 0;
        
        if (empty($naran_escola)) {
            $error = 'Naran escola tenki prense.';
        } elseif ($kategoria_id <= 0) {
            $error = 'Kategoria escola tenki hili.';
        } elseif ($latitude == 0 || $longitude == 0) {
            $error = 'Lokasaun escola tenki hili iha mapa.';
        } else {
            // Handle main photo upload
            $foto_prinsipal = $escola['foto_prinsipal'];
            if (isset($_FILES['foto_prinsipal']) && $_FILES['foto_prinsipal']['error'] === UPLOAD_ERR_OK) {
                $new_photo = uploadFoto($_FILES['foto_prinsipal']);
                if ($new_photo) {
                    $foto_prinsipal = $new_photo;
                }
            }
            
            // Update escola
            $stmt = $db->prepare("
                UPDATE escola SET 
                    naran_escola = ?, kategoria_id = ?, enderesu = ?, suku = ?, postu_administrativu = ?, municipio = ?,
                    latitude = ?, longitude = ?, telefone = ?, email_escola = ?, website = ?,
                    total_estudante = ?, total_estudante_feto = ?, total_estudante_mane = ?,
                    total_profesor = ?, total_profesor_feto = ?, total_profesor_mane = ?,
                    klase_hosi = ?, klase_too = ?, sistema_ensinu = ?, foto_prinsipal = ?,
                    iha_bee_moos = ?, iha_eletrisidade = ?, iha_toilet = ?,
                    destakadu = ?, aktivo = ?
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $naran_escola, $kategoria_id, $enderesu, $suku, $postu_administrativu, $municipio,
                $latitude, $longitude, $telefone, $email_escola, $website,
                $total_estudante, $total_estudante_feto, $total_estudante_mane,
                $total_profesor, $total_profesor_feto, $total_profesor_mane,
                $klase_hosi, $klase_too, $sistema_ensinu, $foto_prinsipal,
                $iha_bee_moos, $iha_eletrisidade, $iha_toilet,
                $destakadu, $aktivo, $id
            ]);
            
            if ($result) {
                // Update facilities
                $db->prepare("DELETE FROM escola_fasilidade WHERE escola_id = ?")->execute([$id]);
                if (isset($_POST['fasilidade']) && is_array($_POST['fasilidade'])) {
                    $stmt_f = $db->prepare("INSERT INTO escola_fasilidade (escola_id, fasilidade_id) VALUES (?, ?)");
                    foreach ($_POST['fasilidade'] as $f_id) {
                        $stmt_f->execute([$id, (int)$f_id]);
                    }
                }
                
                // Upload new gallery photos
                if (isset($_FILES['foto_galeri']) && !empty($_FILES['foto_galeri']['name'][0])) {
                    uploadMultipleFoto($_FILES['foto_galeri'], $id, $db, 'foto_escola');
                }
                
                setFlashMessage('success', '✅ Escola atualiza ho susesu!');
                redirect('/admin/escola/list.php');
                exit();
            } else {
                $error = 'Falha atualiza escola. Favor halo di\'ak tan.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();

// Include admin header
require_once '../../includes/admin-header.php';
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<!-- ============================================= -->
<!-- FLASH MESSAGE & ERROR -->
<!-- ============================================= -->
<?php if ($error): ?>
<div class="th-alert th-alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <span><?= htmlspecialchars($error) ?></span>
    <button type="button" class="th-alert-close" onclick="this.parentElement.remove()">&times;</button>
</div>
<?php endif; ?>

<div class="th-page-header">
    <div class="th-page-icon">
        <i class="fas fa-edit"></i>
    </div>
    <div>
        <h1 class="th-page-title">Edita Escola</h1>
        <p class="th-page-sub">GIS Ensino Primaria Timor-Leste — atualiza informasaun escola nian</p>
    </div>
</div>

<!-- ============================================= -->
<!-- FORM EDIT ESCOLA -->
<!-- ============================================= -->
<form method="POST" enctype="multipart/form-data" id="escolaForm">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="latitude" id="latitude" value="<?= $escola['latitude'] ?>">
    <input type="hidden" name="longitude" id="longitude" value="<?= $escola['longitude'] ?>">

    <!-- TAB NAV -->
    <div class="th-tab-nav" role="tablist">
        <button type="button" class="th-tab active" data-tab="basic">
            <i class="fas fa-info-circle"></i> Informasaun
        </button>
        <button type="button" class="th-tab" data-tab="location">
            <i class="fas fa-map-marker-alt"></i> Lokasaun
        </button>
        <button type="button" class="th-tab" data-tab="photos">
            <i class="fas fa-camera"></i> Foto
        </button>
        <button type="button" class="th-tab" data-tab="facilities">
            <i class="fas fa-school"></i> Fasilidade
        </button>
    </div>

    <!-- ==================== TAB 1: INFORMASAUN ==================== -->
    <div class="th-pane active" id="tab-basic">
        <div class="th-card">
            <div class="th-card-head">
                <i class="fas fa-building"></i>
                <span>Informasaun Baziku Escola</span>
            </div>
            <div class="th-card-body">

                <div class="th-row th-col-2">
                    <div class="th-field">
                        <label>Naran Escola <span class="req">*</span></label>
                        <input type="text" name="naran_escola" value="<?= htmlspecialchars($escola['naran_escola']) ?>" required>
                    </div>
                    <div class="th-field">
                        <label>Kategoria <span class="req">*</span></label>
                        <select name="kategoria_id" required>
                            <option value="">— Hili Kategoria —</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $escola['kategoria_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['naran_kategoria']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="th-row th-col-1">
                    <div class="th-field">
                        <label>Enderesu Kompletu</label>
                        <textarea name="enderesu" rows="2"><?= htmlspecialchars($escola['enderesu']) ?></textarea>
                    </div>
                </div>

                <div class="th-row th-col-3">
                    <div class="th-field">
                        <label>Suku</label>
                        <input type="text" name="suku" value="<?= htmlspecialchars($escola['suku']) ?>">
                    </div>
                    <div class="th-field">
                        <label>Postu Administrativu</label>
                        <input type="text" name="postu_administrativu" value="<?= htmlspecialchars($escola['postu_administrativu']) ?>">
                    </div>
                    <div class="th-field">
                        <label>Municipio</label>
                        <input type="text" name="municipio" value="<?= htmlspecialchars($escola['municipio']) ?>">
                    </div>
                </div>

                <div class="th-row th-col-3">
                    <div class="th-field">
                        <label>Telefone</label>
                        <input type="text" name="telefone" value="<?= htmlspecialchars($escola['telefone']) ?>">
                    </div>
                    <div class="th-field">
                        <label>Email Escola</label>
                        <input type="email" name="email_escola" value="<?= htmlspecialchars($escola['email_escola']) ?>">
                    </div>
                    <div class="th-field">
                        <label>Website</label>
                        <input type="url" name="website" value="<?= htmlspecialchars($escola['website']) ?>">
                    </div>
                </div>

                <div class="th-card-head" style="margin-top:16px">
                    <i class="fas fa-users"></i>
                    <span>Dadus Estudante no Profesor</span>
                </div>

                <div class="th-row th-col-3">
                    <div class="th-field">
                        <label>Total Estudante</label>
                        <input type="number" name="total_estudante" value="<?= $escola['total_estudante'] ?>" min="0">
                    </div>
                    <div class="th-field">
                        <label>Estudante Feto</label>
                        <input type="number" name="total_estudante_feto" value="<?= $escola['total_estudante_feto'] ?>" min="0">
                    </div>
                    <div class="th-field">
                        <label>Estudante Mane</label>
                        <input type="number" name="total_estudante_mane" value="<?= $escola['total_estudante_mane'] ?>" min="0">
                    </div>
                </div>

                <div class="th-row th-col-3">
                    <div class="th-field">
                        <label>Total Profesor</label>
                        <input type="number" name="total_profesor" value="<?= $escola['total_profesor'] ?>" min="0">
                    </div>
                    <div class="th-field">
                        <label>Profesor Feto</label>
                        <input type="number" name="total_profesor_feto" value="<?= $escola['total_profesor_feto'] ?>" min="0">
                    </div>
                    <div class="th-field">
                        <label>Profesor Mane</label>
                        <input type="number" name="total_profesor_mane" value="<?= $escola['total_profesor_mane'] ?>" min="0">
                    </div>
                </div>

                <div class="th-row th-col-2">
                    <div class="th-field">
                        <label>Klase Hosi</label>
                        <select name="klase_hosi">
                            <?php for($i=1; $i<=6; $i++): ?>
                            <option value="<?= $i ?>" <?= $escola['klase_hosi'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="th-field">
                        <label>Klase Too</label>
                        <select name="klase_too">
                            <?php for($i=1; $i<=6; $i++): ?>
                            <option value="<?= $i ?>" <?= $escola['klase_too'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="th-row th-col-1">
                    <div class="th-field">
                        <label>Sistema Ensinu</label>
                        <div class="th-pill-group">
                            <?php foreach ($languages as $lang): ?>
                            <label class="th-pill">
                                <input type="checkbox" name="sistema_ensinu[]" value="<?= $lang ?>" <?= in_array($lang, $escola_languages) ? 'checked' : '' ?>>
                                <span><?= $lang ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="th-card-head" style="margin-top:16px">
                    <i class="fas fa-water"></i>
                    <span>Infrastrutura Baziku</span>
                </div>

                <div class="th-row th-col-1">
                    <div class="th-pill-group">
                        <label class="th-pill">
                            <input type="checkbox" name="iha_bee_moos" <?= $escola['iha_bee_moos'] ? 'checked' : '' ?>>
                            <span><i class="fas fa-water"></i> Iha Bee Moos</span>
                        </label>
                        <label class="th-pill">
                            <input type="checkbox" name="iha_eletrisidade" <?= $escola['iha_eletrisidade'] ? 'checked' : '' ?>>
                            <span><i class="fas fa-plug"></i> Iha Eletrisidade</span>
                        </label>
                        <label class="th-pill">
                            <input type="checkbox" name="iha_toilet" <?= $escola['iha_toilet'] ? 'checked' : '' ?>>
                            <span><i class="fas fa-toilet"></i> Iha Toilet</span>
                        </label>
                    </div>
                </div>

                <div class="th-row th-col-1">
                    <div class="th-pill-group">
                        <label class="th-pill">
                            <input type="checkbox" name="destakadu" <?= $escola['destakadu'] ? 'checked' : '' ?>>
                            <span><i class="fas fa-star" style="color:#2C3E50;font-size:11px;margin-right:4px"></i> Escola Destakadu</span>
                        </label>
                        <label class="th-pill">
                            <input type="checkbox" name="aktivo" <?= $escola['aktivo'] ? 'checked' : '' ?>>
                            <span><i class="fas fa-eye" style="color:#2C3E50;font-size:11px;margin-right:4px"></i> Aktivo iha Website</span>
                        </label>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ==================== TAB 2: LOKASAUN ==================== -->
    <div class="th-pane" id="tab-location">
        <div class="th-card">
            <div class="th-card-head">
                <i class="fas fa-map-marked-alt"></i>
                <span>Lokasaun Escola — klik iha mapa atu troka fatin</span>
            </div>
            <div class="th-card-body">

                <!-- Search -->
                <div class="th-map-search">
                    <i class="fas fa-search th-search-icon"></i>
                    <input type="text" id="searchLocation" placeholder="Buka fatin... (Ex: Escola Primaria, Comoro, Bidau)">
                    <button type="button" id="searchBtn" class="th-btn-search">
                        <i class="fas fa-location-dot"></i> Buka
                    </button>
                </div>
                <p class="th-map-hint">✏️ Hakerek naran fatin iha Timor-Leste, depois klik "Buka" — ka klik diretamente iha mapa</p>

                <!-- Koordinat display -->
                <div class="th-coord-row">
                    <div class="th-coord-box <?= $escola['latitude'] != 0 ? 'filled' : '' ?>" id="coordBoxLat">
                        <div class="th-coord-label">Latitude <span class="req">*</span></div>
                        <div class="th-coord-val" id="dispLat"><?= $escola['latitude'] != 0 ? $escola['latitude'] : '—' ?></div>
                        <div class="th-coord-status" id="latStatus"><?= $escola['latitude'] != 0 ? '✓ Hili ona' : 'Seidauk hili' ?></div>
                    </div>
                    <div class="th-coord-box <?= $escola['longitude'] != 0 ? 'filled' : '' ?>" id="coordBoxLng">
                        <div class="th-coord-label">Longitude <span class="req">*</span></div>
                        <div class="th-coord-val" id="dispLng"><?= $escola['longitude'] != 0 ? $escola['longitude'] : '—' ?></div>
                        <div class="th-coord-status" id="lngStatus"><?= $escola['longitude'] != 0 ? '✓ Hili ona' : 'Seidauk hili' ?></div>
                    </div>
                </div>

                <!-- Peta -->
                <div id="timorMap"></div>

                <!-- Konfirmasi lokasi -->
                <div class="th-coord-confirm" id="coordConfirm" style="<?= $escola['latitude'] != 0 ? 'display:flex' : 'display:none' ?>">
                    <i class="fas fa-check-circle"></i>
                    <strong>✅ Fatin hili ona!</strong>
                    <span id="coordText"><?= $escola['latitude'] != 0 ? $escola['latitude'] . ', ' . $escola['longitude'] : '' ?></span>
                </div>

            </div>
        </div>
    </div>

    <!-- ==================== TAB 3: FOTO ==================== -->
    <div class="th-pane" id="tab-photos">
        <div class="th-card">
            <div class="th-card-head">
                <i class="fas fa-images"></i>
                <span>Foto Escola</span>
            </div>
            <div class="th-card-body">

                <!-- Current Main Photo -->
                <div class="th-field" style="margin-bottom:20px">
                    <label>Foto Prinsipal Atual</label>
                    <?php if ($escola['foto_prinsipal']): ?>
                        <div class="current-photo">
                            <img src="<?= BASE_URL . '/' . $escola['foto_prinsipal'] ?>" class="current-main-photo" alt="Main Photo">
                        </div>
                    <?php else: ?>
                        <div class="placeholder-photo">
                            <i class="fas fa-image"></i>
                            <span>Seidauk iha foto prinsipal</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Change Main Photo -->
                <div class="th-field" style="margin-bottom:20px">
                    <label>Troka Foto Prinsipal</label>
                    <div class="th-upload-zone" id="mainZone">
                        <input type="file" name="foto_prinsipal" id="mainPhoto" accept="image/jpeg,image/png,image/webp">
                        <div class="th-upload-inner">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Klik atu troka foto prinsipal</p>
                            <small>JPG, PNG, WEBP — maks 5MB</small>
                        </div>
                        <div class="th-previews" id="mainPreview"></div>
                    </div>
                </div>
                
                <!-- Gallery Photos -->
                <div class="th-field" style="margin-bottom:20px">
                    <label>Galeri Foto Atual</label>
                    <div class="gallery-grid">
                        <?php foreach ($gallery_photos as $photo): ?>
                            <div class="gallery-item">
                                <img src="<?= BASE_URL . '/' . $photo['naran_fail'] ?>" class="gallery-img" alt="Gallery">
                                <a href="delete-gallery.php?id=<?= $photo['id'] ?>&escola_id=<?= $id ?>" class="gallery-delete" onclick="return confirm('Hamos foto ne\'e?')">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($gallery_photos)): ?>
                            <div class="gallery-empty">
                                <i class="fas fa-images"></i>
                                <span>Seidauk iha foto galeri</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Add New Gallery Photos -->
                <div class="th-field">
                    <label>Tambah Foto Galeri Foun</label>
                    <div class="th-upload-zone" id="galleryZone">
                        <input type="file" name="foto_galeri[]" id="galleryPhoto" multiple accept="image/jpeg,image/png,image/webp">
                        <div class="th-upload-inner">
                            <i class="fas fa-photo-video"></i>
                            <p>Klik atu hili foto galeri foun</p>
                            <small>Bele hili barak fotos iha dala ida</small>
                        </div>
                        <div class="th-previews" id="galleryPreview"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ==================== TAB 4: FASILIDADE ==================== -->
    <div class="th-pane" id="tab-facilities">
        <div class="th-card">
            <div class="th-card-head">
                <i class="fas fa-school"></i>
                <span>Fasilidade Escola</span>
            </div>
            <div class="th-card-body">
                <div class="th-fac-grid">
                    <?php foreach ($all_facilities as $facility): ?>
                    <label class="th-fac-item">
                        <input type="checkbox" name="fasilidade[]" value="<?= $facility['id'] ?>" <?= in_array($facility['id'], $escola_facilities) ? 'checked' : '' ?>>
                        <i class="fas fa-<?= htmlspecialchars($facility['ikonu'] ?? 'school') ?>"></i>
                        <span><?= htmlspecialchars($facility['naran_fasilidade']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTIONS -->
    <div class="th-form-actions">
        <a href="list.php" class="th-btn-back">
            <i class="fas fa-arrow-left"></i> Fila
        </a>
        <button type="submit" class="th-btn-submit">
            <i class="fas fa-save"></i> Atualiza
        </button>
    </div>

</form>

<style>
/* ---- Reset & Base ---- */
.th-page-header { display:flex; align-items:center; gap:14px; margin-bottom:24px; }
.th-page-icon { width:46px; height:46px; background:#E8F0F8; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.th-page-icon i { color:#2C3E50; font-size:18px; }
.th-page-title { margin:0; font-size:20px; font-weight:600; color:#1a1a1a; }
.th-page-sub { margin:2px 0 0; font-size:13px; color:#888; }

/* ---- Alert ---- */
.th-alert { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:13px; }
.th-alert-danger { background:#FCEBEB; border:1px solid #F7C1C1; color:#791F1F; }
.th-alert-close { margin-left:auto; background:none; border:none; font-size:18px; cursor:pointer; color:inherit; line-height:1; }

/* ---- Tab Nav ---- */
.th-tab-nav { display:flex; gap:4px; background:#f4f4f4; padding:5px; border-radius:12px; margin-bottom:20px; flex-wrap:wrap; }
.th-tab { flex:1; padding:10px 16px; border:none; background:transparent; border-radius:9px; font-size:13px; font-weight:500; color:#666; cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:center; gap:7px; white-space:nowrap; }
.th-tab i { font-size:13px; }
.th-tab:hover:not(.active) { background:#ebebeb; color:#333; }
.th-tab.active { background:#fff; color:#2C3E50; box-shadow:0 1px 5px rgba(0,0,0,.1); }

/* ---- Pane ---- */
.th-pane { display:none; }
.th-pane.active { display:block; }

/* ---- Card ---- */
.th-card { background:#fff; border-radius:14px; border:1px solid #ebebeb; overflow:hidden; margin-bottom:16px; }
.th-card-head { display:flex; align-items:center; gap:10px; padding:14px 20px; border-bottom:1px solid #f0f0f0; }
.th-card-head i { color:#2C3E50; font-size:15px; }
.th-card-head span { font-size:14px; font-weight:600; color:#1a1a1a; }
.th-card-body { padding:20px; }

/* ---- Form Layout ---- */
.th-row { display:grid; gap:14px; margin-bottom:14px; }
.th-col-1 { grid-template-columns:1fr; }
.th-col-2 { grid-template-columns:1fr 1fr; }
.th-col-3 { grid-template-columns:1fr 1fr 1fr; }
.th-field { display:flex; flex-direction:column; gap:5px; }
.th-field label { font-size:12px; font-weight:500; color:#666; }
.req { color:#E81828; }

input, select, textarea {
    padding:9px 13px; border:1px solid #e0e0e0; border-radius:8px;
    font-size:13px; color:#1a1a1a; background:#fff;
    transition:border-color .15s, box-shadow .15s; outline:none; width:100%;
}
input:focus, select:focus, textarea:focus {
    border-color:#2C3E50; box-shadow:0 0 0 3px rgba(44,62,80,.08);
}
textarea { resize:vertical; }

/* ---- Pill Checkboxes ---- */
.th-pill-group { display:flex; flex-wrap:wrap; gap:8px; }
.th-pill { display:inline-flex; align-items:center; gap:6px; padding:6px 13px; border:1px solid #e0e0e0; border-radius:20px; cursor:pointer; font-size:12.5px; color:#555; transition:all .15s; background:#fff; }
.th-pill input { accent-color:#2C3E50; margin:0; }
.th-pill:hover { border-color:#2C3E50; color:#2C3E50; background:#E8F0F8; }

/* ---- Map ---- */
.th-map-search { display:flex; align-items:center; gap:8px; background:#f8f8f8; border:1px solid #e0e0e0; border-radius:10px; padding:6px 6px 6px 14px; margin-bottom:6px; }
.th-search-icon { color:#888; font-size:14px; flex-shrink:0; }
.th-map-search input { flex:1; border:none; background:transparent; font-size:13px; outline:none; padding:4px 0; }
.th-btn-search { padding:8px 14px; background:#2C3E50; color:#fff; border:none; border-radius:8px; font-size:12.5px; font-weight:500; cursor:pointer; white-space:nowrap; display:flex; align-items:center; gap:6px; transition:background .15s; flex-shrink:0; }
.th-btn-search:hover { background:#1A252F; }
.th-map-hint { font-size:11.5px; color:#aaa; margin-bottom:14px; }

.th-coord-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px; }
.th-coord-box { padding:12px 16px; border:1px solid #e8e8e8; border-radius:10px; background:#fafafa; }
.th-coord-box.filled { background:#EAF3DE; border-color:#C0DD97; }
.th-coord-label { font-size:11px; font-weight:500; color:#888; margin-bottom:4px; }
.th-coord-val { font-size:15px; font-weight:600; color:#1a1a1a; font-family:monospace; min-height:22px; }
.th-coord-status { font-size:11px; color:#aaa; margin-top:3px; }
.th-coord-status.ok { color:#3B6D11; }

#timorMap { height:420px; border-radius:10px; border:1px solid #e0e0e0; overflow:hidden; margin-bottom:12px; }

.th-coord-confirm { display:flex; align-items:center; gap:8px; padding:10px 14px; background:#EAF3DE; border:1px solid #C0DD97; border-radius:8px; font-size:13px; color:#3B6D11; }

/* ---- Upload ---- */
.th-upload-zone { position:relative; border:1.5px dashed #ddd; border-radius:12px; padding:28px 20px; text-align:center; cursor:pointer; transition:all .2s; overflow:hidden; }
.th-upload-zone:hover { border-color:#2C3E50; background:rgba(44,62,80,.02); }
.th-upload-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.th-upload-inner i { font-size:36px; color:#2C3E50; display:block; margin-bottom:8px; }
.th-upload-inner p { margin:0 0 4px; font-size:13px; font-weight:600; color:#333; }
.th-upload-inner small { font-size:11.5px; color:#aaa; }
.th-previews { display:flex; flex-wrap:wrap; gap:8px; margin-top:14px; justify-content:center; }
.th-previews img { width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid #2C3E50; }

/* ---- Current Photo ---- */
.current-photo { margin-top:10px; }
.current-main-photo { width:150px; height:150px; object-fit:cover; border-radius:12px; border:3px solid #2C3E50; }
.placeholder-photo { width:150px; height:150px; background:#f8f9fa; border-radius:12px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; color:#999; }
.placeholder-photo i { font-size:40px; }

/* ---- Gallery ---- */
.gallery-grid { display:flex; flex-wrap:wrap; gap:15px; margin-top:10px; }
.gallery-item { position:relative; }
.gallery-img { width:120px; height:120px; object-fit:cover; border-radius:10px; border:2px solid #eee; transition:all .3s; }
.gallery-img:hover { border-color:#2C3E50; }
.gallery-delete { position:absolute; top:-8px; right:-8px; width:28px; height:28px; background:#E74C3C; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:12px; transition:all .3s; box-shadow:0 2px 5px rgba(0,0,0,.2); }
.gallery-delete:hover { background:#C0392B; transform:scale(1.1); }
.gallery-empty { width:120px; height:120px; background:#f8f9fa; border-radius:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; color:#999; }
.gallery-empty i { font-size:30px; }

/* ---- Facilities ---- */
.th-fac-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(190px, 1fr)); gap:8px; }
.th-fac-item { display:flex; align-items:center; gap:9px; padding:10px 13px; border:1px solid #ebebeb; border-radius:9px; cursor:pointer; font-size:13px; color:#555; transition:all .15s; background:#fafafa; }
.th-fac-item input { accent-color:#2C3E50; margin:0; }
.th-fac-item i { color:#2C3E50; font-size:13px; width:16px; text-align:center; }
.th-fac-item:hover { border-color:#2C3E50; background:#E8F0F8; color:#1a1a1a; }

/* ---- Actions ---- */
.th-form-actions { display:flex; justify-content:space-between; align-items:center; margin-top:24px; padding-top:20px; border-top:1px solid #ebebeb; }
.th-btn-back { display:inline-flex; align-items:center; gap:7px; padding:10px 20px; border:1px solid #ddd; border-radius:9px; background:#fff; color:#555; font-size:13px; font-weight:500; text-decoration:none; transition:all .15s; }
.th-btn-back:hover { border-color:#aaa; background:#f5f5f5; color:#333; }
.th-btn-submit { display:inline-flex; align-items:center; gap:7px; padding:10px 24px; border:none; border-radius:9px; background:#2C3E50; color:#fff; font-size:13px; font-weight:600; cursor:pointer; transition:background .15s; }
.th-btn-submit:hover { background:#1A252F; }

/* ---- Leaflet ---- */
.leaflet-popup-content-wrapper { border-radius:10px; font-size:13px; }
.leaflet-control-attribution { font-size:9px; }
.leaflet-bar a { border-radius:6px !important; }

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .th-tab { font-size:11px; padding:8px 10px; gap:5px; }
    .th-col-2, .th-col-3 { grid-template-columns:1fr; }
    .th-coord-row { grid-template-columns:1fr; }
    #timorMap { height:320px; }
    .th-form-actions { flex-direction:column; gap:10px; }
    .th-btn-back, .th-btn-submit { width:100%; justify-content:center; }
    .th-fac-grid { grid-template-columns:1fr 1fr; }
    .gallery-grid { justify-content:center; }
}
@media (max-width: 480px) {
    .th-fac-grid { grid-template-columns:1fr; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    'use strict';

    var CENTER_LAT = <?= $escola['latitude'] ?: -8.553 ?>;
    var CENTER_LNG = <?= $escola['longitude'] ?: 125.579 ?>;
    var map, currentMarker;

    // ---- Tab switching ----
    document.querySelectorAll('.th-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = btn.getAttribute('data-tab');
            document.querySelectorAll('.th-tab').forEach(function (b) { b.classList.remove('active'); });
            document.querySelectorAll('.th-pane').forEach(function (p) { p.classList.remove('active'); });
            btn.classList.add('active');
            document.getElementById('tab-' + target).classList.add('active');

            if (target === 'location') {
                setTimeout(function () { if (map) map.invalidateSize(); }, 120);
            }
        });
    });

    // ---- Map init ----
    function initMap() {
        map = L.map('timorMap').setView([CENTER_LAT, CENTER_LNG], 16);

        var streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19
        });
        streetLayer.addTo(map);

        var satLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            subdomains: ['mt0','mt1','mt2','mt3'],
            attribution: '© Google Satellite',
            maxZoom: 20
        });

        L.control.layers({ 'Mapa Rua': streetLayer, 'Satélite': satLayer }).addTo(map);

        // GPS button
        var gpsCtrl = L.control({ position: 'topright' });
        gpsCtrl.onAdd = function () {
            var div = L.DomUtil.create('div', 'leaflet-bar');
            div.innerHTML = '<a href="#" title="Lokasaun atual" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;font-size:16px;color:#2C3E50"><i class="fas fa-location-crosshairs"></i></a>';
            L.DomEvent.on(div, 'click', function (e) {
                L.DomEvent.stopPropagation(e);
                L.DomEvent.preventDefault(e);
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (pos) {
                        map.setView([pos.coords.latitude, pos.coords.longitude], 16);
                        updateLocation(pos.coords.latitude, pos.coords.longitude);
                    }, function (err) {
                        alert('La bele asesu lokasaun: ' + err.message);
                    });
                } else {
                    alert('Navegador la suporta geolocation.');
                }
            });
            return div;
        };
        gpsCtrl.addTo(map);

        // Existing marker
        var icon = L.divIcon({
            className: '',
            html: '<div style="width:42px;height:42px;background:#2C3E50;border-radius:50%;border:3px solid #FFD700;box-shadow:0 3px 12px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center"><i class="fas fa-school" style="color:#fff;font-size:18px"></i></div>',
            iconSize: [42, 42],
            popupAnchor: [0, -22]
        });
        currentMarker = L.marker([CENTER_LAT, CENTER_LNG], { icon: icon }).addTo(map)
            .bindPopup('<strong>Lokasaun Escola</strong><br><small>' + CENTER_LAT.toFixed(6) + ', ' + CENTER_LNG.toFixed(6) + '</small>')
            .openPopup();

        map.on('click', function (e) {
            updateLocation(e.latlng.lat, e.latlng.lng);
        });
    }

    // ---- Update location ----
    function updateLocation(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(8);
        document.getElementById('longitude').value = lng.toFixed(8);

        document.getElementById('dispLat').textContent = lat.toFixed(6);
        document.getElementById('dispLng').textContent = lng.toFixed(6);

        var latS = document.getElementById('latStatus');
        var lngS = document.getElementById('lngStatus');
        latS.textContent = '✓ Hili ona';
        latS.className = 'th-coord-status ok';
        lngS.textContent = '✓ Hili ona';
        lngS.className = 'th-coord-status ok';

        document.getElementById('coordBoxLat').classList.add('filled');
        document.getElementById('coordBoxLng').classList.add('filled');

        var confirm = document.getElementById('coordConfirm');
        document.getElementById('coordText').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
        confirm.style.display = 'flex';

        if (currentMarker) map.removeLayer(currentMarker);
        var icon = L.divIcon({
            className: '',
            html: '<div style="width:42px;height:42px;background:#2C3E50;border-radius:50%;border:3px solid #FFD700;box-shadow:0 3px 12px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center"><i class="fas fa-school" style="color:#fff;font-size:18px"></i></div>',
            iconSize: [42, 42],
            popupAnchor: [0, -22]
        });
        currentMarker = L.marker([lat, lng], { icon: icon }).addTo(map)
            .bindPopup('<strong>Lokasaun Escola</strong><br><small>' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</small>')
            .openPopup();
    }

    // ---- Search ----
    function searchLocation() {
        var q = document.getElementById('searchLocation').value.trim();
        if (!q) { alert('Hakerek naran fatin atu buka.'); return; }

        var btn = document.getElementById('searchBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buka...';
        btn.disabled = true;

        var url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' +
                  encodeURIComponent(q + ', Timor-Leste');

        fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.innerHTML = '<i class="fas fa-location-dot"></i> Buka';
                btn.disabled = false;
                if (data && data.length > 0) {
                    var lat = parseFloat(data[0].lat);
                    var lng = parseFloat(data[0].lon);
                    map.setView([lat, lng], 16);
                    updateLocation(lat, lng);
                } else {
                    alert('Fatin "' + q + '" la hetan iha Timor-Leste.\nKoha ho naran seluk.');
                }
            })
            .catch(function () {
                btn.innerHTML = '<i class="fas fa-location-dot"></i> Buka';
                btn.disabled = false;
                alert('La bele buka fatin. Verifika koneksaun internet.');
            });
    }

    // ---- Photo preview ----
    function bindPreview(inputId, previewId) {
        var input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('change', function (e) {
            var wrap = document.getElementById(previewId);
            wrap.innerHTML = '';
            Array.from(e.target.files).forEach(function (file) {
                var img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.className = 'preview-img';
                wrap.appendChild(img);
            });
        });
    }

    // ---- Init ----
    document.addEventListener('DOMContentLoaded', function () {
        initMap();

        var searchBtn = document.getElementById('searchBtn');
        if (searchBtn) searchBtn.addEventListener('click', searchLocation);
        var searchInput = document.getElementById('searchLocation');
        if (searchInput) searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); searchLocation(); }
        });

        bindPreview('mainPhoto', 'mainPreview');
        bindPreview('galleryPhoto', 'galleryPreview');
    });

}());
</script>

<?php require_once '../../includes/admin-footer.php'; ?>