<?php
// =============================================
// Edita Kategoria Escola - GIS Ensino Primaria Timor-Leste
// =============================================

// Set page configuration
$page_title = 'Edita Kategoria Escola';
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

// Get category data
$stmt = $db->prepare("SELECT * FROM kategoria_escola WHERE id = ?");
$stmt->execute([$id]);
$cat = $stmt->fetch();

if (!$cat) {
    redirect('/admin/kategoria/list.php');
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
        $naran = sanitize($_POST['naran_kategoria']);
        $deskrisaun = sanitize($_POST['deskrisaun']);
        $ikonu = sanitize($_POST['ikonu']);
        
        if (empty($naran)) {
            $error = 'Naran kategoria tenki prense.';
        } else {
            $stmt = $db->prepare("UPDATE kategoria_escola SET naran_kategoria=?, deskrisaun=?, ikonu=? WHERE id=?");
            $stmt->execute([$naran, $deskrisaun, $ikonu, $id]);
            setFlashMessage('success', '✅ Kategoria escola atualiza ho susesu!');
            redirect('/admin/kategoria/list.php');
            exit();
        }
    }
}

$csrf_token = generateCSRFToken();

// Include admin header
require_once '../../includes/admin-header.php';
?>

<!-- ============================================= -->
FORM EDITA KATEGORIA ESCOLA
<!-- ============================================= -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-table">
            <div class="card-table-header">
                <div class="header-title">
                    <i class="fas fa-edit"></i>
                    <h5>Edita Kategoria Escola</h5>
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
                            <i class="fas fa-tag text-primary me-2"></i> Naran Kategoria Escola <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="naran_kategoria" class="form-control modern-input" 
                               value="<?= htmlspecialchars($cat['naran_kategoria']) ?>" required>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Naran ba tipu/klase husi escola primaria.
                        </small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-align-left text-primary me-2"></i> Deskrisaun
                        </label>
                        <textarea name="deskrisaun" class="form-control modern-input" rows="3" 
                                  placeholder="Deskrisaun badak kona-ba kategoria escola ne'e..."><?= htmlspecialchars($cat['deskrisaun']) ?></textarea>
                        <small class="text-muted">Deskrisaun badak kona-ba kategoria escola ne'e.</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-icons text-primary me-2"></i> Ikon (FontAwesome)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light" id="iconPreview">
                                <i class="fas fa-<?= htmlspecialchars($cat['ikonu'] ?? 'school') ?>"></i>
                            </span>
                            <input type="text" name="ikonu" id="iconInput" class="form-control modern-input" 
                                   placeholder="Ezemplu: school, building, church, cross, users" 
                                   value="<?= htmlspecialchars($cat['ikonu'] ?? 'school') ?>">
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
                                <i class="fas fa-<?= htmlspecialchars($cat['ikonu'] ?? 'school') ?>"></i>
                                <?= htmlspecialchars($cat['naran_kategoria']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Informasaun:</strong> Ikon ne'e sei hatudu iha públiku nu'udar símbolu ba kategoria escola ne'e.
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
EZEMPLU KATEGORIA ESKOLA POPULÁR
<!-- ============================================= -->
<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
        <div class="card-table">
            <div class="card-table-header">
                <div class="header-title">
                    <i class="fas fa-lightbulb text-warning"></i>
                    <h5>Ezemplu Kategoria Eskola Popular</h5>
                </div>
            </div>
            <div class="card-table-body">
                <div class="row g-3">
                    <div class="col-md-4 col-6">
                        <div class="category-example" onclick="setCategory('Eskola Pública', 'Jestu husi governu, padraun nasionál', 'landmark')">
                            <i class="fas fa-landmark"></i>
                            <span>Eskola Pública</span>
                            <code>landmark</code>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="category-example" onclick="setCategory('Eskola Privada', 'Jestu husi fundasaun ka setor privadu', 'building')">
                            <i class="fas fa-building"></i>
                            <span>Eskola Privada</span>
                            <code>building</code>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="category-example" onclick="setCategory('Eskola Katólika', 'Eskola iha ligasaun ho Igreja Katólika', 'church')">
                            <i class="fas fa-church"></i>
                            <span>Eskola Katólika</span>
                            <code>church</code>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="category-example" onclick="setCategory('Eskola Evangeliku', 'Eskola protestante ka evangeliku', 'cross')">
                            <i class="fas fa-cross"></i>
                            <span>Eskola Evangeliku</span>
                            <code>cross</code>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="category-example" onclick="setCategory('Eskola Komunitária', 'Jestu husi komunidade lokal', 'users')">
                            <i class="fas fa-users"></i>
                            <span>Eskola Komunitária</span>
                            <code>users</code>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="category-example" onclick="setCategory('Eskola Inkluziva', 'Eskola ho acessibilidade ba estudante ho necessidade', 'wheelchair')">
                            <i class="fas fa-wheelchair"></i>
                            <span>Eskola Inkluziva</span>
                            <code>wheelchair</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modern Form Styles */
    .modern-input {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 12px 15px;
        transition: all 0.3s ease;
        width: 100%;
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
    
    .category-example {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .category-example:hover {
        background: rgba(44, 62, 80, 0.1);
        transform: translateX(5px);
    }
    
    .category-example i {
        width: 30px;
        color: #2C3E50;
        font-size: 18px;
    }
    
    .category-example span {
        flex: 1;
        font-weight: 500;
        font-size: 13px;
    }
    
    .category-example code {
        background: #e9ecef;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
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
    const categoryName = document.querySelector('input[name="naran_kategoria"]');
    
    function updatePreview() {
        let iconName = iconInput.value.trim();
        if (iconName === '') {
            iconName = 'school';
        }
        iconPreview.innerHTML = '<i class="fas fa-' + iconName + '"></i>';
        
        let name = categoryName ? categoryName.value.trim() : 'Kategoria';
        if (name === '') name = 'Kategoria';
        previewBadge.innerHTML = '<i class="fas fa-' + iconName + '"></i> ' + name;
    }
    
    function setCategory(name, desc, icon) {
        if (categoryName) categoryName.value = name;
        const descTextarea = document.querySelector('textarea[name="deskrisaun"]');
        if (descTextarea) descTextarea.value = desc;
        if (iconInput) iconInput.value = icon;
        updatePreview();
        
        // Effect visual
        if (categoryName) {
            categoryName.style.backgroundColor = '#e8f5e9';
            setTimeout(() => {
                categoryName.style.backgroundColor = '';
            }, 500);
        }
    }
    
    iconInput.addEventListener('input', updatePreview);
    if (categoryName) categoryName.addEventListener('input', updatePreview);
    updatePreview();
</script>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?>