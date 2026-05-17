<?php


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../config/functions.php';

$database = new Database();
$db = $database->getConnection();

// Request method එක සහ Action එක ලබා ගැනීම
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// 1. POST රික්වෙස්ට් සඳහා (Login & Logout)
if ($method === 'POST') {
    switch ($action) {
        case 'login':
            $data = json_decode(file_get_contents('php://input'), true);
            $email = sanitize($data['email'] ?? '');
            $password = $data['password'] ?? '';

            if (empty($email) || empty($password)) {
                response(['error' => 'Email and password are required'], 400);
            }

            $stmt = $db->prepare("SELECT * FROM admins WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_logged_in'] = true;

                $updateStmt = $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$admin['id']]);

                response([
                    'success' => true,
                    'message' => 'Login successful',
                    'admin' => [
                        'id' => $admin['id'],
                        'name' => $admin['name'],
                        'email' => $admin['email'],
                        'role' => $admin['role']
                    ]
                ]);
            } else {
                response(['error' => 'Invalid email or password'], 401);
            }
            break;

        case 'logout':
            session_destroy();
            response(['success' => true, 'message' => 'Logged out successfully']);
            break;
            
        default:
            response(['error' => 'Invalid POST action'], 400);
    }
} 

else if ($method === 'GET') {
    if ($action === 'check') {
        if (isLoggedIn()) {
            response([
                'logged_in' => true,
                'admin' => getCurrentAdmin()
            ]);
        } else {
            response(['logged_in' => false]);
        }
    } else {
        response(['error' => 'Invalid GET action'], 400);
    }
}


response(['error' => 'Method not allowed'], 405);
?>