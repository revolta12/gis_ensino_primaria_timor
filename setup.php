<?php
// =============================================
// Setup - GIS Ensino Primaria Timor-Leste
// =============================================
// File ne'e atu kria admin user ba sistema.
// Hamos file ne'e depois de uza ba seguransa!

require_once 'config/database.php';
require_once 'includes/functions.php';

$db = getDB();

// =============================================
// ADMIN CREDENTIALS
// =============================================
$email = 'admin@ensinoprimaria.tl';
$nama = 'Admin GIS Ensino Primaria';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// =============================================
// CHECK OR CREATE/UPDATE ADMIN USER
// =============================================
try {
    // Check if user already exists
    $stmt = $db->prepare("SELECT id FROM utilizador WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing user to admin
        $stmt = $db->prepare("UPDATE utilizador SET naran = ?, liafuan_segredu = ?, papel = ?, aktivo = 1 WHERE email = ?");
        $stmt->execute([$nama, $hashed_password, 'admin', $email]);
        
        $message = "✅ Admin user atualiza ho susesu!<br>";
        $message .= "<strong>Email:</strong> $email<br>";
        $message .= "<strong>Password:</strong> $password<br>";
        $message .= "<small class='text-muted'> (Password hashed secure)</small>";
    } else {
        // Insert new admin user
        $stmt = $db->prepare("INSERT INTO utilizador (naran, email, liafuan_segredu, papel, aktivo) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$nama, $email, $hashed_password, 'admin']);
        
        $message = "✅ Admin user kria foun ho susesu!<br>";
        $message .= "<strong>Email:</strong> $email<br>";
        $message .= "<strong>Password:</strong> $password<br>";
        $message .= "<small class='text-muted'> (Password hashed secure)</small>";
    }
    
    $success = true;
    
    // Optional: Check if database tables exist
    $stmt = $db->query("SHOW TABLES LIKE 'escola'");
    $tables_exist = $stmt->rowCount() > 0;
    
    if (!$tables_exist) {
        $warning = "⚠️ Atenasaun: Tabela 'escola' la hetan. Favor importa database estrutura uluk!";
    }
    
} catch (PDOException $e) {
    $message = "❌ Error: " . $e->getMessage();
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - GIS Ensino Primaria Timor-Leste</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2C3E50 0%, #1A252F 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Nunito', sans-serif;
        }
        .setup-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 550px;
            width: 100%;
            text-align: center;
        }
        .setup-icon {
            font-size: 64px;
            color: #F39C12;
            margin-bottom: 20px;
        }
        .alert {
            border-radius: 12px;
            text-align: left;
        }
        .btn-primary {
            background: #2C3E50;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: #1A252F;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(44, 62, 80, 0.4);
        }
        .security-warning {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 10px;
            padding: 12px;
            font-size: 12px;
            color: #856404;
        }
        .credential-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            font-family: monospace;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-card mx-auto">
            <div class="setup-icon">
                <i class="fas fa-school"></i>
            </div>
            <h2 class="mb-2 fw-bold">GIS Ensino Primaria</h2>
            <p class="text-muted mb-4">Sistema Jestaun Eskola Primaria Timor-Leste</p>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-<?= $success ? 'success' : 'danger' ?>" role="alert">
                    <i class="fas fa-<?= $success ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                    <?= $message ?>
                </div>
                
                <?php if (isset($warning)): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= $warning ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="credential-box mb-4">
                        <strong><i class="fas fa-key"></i> Dadus Login:</strong><br>
                        <i class="fas fa-envelope"></i> Email: <code><?= $email ?></code><br>
                        <i class="fas fa-lock"></i> Password: <code><?= $password ?></code>
                    </div>
                    
                    <a href="admin/login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i> Login ba Dashboard
                    </a>
                    
                    <div class="security-warning mt-4">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Seguransa!</strong> Hamos file <code>setup.php</code> depois de login ba dala uluk!
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="text-start small text-muted">
                        <p><i class="fas fa-info-circle"></i> <strong>Nota:</strong></p>
                        <ul>
                            <li>Password atu seguru ho hashing (bcrypt)</li>
                            <li>File ne'e bele uza atu reset admin password</li>
                            <li>Hamos file ne'e depois atu prevene asesu la autoriza</li>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-muted mb-4">Setup sistema atu kria admin account</p>
                <button type="button" class="btn btn-primary w-100" onclick="setupAdmin()">
                    <i class="fas fa-play me-2"></i> Kria Admin User
                </button>
                <p class="text-muted small text-center mt-3 mb-0">
                    <i class="fas fa-info-circle"></i> Kria admin ho:<br>
                    <strong>Email:</strong> <?= $email ?> <br>
                    <strong>Password:</strong> <?= $password ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function setupAdmin() {
            // Show loading
            const btn = document.querySelector('.btn-primary');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Prosesu...';
            btn.disabled = true;
            location.reload();
        }
    </script>
</body>
</html>