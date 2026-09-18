<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'smartec2_smartechweb');
define('DB_USER', 'smartbqc_abitghar');
define('DB_PASS', 'Bunty@123');

// Application Configuration
define('APP_SECRET_KEY', 'secretkey');
define('ADMIN_MGMT_USERNAME', 'admin');
define('ADMIN_MGMT_PASSWORD', 'admin123');

// Groq API Configuration
define('GROQ_API_KEY', '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');

// Base paths
define('BASE_DIR', __DIR__);
define('BASE_URL', '/');
define('ORDER_FILES_DIR', BASE_DIR . '/orderformfiles');

// Session lifetime (31 days)
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 31);
session_start();

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }
    return $pdo;
}