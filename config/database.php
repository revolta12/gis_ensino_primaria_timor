<?php
// =============================================
// Database Configuration - GIS Ensino Primaria Timor-Leste
// =============================================
// 
// Konfigurasaun koneksaun ba database
// Ajusta tuir ambiente ita boot nia (local / server)
//

// =============================================
// DATABASE CONNECTION SETTINGS
// =============================================

// Database host (normalmente 'localhost' ka '127.0.0.1')
define('DB_HOST', 'localhost');

// Nome database - MUDAR BA foun 'gis_ensino_primaria_timor'
define('DB_NAME', 'gis_ensino_primaria_timor');

// Database user (default 'root' ba local)
define('DB_USER', 'root');

// Database password (deixa baze ba local)
define('DB_PASS', '');

// Database port (MySQL padraun 3306, se uza XAMPP/MySQL port 3307)
define('DB_PORT', 3306); // MUDAR ba 3306 ka tuir ita boot nia konfigurasaun

// Database charset (UTF-8 suporta karakteres Timór nian)
define('DB_CHARSET', 'utf8mb4');

// =============================================
// APPLICATION URL SETTINGS
// =============================================

// Base URL - SESUAIKA HO ENVIRONMENT ITA BOOT NIAN
// Ezemplu:
// - Local: '/gis_ensino_primaria_timor'
// - Server: 'https://domain.com'
define('BASE_URL', '/gis_ensino_primaria_timor');

// Base path (absoluto ba direktori projetu)
define('BASE_PATH', dirname(__DIR__));

// =============================================
// DATABASE CONNECTION FUNCTION
// =============================================

/**
 * Get database connection (PDO)
 * Uza singleton pattern atu evita multiple connections
 * 
 * @return PDO Database connection object
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . 
                   ";port=" . DB_PORT . 
                   ";dbname=" . DB_NAME . 
                   ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
                PDO::ATTR_TIMEOUT => 5, // 5 seconds timeout
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log error untuk debugging (jangan ditampilkan ke user di production)
            error_log("Database connection failed: " . $e->getMessage());
            
            // Tampilkan message yang user-friendly
            die("Koneksaun database falha. Favor verifika konfigurasaun database iha 'config/database.php'.<br>
                <small>Error: " . $e->getMessage() . "</small>");
        }
    }
    return $pdo;
}

// =============================================
// CHECK CONNECTION ON LOAD (optional)
// =============================================
// Test connection immediately to catch errors early
try {
    $db = getDB();
    // Simple query to test connection
    $db->query("SELECT 1");
} catch (PDOException $e) {
    die("Database konfigurasaun la los. Favor verifika DB_NAME, DB_USER, DB_PASS, no DB_PORT.<br>
        <small>Error: " . $e->getMessage() . "</small>");
}

// =============================================
// HELPER FUNCTIONS
// =============================================

/**
 * Get database name (for debugging/info)
 * 
 * @return string Database name
 */
function getDatabaseName() {
    return DB_NAME;
}

/**
 * Check if we're in development environment
 * Bazeia ba hostname ka BASE_URL
 * 
 * @return bool
 */
function isDevelopment() {
    $hostname = $_SERVER['HTTP_HOST'] ?? '';
    return (strpos($hostname, 'localhost') !== false || 
            strpos($hostname, '127.0.0.1') !== false ||
            strpos(BASE_URL, 'localhost') !== false);
}

/**
 * Get environment info (for debugging)
 * 
 * @return array
 */
function getEnvironmentInfo() {
    return [
        'database' => DB_NAME,
        'host' => DB_HOST . ':' . DB_PORT,
        'base_url' => BASE_URL,
        'environment' => isDevelopment() ? 'Development' : 'Production',
        'php_version' => PHP_VERSION,
    ];
}
?>