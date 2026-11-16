<?php
// Pearl Creamery — configuration, database connection and shared helpers.
// Local (XAMPP) vs live (InfinityFree) is detected automatically from the hostname.

$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal = PHP_SAPI === 'cli'
        || $host === '' || $host === 'localhost' || str_starts_with($host, 'localhost:')
        || $host === '127.0.0.1'   || str_starts_with($host, '127.0.0.1:');

// ---------- Database ----------
if ($isLocal) {
    // XAMPP defaults
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', '3306');
    define('DB_NAME', 'pearl_creamery');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // InfinityFree — copy these from hPanel > MySQL Databases
    define('DB_HOST', 'sql206.infinityfree.com');
    define('DB_PORT', '3306');
    define('DB_NAME', 'if0_43175797_creamery');
    define('DB_USER', 'if0_43175797');
    define('DB_PASS', 'ysCfGaqaAXP7z'); // <- type your hosting account password
}

// ---------- Store ----------
define('CONTACT_PHONE', '+256 780 457866');
define('WHATSAPP_NUMBER', '256780457866');
define('INSTAGRAM_URL', getenv('PC_INSTAGRAM') ?: 'https://www.instagram.com/pearlcreamery');
define('ADMIN_EMAIL', getenv('PC_ADMIN_EMAIL') ?: 'orders@example.com');

// ---------- Weather ----------
// Open-Meteo needs no key. Paste an OpenWeatherMap key to use that service instead.
define('DEFAULT_LAT', 0.3476);   // Kampala fallback
define('DEFAULT_LNG', 32.5825);
define('WEATHER_API_KEY', getenv('WEATHER_API_KEY') ?: '');

// ---------- Sessions ----------
if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
    session_start();
}

// ---------- Shared helpers ----------
// Guarded with function_exists so older files (db.php, includes/auth.php)
// that also declare helpers never cause a "Cannot redeclare" fatal.

if (!function_exists('db')) {
    function db(): PDO {
        static $pdo = null;
        if ($pdo === null) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return $pdo;
    }
}

if (!function_exists('json_out')) {
    function json_out($data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
