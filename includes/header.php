<?php
// =============================================
// Public Header - GIS Ensino Primaria Timor-Leste
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/functions.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="GIS Ensino Primaria Timor-Leste - Sistema Informasaun Geografiku ba Eskola Primaria iha Timor-Leste">
    <meta name="keywords" content="eskola primaria, GIS, Timor-Leste, mapa eskola, edukasaun">
    <meta name="author" content="Ministério da Educação Timor-Leste">
    <title>GIS Ensino Primaria Timor-Leste | Ita nia rai, ita nia eskola</title>
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/map.css">
    
    <style>
        /* ============================================ */
        /* TIMOR-LESTE EDUCATION THEME COLORS */
        /* ============================================ */
        :root {
            --primary-blue: #1F5A7A;
            --primary-blue-dark: #0D3B52;
            --secondary-gold: #F39C12;
            --secondary-gold-dark: #D68910;
            --accent-teal: #1ABC9C;
            --success-green: #27AE60;
            --danger-red: #E74C3C;
            --info-blue: #3498DB;
            --dark-bg: #0F1419;
            --light-bg: #F5F7FA;
            --text-dark: #1F2937;
            --text-light: #6B7280;
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
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* ============================================ */
        /* NAVBAR STYLES */
        /* ============================================ */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 12px 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }
        
        .navbar.scrolled {
            padding: 8px 0;
            background: white;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        }
        
        .navbar-brand {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary-blue) !important;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            color: var(--accent-teal) !important;
        }
        
        .navbar-brand i {
            color: var(--secondary-gold);
            font-size: 1.5rem;
        }
        
        .navbar-brand span {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .navbar-brand small {
            font-size: 0.6rem;
            font-weight: 500;
            color: var(--text-light);
            margin-left: 2px;
        }
        
        /* Navbar Links */
        .nav-link {
            font-weight: 600;
            margin: 0 6px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--text-dark) !important;
            position: relative;
            padding: 8px 14px !important;
            border-radius: var(--radius-md);
        }
        
        .nav-link i {
            margin-right: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--secondary-gold) !important;
            background: rgba(243, 156, 18, 0.1);
            transform: translateY(-2px);
        }
        
        .nav-link.active {
            color: var(--accent-teal) !important;
            background: rgba(26, 188, 156, 0.12);
            border-bottom: 2px solid var(--accent-teal);
        }
        
        /* Admin Badge */
        .admin-badge {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-teal));
            color: white !important;
            border-radius: 30px;
            padding: 6px 18px !important;
            margin-left: 10px;
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.3);
        }
        
        .admin-badge:hover {
            background: linear-gradient(135deg, var(--primary-blue-dark), #0f1a24);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 62, 80, 0.4);
            color: white !important;
        }
        
        .admin-badge i {
            margin-right: 6px;
        }
        
        /* Logout Link */
        .nav-link:last-child {
            color: #999 !important;
        }
        
        .nav-link:last-child:hover {
            color: var(--danger-red) !important;
        }
        
        /* Navbar Toggler */
        .navbar-toggler {
            border: none;
            padding: 8px 12px;
            border-radius: 10px;
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.25);
            outline: none;
        }
        
        /* ============================================ */
        /* RESPONSIVE */
        /* ============================================ */
        @media (max-width: 991px) {
            .navbar-nav {
                padding: 20px 0 10px;
            }
            .nav-link {
                padding: 12px 15px !important;
                text-align: center;
                margin: 2px 0;
            }
            .nav-link i {
                width: 25px;
            }
            .admin-badge {
                display: inline-block;
                width: auto;
                margin: 10px auto 0;
            }
            .navbar-nav {
                align-items: center;
            }
        }
        
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.2rem;
            }
            .navbar-brand i {
                font-size: 1.3rem;
            }
        }
        
        /* ============================================ */
        /* UTILITY CLASSES */
        /* ============================================ */
        .text-primary-blue {
            color: var(--primary-blue);
        }
        .bg-primary-blue {
            background: var(--primary-blue);
        }
        .text-secondary-gold {
            color: var(--secondary-gold);
        }
        .bg-secondary-gold {
            background: var(--secondary-gold);
        }
        
        /* Dropdown menu for user (if needed) */
        .dropdown-menu {
            border-radius: 12px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .dropdown-item {
            padding: 8px 20px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background: rgba(243, 156, 18, 0.1);
            color: var(--secondary-gold);
        }
    </style>
</head>
<body>

<!-- Navbar Public -->
<nav class="navbar navbar-expand-lg fixed-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
            <i class="fas fa-school"></i>
            <div>
                <span>GIS Ensino Primaria</span>
                <small>Timor-Leste</small>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">
                        <i class="fas fa-home"></i> Varanda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'escola.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/public/escola.php">
                        <i class="fas fa-school"></i> Eskola
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'mapa.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/public/mapa.php">
                        <i class="fas fa-map"></i> Mapa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'kontaktu.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/public/kontaktu.php">
                        <i class="fas fa-envelope"></i> Kontaktu
                    </a>
                </li>
                <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                <li class="nav-item">
                    <a class="nav-link admin-badge" href="<?= BASE_URL ?>/admin/">
                        <i class="fas fa-user-shield"></i> Admin
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/admin/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Sai
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Spacer for fixed navbar -->
<div style="height: 80px;"></div>

<script>
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('mainNav');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Active link highlight based on current page
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && href !== '#' && href !== '') {
                // Check if current path ends with the link href
                if (currentPath === href || 
                    (href !== '/index.php' && currentPath.includes(href)) ||
                    (href === '/index.php' && (currentPath === '/' || currentPath === '/index.php'))) {
                    link.classList.add('active');
                }
            }
        });
    });
</script>