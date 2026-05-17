<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function response($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_logged_in']);
}

function requireAuth() {
    if (!isLoggedIn()) {
        response(["error" => "Unauthorized", "message" => "Please login to continue"], 401);
    }
}

function getCurrentAdmin() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['admin_id'],
            'name' => $_SESSION['admin_name'],
            'email' => $_SESSION['admin_email'],
            'role' => $_SESSION['admin_role']
        ];
    }
    return null;
}

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateOrderNumber() {
    return 'ORD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function generateTrackingNumber() {
    return 'MG' . strtoupper(substr(md5(uniqid()), 0, 12));
}

function formatCurrency($amount) {
    return 'Rs ' . number_format((float)$amount, 2);
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M d, Y H:i', strtotime($datetime));
}

function getRelativeTime($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return formatDate($datetime);
}

function uploadImage($file, $folder = '') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => 'Invalid file type'];
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['error' => 'File size too large (max 5MB)'];
    }
    
    $relativeFolder = 'uploads/assets/images/';
    if (empty($folder)) {
        $basePath = dirname(dirname(__FILE__));
        $folder = $basePath . '/' . $relativeFolder;
    }
    
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $folder . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['path' => $relativeFolder . $filename];
    }
    
    return ['error' => 'Failed to upload file'];
}

function calculateTax($subtotal, $taxRate = 0.08) {
    return round($subtotal * $taxRate, 2);
}

function paginate($page = 1, $perPage = 10) {
    $page = max(1, (int)$page);
    $perPage = max(1, min(100, (int)$perPage));
    $offset = ($page - 1) * $perPage;
    
    return [
        'offset' => $offset,
        'limit' => $perPage,
        'page' => $page,
        'per_page' => $perPage
    ];
}

function getStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'processing' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger',
        'paid' => 'success',
        'failed' => 'danger',
        'refunded' => 'warning',
        'scheduled' => 'info',
        'in_transit' => 'primary',
        'blocked' => 'danger',
        'active' => 'success'
    ];
    
    return $colors[$status] ?? 'default';
}

function logActivity($adminId, $action, $details = null) {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("INSERT INTO activity_logs (admin_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$adminId, $action, $details, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
}
?>
