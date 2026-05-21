<?php
// =============================================
// Admin Header - GIS Ensino Primaria Timor-Leste
// =============================================

// Start session ONLY if not started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/login.php');
    exit();
}

// Database connection for notification badges
require_once dirname(dirname(__DIR__)) . '/config/database.php';
$db = getDB();

// Get notification counts for badges
$stmt = $db->query("SELECT COUNT(*) as total FROM avaliasaun_escola WHERE aprovadu = 0");
$pending_review_count = (int)($stmt->fetch()['total'] ?? 0);

$stmt = $db->query("SELECT COUNT(*) as total FROM kontaktu_mensajen WHERE lee_ona = 0");
$unread_message_count = (int)($stmt->fetch()['total'] ?? 0);

// Get total schools count (optional for sidebar)
$stmt = $db->query("SELECT COUNT(*) as total FROM escola WHERE aktivo = 1");
$total_schools = (int)($stmt->fetch()['total'] ?? 0);

// Page data (optional override from page)
$page_title = $page_title ?? 'Dashboard';
$page_icon = $page_icon ?? 'fa-school';

// Get current page for active menu
$current_uri = $_SERVER['REQUEST_URI'];

// Function to check if menu is active
function isActiveMenu($paths) {
    global $current_uri;
    foreach ((array)$paths as $path) {
        if (strpos($current_uri, $path) !== false) {
            return 'active';
        }
    }
    return '';
}

// Get admin name for greeting
$admin_name = $_SESSION['admin_naran'] ?? 'Administrador';
$greeting = '';
$hour = date('H');
if ($hour < 12) $greeting = 'Bondia';
elseif ($hour < 18) $greeting = 'Botarde';
else $greeting = 'Bonoite';

// Pastikan tidak ada output sebelum HTML
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Admin Panel - GIS Ensino Primaria Timor-Leste</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Page custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin-education.css">

    <style>
        :root {
            --primary-blue: #2C3E50;
            --primary-blue-dark: #1A252F;
            --secondary-gold: #F39C12;
            --secondary-gold-dark: #E67E22;
            --success-green: #27AE60;
            --danger-red: #E74C3C;
            --info-blue: #3498DB;
            --dark-bg: #1A1A2E;
            --light-bg: #f5f6fa;
            --text-dark: #2C3E50;
            --text-light: #7F8C8D;
            --white: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--light-bg);
            display: flex;
            overflow-x: hidden;
        }

        /* ============================================= */
        /* SIDEBAR */
        /* ============================================= */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
            border-right: 4px solid var(--secondary-gold);
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 30px 20px;
            background: linear-gradient(135deg, var(--primary-blue-dark) 0%, var(--dark-bg) 100%);
            text-align: center;
            border-bottom: 3px solid var(--secondary-gold);
            position: relative;
            overflow: hidden;
        }

        .sidebar-header::before {
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
            pointer-events: none;
        }

        .sidebar-header i {
            font-size: 2.8rem;
            color: var(--secondary-gold);
            display: block;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        .sidebar-header h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            position: relative;
            z-index: 2;
            line-height: 1.3;
        }

        .sidebar-header small {
            color: var(--secondary-gold);
            font-size: 0.7rem;
            font-weight: 700;
            display: block;
            margin-top: 8px;
            position: relative;
            z-index: 2;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            font-weight: 600;
            gap: 12px;
        }

        .sidebar-menu a i {
            width: 24px;
            text-align: center;
            font-size: 1.1rem;
        }

        .sidebar-menu a:hover {
            background: rgba(243, 156, 18, 0.15);
            border-left-color: var(--secondary-gold);
            color: var(--secondary-gold);
            padding-left: 30px;
        }

        .sidebar-menu a.active {
            background: linear-gradient(90deg, var(--primary-blue-dark), rgba(44, 62, 80, 0.5));
            border-left-color: var(--secondary-gold);
            color: var(--secondary-gold);
        }

        .sidebar-menu .badge-notif {
            margin-left: auto;
            background: var(--danger-red);
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            min-width: 22px;
            text-align: center;
        }

        .sidebar-menu hr {
            margin: 15px 20px;
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* ============================================= */
        /* MAIN CONTENT */
        /* ============================================= */
        .main-content {
            margin-left: 280px;
            padding: 20px;
            flex: 1;
            min-height: 100vh;
            width: calc(100% - 280px);
        }

        /* ============================================= */
        /* TOP NAVBAR */
        /* ============================================= */
        .top-navbar {
            background: var(--white);
            padding: 15px 25px;
            border-radius: 16px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border-left: 5px solid var(--secondary-gold);
        }

        .page-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-title i {
            color: var(--secondary-gold);
            background: rgba(243, 156, 18, 0.1);
            padding: 10px;
            border-radius: 12px;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #f8f9fa;
            padding: 6px 20px 6px 15px;
            border-radius: 50px;
        }

        .admin-avatar {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary-gold);
            font-size: 1.2rem;
            border: 2px solid var(--secondary-gold);
        }

        .admin-name {
            font-weight: 800;
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .admin-greeting {
            font-size: 0.7rem;
            color: var(--text-light);
            margin-top: 2px;
        }

        /* ============================================= */
        /* MOBILE RESPONSIVE */
        /* ============================================= */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }
            .menu-toggle {
                display: flex;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 999;
                background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-dark));
                color: var(--secondary-gold);
                border: none;
                width: 45px;
                height: 45px;
                border-radius: 12px;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                cursor: pointer;
                box-shadow: 0 4px 15px rgba(44, 62, 80, 0.4);
            }
            .top-navbar {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                margin-top: 50px;
            }
            .page-title {
                font-size: 1.1rem;
            }
        }

        @media (min-width: 769px) {
            .menu-toggle {
                display: none;
            }
        }
        
        /* Scrollbar styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: var(--primary-blue-dark);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--secondary-gold);
            border-radius: 3px;
        }
    </style>
</head>
<body>

<!-- Mobile Menu Toggle -->
<button class="menu-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-school"></i>
        <h4>GIS Ensino Primaria<br>Timor-Leste</h4>
        <small>🇹🇱 Sistema Jestaun Eskola</small>
    </div>

    <div class="sidebar-menu">
        <a href="<?= BASE_URL ?>/admin/index.php" class="<?= isActiveMenu('/admin/index.php') ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="<?= BASE_URL ?>/admin/escola/list.php" class="<?= isActiveMenu('/admin/escola/') ?>">
            <i class="fas fa-school"></i> Escola
            <?php if ($total_schools > 0): ?>
                <span class="badge-notif" style="background: var(--success-green);"><?= $total_schools ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>/admin/kategoria/list.php" class="<?= isActiveMenu('/admin/kategoria/') ?>">
            <i class="fas fa-tags"></i> Kategoria
        </a>
        <a href="<?= BASE_URL ?>/admin/fasilidade/list.php" class="<?= isActiveMenu('/admin/fasilidade/') ?>">
            <i class="fas fa-hand-holding-heart"></i> Fasilidade
        </a>
        <a href="<?= BASE_URL ?>/admin/avaliasaun/list.php" class="<?= isActiveMenu('/admin/avaliasaun/') ?>">
            <i class="fas fa-star"></i> Avaliasaun
            <?php if ($pending_review_count > 0): ?>
                <span class="badge-notif"><?= $pending_review_count ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>/admin/mensajen/list.php" class="<?= isActiveMenu('/admin/mensajen/') ?>">
            <i class="fas fa-envelope"></i> Mensajen
            <?php if ($unread_message_count > 0): ?>
                <span class="badge-notif"><?= $unread_message_count ?></span>
            <?php endif; ?>
        </a>

        <hr>

        <a href="<?= BASE_URL ?>/index.php" target="_blank">
            <i class="fas fa-globe"></i> Website Públiku
        </a>
        <a href="<?= BASE_URL ?>/admin/logout.php">
            <i class="fas fa-sign-out-alt"></i> Sai
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="top-navbar">
        <h5 class="page-title">
            <i class="fas <?= $page_icon ?>"></i>
            <?= $page_title ?>
        </h5>
        <div class="admin-info">
            <div class="admin-avatar">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <div class="admin-name"><?= htmlspecialchars($admin_name) ?></div>
                <div class="admin-greeting">
                    <i class="fas fa-sun"></i> <?= $greeting ?>, Benvindu!
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.menu-toggle');
            
            if (window.innerWidth <= 768) {
                if (sidebar && toggle && !sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });

        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    const sidebar = document.getElementById('sidebar');
                    if (sidebar) sidebar.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>