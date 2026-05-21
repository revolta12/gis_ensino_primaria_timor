<?php
// =============================================
// CSRF Protection - GIS Ensino Primaria Timor-Leste
// =============================================

/**
 * Generate CSRF token
 * 
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token from form
 * @return bool
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    $valid = hash_equals($_SESSION['csrf_token'], $token);
    
    // Regenerate token after verification (for security)
    if ($valid) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $valid;
}

/**
 * Get CSRF token field HTML
 * 
 * @return string
 */
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Verify CSRF token from POST request
 * 
 * @return bool
 */
function verifyCSRF() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true; // Only check for POST requests
    }
    
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    return verifyCSRFToken($token);
}

/**
 * Check CSRF and return JSON error if fails (for AJAX)
 * 
 * @return void
 */
function requireValidCSRF() {
    if (!verifyCSRF()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Token seguransa invalidu. Favor refresh página no tenta fali.'
        ]);
        exit();
    }
}

/**
 * Generate double submit cookie token (for AJAX requests)
 * 
 * @return string
 */
function generateDoubleSubmitToken() {
    $token = bin2hex(random_bytes(32));
    setcookie('XSRF-TOKEN', $token, 0, '/', '', false, true);
    $_SESSION['xsrf_token'] = $token;
    return $token;
}

/**
 * Verify double submit cookie token
 * 
 * @param string $token Token from request header
 * @return bool
 */
function verifyDoubleSubmitToken($token) {
    if (!isset($_SESSION['xsrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['xsrf_token'], $token);
}

/**
 * Get CSRF meta tag for JavaScript/AJAX
 * 
 * @return string
 */
function csrfMetaTag() {
    $token = generateCSRFToken();
    return '<meta name="csrf-token" content="' . $token . '">';
}