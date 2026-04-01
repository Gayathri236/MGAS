<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'microgreens_admin');

define('SITE_NAME', 'Microgreens Admin');
define('SITE_URL', 'http://localhost/admin');

define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed']));
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function jsonResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized access', null, 401);
    }
}

function generateOrderNumber() {
    return 'ORD-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function logActivity($conn, $adminId, $action, $module, $recordId = null, $details = null) {
    $stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, action, module, record_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt->bind_param("ississ", $adminId, $action, $module, $recordId, $details, $ip);
    $stmt->execute();
    $stmt->close();
}

function uploadImage($file, $prefix = 'img') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return null;
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!in_array($mimeType, ALLOWED_TYPES)) {
        return null;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $extension;
    $destination = UPLOAD_PATH . $filename;
    
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    
    return null;
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M d, Y h:i A', strtotime($datetime));
}

function getStatusColor($status) {
    $colors = [
        'pending' => '#FFC107',
        'confirmed' => '#17A2B8',
        'processing' => '#6610F2',
        'shipped' => '#007BFF',
        'delivered' => '#28A745',
        'cancelled' => '#DC3545',
        'refunded' => '#6C757D',
        'paid' => '#28A745',
        'failed' => '#DC3545',
        'active' => '#28A745',
        'inactive' => '#6C757D',
        'blocked' => '#DC3545'
    ];
    return $colors[$status] ?? '#6C757D';
}
