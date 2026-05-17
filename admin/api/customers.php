<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../config/functions.php';

requireAuth();

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'list' || empty($action)) {
            $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
            $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
            
            // Ensure page is at least 1
            $page = max(1, $page);
            
            $pagination = paginate($page, $perPage);
            
            $where = "WHERE 1=1";
            $params = [];
            
            if (!empty($search)) {
                $where .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR city LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if ($status === 'blocked') {
                $where .= " AND is_blocked = 1";
            } elseif ($status === 'active') {
                $where .= " AND is_blocked = 0";
            }
            
            try {
                $countStmt = $db->prepare("SELECT COUNT(*) as total FROM customers $where");
                $countStmt->execute($params);
                $total = (int)$countStmt->fetch()['total'];
                
                $stmt = $db->prepare("
                    SELECT id, name, email, phone, address, city, postal_code, 
                           total_spent, is_blocked, created_at, updated_at 
                    FROM customers 
                    $where 
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?
                ");
                $stmt->execute(array_merge($params, [$pagination['limit'], $pagination['offset']]));
                $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                response([
                    'success' => true,
                    'customers' => $customers,
                    'pagination' => [
                        'total' => $total,
                        'page' => $pagination['page'],
                        'per_page' => $pagination['per_page'],
                        'total_pages' => ceil($total / $pagination['per_page'])
                    ]
                ]);
            } catch (PDOException $e) {
                response(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        } elseif ($action === 'get' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            
            try {
                $stmt = $db->prepare("
                    SELECT id, name, email, phone, address, city, postal_code, 
                           total_spent, is_blocked, created_at, updated_at 
                    FROM customers WHERE id = ?
                ");
                $stmt->execute([$id]);
                $customer = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($customer) {
                    // Get recent orders for this customer
                    $ordersStmt = $db->prepare("
                        SELECT * FROM orders 
                        WHERE customer_id = ? 
                        ORDER BY created_at DESC 
                        LIMIT 10
                    ");
                    $ordersStmt->execute([$id]);
                    $orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    response([
                        'success' => true,
                        'customer' => $customer,
                        'orders' => $orders
                    ]);
                } else {
                    response(['success' => false, 'error' => 'Customer not found'], 404);
                }
            } catch (PDOException $e) {
                response(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        } elseif ($action === 'stats') {
            try {
                $stmt = $db->query("SELECT COUNT(*) as total FROM customers");
                $total = (int)$stmt->fetch()['total'];
                
                $stmt = $db->query("SELECT COUNT(*) as blocked FROM customers WHERE is_blocked = 1");
                $blocked = (int)$stmt->fetch()['blocked'];
                
                $stmt = $db->query("SELECT COALESCE(AVG(total_spent), 0) as avg_spent FROM customers");
                $avgSpent = (float)$stmt->fetch()['avg_spent'];
                
                $stmt = $db->query("SELECT COALESCE(SUM(total_spent), 0) as total_revenue FROM customers");
                $totalRevenue = (float)$stmt->fetch()['total_revenue'];
                
                response([
                    'success' => true,
                    'stats' => [
                        'total' => $total,
                        'blocked' => $blocked,
                        'active' => $total - $blocked,
                        'avg_spent' => round($avgSpent, 2),
                        'total_revenue' => round($totalRevenue, 2)
                    ]
                ]);
            } catch (PDOException $e) {
                response(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        } else {
            response(['error' => 'Invalid action'], 400);
        }
        break;
        
    case 'POST':
        if ($action === 'add') {
            $jsonInput = file_get_contents("php://input");
            $data = json_decode($jsonInput, true);
            
            if (!$data) {
                response(['error' => 'Invalid JSON data'], 400);
            }
            
            // Validate required fields
            if (empty($data['name']) || empty($data['email'])) {
                response(['error' => 'Name and email are required'], 400);
            }
            
            $name = sanitize($data['name']);
            $email = sanitize($data['email']);
            $phone = sanitize($data['phone'] ?? '');
            $address = sanitize($data['address'] ?? '');
            $city = sanitize($data['city'] ?? '');
            $postal_code = sanitize($data['postal_code'] ?? '');
            // Generate a random password or set a default one
            $password = password_hash('customer123', PASSWORD_DEFAULT);
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                response(['error' => 'Invalid email format'], 400);
            }
            
            try {
                // Check if email already exists
                $check = $db->prepare("SELECT id FROM customers WHERE email = ?");
                $check->execute([$email]);
                
                if ($check->fetch()) {
                    response(['success' => false, 'error' => 'Email already exists'], 400);
                }
                
                $stmt = $db->prepare("
                    INSERT INTO customers 
                    (name, email, phone, address, city, postal_code, password, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                
                if ($stmt->execute([$name, $email, $phone, $address, $city, $postal_code, $password])) {
                    $newId = $db->lastInsertId();
                    response([
                        'success' => true, 
                        'message' => 'Customer added successfully',
                        'id' => $newId
                    ]);
                } else {
                    response(['success' => false, 'error' => 'Failed to add customer'], 500);
                }
            } catch (PDOException $e) {
                response(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        } else {
            response(['error' => 'Invalid action'], 400);
        }
        break;

    case 'PUT':
        if ($action === 'update' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $jsonInput = file_get_contents('php://input');
            $putData = json_decode($jsonInput, true);
            
            if (!$putData) {
                response(['error' => 'Invalid JSON data'], 400);
            }
            
            try {
                // Get existing customer data
                $stmt = $db->prepare("
                    SELECT id, name, email, phone, address, city, postal_code, 
                           total_spent, is_blocked, created_at, updated_at 
                    FROM customers WHERE id = ?
                ");
                $stmt->execute([$id]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$existing) {
                    response(['success' => false, 'error' => 'Customer not found'], 404);
                }
                
                $name = sanitize($putData['name'] ?? $existing['name']);
                $email = sanitize($putData['email'] ?? $existing['email']);
                $phone = sanitize($putData['phone'] ?? $existing['phone']);
                $address = sanitize($putData['address'] ?? $existing['address']);
                $city = sanitize($putData['city'] ?? $existing['city']);
                $postal_code = sanitize($putData['postal_code'] ?? $existing['postal_code']);
                
                // Validate email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    response(['error' => 'Invalid email format'], 400);
                }
                
                // Check if email already exists for another customer
                if ($email !== $existing['email']) {
                    $checkStmt = $db->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
                    $checkStmt->execute([$email, $id]);
                    if ($checkStmt->fetch()) {
                        response(['success' => false, 'error' => 'Email already exists for another customer'], 400);
                    }
                }
                
                $updateStmt = $db->prepare("
                    UPDATE customers 
                    SET name = ?, email = ?, phone = ?, address = ?, city = ?, postal_code = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                if ($updateStmt->execute([$name, $email, $phone, $address, $city, $postal_code, $id])) {
                    response([
                        'success' => true, 
                        'message' => 'Customer updated successfully'
                    ]);
                } else {
                    response(['success' => false, 'error' => 'Failed to update customer'], 500);
                }
            } catch (PDOException $e) {
                response(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        } elseif ($action === 'block' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $blocked = isset($_GET['blocked']) ? (int)$_GET['blocked'] : 1;
            
            try {
                // Check if customer exists
                $checkStmt = $db->prepare("SELECT id FROM customers WHERE id = ?");
                $checkStmt->execute([$id]);
                
                if (!$checkStmt->fetch()) {
                    response(['success' => false, 'error' => 'Customer not found'], 404);
                }
                
                $stmt = $db->prepare("UPDATE customers SET is_blocked = ?, updated_at = NOW() WHERE id = ?");
                if ($stmt->execute([$blocked, $id])) {
                    $message = $blocked ? 'Customer blocked successfully' : 'Customer unblocked successfully';
                    response(['success' => true, 'message' => $message]);
                } else {
                    response(['success' => false, 'error' => 'Failed to update customer status'], 500);
                }
            } catch (PDOException $e) {
                response(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        } else {
            response(['error' => 'Invalid action'], 400);
        }
        break;
        
    case 'DELETE':
        if ($action === 'delete' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            
            try {
                // Check if customer exists
                $checkStmt = $db->prepare("SELECT id FROM customers WHERE id = ?");
                $checkStmt->execute([$id]);
                
                if (!$checkStmt->fetch()) {
                    response(['success' => false, 'error' => 'Customer not found'], 404);
                }
                
                // Check if customer has orders
                $orderCheck = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE customer_id = ?");
                $orderCheck->execute([$id]);
                $orderCount = (int)$orderCheck->fetch()['count'];
                
                if ($orderCount > 0) {
                    response([
                        'success' => false, 
                        'error' => 'Cannot delete customer with existing orders. Consider blocking instead.'
                    ], 400);
                }
                
                $stmt = $db->prepare("DELETE FROM customers WHERE id = ?");
                if ($stmt->execute([$id])) {
                    response(['success' => true, 'message' => 'Customer deleted successfully']);
                } else {
                    response(['success' => false, 'error' => 'Failed to delete customer'], 500);
                }
            } catch (PDOException $e) {
                response(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        } else {
            response(['error' => 'Invalid action'], 400);
        }
        break;
        
    default:
        response(['error' => 'Invalid request method'], 405);
        break;
}
?>