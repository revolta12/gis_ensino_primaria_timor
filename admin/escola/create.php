<?php
// =============================================
// Aumenta Escola - GIS Ensino Primaria Timor-Leste
// =============================================

$page_title = 'Aumenta Escola Foun';
$page_icon = 'fa-plus';

require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
require_once '../../includes/csrf.php';

checkAdminLogin();
require_once '../../includes/admin-header.php';

$db = getDB();
$error = '';

$categories = $db->query("SELECT id, naran_kategoria FROM kategoria_escola ORDER BY naran_kategoria")->fetchAll();
$all_facilities = $db->query("SELECT id, naran_fasilidade, ikonu FROM fasilidade_escola ORDER BY naran_fasilidade")->fetchAll();
$languages = ['Tetun', 'Portugues', 'Ingles', 'Bahasa Indonesia'];

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
            $error = 'Lokasaun seidauk hili! Klik iha mapa iha tab "Lokasaun" atu marka fatin escola.';
        } else {
            $slug = slugify($naran_escola);
            $original_slug = $slug;
            $counter = 1;
            while (true) {
                $stmt = $db->prepare("SELECT id FROM escola WHERE slug = ?");
                $stmt->execute([$slug]);
                if (!$stmt->fetch()) break;
                $slug = $original_slug . '-' . $counter++;
            }

            $foto_prinsipal = '';
            if (isset($_FILES['foto_prinsipal']) && $_FILES['foto_prinsipal']['error'] === UPLOAD_ERR_OK) {
                $foto_prinsipal = uploadFoto($_FILES['foto_prinsipal']);
            }

            $stmt = $db->prepare("
                INSERT INTO escola (naran_escola, slug, kategoria_id, enderesu, suku, postu_administrativu, municipio,
                                  latitude, longitude, telefone, email_escola, website,
                                  total_estudante, total_estudante_feto, total_estudante_mane,
                                  total_profesor, total_profesor_feto, total_profesor_mane,
                                  klase_hosi, klase_too, sistema_ensinu, foto_prinsipal,
                                  iha_bee_moos, iha_eletrisidade, iha_toilet,
                                  destakadu, aktivo, kria_husi)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $result = $stmt->execute([
                $naran_escola, $slug, $kategoria_id, $enderesu, $suku, $postu_administrativu, $municipio,
                $latitude, $longitude, $telefone, $email_escola, $website,
                $total_estudante, $total_estudante_feto, $total_estudante_mane,
                $total_profesor, $total_profesor_feto, $total_profesor_mane,
                $klase_hosi, $klase_too, $sistema_ensinu, $foto_prinsipal,
                $iha_bee_moos, $iha_eletrisidade, $iha_toilet,
                $destakadu, $aktivo, $_SESSION['admin_id']
            ]);

            if ($result) {
                $escola_id = $db->lastInsertId();

                if (isset($_POST['fasilidade']) && is_array($_POST['fasilidade'])) {
                    $stmt_f = $db->prepare("INSERT INTO escola_fasilidade (escola_id, fasilidade_id) VALUES (?, ?)");
                    foreach ($_POST['fasilidade'] as $f_id) {
                        $stmt_f->execute([$escola_id, (int)$f_id]);
                    }
                }

                if (isset($_FILES['foto_galeri']) && !empty($_FILES['foto_galeri']['name'][0])) {
                    uploadMultipleFoto($_FILES['foto_galeri'], $escola_id, $db, 'foto_escola');
                }

                setFlashMessage('success', '✅ Escola aumenta ho susesu!');
                redirect('/admin/escola/list.php');
                exit();
            } else {
                $error = 'Falha aumenta escola. Favor halo di' . "'ak tan.";
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<?php if ($error): ?>
<div class="th-alert th-alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <span><?= htmlspecialchars($error) ?></span>
    <button type="button" class="th-alert-close" onclick="this.parentElement.remove()">&times;</button>
</div>
<?php endif; ?>

<div class="th-page-header">
    <div class="th-page-icon">
        <i class="fas fa-plus"></i>
    </div>
    <div>
        <h1 class="th-page-title">Aumenta Escola Foun</h1>
        <p class="th-page-sub">GIS Ensino Primaria Timor-Leste — prense informasaun no hili lokasaun iha mapa</p>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" id="escolaForm">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="latitude" id="latitude" value="">
    <input type="hidden" name="longitude" id="longitude" value="">

    <!-- TAB NAV -->
    <div class="th-tab-nav" role="tablist">
        <button type="button" class="th-tab active" data-tab="basic" role="tab">
            <i class="fas fa-info-circle"></i> Informasaun
        </button>
        <button type="button" class="th-tab" data-tab="location" role="tab">
            <i class="fas fa-map-marker-alt"></i> Lokasaun
        </button>
        <button type="button" class="th-tab" data-tab="photos" role="tab">
            <i class="fas fa-camera"></i> Foto
        </button>
        <button type="button" class="th-tab" data-tab="facilities" role="tab">
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
                        <input type="text" name="naran_escola" placeholder="Ezemplu: Escola Primaria Dili Centro" required>
                    </div>
                    <div class="th-field">
                        <label>Kategoria <span class="req">*</span></label>
                        <select name="kategoria_id" required>
                            <option value="">— Hili Kategoria —</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['naran_kategoria']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="th-row th-col-1">
                    <div class="th-field">
                        <label>Enderesu Kompletu</label>
                        <textarea name="enderesu" rows="2" placeholder="Rua, numeru, suku..."></textarea>
                    </div>
                </div>

                <div class="th-row th-col-3">
                    <div class="th-field">
                        <label>Suku</label>
                        <input type="text" name="suku" placeholder="Bidau Lecidere">
                    </div>
                    <div class="th-field">
                        <label>Postu Administrativu</label>
                        <input type="text" name="postu_administrativu" placeholder="Nain Feto">
                    </div>
                    <div class="th-field">
                        <label>Municipio</label>
                        <input type="text" name="municipio" value="Díli">
                    </div>
                </div>

                <div class="th-row th-col-3">
                    <div class="th-field">
                        <label>Telefone</label>
                        <input type="text" name="telefone" placeholder="+670 ...">
                    </div>
                    <div class="th-field">
                        <label>Email Escola</label>
                        <input type="email" name="email_escola" placeholder="escola@example.com">
                    </div>
                    <div class="th-field">
                        <label>Website</label>
                        <input type="url" name="website" placeholder="https://...">
                    </div>
                </div>

                <div class="th-card-head" style="margin-top:16px">
                    <i class="fas fa-users"></i>
                    <span>Dadus Estudante no Profesor</span>
                </div>

                <div class="th-row th-col-3">
                    <div class="th-field">
                        <label>Total Estudante</label>
                        <input type="number" name="total_estudante" value="0" min="0">
                    </div>
                    <div class="th-field">
                        <label>Estudante Feto</label>
                        <input type="number" name="total_estudante_feto" value="0" min="0">
                    </div>
                    <div class="th-field">
                        <label>Estudante Mane</label>
                        <input type="number" name="total_estudante_mane" value="0" min="0">
                    </div>
                </div>

                <div class="th-row th-col-3">
                    <div class="th-field">
                        <label>Total Profesor</label>
                        <input type="number" name="total_profesor" value="0" min="0">
                    </div>
                    <div class="th-field">
                        <label>Profesor Feto</label>
                        <input type="number" name="total_profesor_feto" value="0" min="0">
                    </div>
                    <div class="th-field">
                        <label>Profesor Mane</label>
                        <input type="number" name="total_profesor_mane" value="0" min="0">
                    </div>
                </div>

                <div class="th-row th-col-2">
                    <div class="th-field">
                        <label>Klase Hosi</label>
                        <select name="klase_hosi">
                            <?php for($i=1; $i<=6; $i++): ?>
                            <option value="<?= $i ?>" <?= $i==1 ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="th-field">
                        <label>Klase Too</label>
                        <select name="klase_too">
                            <?php for($i=1; $i<=6; $i++): ?>
                            <option value="<?= $i ?>" <?= $i==6 ? 'selected' : '' ?>><?= $i ?></option>
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
                                <input type="checkbox" name="sistema_ensinu[]" value="<?= $lang ?>">
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
                            <input type="checkbox" name="iha_bee_moos">
                            <span><i class="fas fa-water"></i> Iha Bee Moos</span>
                        </label>
                        <label class="th-pill">
                            <input type="checkbox" name="iha_eletrisidade">
                            <span><i class="fas fa-plug"></i> Iha Eletrisidade</span>
                        </label>
                        <label class="th-pill">
                            <input type="checkbox" name="iha_toilet">
                            <span><i class="fas fa-toilet"></i> Iha Toilet</span>
                        </label>
                    </div>
                </div>

                <div class="th-row th-col-1">
                    <div class="th-pill-group">
                        <label class="th-pill">
                            <input type="checkbox" name="destakadu">
                            <span><i class="fas fa-star" style="color:#2C3E50;font-size:11px;margin-right:4px"></i> Escola Destakadu</span>
                        </label>
                        <label class="th-pill">
                            <input type="checkbox" name="aktivo" checked>
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
                <span>Lokasaun Escola iha Timor-Leste — klik iha mapa atu hili fatin</span>
            </div>
            <div class="th-card-body">

                <!-- Search -->
                <div class="th-map-search">
                    <i class="fas fa-search th-search-icon"></i>
                    <input type="text" id="searchLocation" placeholder="Buka fatin... (Ezemplu: Escola Primaria, Comoro, Bidau)">
                    <button type="button" id="searchBtn" class="th-btn-search">
                        <i class="fas fa-location-dot"></i> Buka
                    </button>
                </div>
                <p class="th-map-hint">✏️ Hakerek naran fatin iha Timor-Leste, depois klik "Buka" — ka klik diretamente iha mapa</p>

                <!-- Koordinat display -->
                <div class="th-coord-row">
                    <div class="th-coord-box" id="coordBoxLat">
                        <div class="th-coord-label">Latitude <span class="req">*</span></div>
                        <div class="th-coord-val" id="dispLat">—</div>
                        <div class="th-coord-status" id="latStatus">Seidauk hili</div>
                    </div>
                    <div class="th-coord-box" id="coordBoxLng">
                        <div class="th-coord-label">Longitude <span class="req">*</span></div>
                        <div class="th-coord-val" id="dispLng">—</div>
                        <div class="th-coord-status" id="lngStatus">Seidauk hili</div>
                    </div>
                </div>

                <!-- Peta -->
                <div id="timorMap"></div>

                <!-- Konfirmasi lokasi -->
                <div class="th-coord-confirm" id="coordConfirm" style="display:none">
                    <i class="fas fa-check-circle"></i>
                    <strong>✅ Fatin hili ona!</strong>
                    <span id="coordText"></span>
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

                <div class="th-field" style="margin-bottom:20px">
                    <label>Foto Prinsipal</label>
                    <div class="th-upload-zone" id="mainZone">
                        <input type="file" name="foto_prinsipal" id="mainPhoto" accept="image/jpeg,image/png,image/webp">
                        <div class="th-upload-inner">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Klik atu upload foto prinsipal</p>
                            <small>JPG, PNG, WEBP — maks 5MB</small>
                        </div>
                        <div class="th-previews" id="mainPreview"></div>
                    </div>
                </div>

                <div class="th-field">
                    <label>Galeri Foto (bele hili barak)</label>
                    <div class="th-upload-zone" id="galleryZone">
                        <input type="file" name="foto_galeri[]" id="galleryPhoto" multiple accept="image/jpeg,image/png,image/webp">
                        <div class="th-upload-inner">
                            <i class="fas fa-photo-video"></i>
                            <p>Klik atu hili foto galeri</p>
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
                        <input type="checkbox" name="fasilidade[]" value="<?= $facility['id'] ?>">
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
            <i class="fas fa-save"></i> Rai Escola
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

input[type=text], input[type=email], input[type=url], input[type=number], select, textarea {
    padding:9px 13px; border:1px solid #e0e0e0; border-radius:8px;
    font-size:13px; color:#1a1a1a; background:#fff;
    transition:border-color .15s, box-shadow .15s; outline:none; width:100%;
    font-family:inherit;
}
input:focus, select:focus, textarea:focus {
    border-color:#2C3E50; box-shadow:0 0 0 3px rgba(44,62,80,.08);
}
textarea { resize:vertical; }
select { cursor:pointer; }

/* ---- Pill Checkboxes ---- */
.th-pill-group { display:flex; flex-wrap:wrap; gap:8px; }
.th-pill { display:inline-flex; align-items:center; gap:6px; padding:6px 13px; border:1px solid #e0e0e0; border-radius:20px; cursor:pointer; font-size:12.5px; color:#555; transition:all .15s; }
.th-pill input { accent-color:#2C3E50; }
.th-pill:hover { border-color:#2C3E50; color:#2C3E50; background:#E8F0F8; }

/* ---- Map ---- */
.th-map-search { display:flex; align-items:center; gap:8px; background:#f8f8f8; border:1px solid #e0e0e0; border-radius:10px; padding:6px 6px 6px 14px; margin-bottom:6px; }
.th-search-icon { color:#888; font-size:14px; flex-shrink:0; }
.th-map-search input { flex:1; border:none; background:transparent; font-size:13px; color:#1a1a1a; outline:none; padding:4px 0; }
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
.th-coord-confirm i { font-size:16px; }
.th-coord-confirm strong { margin-right:4px; }

/* ---- Upload ---- */
.th-upload-zone { position:relative; border:1.5px dashed #ddd; border-radius:12px; padding:28px 20px; text-align:center; cursor:pointer; transition:all .2s; overflow:hidden; }
.th-upload-zone:hover { border-color:#2C3E50; background:rgba(44,62,80,.02); }
.th-upload-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.th-upload-inner i { font-size:36px; color:#2C3E50; display:block; margin-bottom:8px; }
.th-upload-inner p { margin:0 0 4px; font-size:13px; font-weight:600; color:#333; }
.th-upload-inner small { font-size:11.5px; color:#aaa; }
.th-previews { display:flex; flex-wrap:wrap; gap:8px; margin-top:14px; justify-content:center; }
.th-previews img { width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid #2C3E50; }

/* ---- Facilities ---- */
.th-fac-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(190px, 1fr)); gap:8px; }
.th-fac-item { display:flex; align-items:center; gap:9px; padding:10px 13px; border:1px solid #ebebeb; border-radius:9px; cursor:pointer; font-size:13px; color:#555; transition:all .15s; background:#fafafa; }
.th-fac-item input { accent-color:#2C3E50; }
.th-fac-item i { color:#2C3E50; font-size:13px; width:16px; text-align:center; }
.th-fac-item:hover { border-color:#2C3E50; background:#E8F0F8; color:#1a1a1a; }

/* ---- Actions ---- */
.th-form-actions { display:flex; justify-content:space-between; align-items:center; margin-top:24px; padding-top:20px; border-top:1px solid #ebebeb; }
.th-btn-back { display:inline-flex; align-items:center; gap:7px; padding:10px 20px; border:1px solid #ddd; border-radius:9px; background:#fff; color:#555; font-size:13px; font-weight:500; text-decoration:none; transition:all .15s; }
.th-btn-back:hover { border-color:#aaa; background:#f5f5f5; color:#333; }
.th-btn-submit { display:inline-flex; align-items:center; gap:7px; padding:10px 24px; border:none; border-radius:9px; background:#2C3E50; color:#fff; font-size:13px; font-weight:600; cursor:pointer; transition:background .15s; }
.th-btn-submit:hover { background:#1A252F; }
.th-btn-submit:active { transform:scale(.98); }

/* ---- Leaflet overrides ---- */
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

    var CENTER_LAT = -8.553;
    var CENTER_LNG = 125.579;
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
        map = L.map('timorMap').setView([CENTER_LAT, CENTER_LNG], 13);

        var streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
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

        // Hint marker at Dili centre
        var hintIcon = L.divIcon({
            className: '',
            html: '<div style="width:30px;height:30px;background:#2C3E50;border-radius:50%;border:2px solid #FFD700;display:flex;align-items:center;justify-content:center;opacity:.8"><i class="fas fa-school" style="color:#FFD700;font-size:12px"></i></div>',
            iconSize: [30, 30]
        });
        L.marker([CENTER_LAT, CENTER_LNG], { icon: hintIcon }).addTo(map)
            .bindPopup('<em>Klik iha mapa atu hili lokasaun escola</em>');

        map.on('click', function (e) {
            updateLocation(e.latlng.lat, e.latlng.lng);
        });
    }

    // ---- Update location ----
    function updateLocation(lat, lng) {
        // Hidden inputs for form submission
        document.getElementById('latitude').value  = lat.toFixed(8);
        document.getElementById('longitude').value = lng.toFixed(8);

        // Display values
        document.getElementById('dispLat').textContent = lat.toFixed(6);
        document.getElementById('dispLng').textContent = lng.toFixed(6);

        var latS = document.getElementById('latStatus');
        var lngS = document.getElementById('lngStatus');
        latS.textContent = '✓ Hili ona';
        latS.className   = 'th-coord-status ok';
        lngS.textContent = '✓ Hili ona';
        lngS.className   = 'th-coord-status ok';

        document.getElementById('coordBoxLat').classList.add('filled');
        document.getElementById('coordBoxLng').classList.add('filled');

        var confirm = document.getElementById('coordConfirm');
        document.getElementById('coordText').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
        confirm.style.display = 'flex';

        // Marker
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
        btn.disabled  = true;

        var url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' +
                  encodeURIComponent(q + ', Timor-Leste');

        fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.innerHTML = '<i class="fas fa-location-dot"></i> Buka';
                btn.disabled  = false;
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
                btn.disabled  = false;
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

        bindPreview('mainPhoto',    'mainPreview');
        bindPreview('galleryPhoto', 'galleryPreview');
    });

}());
</script>

<?php require_once '../../includes/admin-footer.php'; ?>