<?php
// =============================================
// Authentication - GIS Ensino Primaria Timor-Leste
// =============================================

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check admin login and redirect if not logged in
 * 
 * @return void
 */
function checkAdminLogin() {
    // If already logged in, allow access
    if (isset($_SESSION['admin_id'])) {
        return true;
    }
    
    // Not logged in, redirect to login
    $_SESSION['error'] = 'Favor login uluk atu asesu pájina ne\'e.';
    
    // Use relative path to avoid loop
    header('Location: login.php');
    exit();
}

/**
 * Check if user has admin role (die if not)
 * 
 * @return void
 */
function requireAdmin() {
    if (!isset($_SESSION['admin_papel']) || $_SESSION['admin_papel'] !== 'admin') {
        http_response_code(403);
        die('Acesso negadu. Ita boot seidauk iha permissaun atu asesu pájina ne\'e.');
    }
}

/**
 * Login user
 * 
 * @param int $userId User ID
 * @param string $naran User name
 * @param string $email User email
 * @param string $papel User role (admin/staff)
 * @return void
 */
function loginUser($userId, $naran, $email, $papel) {
    $_SESSION['admin_id'] = $userId;
    $_SESSION['admin_naran'] = $naran;
    $_SESSION['admin_email'] = $email;
    $_SESSION['admin_papel'] = $papel;
    $_SESSION['login_time'] = time();
}

/**
 * Logout user
 * 
 * @return void
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Get current logged in user data
 * 
 * @param PDO $db Database connection
 * @return array|null User data or null if not logged in
 */
function getCurrentUser($db) {
    if (!isset($_SESSION['admin_id'])) {
        return null;
    }
    
    $stmt = $db->prepare("SELECT id, naran, email, papel, aktivo FROM utilizador WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

/**
 * Check if session timeout (optional - 8 hours)
 * 
 * @param int $timeout Timeout in seconds (default 8 hours)
 * @return bool
 */
function isSessionTimeout($timeout = 28800) {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > $timeout) {
            return true;
        }
    }
    return false;
}

/**
 * Refresh session timeout
 * 
 * @return void
 */
function refreshSessionTimeout() {
    $_SESSION['login_time'] = time();
}

/**
 * Generate remember token (for "remember me" feature)
 * 
 * @return string
 */
function generateRememberToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Verify password strength
 * 
 * @param string $password
 * @return array ['valid' => bool, 'message' => string]
 */
function verifyPasswordStrength($password) {
    if (strlen($password) < 6) {
        return ['valid' => false, 'message' => 'Password tenki iha karakter 6 liu.'];
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'message' => 'Password tenki iha letra boot ida.'];
    }
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'message' => 'Password tenki iha letra ki\'ik ida.'];
    }
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'message' => 'Password tenki iha numeru ida.'];
    }
    return ['valid' => true, 'message' => 'Password forte.'];
}

/**
 * Get user role badge HTML
 * 
 * @param string $role
 * @return string
 */
function getUserRoleBadge($role) {
    if ($role === 'admin') {
        return '<span class="badge bg-danger"><i class="fas fa-user-shield"></i> Admin</span>';
    }
    return '<span class="badge bg-secondary"><i class="fas fa-user"></i> Staff</span>';
}
?>