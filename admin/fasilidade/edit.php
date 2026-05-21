<?php
// =============================================
// Edita Fasilidade Escola - GIS Ensino Primaria Timor-Leste
// =============================================

// Set page configuration
$page_title = 'Edita Fasilidade Escola';
$page_icon = 'fa-edit';

// Include required files
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
require_once '../../includes/csrf.php';

// Check admin login
checkAdminLogin();

$db = getDB();
$id = (int)$_GET['id'];

// Get facility data
$stmt = $db->prepare("SELECT * FROM fasilidade_escola WHERE id = ?");
$stmt->execute([$id]);
$fasilidade = $stmt->fetch();

if (!$fasilidade) {
    redirect('/admin/fasilidade/list.php');
    exit();
}

$error = '';

// =============================================
// SUBMISAUN FORM
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token seguransa la validu.';
    } else {
        $naran = sanitize($_POST['naran_fasilidade']);
        $ikonu = sanitize($_POST['ikonu']);
        
        if (empty($naran)) {
            $error = 'Naran fasilidade tenki prense.';
        } else {
            $stmt = $db->prepare("UPDATE fasilidade_escola SET naran_fasilidade = ?, ikonu = ? WHERE id = ?");
            $stmt->execute([$naran, $ikonu, $id]);
            setFlashMessage('success', '✅ Fasilidade escola atualiza ho susesu!');
            redirect('/admin/fasilidade/list.php');
            exit();
        }
    }
}

$csrf_token = generateCSRFToken();

// Include admin header
require_once '../../includes/admin-header.php';
?>

<!-- ============================================= -->
FORM EDITA FASILIDADE ESCOLA
<!-- ============================================= -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-table">
            <div class="card-table-header">
                <div class="header-title">
                    <i class="fas fa-edit"></i>
                    <h5>Edita Fasilidade Escola</h5>
                </div>
                <a href="list.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Fila ba Lista
                </a>
            </div>
            
            <div class="card-table-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-tag text-primary me-2"></i> Naran Fasilidade Escola <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="naran_fasilidade" class="form-control modern-input" 
                               value="<?= htmlspecialchars($fasilidade['naran_fasilidade']) ?>" required>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Fasilidade infrastrutura ne'ebé escola iha.
                        </small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-icons text-primary me-2"></i> Ikon (FontAwesome)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light" id="iconPreview">
                                <i class="fas fa-<?= htmlspecialchars($fasilidade['ikonu'] ?? 'school') ?>"></i>
                            </span>
                            <input type="text" name="ikonu" id="iconInput" class="form-control modern-input" 
                                   placeholder="Exemplu: water, plug, toilet, book, computer" 
                                   value="<?= htmlspecialchars($fasilidade['ikonu'] ?? 'school') ?>">
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Hakerek naran ikon FontAwesome (la presiza 'fa-'). 
                            <a href="https://fontawesome.com/icons" target="_blank" class="text-primary">
                                <i class="fas fa-external-link-alt"></i> Lista ikon
                            </a>
                        </small>
                    </div>
                    
                    <div class="bg-light p-3 rounded mb-4">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <i class="fas fa-eye text-primary"></i>
                            <span class="fw-bold">Previzualizasaun:</span>
                            <span class="badge bg-primary" id="previewBadge">
                                <i class="fas fa-<?= htmlspecialchars($fasilidade['ikonu'] ?? 'school') ?>"></i>
                                <?= htmlspecialchars($fasilidade['naran_fasilidade']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Informasaun:</strong> Ikon ne'e sei hatudu iha públiku nu'udar símbolu ba fasilidade escola ne'e.
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-between">
                        <a href="list.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Kansela
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Atualiza
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================= -->
EZEMPLU FASILIDADE ESCOLA
<!-- ============================================= -->
<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
        <div class="card-table">
            <div class="card-table-header">
                <div class="header-title">
                    <i class="fas fa-lightbulb text-warning"></i>
                    <h5>Ezemplu Ikon Fasilidade Escola</h5>
                </div>
            </div>
            <div class="card-table-body">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="facility-example" onclick="setIcon('water')">
                            <i class="fas fa-water"></i>
                            <span>Bee Moos</span>
                            <code>water</code>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="facility-example" onclick="setIcon('plug')">
                            <i class="fas fa-plug"></i>
                            <span>Eletrisidade</span>
                            <code>plug</code>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="facility-example" onclick="setIcon('toilet')">
                            <i class="fas fa-toilet"></i>
                            <span>Toilet</span>
                            <code>toilet</code>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="facility-example" onclick="setIcon('book')">
                            <i class="fas fa-book"></i>
                            <span>Biblioteka</span>
                            <code>book</code>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="facility-example" onclick="setIcon('chalkboard')">
                            <i class="fas fa-chalkboard"></i>
                            <span>Ladrilhosaun</span>
                            <code>chalkboard</code>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="facility-example" onclick="setIcon('futbol')">
                            <i class="fas fa-futbol"></i>
                            <span>Area Joga</span>
                            <code>futbol</code>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="facility-example" onclick="setIcon('computer')">
                            <i class="fas fa-computer"></i>
                            <span>Kompastru</span>
                            <code>computer</code>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="facility-example" onclick="setIcon('road')">
                            <i class="fas fa-road"></i>
                            <span>Akses Estrada</span>
                            <code>road</code>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="facility-example" onclick="setIcon('utensils')">
                            <i class="fas fa-utensils"></i>
                            <span>Kantina</span>
                            <code>utensils</code>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="facility-example" onclick="setIcon('wheelchair')">
                            <i class="fas fa-wheelchair"></i>
                            <span>Rampa</span>
                            <code>wheelchair</code>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="facility-example" onclick="setIcon('hand-holding-heart')">
                            <i class="fas fa-hand-holding-heart"></i>
                            <span>Merenda</span>
                            <code>hand-holding-heart</code>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="facility-example" onclick="setIcon('tree')">
                            <i class="fas fa-tree"></i>
                            <span>Jardim</span>
                            <code>tree</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .modern-input {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 12px 15px;
        transition: all 0.3s ease;
    }
    
    .modern-input:focus {
        border-color: #2C3E50;
        box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
        outline: none;
    }
    
    .input-group-text {
        border-radius: 10px 0 0 10px;
        min-width: 45px;
        justify-content: center;
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
        color: #2C3E50;
    }
    
    .header-title h5 {
        margin: 0;
        font-weight: 700;
    }
    
    .card-table-body {
        padding: 24px;
    }
    
    .facility-example {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .facility-example:hover {
        background: rgba(44, 62, 80, 0.1);
        transform: translateX(5px);
    }
    
    .facility-example i {
        width: 25px;
        color: #2C3E50;
        font-size: 16px;
    }
    
    .facility-example span {
        flex: 1;
        font-size: 13px;
    }
    
    .facility-example code {
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 5px;
        font-size: 10px;
        color: #2C3E50;
    }
    
    @media (max-width: 768px) {
        .card-table-header {
            flex-direction: column;
            text-align: center;
        }
        .header-title {
            justify-content: center;
        }
    }
</style>

<script>
    // Preview ikon langsung
    const iconInput = document.getElementById('iconInput');
    const iconPreview = document.getElementById('iconPreview');
    const previewBadge = document.getElementById('previewBadge');
    const facilityName = document.querySelector('input[name="naran_fasilidade"]');
    
    function updatePreview() {
        let iconName = iconInput.value.trim();
        if (iconName === '') {
            iconName = 'school';
        }
        iconPreview.innerHTML = '<i class="fas fa-' + iconName + '"></i>';
        
        let name = facilityName ? facilityName.value.trim() : 'Fasilidade';
        if (name === '') name = 'Fasilidade';
        previewBadge.innerHTML = '<i class="fas fa-' + iconName + '"></i> ' + name;
    }
    
    function setIcon(iconName) {
        iconInput.value = iconName;
        updatePreview();
        // Effect visual
        iconInput.style.backgroundColor = '#e8f5e9';
        setTimeout(() => {
            iconInput.style.backgroundColor = '';
        }, 300);
    }
    
    iconInput.addEventListener('input', updatePreview);
    if (facilityName) {
        facilityName.addEventListener('input', updatePreview);
    }
    updatePreview();
</script>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?>