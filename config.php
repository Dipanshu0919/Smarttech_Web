<?php
// Load private secrets file if available
$secretsFile = __DIR__ . '/secrets.php';
$secrets = file_exists($secretsFile) ? (require $secretsFile) : [];

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: ($secrets['DB_HOST'] ?? 'localhost'));
define('DB_NAME', getenv('DB_NAME') ?: ($secrets['DB_NAME'] ?? 'smartec2_smartechweb'));
define('DB_USER', getenv('DB_USER') ?: ($secrets['DB_USER'] ?? 'smartbqc_abitghar'));
define('DB_PASS', getenv('DB_PASS') ?: ($secrets['DB_PASS'] ?? ''));

// Application Configuration
define('APP_SECRET_KEY', getenv('APP_SECRET_KEY') ?: ($secrets['APP_SECRET_KEY'] ?? 'secretkey'));
define('ADMIN_MGMT_USERNAME', getenv('ADMIN_MGMT_USERNAME') ?: ($secrets['ADMIN_MGMT_USERNAME'] ?? 'admin'));
define('ADMIN_MGMT_PASSWORD', getenv('ADMIN_MGMT_PASSWORD') ?: ($secrets['ADMIN_MGMT_PASSWORD'] ?? ''));

// Groq API Configuration
define('GROQ_API_KEY', getenv('GROQ_API_KEY') ?: ($secrets['GROQ_API_KEY'] ?? ''));

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
                keywords TEXT DEFAULT '',
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

        try {
            $cols = $pdo->query("PRAGMA table_info(blogs)")->fetchAll();
            $colNames = array_column($cols, 'name');
            if (!in_array('keywords', $colNames)) {
                $pdo->exec("ALTER TABLE blogs ADD COLUMN keywords TEXT DEFAULT ''");
            }
        } catch (Exception $e) {}
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
                keywords TEXT,
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
        $sqlitePath = BASE_DIR . '/database.sqlite';
        if (extension_loaded('pdo_sqlite')) {
            try {
                $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (Exception $e) {
                require_once BASE_DIR . '/sqlite-adapter.php';
                $pdo = new SqliteCliPDO($sqlitePath);
            }
        } else {
            require_once BASE_DIR . '/sqlite-adapter.php';
            $pdo = new SqliteCliPDO($sqlitePath);
        }
        initDatabaseTables($pdo, true);
    }
    return $pdo;
}