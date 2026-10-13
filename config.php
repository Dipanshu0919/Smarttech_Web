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
define('BLOG_IMAGES_DIR', BASE_DIR . '/assets/blog_images');

// Session lifetime (31 days)
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 31);
session_start();

function initDatabaseTables($pdo, $is_sqlite = false) {
    if ($is_sqlite) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE,
                password TEXT,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                representative_name TEXT,
                order_for TEXT,
                phone TEXT,
                email TEXT,
                date TEXT,
                remark TEXT,
                filename TEXT,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS enquiries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                email TEXT,
                phone TEXT,
                message TEXT,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS blogs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                meta_description TEXT,
                author TEXT DEFAULT 'SmarTech Team',
                category TEXT DEFAULT 'Technology',
                featured_image TEXT,
                content TEXT NOT NULL,
                images_json TEXT,
                status TEXT DEFAULT 'published',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
    } else {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) UNIQUE,
                password VARCHAR(255),
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                representative_name VARCHAR(255),
                order_for VARCHAR(255),
                phone VARCHAR(50),
                email VARCHAR(255),
                date VARCHAR(50),
                remark TEXT,
                filename VARCHAR(255),
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS enquiries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255),
                email VARCHAR(255),
                phone VARCHAR(50),
                message TEXT,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS blogs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                meta_description TEXT,
                author VARCHAR(100) DEFAULT 'SmarTech Team',
                category VARCHAR(100) DEFAULT 'Technology',
                featured_image VARCHAR(255),
                content LONGTEXT NOT NULL,
                images_json TEXT,
                status VARCHAR(50) DEFAULT 'published',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );
        ");
    }
}

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            initDatabaseTables($pdo, false);
        } catch (Exception $e) {
            require_once BASE_DIR . '/database-adapter.php';
            $pdo = new JsonPDO(BASE_DIR . '/database.json');
        }
    }
    return $pdo;
}