<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    switch ($action) {
        case 'login':
            handleLogin();
            break;
        case 'logout':
            handleLogout();
            break;
        case 'check_session':
            checkSession();
            break;
        default:
            jsonResponse(false, 'Invalid action');
    }
}

function handleLogin() {
    $conn = connectDB();
    
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        jsonResponse(false, 'Please fill in all fields');
    }
    
    $stmt = $conn->prepare("SELECT id, username, email, password, full_name, role, status FROM admins WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        jsonResponse(false, 'Invalid credentials');
    }
    
    $admin = $result->fetch_assoc();
    
    if ($admin['status'] === 'blocked') {
        jsonResponse(false, 'Your account has been blocked');
    }
    
    if (!password_verify($password, $admin['password'])) {
        jsonResponse(false, 'Invalid credentials');
    }
    
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_name'] = $admin['full_name'];
    $_SESSION['admin_role'] = $admin['role'];
    
    $updateStmt = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
    $updateStmt->bind_param("i", $admin['id']);
    $updateStmt->execute();
    $updateStmt->close();
    
    logActivity($conn, $admin['id'], 'login', 'auth');
    
    jsonResponse(true, 'Login successful', [
        'id' => $admin['id'],
        'name' => $admin['full_name'],
        'role' => $admin['role']
    ]);
}

function handleLogout() {
    if (isset($_SESSION['admin_id'])) {
        $conn = connectDB();
        logActivity($conn, $_SESSION['admin_id'], 'logout', 'auth');
        $conn->close();
    }
    
    session_destroy();
    jsonResponse(true, 'Logged out successfully');
}

function checkSession() {
    if (isLoggedIn()) {
        jsonResponse(true, 'Session active', [
            'id' => $_SESSION['admin_id'],
            'name' => $_SESSION['admin_name'],
            'role' => $_SESSION['admin_role']
        ]);
    } else {
        jsonResponse(false, 'No active session');
    }
}
