<?php
require_once 'config.php';

if (!is_dir(ORDER_FILES_DIR)) {
    mkdir(ORDER_FILES_DIR, 0777, true);
}

function adminRequired() {
    if (!dashboard_session_is_valid()) {
        header('Location: dashboard.php?error=login_required');
        exit;
    }
}

function superadminRequired() {
    if (!isset($_SESSION['superadmin_access']) || $_SESSION['superadmin_access'] !== true) {
        header('Location: admin-management.html?error=login_required');
        exit;
    }
}

function dashboard_session_is_valid() {
    $username = $_SESSION['dashboard_username'] ?? null;
    if (!$username) {
        unset($_SESSION['dashboard_admin']);
        return false;
    }

    if ($username === ADMIN_MGMT_USERNAME) {
        $_SESSION['dashboard_admin'] = true;
        return true;
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['dashboard_admin'] = true;
        return true;
    }

    unset($_SESSION['dashboard_admin']);
    unset($_SESSION['dashboard_username']);
    return false;
}

function authenticate_admin($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT username, password FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if (!$user) return false;
    return $user['password'] === $password;
}

function fetch_rows($table_name) {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM {$table_name} ORDER BY timestamp DESC, id DESC");
    return $stmt->fetchAll();
}

function fetch_users() {
    $db = getDB();
    $stmt = $db->query("SELECT id, username, password, timestamp FROM users ORDER BY timestamp DESC, id DESC");
    return $stmt->fetchAll();
}

function add_user($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $password]);
}

function delete_user($user_id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
}

function delete_row($table_name, $row_id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM {$table_name} WHERE id = ?");
    $stmt->execute([$row_id]);
}

function enquiry_insert($name, $email, $phone, $message) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO enquiries (name, email, phone, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $message]);
}

function order_insert($representative_name, $order_for, $phone, $email, $date, $remark, $filename = null) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO orders (representative_name, order_for, phone, email, date, remark, filename) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$representative_name, $order_for, $phone, $email, $date, $remark, $filename]);
}

function get_order_file_path($order_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, filename FROM orders WHERE id = ? LIMIT 1");
    $stmt->execute([$order_id]);
    return $stmt->fetch();
}

function save_order_attachment($uploaded_file) {
    if ($uploaded_file === null || empty($uploaded_file['name'])) {
        return null;
    }
    $original_name = basename($uploaded_file['name']);
    if (!$original_name) return null;
    $unique_name = bin2hex(random_bytes(16)) . '_' . $original_name;
    $stored_path = ORDER_FILES_DIR . '/' . $unique_name;
    move_uploaded_file($uploaded_file['tmp_name'], $stored_path);
    return 'orderformfiles/' . $unique_name;
}

function send_json($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? parse_url($request_uri, PHP_URL_PATH);

// Normalize path - remove /smart/ prefix if present and redirect index.php to /
if (strpos($path, '/smart/') === 0) {
    $path = substr($path, 6);
} elseif ($path === '/smart' || $path === '/smart/') {
    $path = '/';
}

// Ensure path starts with /
if ($path !== '' && strpos($path, '/') !== 0) {
    $path = '/' . $path;
}

// If path is empty after normalization, treat as /
if ($path === '') {
    $path = '/';
}

// Special case: index.php requests
if (basename($path) === 'index.php' || $path === '/index.php') {
    $path = '/';
}

if ($path === '/api/chat' && $request_method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_input = $input['messages'] ?? [];
    $txt = file_exists('content.txt') ? file_get_contents('content.txt') : '';
    
    $msgs = [];
    foreach ($user_input as $x) {
        if (!empty($x['content'])) {
            $msgs[] = ['role' => $x['role'] ?? 'user', 'content' => $x['content']];
        }
    }
    
    $system_msg = [
        'role' => 'system',
        'content' => $txt . "\n\nAnswer the user using the company details above. Format the reply cleanly using markdown with bold headings and bullet points for readability. Keep it formal, helpful, and accurate."
    ];
    
    $models = ['qwen/qwen3.8-27b', 'groq/compound-mini'];
    $reply = null;

    foreach ($models as $model_name) {
        $data = [
            'model' => $model_name,
            'messages' => array_merge([$system_msg], $msgs),
            'temperature' => 0.7,
            'max_completion_tokens' => 1024,
            'top_p' => 1,
            'stream' => false
        ];
        
        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response && $http_code === 200) {
            $result = json_decode($response, true);
            if (!empty($result['choices'][0]['message']['content'])) {
                $reply = trim($result['choices'][0]['message']['content']);
                break;
            }
        }
    }
    
    if (empty($reply)) {
        $reply = 'Thank you for reaching out to SmarTech Solutions! We are currently experiencing high request volume. Please reach us directly at +91 9221204466 / 7506513784 or smartech.009@gmail.com.';
    }
    
    send_json(['reply' => $reply]);
}

if ($path === '/api/enquiry' && $request_method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $phone = $data['mobile'] ?? '';
    $message = $data['message'] ?? '';
    
    if (!$name || !$email || !$phone || !$message) {
        send_json(['error' => 'All fields are required.'], 400);
    }
    
    enquiry_insert($name, $email, $phone, $message);
    send_json(['message' => 'Enquiry submitted successfully.']);
}

if ($path === '/api/order' && $request_method === 'POST') {
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($content_type, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $representative_name = $data['representative_name'] ?? '';
        $order_for = $data['order_for'] ?? '';
        $phone = $data['phone'] ?? '';
        $email = $data['email'] ?? '';
        $date = $data['date'] ?? '';
        $remark = $data['remark'] ?? '';
        $filename = null;
    } else {
        $representative_name = $_POST['representative_name'] ?? '';
        $order_for = $_POST['order_for'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $date = $_POST['date'] ?? '';
        $remark = $_POST['remark'] ?? '';
        $filename = save_order_attachment($_FILES['attachment'] ?? null);
    }
    
    if (!$representative_name || !$order_for || !$phone || !$email || !$date || !$remark) {
        send_json(['error' => 'All fields are required.'], 400);
    }
    
    order_insert($representative_name, $order_for, $phone, $email, $date, $remark, $filename);
    send_json(['message' => 'Order submitted successfully.']);
}

if ($path === '/api/dashboard/orders' && $request_method === 'GET') {
    adminRequired();
    send_json(['orders' => fetch_rows('orders')]);
}

if ($path === '/api/dashboard/enquiries' && $request_method === 'GET') {
    adminRequired();
    send_json(['enquiries' => fetch_rows('enquiries')]);
}

if (preg_match('#^/dashboard/orders/(\d+)/delete$#', $path, $m) && $request_method === 'POST') {
    adminRequired();
    $order_id = $m[1];
    $row = get_order_file_path($order_id);
    if ($row && $row['filename']) {
        $file_path = BASE_DIR . '/' . $row['filename'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    delete_row('orders', $order_id);
    header('Location: dashboard.php');
    exit;
}

if (preg_match('#^/dashboard/enquiries/(\d+)/delete$#', $path, $m) && $request_method === 'POST') {
    adminRequired();
    delete_row('enquiries', $m[1]);
    header('Location: dashboard.php');
    exit;
}

if (preg_match('#^/dashboard/orders/(\d+)/file$#', $path, $m) && $request_method === 'GET') {
    adminRequired();
    $order_id = $m[1];
    $row = get_order_file_path($order_id);
    if (!$row || !$row['filename']) {
        http_response_code(404);
        exit;
    }
    $file_path = BASE_DIR . '/' . $row['filename'];
    if (!file_exists($file_path)) {
        http_response_code(404);
        exit;
    }
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit;
}

if ($path === '/dashboard.php' && ($_GET['action'] ?? '') === 'order_file') {
    $order_id = $_GET['id'] ?? 0;
    if (!$order_id) {
        http_response_code(404);
        exit;
    }
    adminRequired();
    $row = get_order_file_path($order_id);
    if (!$row || !$row['filename']) {
        http_response_code(404);
        exit;
    }
    $file_path = BASE_DIR . '/' . $row['filename'];
    if (!file_exists($file_path)) {
        http_response_code(404);
        exit;
    }
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit;
}

if ($path === '/dashboard.php' && ($_GET['action'] ?? '') === 'delete_order') {
    $order_id = $_GET['id'] ?? 0;
    if ($order_id) {
        adminRequired();
        $row = get_order_file_path($order_id);
        if ($row && $row['filename']) {
            $file_path = BASE_DIR . '/' . $row['filename'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        delete_row('orders', $order_id);
    }
    header('Location: dashboard.php');
    exit;
}

if ($path === '/dashboard.php' && ($_GET['action'] ?? '') === 'delete_enquiry') {
    $enquiry_id = $_GET['id'] ?? 0;
    if ($enquiry_id) {
        adminRequired();
        delete_row('enquiries', $enquiry_id);
    }
    header('Location: dashboard.php');
    exit;
}

if ($path === '/dashboard.php' && $request_method === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $is_superadmin = ($username === ADMIN_MGMT_USERNAME && $password === ADMIN_MGMT_PASSWORD);
    
    if ($username && $password && ($is_superadmin || authenticate_admin($username, $password))) {
        $_SESSION['dashboard_admin'] = true;
        $_SESSION['dashboard_username'] = $username;
        if ($is_superadmin || $username === ADMIN_MGMT_USERNAME) {
            $_SESSION['superadmin_access'] = true;
            $_SESSION['superadmin_username'] = $username;
        }
header('Location: dashboard.php');
        exit;
    }
    
    if ($username || $password) {
        $_SESSION['login_error'] = 'Invalid username or password.';
        header('Location: dashboard.php?error=login_failed');
        exit;
    }
}

if ($path === '/dashboard/logout' && $request_method === 'POST') {
    unset($_SESSION['dashboard_admin']);
    unset($_SESSION['dashboard_username']);
    unset($_SESSION['superadmin_access']);
    unset($_SESSION['superadmin_username']);
    header('Location: dashboard.php');
    exit;
}

if ($path === '/dashboard.php' && $request_method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
    unset($_SESSION['dashboard_admin']);
    unset($_SESSION['dashboard_username']);
    unset($_SESSION['superadmin_access']);
    unset($_SESSION['superadmin_username']);
    header('Location: dashboard.php');
    exit;
}

if ($path === '/admin-management/login' && $request_method === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_MGMT_USERNAME && $password === ADMIN_MGMT_PASSWORD) {
        $_SESSION['superadmin_access'] = true;
        $_SESSION['superadmin_username'] = $username;
        $_SESSION['dashboard_admin'] = true;
        $_SESSION['dashboard_username'] = $username;
        header('Location: admin-management.html');
        exit;
    }
    
    $_SESSION['login_error'] = 'Invalid super-admin username or password.';
    header('Location: admin-management.html?error=login_failed');
    exit;
}

if ($path === '/admin-management/logout' && $request_method === 'POST') {
    unset($_SESSION['superadmin_access']);
    unset($_SESSION['superadmin_username']);
    unset($_SESSION['dashboard_admin']);
    unset($_SESSION['dashboard_username']);
    header('Location: admin-management.html');
    exit;
}

if ($path === '/admin-management/users/add' && $request_method === 'POST') {
    superadminRequired();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$username || !$password) {
        $_SESSION['action_error'] = 'Username and password are required.';
        header('Location: admin-management.html?error=action_failed');
        exit;
    }
    
    try {
        add_user($username, $password);
    } catch (Exception $e) {
        $_SESSION['action_error'] = 'That username already exists.';
        header('Location: admin-management.html?error=action_failed');
        exit;
    }
    
    header('Location: admin-management.html');
    exit;
}

if (preg_match('#^/admin-management/users/(\d+)/delete$#', $path, $m) && $request_method === 'POST') {
    superadminRequired();
    delete_user($m[1]);
    header('Location: admin-management.html');
    exit;
}

$static_routes = [
    '/' => 'index.html',
    '/index.html' => 'index.html',
    '/index.php' => 'index.html',
    '/aichat.html' => 'aichat.html',
    '/career.html' => 'career.html',
    '/clients.html' => 'clients.html',
    '/enquiry.html' => 'enquiry.html',
    '/grampanchayat.html' => 'grampanchayat.html',
    '/orderform.html' => 'orderform.html',
    '/qrvideo.html' => 'qrvideo.html',
    '/school-management-system.html' => 'school-management-system.html',
    '/robots.txt' => 'robots.txt',
    '/sitemap.xml' => 'sitemap.xml',
    '/dashboard.html' => 'dashboard.php',
    '/dashboard.php' => 'dashboard.php',
];

if (isset($static_routes[$path])) {
    $file = $static_routes[$path];
    if (file_exists($file)) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            include $file;
            exit;
        }
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mime_types = [
            'html' => 'text/html; charset=UTF-8',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain; charset=UTF-8',
            'xml' => 'application/xml; charset=UTF-8'
        ];
        $mime = $mime_types[$ext] ?? 'text/html';
        header('Content-Type: ' . $mime);
        readfile($file);
        exit;
    }
}

if (strpos($path, '/partials/') === 0) {
    $file = ltrim($path, '/');
    if (file_exists($file)) {
        header('Content-Type: text/html');
        readfile($file);
        exit;
    }
}

if (strpos($path, '/assets/') === 0) {
    $file = ltrim($path, '/');
    if (file_exists($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mime_types = [
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf'
        ];
        $mime = $mime_types[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        readfile($file);
        exit;
    }
}

http_response_code(404);
echo '404 Not Found';