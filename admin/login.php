<?php
// =============================================
// Admin Login - GIS Ensino Primaria Timor-Leste
// =============================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/csrf.php';

$error = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token seguransa la validu. Favor refresh página.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'Email no password tenki prense.';
        } else {
            $stmt = $db->prepare("SELECT id, naran, email, liafuan_segredu, papel, aktivo FROM utilizador WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['liafuan_segredu'])) {
                if ($user['aktivo'] == 0) {
                    $error = 'Ita nia conta la ativu. Favor kontaktu administrator.';
                } else {
                    // Set session
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_naran'] = $user['naran'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_papel'] = $user['papel'];
                    $_SESSION['login_time'] = time();
                    
                    // Redirect to dashboard
                    header('Location: index.php');
                    exit();
                }
            } else {
                $error = 'Email ka password sala.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GIS Ensino Primaria Timor-Leste</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #2C3E50;
            --primary-dark: #1A252F;
            --secondary: #F39C12;
            --accent: #1a1a1a;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Nunito', sans-serif;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 20px,
                rgba(243, 156, 18, 0.03) 20px,
                rgba(243, 156, 18, 0.03) 40px
            );
            pointer-events: none;
        }
        
        .login-card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            overflow: hidden;
            position: relative;
            z-index: 10;
            max-width: 420px;
            width: 100%;
            margin: 0 auto;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            border-bottom: 4px solid var(--secondary);
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(243, 156, 18, 0.1) 10px,
                rgba(243, 156, 18, 0.1) 20px
            );
        }
        
        .login-header i {
            font-size: 3rem;
            margin-bottom: 10px;
            color: var(--secondary);
            position: relative;
            z-index: 2;
        }
        
        .login-header h3 {
            font-weight: 800;
            margin: 0;
            position: relative;
            z-index: 2;
            font-size: 1.8rem;
        }
        
        .login-header small {
            color: var(--secondary);
            font-weight: 600;
            font-size: 0.85rem;
            display: block;
            margin-top: 5px;
            position: relative;
            z-index: 2;
        }
        
        .login-body {
            padding: 40px;
            background: white;
        }
        
        .form-label {
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.15);
            outline: none;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            padding: 12px;
            font-weight: 700;
            border-radius: 8px;
            color: white;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, var(--primary-dark), #0f1a24);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(44, 62, 80, 0.4);
            color: white;
        }
        
        .alert {
            border-radius: 8px;
            border-left: 4px solid var(--primary);
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            color: #E74C3C;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }
        
        .demo-info {
            background: linear-gradient(135deg, rgba(243, 156, 18, 0.1), rgba(44, 62, 80, 0.05));
            border: 1px solid var(--secondary);
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.9rem;
            color: var(--accent);
        }
        
        .demo-info strong {
            color: var(--primary);
        }
        
        .demo-info i {
            color: var(--secondary);
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card">
                    <div class="login-header">
                        <i class="fas fa-school"></i>
                        <h3>GIS Ensino Primaria<br>Timor-Leste</h3>
                        <small>🇹🇱 Ita nia rai, ita nia eskola</small>
                        <p class="mb-0 mt-2" style="font-size: 0.85rem;">Admin Portal</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                <input type="email" name="email" class="form-control" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-login w-100">
                                <i class="fas fa-sign-in-alt me-2"></i> Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>