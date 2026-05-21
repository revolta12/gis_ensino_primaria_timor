<?php
// =============================================
// Functions - GIS Ensino Primaria Timor-Leste
// =============================================

// =============================================
// SANITIZATION FUNCTIONS
// =============================================

/**
 * Sanitize input string (XSS prevention)
 * 
 * @param string $input Raw input
 * @return string Sanitized output
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize array recursively
 * 
 * @param array $array Input array
 * @return array Sanitized array
 */
function sanitizeArray($array) {
    return array_map('sanitize', $array);
}

/**
 * Create URL-friendly slug from text
 * 
 * @param string $text Input text
 * @return string URL slug
 */
function slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// =============================================
// FILE UPLOAD FUNCTIONS
// =============================================

/**
 * Upload single photo (school main photo or gallery)
 * 
 * @param array $file $_FILES array element
 * @param string $dest Upload destination folder
 * @return string|false Uploaded file path or false on failure
 */
function uploadFoto($file, $dest = 'uploads/escola/') {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if (!in_array($file['type'], $allowedTypes)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false; // 5MB max

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) return false;

    $fname = uniqid('escola_', true) . '.' . $ext;
    $fullPath = BASE_PATH . '/' . $dest;

    if (!is_dir($fullPath)) mkdir($fullPath, 0755, true);

    if (move_uploaded_file($file['tmp_name'], $fullPath . $fname)) {
        return $dest . $fname;
    }

    return false;
}

/**
 * Upload multiple gallery photos
 * 
 * @param array $files $_FILES array
 * @param int $escolaId School ID
 * @param PDO $db Database connection
 * @param string $table Table name for gallery photos
 * @return array Uploaded file paths
 */
function uploadMultipleFoto($files, $escolaId, $db, $table = 'foto_escola') {
    $uploaded = [];

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];

            $filename = uploadFoto($file);
            if ($filename) {
                $stmt = $db->prepare("INSERT INTO $table (escola_id, naran_fail, ordem) VALUES (?, ?, ?)");
                $stmt->execute([$escolaId, $filename, $i]);
                $uploaded[] = $filename;
            }
        }
    }

    return $uploaded;
}

/**
 * Update school rating based on approved reviews
 * 
 * @param int $escolaId School ID
 * @param PDO $db Database connection
 */
function updateRatingEscola($escola_id, $db) {
    $stmt = $db->prepare(
        "SELECT AVG(pontuasaun) as avg_rating, COUNT(*) as total
         FROM avaliasaun_escola
         WHERE escola_id = ? AND aprovadu = 1"
    );
    $stmt->execute([$escola_id]);
    $r = $stmt->fetch();

    $stmt2 = $db->prepare(
        "UPDATE escola SET avaliasaun = ?, total_avaliasaun = ? WHERE id = ?"
    );
    $stmt2->execute([
        round($r['avg_rating'] ?? 0, 2),
        $r['total'] ?? 0,
        $escola_id
    ]);
}

// =============================================
// UI HELPER FUNCTIONS
// =============================================

/**
 * Render star rating HTML
 * 
 * @param float $rating Rating value (0-5)
 * @return string HTML stars
 */
function renderStars($rating) {
    $stars = '';
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;

    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $fullStars) {
            $stars .= '<i class="fas fa-star text-warning"></i>';
        } elseif ($halfStar && $i == $fullStars + 1) {
            $stars .= '<i class="fas fa-star-half-alt text-warning"></i>';
        } else {
            $stars .= '<i class="far fa-star text-warning"></i>';
        }
    }

    return $stars;
}

/**
 * Get school main photo URL
 * 
 * @param int $escolaId School ID
 * @param PDO $db Database connection
 * @return string Photo URL
 */
function getEscolaFotoUtama($escolaId, $db) {
    $stmt = $db->prepare(
        "SELECT naran_fail
         FROM foto_escola
         WHERE escola_id = ?
         ORDER BY ordem ASC
         LIMIT 1"
    );
    $stmt->execute([$escolaId]);
    $foto = $stmt->fetch();

    return $foto ? BASE_URL . '/' . $foto['naran_fail'] : BASE_URL . '/assets/img/escola-placeholder.jpg';
}

/**
 * Get school facilities
 * 
 * @param int $escolaId School ID
 * @param PDO $db Database connection
 * @return array Array of facilities
 */
function getEscolaFasilidades($escolaId, $db) {
    $stmt = $db->prepare(
        "SELECT f.*
         FROM fasilidade_escola f
         INNER JOIN escola_fasilidade ef ON f.id = ef.fasilidade_id
         WHERE ef.escola_id = ?
         ORDER BY f.naran_fasilidade ASC"
    );
    $stmt->execute([$escolaId]);
    return $stmt->fetchAll();
}

/**
 * Format number with dot separator (Tetun style)
 * 
 * @param float|int $number Number to format
 * @return string Formatted number
 */
function formatNumber($number) {
    return number_format($number, 0, ',', '.');
}

/**
 * Format student count with label
 * 
 * @param int $count Number of students
 * @return string Formatted string
 */
function formatStudentCount($count) {
    return number_format($count, 0, ',', '.') . ' alunu';
}

/**
 * Format teacher count with label
 * 
 * @param int $count Number of teachers
 * @return string Formatted string
 */
function formatTeacherCount($count) {
    return number_format($count, 0, ',', '.') . ' professor';
}

/**
 * Get student-to-teacher ratio
 * 
 * @param int $students Total students
 * @param int $teachers Total teachers
 * @return string Ratio string
 */
function getStudentTeacherRatio($students, $teachers) {
    if ($teachers == 0) return 'N/A';
    $ratio = round($students / $teachers, 1);
    return $ratio . ':1';
}

/**
 * Format timestamp to human readable (Tetun)
 * 
 * @param string $timestamp MySQL timestamp
 * @return string Human readable time
 */
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) return $diff . ' segundu liuba';
    if ($diff < 3600) return floor($diff / 60) . ' minutu liuba';
    if ($diff < 86400) return floor($diff / 3600) . ' hora liuba';
    if ($diff < 2592000) return floor($diff / 86400) . ' loron liuba';
    if ($diff < 31536000) return floor($diff / 2592000) . ' fulan liuba';

    return date('d/m/Y', $time);
}

/**
 * Get facility status badge HTML
 * 
 * @param bool $status Facility status
 * @param string $label Label text
 * @return string HTML badge
 */
function getFacilityBadge($status, $label) {
    if ($status) {
        return '<span class="badge bg-success"><i class="fas fa-check-circle"></i> ' . $label . '</span>';
    }
    return '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> La iha ' . $label . '</span>';
}

// =============================================
// AUTHENTICATION FUNCTIONS
// =============================================

/**
 * Check if current user is admin
 * 
 * @return bool
 */
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['admin_id']) && 
               isset($_SESSION['admin_papel']) && 
               $_SESSION['admin_papel'] === 'admin';
    }
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['admin_id']);
    }
}
/**
 * Redirect to URL
 * 
 * @param string $url Relative URL (will be prefixed with BASE_URL)
 */
function redirect($url) {
    // Clean any output buffers
    if (ob_get_level()) {
        ob_clean();
    }

    $fullUrl = (defined('BASE_URL') ? BASE_URL : '') . $url;
    header("Location: " . $fullUrl);
    exit();
}

// =============================================
// FLASH MESSAGE FUNCTIONS
// =============================================

/**
 * Set flash message in session
 * 
 * @param string $type 'success' or 'danger'
 * @param string $message Message content
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 * 
 * @return array|null Flash message or null if none
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// =============================================
// SCHOOL SPECIFIC FUNCTIONS
// =============================================

/**
 * Calculate school completion percentage based on facilities
 * 
 * @param array $school School data array
 * @return int Percentage (0-100)
 */
function calculateSchoolCompletion($school) {
    $total = 3; // water, electricity, toilet
    $completed = 0;
    
    if ($school['iha_bee_moos']) $completed++;
    if ($school['iha_eletrisidade']) $completed++;
    if ($school['iha_toilet']) $completed++;
    
    return round(($completed / $total) * 100);
}

/**
 * Get school completion status text
 * 
 * @param int $percentage Completion percentage
 * @return string Status text
 */
function getCompletionStatus($percentage) {
    if ($percentage >= 80) return 'Kompleitu';
    if ($percentage >= 50) return 'Mediu';
    if ($percentage >= 25) return 'Di\'ak liu';
    return 'Presija ajuda';
}

/**
 * Get school priority level based on facilities
 * 
 * @param array $school School data array
 * @return string Priority level (Critical, High, Medium, Good)
 */
function getSchoolPriority($school) {
    if (!$school['iha_bee_moos']) return 'Critical';
    if (!$school['iha_eletrisidade']) return 'High';
    if (!$school['iha_toilet']) return 'Medium';
    return 'Good';
}

/**
 * Get priority badge HTML
 * 
 * @param string $priority Priority level
 * @return string HTML badge
 */
function getPriorityBadge($priority) {
    switch ($priority) {
        case 'Critical':
            return '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Urjente</span>';
        case 'High':
            return '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle"></i> Prioridade</span>';
        case 'Medium':
            return '<span class="badge bg-info"><i class="fas fa-info-circle"></i> Mediu</span>';
        default:
            return '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Di\'ak</span>';
    }
}

/**
 * Format school address for display
 * 
 * @param array $school School data array
 * @return string Formatted address
 */
function formatSchoolAddress($school) {
    $parts = [];
    if (!empty($school['suku'])) $parts[] = $school['suku'];
    if (!empty($school['postu_administrativu'])) $parts[] = $school['postu_administrativu'];
    if (!empty($school['municipio'])) $parts[] = $school['municipio'];
    
    return implode(', ', $parts);
}

/**
 * Get statistics summary for dashboard
 * 
 * @param PDO $db Database connection
 * @return array Statistics array
 */
function getSchoolStatistics($db) {
    $stats = [];
    
    // Total schools
    $stmt = $db->query("SELECT COUNT(*) FROM escola WHERE aktivo = 1");
    $stats['total'] = $stmt->fetchColumn();
    
    // Schools with clean water
    $stmt = $db->query("SELECT COUNT(*) FROM escola WHERE iha_bee_moos = 1 AND aktivo = 1");
    $stats['water'] = $stmt->fetchColumn();
    
    // Schools with electricity
    $stmt = $db->query("SELECT COUNT(*) FROM escola WHERE iha_eletrisidade = 1 AND aktivo = 1");
    $stats['electricity'] = $stmt->fetchColumn();
    
    // Schools with toilet
    $stmt = $db->query("SELECT COUNT(*) FROM escola WHERE iha_toilet = 1 AND aktivo = 1");
    $stats['toilet'] = $stmt->fetchColumn();
    
    // Total students
    $stmt = $db->query("SELECT SUM(total_estudante) FROM escola WHERE aktivo = 1");
    $stats['students'] = $stmt->fetchColumn() ?: 0;
    
    // Total teachers
    $stmt = $db->query("SELECT SUM(total_profesor) FROM escola WHERE aktivo = 1");
    $stats['teachers'] = $stmt->fetchColumn() ?: 0;
    
    return $stats;
}
?>